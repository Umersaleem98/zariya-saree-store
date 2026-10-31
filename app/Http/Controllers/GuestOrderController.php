<?php

namespace App\Http\Controllers;

use App\Support\StoreCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class GuestOrderController extends Controller
{
    public function place(Request $r, array $payments)
    {
        $r->merge(['email' => strtolower(trim((string) $r->input('email')))]);
        $v = $r->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|max:190',
            'phone' => ['required', 'regex:/^\+?[\d\s-]{10,20}$/'], 'address' => 'required|string|max:500',
            'city' => 'required|string|max:100', 'postal_code' => ['required', 'regex:/^[A-Za-z0-9 -]{3,15}$/'],
            'payment_method' => 'required|in:cod,bank', 'checkout_token' => 'required|uuid',
            'items' => 'required|array|min:1|max:50', 'items.*.id' => 'required|integer|min:1',
            'items.*.variant_id' => 'required|integer|min:1', 'items.*.quantity' => 'required|integer|min:1|max:20',
        ]);
        abort_unless($payments[$v['payment_method'].'_enabled'], 400, 'This payment option is currently disabled.');
        $lines = [];
        foreach ($v['items'] as $i) {
            $key = $i['variant_id'];
            if (isset($lines[$key]) && $lines[$key]['id'] !== $i['id']) abort(422, 'Invalid variation.');
            $lines[$key] = ['id' => $i['id'], 'quantity' => ($lines[$key]['quantity'] ?? 0) + $i['quantity']];
            abort_if($lines[$key]['quantity'] > 20, 422, 'Maximum 20 pieces per variation.');
        }
        ksort($lines);
        $hash = hash('sha256', json_encode([collect($v)->except(['items', 'checkout_token'])->all(), $lines]));
        $result = DB::transaction(function () use ($v, $lines, $hash) {
            // Lock product rows in a consistent order to serialize stock checks and retries.
            $products = DB::table('products')->whereIn('id', collect($lines)->pluck('id')->unique()->sort()->values())->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $old = DB::table('orders')->where('checkout_token', $v['checkout_token'])->first();
            if ($old) {
                abort_unless(hash_equals($old->request_hash, $hash), 409, 'Checkout details changed. Please start a new checkout.');
                return ['reference' => $old->reference, 'total' => (float) $old->total, 'payment_method' => $old->payment_method, 'new' => false];
            }
            $items = []; $cents = 0;
            foreach ($lines as $variantId => $line) {
                $p = $products[$line['id']] ?? null;
                $variant = DB::table('product_variants')->where('id', $variantId)->lockForUpdate()->first();
                abort_unless($p && $p->active && $variant && $variant->product_id === $p->id && $variant->enabled, 409, 'This product option is unavailable.');
                abort_if($variant->stock < $line['quantity'], 409, 'Not enough stock. Please update your shopping bag.');
                $price = StoreCatalog::price($variant);
                $cents += (int) round($price * 100) * $line['quantity'];
                $items[] = ['product_id' => $p->id, 'variant_id' => $variant->id, 'variant' => $variant->type, 'name' => $p->name, 'price' => $price, 'quantity' => $line['quantity']];
            }
            $shipping = $cents >= 1500000 ? 0 : 250;
            $reference = 'RM-'.strtoupper(bin2hex(random_bytes(16)));
            $id = DB::table('orders')->insertGetId([...collect($v)->except('items')->all(), 'user_id' => null, 'request_hash' => $hash, 'reference' => $reference, 'total' => $cents / 100 + $shipping, 'shipping' => $shipping]);
            foreach ($items as $item) {
                DB::table('order_items')->insert([...$item, 'order_id' => $id]);
                DB::table('product_variants')->where('id', $item['variant_id'])->decrement('stock', $item['quantity']);
            }
            foreach ($products as $p) StoreCatalog::syncStock($p->id);
            return ['reference' => $reference, 'total' => $cents / 100 + $shipping, 'payment_method' => $v['payment_method'], 'new' => true];
        }, 3);
        if ($result['new'] && ! in_array(config('mail.default'), ['log', 'array'])) {
            try {
                Mail::raw("Thank you for choosing Rang Mahal.\nTracking ID: {$result['reference']}\nTotal: PKR {$result['total']}\nTrack your order at ".url('/?tracking='.$result['reference'])."\nPayment: ".$result['payment_method'], fn ($m) => $m->to($v['email'])->subject('Your Rang Mahal order — '.$result['reference']));
            } catch (\Throwable $e) { report($e); }
        }
        unset($result['new']);
        return response()->json($result, 201);
    }

    public function track(Request $r)
    {
        $key = 'tracking:'.$r->ip();
        abort_if(RateLimiter::tooManyAttempts($key, 20), 429, 'Too many tracking requests. Please try again shortly.');
        RateLimiter::hit($key, 60);
        $r->validate(['reference' => 'required|string|max:50']);
        $query = DB::table('orders')->where('reference', strtoupper(trim($r->reference)));
        // Older references were issued with email verification; preserve that protection.
        if (! str_starts_with(strtoupper(trim($r->reference)), 'RM-')) {
            $r->validate(['email' => 'required|email']);
            $query->where('email', strtolower(trim($r->email)));
        }
        $o = $query->first(['id', 'reference', 'status', 'total', 'shipping', 'city', 'created_at', 'payment_method']);
        abort_unless($o, 404, 'No order matches that tracking ID.');
        $o->items = DB::table('order_items')->where('order_id', $o->id)->get(['name', 'variant', 'quantity', 'price']);
        unset($o->id);
        return response()->json(['order' => $o]);
    }
}

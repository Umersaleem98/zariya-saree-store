<?php

namespace App\Http\Controllers;

use App\Models\ShopUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;

class StoreApiController extends Controller
{
    private function payments()
    {
        $s = DB::table('store_settings')->find(1);

        return ['cod_enabled' => (bool) ($s->cod_enabled ?? true), 'bank_enabled' => (bool) ($s->bank_enabled ?? false), 'card_enabled' => false, 'card_ready' => false];
    }

    private function identity()
    {
        $u = Auth::user();

        return $u ? ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role] : null;
    }

    public function handle(Request $r, string $path)
    {
        $method = $r->method();
        if ($path === 'catalog' && $method === 'GET') {
            return response()->json(['products' => DB::table('products')->where('active', 1)->orderBy('id')->get(), 'categories' => DB::table('categories')->pluck('name'), 'brands' => DB::table('brands')->pluck('name'), 'demo' => false, 'payments' => $this->payments()]);
        }
        if ($path === 'auth' && $method === 'GET') {
            return response()->json(['user' => $this->identity(), 'google_enabled' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret'))]);
        }
        if ($path === 'settings' && $method === 'GET') {
            return response()->json(['payments' => $this->payments(), 'google_enabled' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret'))]);
        }
        if (in_array($path, ['auth/login', 'auth/register']) && $method === 'POST') {
            $r->merge(['email' => strtolower(trim((string) $r->input('email')))]);
            $r->validate(['email' => 'required|email|max:190', 'password' => 'required|string|max:128']);
            $key = 'login:'.hash('sha256', $r->email.'|'.$r->ip());
            if (RateLimiter::tooManyAttempts($key, 10)) {
                return response()->json(['error' => 'Too many attempts. Try again later.'], 429);
            }RateLimiter::hit($key, 900);
            if ($path === 'auth/register') {
                $r->validate(['name' => 'required|string|max:100', 'password' => 'min:12', 'email' => 'unique:users,email']);
                $id = DB::table('users')->insertGetId(['name' => trim($r->name), 'email' => $r->email, 'password_hash' => Hash::make($r->password), 'role' => 'customer']);
                $u = ShopUser::find($id);
            } else {
                $u = ShopUser::where('email', $r->email)->first();
                if (! $u || ! str_starts_with($u->password_hash, '$') || ! Hash::check($r->password, $u->password_hash)) {
                    return response()->json(['error' => 'Email or password is incorrect.'], 401);
                }
            }
            Auth::login($u);
            $r->session()->regenerate();
            RateLimiter::clear($key);

            return response()->json(['user' => $this->identity(), 'csrf_token' => csrf_token()]);
        }
        if ($path === 'auth/logout' && $method === 'POST') {
            Auth::logout();
            $r->session()->invalidate();
            $r->session()->regenerateToken();

            return response()->json(['ok' => true, 'csrf_token' => csrf_token()]);
        }
        if ($path === 'orders' && ! Auth::check()) {
            return response()->json(['error' => 'Sign in or create an account before placing your order.', 'code' => 'AUTH_REQUIRED'], 401);
        }
        if ($path === 'orders' && $method === 'GET') {
            return response()->json(['orders' => DB::table('orders')->where('user_id', Auth::id())->orderByDesc('id')->get()]);
        }
        if ($path === 'orders' && $method === 'POST') {
            $v = $r->validate(['name' => 'required|string|max:100', 'email' => 'required|email|max:190', 'phone' => ['required', 'regex:/^\+?[\d\s-]{10,20}$/'], 'address' => 'required|string|max:500', 'city' => 'required|string|max:100', 'payment_method' => ['required', Rule::in(['cod', 'bank'])], 'items' => 'required|array|min:1|max:50', 'items.*.id' => 'required|integer|min:1', 'items.*.quantity' => 'required|integer|min:1|max:20']);
            if (! $this->payments()[$v['payment_method'].'_enabled']) {
                return response()->json(['error' => 'This payment option is currently disabled.'], 400);
            }
            $items = [];
            foreach ($v['items'] as $i) {
                $items[$i['id']] = ($items[$i['id']] ?? 0) + $i['quantity'];
                if ($items[$i['id']] > 20) {
                    abort(422, 'Maximum 20 of each saree per order.');
                }
            }ksort($items);

            return DB::transaction(function () use ($v, $items) {
                $subtotal = 0;
                $lines = [];
                foreach ($items as $id => $qty) {
                    $p = DB::table('products')->where('id', $id)->where('active', 1)->lockForUpdate()->first();
                    if (! $p || $p->stock < $qty) {
                        abort(409, 'A saree has insufficient stock. Please update your bag.');
                    }$subtotal += (int) round($p->price * 100) * $qty;
                    $lines[] = ['product_id' => $id, 'name' => $p->name, 'quantity' => $qty, 'price' => $p->price];
                }$shipping = $subtotal >= 1500000 ? 0 : 250;
                $total = $subtotal / 100 + $shipping;
                $ref = 'ZR-'.strtoupper(bin2hex(random_bytes(8)));
                $id = DB::table('orders')->insertGetId([...collect($v)->except('items')->all(), 'user_id' => Auth::id(), 'reference' => $ref, 'total' => $total, 'shipping' => $shipping]);
                foreach ($lines as $line) {
                    DB::table('order_items')->insert([...$line, 'order_id' => $id]);
                    DB::table('products')->where('id', $line['product_id'])->decrement('stock', $line['quantity']);
                }

                return response()->json(['reference' => $ref, 'total' => $total, 'payment_method' => $v['payment_method']], 201);
            }, 3);
        }
        if ($path === 'track' && $method === 'POST') {
            $r->validate(['reference' => 'required|string|max:50', 'email' => 'required|email']);
            $o = DB::table('orders')->where('reference', strtoupper(trim($r->reference)))->where('email', strtolower(trim($r->email)))->first(['reference', 'status', 'total', 'created_at', 'payment_method']);

            return $o ? response()->json(['order' => $o]) : response()->json(['error' => 'No order matches those details.'], 404);
        }
        if (str_starts_with($path, 'admin/')) {
            if (Auth::user()?->role !== 'admin') {
                return response()->json(['error' => 'Administrator access required.'], 403);
            }
            if ($path === 'admin/data' && $method === 'GET') {
                return response()->json(['products' => DB::table('products')->orderByDesc('id')->get(), 'orders' => DB::table('orders')->orderByDesc('id')->limit(500)->get(), 'categories' => DB::table('categories')->get(), 'brands' => DB::table('brands')->get(), 'customers' => DB::table('users')->where('role', 'customer')->orderByDesc('id')->limit(500)->get(['id', 'name', 'email', 'created_at']), 'stats' => DB::table('orders')->selectRaw("COUNT(*) total_orders,COALESCE(SUM(status='Pending'),0) pending_orders,COALESCE(SUM(CASE WHEN status='Delivered' THEN total ELSE 0 END),0) revenue")->first(), 'payments' => $this->payments(), 'google_enabled' => filled(config('services.google.client_id')) && filled(config('services.google.client_secret'))]);
            }
            if ($path === 'admin/settings' && $method === 'PUT') {
                $v = $r->validate(['cod_enabled' => 'required|boolean', 'bank_enabled' => 'required|boolean', 'card_enabled' => 'required|boolean']);
                if ($v['card_enabled']) {
                    abort(422, 'Card payments require an integrated gateway.');
                }if (! $v['cod_enabled'] && ! $v['bank_enabled']) {
                    abort(422, 'Keep at least one payment option enabled.');
                }DB::table('store_settings')->where('id', 1)->update($v);

                return response()->json(['payments' => $this->payments()]);
            }
            if ($path === 'admin/products' && in_array($method, ['POST', 'PUT'])) {
                $v = $r->validate(['name' => 'required|string|max:150', 'category' => 'required|string|exists:categories,name', 'brand' => 'required|string|exists:brands,name', 'price' => 'required|numeric|min:1|max:10000000', 'compare_price' => 'nullable|numeric|min:0', 'stock' => 'required|integer|min:0|max:1000000', 'color' => 'nullable|string|max:50', 'fabric' => 'nullable|string|max:100', 'badge' => 'nullable|string|max:50', 'description' => 'nullable|string|max:10000', 'image' => ['required', 'string', 'max:500', 'regex:~^(?:/images/[a-zA-Z0-9_.-]+|https://[^\s]+)$~']]);
                if ($method === 'POST') {
                    DB::table('products')->insert($v);
                } else {
                    $r->validate(['id' => 'required|integer|exists:products,id']);
                    DB::table('products')->where('id', $r->id)->update($v);
                }

                return response()->json(['ok' => true]);
            }
            if ($path === 'admin/products' && $method === 'DELETE') {
                $r->validate(['id' => 'required|integer|exists:products,id']);
                DB::table('products')->where('id', $r->id)->update(['active' => DB::raw('NOT active')]);

                return response()->json(['ok' => true]);
            }
            if (in_array($path, ['admin/categories', 'admin/brands'])) {
                $table = explode('/', $path)[1];
                if ($method === 'POST') {
                    $v = $r->validate(['name' => "required|string|max:100|unique:$table,name"]);
                    DB::table($table)->insert($v);

                    return response()->json(['ok' => true]);
                }if ($method === 'DELETE') {
                    $r->validate(['id' => "required|integer|exists:$table,id"]);
                    $name = DB::table($table)->where('id', $r->id)->value('name');
                    if (DB::table('products')->where($table === 'brands' ? 'brand' : 'category', $name)->exists()) {
                        abort(409, 'Move associated products before deleting this entry.');
                    }DB::table($table)->where('id', $r->id)->delete();

                    return response()->json(['ok' => true]);
                }
            }
            if ($path === 'admin/order-items' && $method === 'GET') {
                $r->validate(['id' => 'required|integer|exists:orders,id']);

                return response()->json(['items' => DB::table('order_items')->where('order_id', $r->id)->get()]);
            }
            if ($path === 'admin/orders' && $method === 'PUT') {
                $v = $r->validate(['id' => 'required|integer|exists:orders,id', 'status' => ['required', Rule::in(['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'])]]);

                return DB::transaction(function () use ($v) {
                    $o = DB::table('orders')->where('id', $v['id'])->lockForUpdate()->first();
                    $allowed = ['Pending' => ['Confirmed', 'Cancelled'], 'Confirmed' => ['Shipped', 'Cancelled'], 'Shipped' => ['Delivered'], 'Delivered' => [], 'Cancelled' => []];
                    if ($o->status !== $v['status'] && ! in_array($v['status'], $allowed[$o->status])) {
                        abort(409, 'That status transition is not allowed.');
                    }if ($v['status'] === 'Cancelled' && $o->status !== 'Cancelled') {
                        foreach (DB::table('order_items')->where('order_id', $o->id)->orderBy('product_id')->get() as $i) {
                            DB::table('products')->where('id', $i->product_id)->increment('stock', $i->quantity);
                        }
                    }DB::table('orders')->where('id', $o->id)->update(['status' => $v['status']]);

                    return response()->json(['ok' => true]);
                }, 3);
            }
        }

        return response()->json(['error' => 'Endpoint not found.'], 404);
    }
}

<?php
namespace App\Http\Controllers;
use App\Support\BusinessAnalytics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerInsightsController extends Controller
{
    public function handle(Request $r)
    {
        if ($r->isMethod('PUT')) {
            $v = $r->validate(['email' => 'required|email|max:190', 'category' => 'required|string|max:50', 'status' => 'required|in:Active,Inactive', 'notes' => 'nullable|string|max:5000']);
            abort_unless(DB::table('orders')->where('email', $v['email'])->exists() || DB::table('users')->where('email', $v['email'])->where('role', 'customer')->exists(), 404);
            DB::table('customer_profiles')->updateOrInsert(['email' => $v['email']], $v);
            return response()->json(['ok' => true]);
        }
        $f = BusinessAnalytics::validate($r); $rows = BusinessAnalytics::customerRows($f);
        if ($r->filled('email')) {
            $profile = $rows->firstWhere('email', strtolower($r->email)); abort_unless($profile, 404);
            return response()->json(['customer' => $profile, 'orders' => DB::table('orders')->where('email', $profile['email'])->orderByDesc('created_at')->get()->map(fn ($o) => collect($o)->except(['checkout_token', 'request_hash'])->all())]);
        }
        $group = fn ($key) => $rows->groupBy($key)->map(fn ($g, $label) => ['label' => (string) $label, 'value' => $g->count()])->values();
        $top = $rows->sortByDesc('spending')->take(10)->map(fn ($x) => ['label' => $x['name'] ?: $x['email'], 'value' => $x['spending']])->values();
        $totalOrders = $rows->sum('order_count'); $revenue = $rows->sum('spending'); $page = (int) ($f['page'] ?? 1);
        return response()->json(['customers' => $rows->sortByDesc('last_order')->slice(($page - 1) * 20, 20)->values(), 'total' => $rows->count(), 'page' => $page, 'pages' => max(1, (int) ceil($rows->count() / 20)),
            'summary' => ['total' => $rows->count(), 'new' => $rows->where('order_count', '<=', 1)->count(), 'returning' => $rows->where('order_count', '>', 1)->count(), 'completed' => $rows->where('completed', '>', 0)->count(), 'pending' => $rows->where('pending', '>', 0)->count(), 'revenue' => round($revenue, 2), 'aov' => $totalOrders ? round($rows->sum('order_value') / $totalOrders, 2) : null],
            'charts' => ['cities' => $group('city'), 'new_customers' => $rows->filter(fn ($x) => $x['created_at'])->groupBy(fn ($x) => substr($x['created_at'], 0, 7))->map(fn ($g, $label) => ['label' => $label, 'value' => $g->count()])->sortBy('label')->values(), 'returning' => [['label' => 'New / one order', 'value' => $rows->where('order_count', '<=', 1)->count()], ['label' => 'Returning', 'value' => $rows->where('order_count', '>', 1)->count()]], 'spending' => $rows->groupBy(fn ($x) => $x['spending'] == 0 ? 'No recorded payment' : ($x['spending'] <= 10000 ? '1–10,000' : ($x['spending'] <= 50000 ? '10,001–50,000' : 'Above 50,000')))->map(fn ($g, $label) => ['label' => $label, 'value' => $g->count()])->values(), 'orders' => $rows->groupBy('order_count')->map(fn ($g, $label) => ['label' => $label.' orders', 'value' => $g->count()])->values(), 'top' => $top],
        ]);
    }
}

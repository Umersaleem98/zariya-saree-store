<?php
namespace App\Http\Controllers;

use App\Support\BusinessSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BusinessController extends Controller
{
    public function settings(Request $r)
    {
        if ($r->isMethod('GET')) return response()->json(['business' => BusinessSettings::get()]);
        $rules = [];
        foreach (BusinessSettings::defaults() as $key => $value) {
            $rules[$key] = is_bool($value) ? 'sometimes|boolean' : (is_int($value) ? 'sometimes|numeric|decimal:0,2|min:0|max:10000000' : 'sometimes|nullable|string|max:10000');
        }
        $rules['order_prefix'] = ['sometimes', 'required', 'regex:/^[A-Z]{2,6}$/'];
        $rules['order_category_code'] = ['sometimes', 'required', 'regex:/^[A-Z]{2,6}$/'];
        $rules['brand_name'] = 'sometimes|required|string|max:100';
        $rules['tagline'] = 'sometimes|required|string|max:200';
        $v = $r->validate($rules);
        return DB::transaction(function () use ($v) {
            $row = DB::table('business_settings')->where('id', 1)->lockForUpdate()->first();
            $merged = array_replace(BusinessSettings::defaults(), json_decode($row->values, true), $v);
            DB::table('business_settings')->where('id', 1)->update(['values' => json_encode($merged)]);
            return response()->json(['business' => $merged]);
        });
    }

    public function shipment(Request $r)
    {
        $v = $r->validate([
            'id' => 'required|integer|exists:orders,id', 'courier_name' => 'nullable|string|max:100', 'courier_tracking_number' => 'nullable|string|max:150',
            'courier_tracking_url' => ['nullable', 'url:https', 'max:500'], 'estimated_delivery_date' => 'nullable|date',
            'payment_status' => 'required|in:Unrecorded,Pending,Paid,Partially Paid,Partially Refunded,Refunded',
            'amount_paid' => 'nullable|numeric|decimal:0,2|min:0|max:100000000', 'refund_amount' => 'required|numeric|decimal:0,2|min:0|max:100000000',
        ]);
        return DB::transaction(function () use ($v) {
            $o = DB::table('orders')->where('id', $v['id'])->lockForUpdate()->first();
            $paid = $v['amount_paid'] ?? null; $refund = $v['refund_amount'];
            abort_if($paid !== null && $paid > $o->total, 422, 'Collected payment cannot exceed the order total.');
            abort_if($refund > ($paid ?? 0), 422, 'Refund cannot exceed the recorded payment.');
            abort_if($v['payment_status'] === 'Paid' && ((float) $paid !== (float) $o->total || $refund > 0), 422, 'Paid requires the full order payment and no refund.');
            abort_if($v['payment_status'] === 'Partially Paid' && (! $paid || $paid >= $o->total || $refund > 0), 422, 'Enter a partial payment below the total.');
            abort_if($v['payment_status'] === 'Refunded' && (! $paid || (float) $refund !== (float) $paid), 422, 'Enter the full recorded payment as refunded.');
            abort_if($v['payment_status'] === 'Partially Refunded' && (! $paid || ! $refund || $refund >= $paid), 422, 'Enter a refund below the recorded payment.');
            abort_if(in_array($v['payment_status'], ['Unrecorded', 'Pending']) && (($paid ?? 0) > 0 || $refund > 0), 422, 'Record the corresponding paid/refunded status for collected money.');
            $fields = collect($v)->except('id')->all();
            $fields['paid_at'] = $paid > 0 ? ($o->paid_at ?? now()) : null;
            DB::table('orders')->where('id', $o->id)->update($fields);
            return response()->json(['ok' => true]);
        });
    }
}

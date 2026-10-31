<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $t) { $t->unsignedInteger('id')->primary(); $t->json('values'); });
        DB::table('business_settings')->insert(['id' => 1, 'values' => json_encode(\App\Support\BusinessSettings::defaults())]);
        Schema::create('order_sequences', function (Blueprint $t) { $t->string('key', 80)->primary(); $t->unsignedBigInteger('value')->default(0); });
        Schema::table('products', function (Blueprint $t) {
            $t->string('sku', 80)->nullable()->unique();
            $t->boolean('stitching_enabled')->default(true);
            $t->string('stitching_stock_mode', 16)->default('shared');
            $t->decimal('stitching_charge', 12, 2)->nullable();
            foreach (['cost_price', 'stitching_cost', 'additional_cost'] as $field) $t->decimal($field, 12, 2)->nullable();
            $t->string('shipping_info', 1000)->nullable();
            $t->string('seo_title', 160)->nullable();
            $t->string('seo_description', 320)->nullable();
        });
        foreach (DB::table('products')->get() as $p) {
            $stitched = DB::table('product_variants')->where('product_id', $p->id)->where('type', 'Stitched')->first();
            DB::table('products')->where('id', $p->id)->update(['sku' => 'RM-SR-'.str_pad($p->id, 6, '0', STR_PAD_LEFT), 'stitching_stock_mode' => $stitched && $stitched->enabled ? 'separate' : 'shared']);
        }
        Schema::table('orders', function (Blueprint $t) {
            $t->string('payment_status', 32)->default('Unrecorded');
            $t->decimal('amount_paid', 12, 2)->nullable();
            $t->decimal('refund_amount', 12, 2)->default(0);
            $t->timestamp('paid_at')->nullable();
            $t->string('courier_name', 100)->nullable();
            $t->string('courier_tracking_number', 150)->nullable();
            $t->string('courier_tracking_url', 500)->nullable();
            $t->date('estimated_delivery_date')->nullable();
            $t->json('status_history')->nullable();
            $t->index(['created_at', 'city']);
        });
        Schema::table('order_items', function (Blueprint $t) {
            $t->unsignedInteger('stock_variant_id')->nullable();
            $t->decimal('stitching_charge', 12, 2)->nullable();
            $t->decimal('unit_cost', 12, 2)->nullable();
            $t->string('category_snapshot', 100)->nullable();
            $t->string('brand_snapshot', 100)->nullable();
            $t->string('sku_snapshot', 80)->nullable();
        });
        DB::table('order_items')->update(['stock_variant_id' => DB::raw('variant_id')]);
        // Never infer historical costs, payment receipts, stitching charges or event timestamps.
        Schema::table('users', fn (Blueprint $t) => $t->timestamp('last_login_at')->nullable());
        Schema::create('customer_profiles', function (Blueprint $t) {
            $t->id(); $t->string('email', 190)->unique(); $t->string('category', 50)->default('Standard');
            $t->string('status', 20)->default('Active'); $t->text('notes')->nullable();
        });
        // Replace only the store's exact former brand label, never customer/order snapshots.
        DB::table('products')->where('brand', 'Zariya Signature')->update(['brand' => 'Rang Mahal Signature']);
        $old = DB::table('brands')->where('name', 'Zariya Signature')->first();
        if ($old) {
            if (DB::table('brands')->where('name', 'Rang Mahal Signature')->exists()) DB::table('brands')->where('id', $old->id)->delete();
            else DB::table('brands')->where('id', $old->id)->update(['name' => 'Rang Mahal Signature']);
        }
    }
    public function down(): void { throw new RuntimeException('Business records must be preserved; restore only a reviewed backup.'); }
};

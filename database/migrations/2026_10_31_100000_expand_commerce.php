<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY role VARCHAR(32) NOT NULL DEFAULT 'customer'");
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('active')->default(true);
            $t->json('permissions')->nullable();
            $t->unsignedInteger('auth_version')->default(1);
        });
        DB::table('users')->where('role', 'admin')->update(['role' => 'super_admin']);
        Schema::table('products', function (Blueprint $t) {
            $t->json('images')->nullable();
            $t->boolean('featured')->default(false);
        });
        Schema::create('product_variants', function (Blueprint $t) {
            $t->increments('id');
            $t->integer('product_id');
            $t->string('type', 20);
            $t->decimal('regular_price', 12, 2);
            $t->decimal('sale_price', 12, 2)->nullable();
            $t->boolean('sale_enabled')->default(false);
            $t->unsignedInteger('stock')->default(0);
            $t->boolean('enabled')->default(true);
            $t->unique(['product_id', 'type']);
            $t->foreign('product_id')->references('id')->on('products');
        });
        foreach (DB::table('products')->get() as $p) {
            $sale = $p->compare_price > $p->price;
            DB::table('product_variants')->insert([
                ['product_id' => $p->id, 'type' => 'Unstitched', 'regular_price' => $sale ? $p->compare_price : $p->price, 'sale_price' => $sale ? $p->price : null, 'sale_enabled' => $sale, 'stock' => max(0, $p->stock), 'enabled' => true],
                ['product_id' => $p->id, 'type' => 'Stitched', 'regular_price' => $p->price, 'sale_price' => null, 'sale_enabled' => false, 'stock' => 0, 'enabled' => false],
            ]);
        }
        DB::statement('ALTER TABLE orders MODIFY status VARCHAR(32) NOT NULL DEFAULT \'Pending\'');
        Schema::table('orders', function (Blueprint $t) {
            $t->string('postal_code', 15)->nullable();
            $t->uuid('checkout_token')->nullable()->unique();
            $t->string('request_hash', 64)->nullable();
        });
        Schema::table('order_items', function (Blueprint $t) {
            $t->unsignedInteger('variant_id')->nullable();
            $t->string('variant', 20)->nullable();
            $t->foreign('variant_id')->references('id')->on('product_variants');
        });
        // Existing orders keep their snapshots; Unstitched was the previous stock pool.
        foreach (DB::table('product_variants')->where('type', 'Unstitched')->get() as $v) {
            DB::table('order_items')->where('product_id', $v->product_id)->update(['variant_id' => $v->id, 'variant' => 'Unstitched']);
        }
    }

    public function down(): void
    {
        throw new RuntimeException('Preserve commerce records. Use a reviewed backup restoration.');
    }
};

<?php

namespace Tests\Feature;

use App\Models\ShopUser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use DatabaseTransactions;

    public function test_catalog_and_admin_permissions(): void
    {
        $this->getJson('/api/catalog')->assertOk()->assertJsonPath('demo', false);
        $this->getJson('/api/admin/data')->assertForbidden();
        $this->postJson('/api/orders', [])->assertUnauthorized();
    }

    public function test_registration_and_login_never_grant_admin_from_input(): void
    {
        $email = 'qa-'.bin2hex(random_bytes(6)).'@example.test';
        $this->postJson('/api/auth/register', ['name' => 'QA Customer', 'email' => $email, 'password' => 'A very secure password!', 'role' => 'admin'])->assertOk()->assertJsonPath('user.role', 'customer');
        $this->getJson('/api/admin/data')->assertForbidden();
        $this->postJson('/api/auth/logout')->assertOk();
        $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'wrong'])->assertUnauthorized();
        $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'A very secure password!'])->assertOk();
    }

    public function test_checkout_recalculates_price_and_cancel_restores_stock_only_once(): void
    {
        $id = DB::table('users')->insertGetId(['name' => 'QA', 'email' => bin2hex(random_bytes(6)).'@example.test', 'password_hash' => Hash::make('A secure password!'), 'role' => 'customer']);
        $this->actingAs(ShopUser::find($id));
        $product = DB::table('products')->insertGetId(['name' => 'QA Product', 'category' => 'Silk Sarees', 'brand' => 'Zariya Signature', 'price' => 1234, 'stock' => 2, 'active' => 1]);
        $payload = ['name' => 'QA', 'email' => 'qa@example.test', 'phone' => '03001234567', 'address' => 'Test address', 'city' => 'Lahore', 'payment_method' => 'cod', 'items' => [['id' => $product, 'quantity' => 1], ['id' => $product, 'quantity' => 1]], 'total' => 1];
        DB::table('store_settings')->where('id', 1)->update(['cod_enabled' => 1, 'bank_enabled' => 0]);
        $this->postJson('/api/orders', [...$payload, 'payment_method' => 'bank'])->assertStatus(400);
        $response = $this->postJson('/api/orders', $payload)->assertCreated()->assertJsonPath('total', 2718);
        $order = DB::table('orders')->where('reference', $response->json('reference'))->first();
        $this->assertEquals(0, DB::table('products')->where('id', $product)->value('stock'));
        $this->postJson('/api/orders', $payload)->assertStatus(409);
        $this->getJson('/api/orders')->assertOk();
        $this->actingAs(ShopUser::where('role', 'admin')->firstOrFail());
        $this->putJson('/api/admin/orders', ['id' => $order->id, 'status' => 'Delivered'])->assertStatus(409);
        $this->putJson('/api/admin/orders', ['id' => $order->id, 'status' => 'Cancelled'])->assertOk();
        $this->putJson('/api/admin/orders', ['id' => $order->id, 'status' => 'Cancelled'])->assertOk();
        $this->assertEquals(2, DB::table('products')->where('id', $product)->value('stock'));
    }

    public function test_payment_settings_and_archive(): void
    {
        $this->actingAs(ShopUser::where('role', 'admin')->firstOrFail());
        $this->putJson('/api/admin/settings', ['cod_enabled' => true, 'bank_enabled' => false, 'card_enabled' => true])->assertStatus(422);
        $this->putJson('/api/admin/settings', ['cod_enabled' => false, 'bank_enabled' => false, 'card_enabled' => false])->assertStatus(422);
        $this->putJson('/api/admin/settings', ['cod_enabled' => true, 'bank_enabled' => false, 'card_enabled' => false])->assertOk();
        $id = DB::table('products')->insertGetId(['name' => 'QA archive', 'category' => 'Silk Sarees', 'brand' => 'Zariya Signature', 'price' => 100, 'stock' => 3]);
        $this->deleteJson('/api/admin/products', ['id' => $id])->assertOk();
        $this->assertEquals(0, DB::table('products')->where('id',$id)->value('active'));
    }
}

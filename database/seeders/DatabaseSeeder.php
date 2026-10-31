<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(file_get_contents(database_path('data/catalog.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach (['categories', 'brands'] as $table) {
            foreach ($catalog[$table] as $name) {
                DB::table($table)->insertOrIgnore(['name' => $name]);
            }
        }
        foreach ($catalog['products'] as $product) {
            DB::table('products')->insertOrIgnore($product);
        }
        foreach (DB::table('products')->get() as $p) {
            $sale = $p->compare_price > $p->price;
            foreach (['Unstitched', 'Stitched'] as $type) {
                DB::table('product_variants')->insertOrIgnore(['product_id' => $p->id, 'type' => $type, 'regular_price' => $sale ? $p->compare_price : $p->price, 'sale_price' => $sale ? $p->price : null, 'sale_enabled' => $sale, 'stock' => $type === 'Unstitched' ? $p->stock : 0, 'enabled' => $type === 'Unstitched']);
            }
        }
        $email = config('store.admin_email');
        $password = config('store.admin_password');
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password ?? '') < 12) {
            throw new \RuntimeException('Set ADMIN_EMAIL and ADMIN_PASSWORD (at least 12 characters) in .env before seeding.');
        }
        if (! DB::table('users')->where('email', $email)->exists()) {
            DB::table('users')->insert(['name' => 'Store Administrator', 'email' => $email, 'password_hash' => Hash::make($password), 'role' => 'super_admin', 'created_at' => now(), 'updated_at' => now()]);
        }
    }
}

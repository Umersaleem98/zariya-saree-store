<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class StoreCatalog
{
    public static function price($v): float
    {
        return (float) ($v->sale_enabled && $v->sale_price !== null ? $v->sale_price : $v->regular_price);
    }

    public static function products(bool $public = true)
    {
        $variants = DB::table('product_variants')->orderBy('id')->get()->groupBy('product_id');
        return DB::table('products')->when($public, fn ($q) => $q->where('active', 1))->orderBy('id')->get()->map(function ($p) use ($variants, $public) {
            $all = ($variants[$p->id] ?? collect())->map(function ($v) {
                $v->price = self::price($v);
                $v->discount = $v->price < $v->regular_price ? (int) round((1 - $v->price / $v->regular_price) * 100) : 0;
                return $v;
            });
            $enabled = $all->where('enabled', true);
            $lowest = $enabled->sortBy('price')->first();
            $p->variants = $all->values();
            $p->price = $lowest?->price ?? (float) $p->price;
            $p->compare_price = $lowest && $lowest->discount ? (float) $lowest->regular_price : 0;
            $p->discount = $lowest?->discount ?? 0;
            $p->stock = $enabled->sum('stock');
            $p->images = json_decode($p->images ?? 'null', true) ?: array_values(array_filter([$p->image]));
            return $p;
        });
    }

    public static function syncStock(int $id): void
    {
        DB::table('products')->where('id', $id)->update(['stock' => DB::table('product_variants')->where('product_id', $id)->where('enabled', true)->sum('stock')]);
    }
}

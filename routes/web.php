<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\StoreApiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'store');
Route::view('/admin', 'store');
Route::view('/track', 'store');
Route::get('/products/{id}', function (int $id) {
    $product = \Illuminate\Support\Facades\DB::table('products')->where('id', $id)->where('active', true)->first();
    abort_unless($product, 404);
    return view('store', ['seoProduct' => $product]);
})->whereNumber('id');
Route::get('/media/products/{file}', [\App\Http\Controllers\ProductMediaController::class, 'show']);
Route::match(['GET', 'POST', 'PUT', 'DELETE'], '/api/{path}', [StoreApiController::class, 'handle'])->where('path', '.*')->middleware('throttle:120,1');

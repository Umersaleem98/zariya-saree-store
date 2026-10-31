<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\StoreApiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'store');
Route::view('/admin', 'store');
Route::get('/media/products/{file}', [\App\Http\Controllers\ProductMediaController::class, 'show']);
Route::match(['GET', 'POST', 'PUT', 'DELETE'], '/api/{path}', [StoreApiController::class, 'handle'])->where('path', '.*')->middleware('throttle:120,1');

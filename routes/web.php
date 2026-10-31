<?php

use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\StoreApiController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'store');
Route::view('/admin', 'store');
Route::get('/api/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:20,1');
Route::get('/api/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:20,1');
Route::match(['GET', 'POST', 'PUT', 'DELETE'], '/api/{path}', [StoreApiController::class, 'handle'])->where('path', '.*')->middleware('throttle:120,1');

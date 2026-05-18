<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CONTROLLERS
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\AdminDashboardController;

use App\Http\Controllers\Home\AboutController;
use App\Http\Controllers\Home\BlogsController;
use App\Http\Controllers\Home\ContactController;
use App\Http\Controllers\Home\FeatureController;
use App\Http\Controllers\Home\ShopController;
use App\Http\Controllers\Home\CartController;
use App\Http\Controllers\Home\CheckoutController;

use App\Http\Controllers\Auth\AuthController;

/*
|--------------------------------------------------------------------------
| PUBLIC PAGES (GUEST + USER BOTH)
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/blogs', [BlogsController::class, 'index'])->name('blogs');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/features', [FeatureController::class, 'index'])->name('features');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (LOGIN / REGISTER)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

});

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| USER ROUTES (AUTH REQUIRED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart');
    
    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');

});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES (ROLE BASED)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('dashboard');

});
<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Home\AboutController;
use App\Http\Controllers\Home\BlogsController;
use App\Http\Controllers\Home\ContactController;
use App\Http\Controllers\Home\FeatureController;
use App\Http\Controllers\Home\ShopController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;





Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/blogs', [BlogsController::class, 'index'])->name('blogs');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/features', [FeatureController::class, 'index'])->name('features');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');





Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])
        ->name('dashboard');

});

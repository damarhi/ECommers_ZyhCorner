<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\PelangganController;

Route::get('/', [PelangganController::class, 'home'])->name('home');
Route::get('/shop', [PelangganController::class, 'shop'])->name('shop');
Route::get('/product/{id}', [PelangganController::class, 'detail'])->name('product.detail');
Route::get('/cart', [PelangganController::class, 'userChart'])->name('userChart');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [PelangganController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [PelangganController::class, 'processCheckout'])->name('checkout.post');
    Route::get('/order-success', [PelangganController::class, 'orderSuccess'])->name('order.success');
    Route::get('/dashboard', [PelangganController::class, 'dashboard'])->name('dashboard');
    Route::get('/order-history', [PelangganController::class, 'orderHistory'])->name('order.history');
    Route::get('/order-history/{id}', [PelangganController::class, 'orderDetail'])->name('order.detail');
    Route::post('/order-history/{id}/cancel', [PelangganController::class, 'cancelOrder'])->name('order.cancel');
    Route::get('/order-history/{id}/return', [PelangganController::class, 'returnOrder'])->name('order.return');
    Route::post('/order-history/{id}/return', [PelangganController::class, 'submitReturn'])->name('order.return.submit');
    Route::get('/return-detail/{id}', [PelangganController::class, 'returnDetail'])->name('return.detail');
    Route::get('/settings', [PelangganController::class, 'settings'])->name('settings');
    Route::post('/settings/account', [PelangganController::class, 'updateAccount'])->name('settings.account');
    Route::post('/settings/password', [PelangganController::class, 'updatePassword'])->name('settings.password');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google Authentication Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/verify', [AuthController::class, 'showGoogleVerifyForm'])->name('login.google.verify');
Route::post('/auth/google/verify', [AuthController::class, 'verifyGoogleOtp'])->name('login.google.verify.post');
Route::post('/auth/google/verify/resend', [AuthController::class, 'resendGoogleOtp'])->name('login.google.verify.resend');

// Registration OTP Email Verification Routes
Route::get('/register/verify', [AuthController::class, 'showRegisterVerifyForm'])->name('register.verify');
Route::post('/register/verify', [AuthController::class, 'verifyRegisterOtp'])->name('register.verify.post');
Route::post('/register/verify/resend', [AuthController::class, 'resendRegisterOtp'])->name('register.verify.resend');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'registerPost'])->name('register.post');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->middleware('auth');

Route::resource('admin/products', \App\Http\Controllers\ProdukController::class)->middleware('auth')->names('products');
Route::resource('admin/categories', \App\Http\Controllers\KategoriController::class)->middleware('auth')->names('categories');
Route::resource('admin/cassiers', \App\Http\Controllers\CassierController::class)->middleware('auth')->names('cassiers');
Route::resource('admin/returs', \App\Http\Controllers\ReturController::class)->middleware('auth')->names('returs');
Route::resource('admin/customers', \App\Http\Controllers\CustomerController::class)->middleware('auth')->names('customers');

Route::resource('admin/orders', \App\Http\Controllers\PesananController::class)->middleware('auth')->names('orders');
Route::get('admin/all-sales', [\App\Http\Controllers\PesananController::class, 'allSales'])->middleware('auth')->name('all-sales.index');

Route::get('admin/pos', [\App\Http\Controllers\PosController::class, 'index'])->middleware('auth')->name('pos.index');
Route::post('admin/pos/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->middleware('auth')->name('pos.checkout');

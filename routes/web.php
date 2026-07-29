<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\PelangganController;

Route::get('/', [PelangganController::class, 'home'])->name('home');
Route::get('/shop', [PelangganController::class, 'shop'])->name('shop');
Route::get('/custom-products', [PelangganController::class, 'customProducts'])->name('custom-products');
Route::get('/about', [PelangganController::class, 'about'])->name('about');
Route::get('/contact', [PelangganController::class, 'contact'])->name('contact');
Route::get('/faq', [PelangganController::class, 'faq'])->name('faq');
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
    Route::get('/order-history/{id}/pay', [PelangganController::class, 'getSnapTokenForOrder'])->name('order.pay');
    Route::get('/order-history/{id}/check-payment', [PelangganController::class, 'checkPaymentStatus'])->name('order.checkPayment');
    Route::get('/order-history/{id}/return', [PelangganController::class, 'returnOrder'])->name('order.return');
    Route::post('/order-history/{id}/return', [PelangganController::class, 'submitReturn'])->name('order.return.submit');
    Route::get('/return-detail/{id}', [PelangganController::class, 'returnDetail'])->name('return.detail');
    Route::get('/settings', [PelangganController::class, 'settings'])->name('settings');
    Route::post('/settings/account', [PelangganController::class, 'updateAccount'])->name('settings.account');
    Route::post('/settings/password', [PelangganController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/address', [PelangganController::class, 'updateAddress'])->name('settings.address');
    Route::post('/settings/avatar', [PelangganController::class, 'updateAvatar'])->name('settings.avatar');
});

// Midtrans Public Callback Hook
Route::post('/midtrans-callback', [PelangganController::class, 'midtransCallback'])->name('midtrans.callback');

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
    if (auth()->check() && auth()->user()->role === 'kasir') {
        return redirect()->route('orders.index')->with('error', 'Anda tidak memiliki hak akses ke halaman dashboard.');
    }
    return view('Admin.dashboard.dashboard');
})->middleware('auth');

Route::get('admin/custom-products', [\App\Http\Controllers\ProdukController::class, 'customIndex'])->middleware('auth')->name('products.custom');
Route::get('admin/expired-products', [\App\Http\Controllers\ExpiredProductController::class, 'index'])->middleware('auth')->name('products.expired.index');
Route::put('admin/expired-products/{id}', [\App\Http\Controllers\ExpiredProductController::class, 'update'])->middleware('auth')->name('products.expired.update');
Route::delete('admin/expired-products/{id}', [\App\Http\Controllers\ExpiredProductController::class, 'destroy'])->middleware('auth')->name('products.expired.destroy');
Route::post('admin/expired-products/bulk-delete', [\App\Http\Controllers\ExpiredProductController::class, 'bulkDelete'])->middleware('auth')->name('products.expired.bulk-delete');
Route::resource('admin/products', \App\Http\Controllers\ProdukController::class)->middleware('auth')->names('products');
Route::resource('admin/discounts', \App\Http\Controllers\DiskonController::class)->middleware('auth')->names('discounts');
Route::post('admin/discounts/bulk-delete', [\App\Http\Controllers\DiskonController::class, 'bulkDelete'])->middleware('auth')->name('discounts.bulk-delete');
Route::post('admin/discounts/{discount}/toggle', [\App\Http\Controllers\DiskonController::class, 'toggleStatus'])->middleware('auth')->name('discounts.toggle');
Route::resource('admin/returs', \App\Http\Controllers\ReturController::class)->middleware('auth')->names('returs');

Route::middleware(['auth', 'blockKasir'])->group(function () {
    Route::resource('admin/categories', \App\Http\Controllers\KategoriController::class)->names('categories');
    Route::resource('admin/cassiers', \App\Http\Controllers\CassierController::class)->names('cassiers');
    Route::resource('admin/customers', \App\Http\Controllers\CustomerController::class)->names('customers');
});

Route::resource('admin/orders', \App\Http\Controllers\PesananController::class)->middleware('auth')->names('orders');
Route::get('admin/orders/{id}/invoice', [\App\Http\Controllers\PesananController::class, 'printInvoice'])->middleware('auth')->name('orders.invoice');
Route::resource('admin/banners', \App\Http\Controllers\BannerController::class)->middleware('auth')->names('banners');
Route::post('admin/banners/bulk-delete', [\App\Http\Controllers\BannerController::class, 'bulkDelete'])->middleware('auth')->name('banners.bulk-delete');
Route::post('admin/banners/{banner}/toggle', [\App\Http\Controllers\BannerController::class, 'toggleStatus'])->middleware('auth')->name('banners.toggle');
Route::get('admin/settings', [\App\Http\Controllers\AdminSettingsController::class, 'index'])->middleware('auth')->name('settings.index');
Route::post('admin/settings/profile', [\App\Http\Controllers\AdminSettingsController::class, 'updateProfile'])->middleware('auth')->name('settings.profile.update');
Route::post('admin/settings/password', [\App\Http\Controllers\AdminSettingsController::class, 'updatePassword'])->middleware('auth')->name('settings.password.update');
Route::post('admin/settings/store', [\App\Http\Controllers\AdminSettingsController::class, 'updateStoreInfo'])->middleware('auth')->name('settings.store.update');

Route::get('admin/about-settings', [\App\Http\Controllers\AdminAboutController::class, 'index'])->middleware('auth')->name('admin.about.index');
Route::post('admin/about-settings', [\App\Http\Controllers\AdminAboutController::class, 'update'])->middleware('auth')->name('admin.about.update');
Route::post('admin/about-settings/team', [\App\Http\Controllers\AdminAboutController::class, 'storeTeam'])->middleware('auth')->name('admin.about.team.store');
Route::put('admin/about-settings/team/{id}', [\App\Http\Controllers\AdminAboutController::class, 'updateTeam'])->middleware('auth')->name('admin.about.team.update');
Route::delete('admin/about-settings/team/{id}', [\App\Http\Controllers\AdminAboutController::class, 'destroyTeam'])->middleware('auth')->name('admin.about.team.destroy');

Route::get('admin/contact-settings', [\App\Http\Controllers\AdminContactController::class, 'index'])->middleware('auth')->name('admin.contact.index');
Route::post('admin/contact-settings', [\App\Http\Controllers\AdminContactController::class, 'update'])->middleware('auth')->name('admin.contact.update');

Route::get('admin/faq-settings', [\App\Http\Controllers\AdminFaqController::class, 'index'])->middleware('auth')->name('admin.faq.index');
Route::post('admin/faq-settings', [\App\Http\Controllers\AdminFaqController::class, 'store'])->middleware('auth')->name('admin.faq.store');
Route::put('admin/faq-settings/{id}', [\App\Http\Controllers\AdminFaqController::class, 'update'])->middleware('auth')->name('admin.faq.update');
Route::post('admin/faq-settings/{id}/toggle', [\App\Http\Controllers\AdminFaqController::class, 'toggleStatus'])->middleware('auth')->name('admin.faq.toggle');
Route::delete('admin/faq-settings/{id}', [\App\Http\Controllers\AdminFaqController::class, 'destroy'])->middleware('auth')->name('admin.faq.destroy');

Route::get('admin/pos', [\App\Http\Controllers\PosController::class, 'index'])->middleware('auth')->name('pos.index');
Route::post('admin/pos/checkout', [\App\Http\Controllers\PosController::class, 'checkout'])->middleware('auth')->name('pos.checkout');

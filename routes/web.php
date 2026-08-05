<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Pelanggan Controllers
use App\Http\Controllers\Pelanggan\PelangganController;
use App\Http\Controllers\Pelanggan\PembayaranController;
use App\Http\Controllers\Pelanggan\CartCheckoutController;
use App\Http\Controllers\Pelanggan\CustomerOrderController;
use App\Http\Controllers\Pelanggan\CustomerReturnController;
use App\Http\Controllers\Pelanggan\CustomerSettingsController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CassierController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DiskonController;
use App\Http\Controllers\Admin\ExpiredProductController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\PesananController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\ReturController;

// Halaman Publik Pelanggan
Route::get('/', [PelangganController::class, 'home'])->name('home');
Route::get('/shop', [PelangganController::class, 'shop'])->name('shop');
Route::get('/custom-products', [PelangganController::class, 'customProducts'])->name('custom-products');
Route::get('/about', [PelangganController::class, 'about'])->name('about');
Route::get('/contact', [PelangganController::class, 'contact'])->name('contact');
Route::get('/faq', [PelangganController::class, 'faq'])->name('faq');
Route::get('/product/{id}', [PelangganController::class, 'detail'])->name('product.detail');
Route::get('/cart', [CartCheckoutController::class, 'userChart'])->name('userChart');

Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CartCheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartCheckoutController::class, 'processCheckout'])->name('checkout.post');
    Route::get('/order-success', [CartCheckoutController::class, 'orderSuccess'])->name('order.success');
    Route::get('/dashboard', [CustomerOrderController::class, 'dashboard'])->name('dashboard');
    Route::get('/order-history', [CustomerOrderController::class, 'orderHistory'])->name('order.history');
    Route::get('/order-history/{id}', [CustomerOrderController::class, 'orderDetail'])->name('order.detail');
    Route::post('/order-history/{id}/cancel', [CustomerOrderController::class, 'cancelOrder'])->name('order.cancel');
    Route::get('/order-history/{id}/pay', [PembayaranController::class, 'getSnapTokenForOrder'])->name('order.pay');
    Route::get('/order-history/{id}/check-payment', [PembayaranController::class, 'checkPaymentStatus'])->name('order.checkPayment');
    Route::get('/order-history/{id}/return', [CustomerReturnController::class, 'returnOrder'])->name('order.return');
    Route::post('/order-history/{id}/return', [CustomerReturnController::class, 'submitReturn'])->name('order.return.submit');
    Route::get('/return-detail/{id}', [CustomerReturnController::class, 'returnDetail'])->name('return.detail');
    Route::get('/settings', [CustomerSettingsController::class, 'settings'])->name('settings');
    Route::post('/settings/account', [CustomerSettingsController::class, 'updateAccount'])->name('settings.account');
    Route::post('/settings/password', [CustomerSettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/address', [CustomerSettingsController::class, 'updateAddress'])->name('settings.address');
    Route::post('/settings/avatar', [CustomerSettingsController::class, 'updateAvatar'])->name('settings.avatar');
});

// Midtrans Public Callback Hook
Route::post('/midtrans-callback', [PembayaranController::class, 'midtransCallback'])->name('midtrans.callback');

// Auth Routes
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

use Illuminate\Support\Facades\Auth;

Route::get('/admin/dashboard', function () {
    if (Auth::check() && Auth::user()->role === 'kasir') {
        return redirect()->route('orders.index')->with('error', 'Anda tidak memiliki hak akses ke halaman dashboard.');
    }
    return view('Admin.dashboard.dashboard');
})->middleware('auth');

Route::get('admin/custom-products', [ProdukController::class, 'customIndex'])->middleware('auth')->name('products.custom');
Route::get('admin/expired-products', [ExpiredProductController::class, 'index'])->middleware('auth')->name('products.expired.index');
Route::put('admin/expired-products/{id}', [ExpiredProductController::class, 'update'])->middleware('auth')->name('products.expired.update');
Route::delete('admin/expired-products/{id}', [ExpiredProductController::class, 'destroy'])->middleware('auth')->name('products.expired.destroy');
Route::post('admin/expired-products/bulk-delete', [ExpiredProductController::class, 'bulkDelete'])->middleware('auth')->name('products.expired.bulk-delete');

// Export Routes (Must be declared before Resource routes)
Route::middleware('auth')->group(function () {
    Route::get('admin/all-sales/export', [PesananController::class, 'exportAllSales'])->name('export.all-sales');
    Route::get('admin/orders/export', [PesananController::class, 'exportOrders'])->name('export.orders');
    Route::get('admin/products/export', [ProdukController::class, 'exportProducts'])->name('export.products');
    Route::get('admin/customers/export', [CustomerController::class, 'exportCustomers'])->name('export.customers');
    Route::get('admin/returs/export', [ReturController::class, 'exportReturs'])->name('export.returs');
});

Route::resource('admin/products', ProdukController::class)->middleware('auth')->names('products');
Route::resource('admin/discounts', DiskonController::class)->middleware('auth')->names('discounts');
Route::post('admin/discounts/bulk-delete', [DiskonController::class, 'bulkDelete'])->middleware('auth')->name('discounts.bulk-delete');
Route::post('admin/discounts/{discount}/toggle', [DiskonController::class, 'toggleStatus'])->middleware('auth')->name('discounts.toggle');
Route::resource('admin/returs', ReturController::class)->middleware('auth')->names('returs');

Route::middleware(['auth', 'blockKasir'])->group(function () {
    Route::resource('admin/categories', KategoriController::class)->names('categories');
    Route::resource('admin/cassiers', CassierController::class)->names('cassiers');
    Route::resource('admin/customers', CustomerController::class)->names('customers');
});

Route::resource('admin/orders', PesananController::class)->middleware('auth')->names('orders');
Route::get('admin/all-sales', [PesananController::class, 'allSales'])->middleware('auth')->name('all-sales.index');
Route::get('admin/orders/{id}/invoice', [PesananController::class, 'printInvoice'])->middleware('auth')->name('orders.invoice');
Route::resource('admin/banners', BannerController::class)->middleware('auth')->names('banners');
Route::post('admin/banners/bulk-delete', [BannerController::class, 'bulkDelete'])->middleware('auth')->name('banners.bulk-delete');
Route::post('admin/banners/{banner}/toggle', [BannerController::class, 'toggleStatus'])->middleware('auth')->name('banners.toggle');
Route::get('admin/settings', [AdminSettingsController::class, 'index'])->middleware('auth')->name('settings.index');
Route::post('admin/settings/profile', [AdminSettingsController::class, 'updateProfile'])->middleware('auth')->name('settings.profile.update');
Route::post('admin/settings/password', [AdminSettingsController::class, 'updatePassword'])->middleware('auth')->name('settings.password.update');
Route::post('admin/settings/store', [AdminSettingsController::class, 'updateStoreInfo'])->middleware('auth')->name('settings.store.update');

Route::get('admin/pos', [PosController::class, 'index'])->middleware('auth')->name('pos.index');
Route::post('admin/pos/checkout', [PosController::class, 'checkout'])->middleware('auth')->name('pos.checkout');

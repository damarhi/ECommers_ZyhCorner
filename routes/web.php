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
use App\Http\Controllers\Pelanggan\CustomerAddressController;
// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BuktiBayarController;
use App\Http\Controllers\Admin\CassierController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DiskonController;
use App\Http\Controllers\Admin\EksporController;
use App\Http\Controllers\Admin\ExpiredProductController;
use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\LaporanController;
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
Route::post('/contact/send', [PelangganController::class, 'sendContactMessage'])
    ->middleware('throttle:contact-form')
    ->name('contact.send');
Route::get('/faq', [PelangganController::class, 'faq'])->name('faq');
Route::get('/product/{id}', [PelangganController::class, 'detail'])->name('product.detail');
Route::get('/search-suggestions', [PelangganController::class, 'searchSuggestions'])
    ->middleware('throttle:public-api')
    ->name('search.suggestions');
Route::get('/cart', [CartCheckoutController::class, 'userChart'])->name('userChart');
Route::get('/biteship/areas', [CartCheckoutController::class, 'biteshipAreas'])
    ->middleware('throttle:public-api')
    ->name('biteship.areas');
Route::post('/biteship/rates', [CartCheckoutController::class, 'biteshipRates'])
    ->middleware('throttle:public-api')
    ->name('biteship.rates');

Route::middleware(['auth', 'role:pelanggan', 'preventBackHistory'])->group(function () {
    Route::get('/checkout', [CartCheckoutController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [CartCheckoutController::class, 'processCheckout'])->name('checkout.post');
    Route::get('/order-success', [CartCheckoutController::class, 'orderSuccess'])->name('order.success');
    Route::get('/dashboard', [CustomerOrderController::class, 'dashboard'])->name('dashboard');
    Route::get('/order-history', [CustomerOrderController::class, 'orderHistory'])->name('order.history');
    Route::get('/order-history/{id}', [CustomerOrderController::class, 'orderDetail'])->name('order.detail');
    Route::post('/order-history/{id}/cancel', [CustomerOrderController::class, 'cancelOrder'])->name('order.cancel');
    Route::post('/order-history/{id}/upload-proof', [CustomerOrderController::class, 'uploadPaymentProof'])->name('order.upload.proof');
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
    // Customer Multi-Address Management Routes
    Route::get('/customer/addresses', [CustomerAddressController::class, 'index'])->name('customer.addresses.index');
    Route::post('/customer/addresses', [CustomerAddressController::class, 'store'])->name('customer.addresses.store');
    Route::put('/customer/addresses/{id}', [CustomerAddressController::class, 'update'])->name('customer.addresses.update');
    Route::delete('/customer/addresses/{id}', [CustomerAddressController::class, 'destroy'])->name('customer.addresses.destroy');
    Route::post('/customer/addresses/{id}/set-default', [CustomerAddressController::class, 'setDefault'])->name('customer.addresses.setDefault');
});
// Midtrans Public Callback Hook
Route::post('/midtrans-callback', [PembayaranController::class, 'midtransCallback'])->name('midtrans.callback');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:auth-login')
    ->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Google Authentication Routes
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('login.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('login.google.callback');
Route::get('/auth/google/verify', [AuthController::class, 'showGoogleVerifyForm'])->name('login.google.verify');
Route::post('/auth/google/verify', [AuthController::class, 'verifyGoogleOtp'])
    ->middleware('throttle:otp-verify')
    ->name('login.google.verify.post');
Route::post('/auth/google/verify/resend', [AuthController::class, 'resendGoogleOtp'])
    ->middleware('throttle:otp-resend')
    ->name('login.google.verify.resend');
// Registration OTP Email Verification Routes
Route::get('/register/verify', [AuthController::class, 'showRegisterVerifyForm'])->name('register.verify');
Route::post('/register/verify', [AuthController::class, 'verifyRegisterOtp'])
    ->middleware('throttle:otp-verify')
    ->name('register.verify.post');
Route::post('/register/verify/resend', [AuthController::class, 'resendRegisterOtp'])
    ->middleware('throttle:otp-resend')
    ->name('register.verify.resend');

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::post('/register', [AuthController::class, 'registerPost'])
    ->middleware('throttle:auth-register')
    ->name('register.post');

Route::middleware(['auth', 'role:admin,kasir', 'preventBackHistory'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Operational Sales, Orders, Returns & POS (Admin & Active Kasir)
    Route::get('admin/orders/export', [EksporController::class, 'exportOrders'])->name('export.orders');
    Route::get('admin/returs/export', [ReturController::class, 'exportReturs'])->name('export.returs');

    Route::post('admin/returs/bulk-delete', [ReturController::class, 'bulkDelete'])->name('returs.bulk-delete');
    Route::patch('admin/returs/{retur}/status', [ReturController::class, 'updateStatus'])->name('returs.update-status');
    Route::resource('admin/returs', ReturController::class)->names('returs')->except(['create', 'edit', 'show']);

    Route::resource('admin/orders', PesananController::class)->names('orders')->except(['create', 'edit', 'show', 'destroy']);
    Route::get('admin/all-sales', [LaporanController::class, 'allSales'])->name('all-sales.index');
    Route::get('admin/orders/{id}/invoice', [PesananController::class, 'printInvoice'])->name('orders.invoice');
    Route::post('admin/orders/{id}/quick-confirm', [PesananController::class, 'quickConfirm'])->name('orders.quickConfirm');
    Route::post('admin/orders/{id}/confirm-proof', [BuktiBayarController::class, 'confirmProof'])->name('orders.confirm-proof');
    Route::post('admin/orders/{id}/reject-proof', [BuktiBayarController::class, 'rejectProof'])->name('orders.reject-proof');
    Route::post('admin/orders/{id}/confirmProof', [BuktiBayarController::class, 'confirmProof'])->name('orders.confirmProof');
    Route::post('admin/orders/{id}/rejectProof', [BuktiBayarController::class, 'rejectProof'])->name('orders.rejectProof');

    // Operational Showcase Products (Create & Edit Showcase for Admin & Kasir; Delete & Custom Products guarded below)
    Route::resource('admin/products', ProdukController::class)->names('products')->except(['show', 'destroy']);

    // Operational Showcase Expiry Check (Read-Only for Kasir; Write/Delete guarded below)
    Route::get('admin/expired-products', [ExpiredProductController::class, 'index'])->name('products.expired.index');

    // Customer Contact Messages CS Access (Read & Update Status for Admin & Kasir; Delete guarded below)
    Route::get('admin/messages', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('admin/messages/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
    Route::patch('admin/messages/{id}/status', [ContactMessageController::class, 'updateStatus'])->name('messages.update-status');

    Route::get('admin/settings', [AdminSettingsController::class, 'index'])->name('settings.index');
    Route::post('admin/settings/profile', [AdminSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::post('admin/settings/password', [AdminSettingsController::class, 'updatePassword'])->name('settings.password.update');

    Route::get('admin/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('admin/pos/checkout', [PosController::class, 'checkoutPage'])->name('pos.checkout.page');
    Route::post('admin/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
    Route::post('admin/pos/products/{id}/add-stock', [PosController::class, 'addStock'])->name('pos.add-stock');

    // Strictly Admin-Only Management Routes
    Route::middleware('blockKasir')->group(function () {
        Route::get('admin/all-sales/report', [LaporanController::class, 'salesReport'])->name('all-sales.report');
        Route::get('admin/all-sales/export', [EksporController::class, 'exportAllSales'])->name('export.all-sales');

        Route::get('admin/custom-products', [ProdukController::class, 'customIndex'])->name('products.custom');
        Route::get('admin/products/export', [ProdukController::class, 'exportProducts'])->name('export.products');
        Route::post('admin/products/bulk-delete', [ProdukController::class, 'bulkDelete'])->name('products.bulk-delete');
        Route::delete('admin/products/{product}', [ProdukController::class, 'destroy'])->name('products.destroy');

        Route::put('admin/expired-products/{id}', [ExpiredProductController::class, 'update'])->name('products.expired.update');
        Route::delete('admin/expired-products/{id}', [ExpiredProductController::class, 'destroy'])->name('products.expired.destroy');
        Route::post('admin/expired-products/bulk-delete', [ExpiredProductController::class, 'bulkDelete'])->name('products.expired.bulk-delete');

        Route::post('admin/discounts/bulk-delete', [DiskonController::class, 'bulkDelete'])->name('discounts.bulk-delete');
        Route::post('admin/discounts/{discount}/toggle', [DiskonController::class, 'toggleStatus'])->name('discounts.toggle');
        Route::resource('admin/discounts', DiskonController::class)->names('discounts')->except(['create', 'edit', 'show']);

        Route::post('admin/categories/bulk-delete', [KategoriController::class, 'bulkDelete'])->name('categories.bulk-delete');
        Route::resource('admin/categories', KategoriController::class)->names('categories');

        Route::post('admin/cassiers/bulk-delete', [CassierController::class, 'bulkDelete'])->name('cassiers.bulk-delete');
        Route::resource('admin/cassiers', CassierController::class)->names('cassiers')->except(['create', 'edit', 'show']);

        Route::get('admin/customers/export', [CustomerController::class, 'exportCustomers'])->name('export.customers');
        Route::post('admin/customers/bulk-delete', [CustomerController::class, 'bulkDelete'])->name('customers.bulk-delete');
        Route::resource('admin/customers', CustomerController::class)->names('customers')->except(['create', 'edit', 'show']);

        Route::post('admin/messages/bulk-delete', [ContactMessageController::class, 'bulkDelete'])->name('messages.bulk-delete');
        Route::delete('admin/messages/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

        Route::post('admin/banners/bulk-delete', [BannerController::class, 'bulkDelete'])->name('banners.bulk-delete');
        Route::post('admin/banners/{banner}/toggle', [BannerController::class, 'toggleStatus'])->name('banners.toggle');
        Route::resource('admin/banners', BannerController::class)->names('banners')->except(['create', 'edit', 'show']);

        Route::post('admin/settings/store', [AdminSettingsController::class, 'updateStoreInfo'])->name('settings.store.update');

        Route::post('admin/orders/bulk-delete', [PesananController::class, 'bulkDelete'])->name('orders.bulk-delete');
        Route::delete('admin/orders/{order}', [PesananController::class, 'destroy'])->name('orders.destroy');
    });
});

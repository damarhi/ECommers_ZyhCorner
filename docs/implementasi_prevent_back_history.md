# Dokumentasi Implementasi Proteksi Tombol Back Setelah Logout (`PreventBackHistory`)

## 1. Latar Belakang & Analisis Masalah

### Gejala Masalah
Setelah pengguna melakukan logout dari aplikasi, menekan tombol **Back ($\leftarrow$)** pada browser (terutama peramban mobile seperti Chrome di Android yang terlihat pada tangkapan layar) menampilkan kembali tampilan dashboard atau akun pengguna sebelumnya seolah-olah akun masuk kembali secara otomatis (*auto re-login*).

### Akar Masalah Teknis
1. **Browser bfcache (Back/Forward Cache)**:
   Peramban modern mengimplementasikan fitur pengoptimalan kinerja bernama *bfcache*. Fitur ini menyimpan cuplikan memori (RAM snapshot) seluruh DOM dan aset halaman ketika pengguna berpindah halaman.
2. **Ketiadaan Header HTTP Anti-Cache**:
   Sebelumnya, respon halaman yang dilindungi otentikasi di Laravel tidak menyertakan instruksi `Cache-Control` yang ketat. Akibatnya, saat tombol Back ditekan, browser memulihkan halaman dari memori lokal tanpa mengirimkan permintaan baru ke server.
3. **Status Sesi Sebenarnya**:
   Di sisi server Laravel, sesi otentikasi pengguna sebenarnya sudah 100% dibatalkan (`Auth::logout()`, `$request->session()->invalidate()`). Namun karena browser menampilkan snapshot lama, pengguna mengalami kebingungan visual seolah sesi mereka masih aktif.

---

## 2. Rincian Perubahan Kode

### A. Middleware `app/Http/Middleware/PreventBackHistory.php` (File Baru)
Membuat middleware untuk menginjeksi header kontrol cache pada setiap HTTP Response untuk rute yang dilindungi:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
```

### B. Registrasi Alias Middleware di `bootstrap/app.php`
Mendaftarkan alias middleware `'preventBackHistory'` ke konfigurasi middleware aplikasi Laravel 11:

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'blockKasir' => \App\Http\Middleware\CheckKasirRole::class,
        'preventBackHistory' => \App\Http\Middleware\PreventBackHistory::class,
    ]);
    $middleware->validateCsrfTokens(except: [
        '/midtrans-callback',
    ]);
})
```

### C. Penerapan Middleware di `routes/web.php`
Menerapkan middleware `'preventBackHistory'` pada semua rute yang memerlukan otentikasi, baik untuk Pelanggan maupun Admin:

1. **Rute Pelanggan Terautentikasi (Checkout, Dashboard, Order History, Settings)**:
   ```php
   Route::middleware(['auth', 'preventBackHistory'])->group(function () {
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
   ```

2. **Rute Admin Terautentikasi (Dashboard, Produk, Pesanan, Laporan, Banner, Pesan Kontak, POS, dll)**:
   ```php
   Route::middleware(['auth', 'preventBackHistory'])->group(function () {
       Route::get('/admin/dashboard', function () {
           if (Auth::check() && Auth::user()->role === 'kasir') {
               return redirect()->route('orders.index')->with('error', 'Anda tidak memiliki hak akses ke halaman dashboard.');
           }
           return view('Admin.dashboard.dashboard');
       });

       // Seluruh resource admin lainnya...
   });
   ```

*Catatan: Rute publik seperti Beranda (`/`), Toko (`/shop`), Detail Produk (`/product/{id}`), dan FAQ (`/faq`) tetap dibiarkan tanpa middleware ini agar kinerja akses publik cepat dan dapat di-cache secara wajar.*

### D. Proteksi Ganda Sisi Klien via Event `pageshow`
Untuk menangani browser mobile (seperti Chrome di Android) yang terkadang secara agresif memulihkan memori bahkan sebelum mengevaluasi header jaringan, ditambahkan skrip event listener `pageshow` pada:
1. `resources/views/Layout/layout_pAdmin.blade.php`
2. `resources/views/Layout/layout_pelanggan.blade.php`

```html
<!-- Anti-bfcache Back Button Protection -->
<script>
    window.addEventListener('pageshow', function (event) {
        if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
            window.location.reload();
        }
    });
</script>
```

Jika `event.persisted === true` (artinya halaman dipulihkan dari cache memori tanpa kontak server), skrip langsung memicu `window.location.reload()`. Permintaan muat ulang ini langsung menghubungi server, terkena filter middleware `auth`, dan pengguna diarahkan ke `/login`.

---

## 3. Bukti Verifikasi Pengujian (*Automated Headless Browser*)

Pengujian dilakukan menggunakan browser Chromium headless dengan skenario end-to-end nyata:

### A. Pengujian Akun Administrator (`admin@gmail.com`)
1. **Login**: Berhasil masuk ke `http://clone_pa_zyhcorner.test/admin/dashboard`.
2. **Pengecekan Header HTTP Response**:
   ```json
   {
     "cacheControl": "max-age=0, must-revalidate, no-cache, no-store, private",
     "pragma": "no-cache",
     "expires": "Sun, 02 Jan 1990 00:00:00 GMT",
     "status": 200
   }
   ```
3. **Logout**: Form logout disubmit, pengguna diarahkan ke `http://clone_pa_zyhcorner.test/login`.
4. **Tekan Tombol Back Peramban (`tab.back()`)**:
   - **URL Akhir**: `http://clone_pa_zyhcorner.test/login`
   - **Judul Halaman**: `Login - Zyh Corner`
   - **Hasil**: Browser tidak menampilkan dasbor admin lama, melainkan tetap di halaman login.

### B. Pengujian Akun Pelanggan (`nama@gmail.com`)
1. **Login & Navigasi**: Masuk ke dasbor pelanggan `http://clone_pa_zyhcorner.test/dashboard`.
2. **Pengecekan Header HTTP Response**:
   ```json
   {
     "cacheControl": "max-age=0, must-revalidate, no-cache, no-store, private",
     "pragma": "no-cache",
     "expires": "Sun, 02 Jan 1990 00:00:00 GMT",
     "status": 200
   }
   ```
3. **Logout**: Form logout disubmit, pengguna diarahkan ke `http://clone_pa_zyhcorner.test/login`.
4. **Tekan Tombol Back Peramban (`tab.back()`)**:
   - **URL Akhir**: `http://clone_pa_zyhcorner.test/login`
   - **Hasil**: Dasbor pelanggan terlindungi dan tidak terbuka kembali.

---

## 4. Kesimpulan
Dengan penggabungan **Middleware `PreventBackHistory`** (di sisi server) dan **Event Listener `pageshow`** (di sisi klien), masalah tampilan snapshot akun lama saat tombol back diklik setelah logout telah teratasi sepenuhnya secara aman dan permanen.

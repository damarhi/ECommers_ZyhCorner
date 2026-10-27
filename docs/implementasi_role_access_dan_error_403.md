# Dokumentasi Implementasi Kontrol Akses Peran (*Role-Based Access Control*) dan Halaman Error 403 (*Forbidden Access*)

## 1. Analisis Masalah & Kebutuhan

### A. Latar Belakang Masalah
Sebelumnya, sistem otentikasi hanya menggunakan middleware bawaan `auth` (`Auth::check()`) tanpa memeriksa nilai kolom `$user->role`. Akibatnya:
1. Akun dengan peran `admin` atau `kasir` yang sedang login dapat membuka rute privat akun pelanggan (`/dashboard`, `/order-history`, `/checkout`, `/settings`).
2. Sebaliknya, akun dengan peran `pelanggan` berpotensi mencoba mengakses rute operasional `/admin/*`.
3. Belum tersedia halaman antarmuka penolakan akses kustom (*HTTP 403 Forbidden Access Page*), sehingga penolakan akses hanya menampilkan halaman kesalahan standar bawaan framework atau *redirect* tanpa kejelasan konteks izin bagi pengguna.

### B. Sasaran Perbaikan
1. Membuat middleware otorisasi peran dinamis `EnsureUserRole` dengan alias `'role'`.
2. Menerapkan pembatasan peran dua arah pada rute:
   - Rute privat pelanggan hanya dapat diakses oleh peran `'pelanggan'`.
   - Rute manajemen dan operasional toko hanya dapat diakses oleh peran `'admin'` dan `'kasir'`.
3. Membangun halaman error **403 Forbidden Access** kustom (`resources/views/errors/403.blade.php`) dengan desain modern bertema Zyh Corner, pendeteksi peran aktif cerdas (*smart role badge*), serta tombol navigasi pemulihan yang relevan untuk setiap peran.

---

## 2. Rincian Perubahan Kode

### A. Middleware Baru: `app/Http/Middleware/EnsureUserRole.php`
Memeriksa status login dan memvalidasi apakah peran pengguna saat ini (`$user->role`) terdaftar dalam parameter peran yang diizinkan untuk rute tersebut. Jika tidak berhak, hentikan eksekusi dengan `abort(403)`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Jika peran pengguna tidak termasuk dalam daftar yang diizinkan
        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
```

### B. Pendaftaran Alias Middleware di `bootstrap/app.php`
Mendaftarkan alias `'role'` pada konfigurasi middleware Laravel 11:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'blockKasir' => \App\Http\Middleware\CheckKasirRole::class,
        'preventBackHistory' => \App\Http\Middleware\PreventBackHistory::class,
        'role' => \App\Http\Middleware\EnsureUserRole::class,
    ]);
    // ...
})
```

### C. Penerapan Otorisasi Rute di `routes/web.php`
1. **Rute Privat Pelanggan (`role:pelanggan`)**:
   ```php
   Route::middleware(['auth', 'role:pelanggan', 'preventBackHistory'])->group(function () {
       Route::get('/checkout', [CartCheckoutController::class, 'checkout'])->name('checkout');
       Route::post('/checkout', [CartCheckoutController::class, 'processCheckout'])->name('checkout.post');
       Route::get('/order-success', [CartCheckoutController::class, 'orderSuccess'])->name('order.success');
       Route::get('/dashboard', [CustomerOrderController::class, 'dashboard'])->name('dashboard');
       Route::get('/order-history', [CustomerOrderController::class, 'orderHistory'])->name('order.history');
       Route::get('/order-history/{id}', [CustomerOrderController::class, 'orderDetail'])->name('order.detail');
       Route::get('/settings', [CustomerSettingsController::class, 'settings'])->name('settings');
       // ... rute profil & pesanan pelanggan lainnya
   });
   ```

2. **Rute Panel Admin & Kasir (`role:admin,kasir`)**:
   ```php
   Route::middleware(['auth', 'role:admin,kasir', 'preventBackHistory'])->group(function () {
       Route::get('/admin/dashboard', ...);
       Route::resource('admin/orders', PesananController::class)->names('orders');
       Route::resource('admin/products', ProdukController::class)->names('products');
       Route::get('admin/pos', [PosController::class, 'index'])->name('pos.index');
       // ... rute operasional toko lainnya
   });
   ```

3. **Rute Publik**:
   Rute etalase publik (`/`, `/shop`, `/custom-products`, `/about`, `/contact`, `/faq`, `/product/{id}`) tetap dibiarkan tanpa middleware peran agar katalog toko dapat dijelajahi oleh semua orang.

### D. Halaman Error 403 Kustom: `resources/views/errors/403.blade.php`
Dibuat dengan karakteristik desain Zyh Corner:
- **Identitas Visual**: Palet sage organik (`#024220`), aksen hijau daun (`#00b207`), kartu terpusat dengan *subtle gradient*, dan ikon perisai/gembok (*shield lock*).
- **Status Pill**: Badge `403 • AKSES DITOLAK`.
- **Deteksi Identitas Aktif**: Menampilkan inisial avatar, nama lengkap pengguna, email, dan pill peran (`Administrator`, `Kasir Toko`, atau `Pelanggan`).
- **Navigasi Cerdas Sesuai Peran**:
  - **Jika Admin**: Disediakan tombol *"Kembali ke Dashboard Admin"* dan *"Buka Etalase Toko"*.
  - **Jika Kasir**: Disediakan tombol *"Kembali ke Kelola Pesanan"* dan *"Buka Mesin Kasir (POS)"*.
  - **Jika Pelanggan**: Disediakan tombol *"Kembali ke Beranda Toko"* dan *"Buka Dashboard Saya"*.
  - **Semua Pengguna**: Tombol terintegrasi *"Keluar & Masuk dengan Akun Lain"* yang langsung memicu form logout aman.

---

## 3. Matriks Hasil Pengujian (*Automated Headless Browser*)

Pengujian dilakukan menggunakan peramban Chromium headless dengan skenario nyata:

| Skenario Pengujian | Akun Penguji | URL yang Dituju | Respon Sistem | Tampilan Halaman | Status |
| :--- | :--- | :--- | :---: | :--- | :---: |
| **Admin membuka rute Pelanggan** | `admin@gmail.com` (role: admin) | `/dashboard` | **403 Forbidden** | Halaman 403 kustom muncul; Badge peran: `Administrator`; Terdapat tombol *"Kembali ke Dashboard Admin"* | **Lolos** |
| **Pelanggan membuka rute Admin** | `nama@gmail.com` (role: pelanggan) | `/admin/dashboard` | **403 Forbidden** | Halaman 403 kustom muncul; Badge peran: `Pelanggan`; Terdapat tombol *"Kembali ke Beranda Toko"* | **Lolos** |
| **Pelanggan membuka rute Sahnya** | `nama@gmail.com` (role: pelanggan) | `/dashboard` | **200 OK** | Masuk ke Dasbor Pelanggan dengan normal | **Lolos** |
| **Admin membuka rute Sahnya** | `admin@gmail.com` (role: admin) | `/admin/dashboard` | **200 OK** | Masuk ke Dasbor Admin dengan normal | **Lolos** |
| **Tombol Logout pada Error 403** | Akun Admin / Pelanggan | Tombol *"Keluar & Masuk Akun Lain"* | **302 Redirect** | Sesi dihapus dan diarahkan kembali ke `/login` | **Lolos** |

---

## 4. Kesimpulan
Implementasi **Role-Based Access Control (RBAC)** dan **Halaman Error 403 Kustom** telah berfungsi secara penuh, melindungi integritas hak akses data pengguna, serta memberikan pengalaman antarmuka (*user experience*) yang profesional dan ramah pengguna.

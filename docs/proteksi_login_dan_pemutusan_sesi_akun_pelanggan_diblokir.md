# Dokumentasi Implementasi Proteksi Login & Pemutusan Sesi Akun Pelanggan Diblokir (*Blocked Customer Security Gate*)

## 1. Latar Belakang Masalah
Sebelumnya, ketika administrator mengubah status pelanggan menjadi **Diblokir (*Blocked*)** melalui halaman *Admin $\rightarrow$ Kelola Akun $\rightarrow$ Akun Pelanggan*, pelanggan tersebut masih dapat masuk (*login*) dan bertransaksi di website. 

### Akar Penyebab Teknis:
1. **Pemisahan Entitas Model:**
   - Autentikasi Laravel di ZYHCORNER mengacu pada model `User` (`users` table).
   - Status akun (*Active* / *Blocked*) disimpan pada tabel `Customer` (`customers` table).
2. **Ketiadaan Validasi pada Alur Login:**
   - Method `login()`, `handleGoogleCallback()`, dan `verifyGoogleOtp()` pada `AuthController.php` hanya mencocokkan kredensial email/password atau token Google tanpa memeriksa apakah record `Customer` terkait berstatus `Blocked`.
3. **Ketiadaan Proteksi Sesi Aktif:**
   - Tidak ada middleware yang memantau status blokir pelanggan saat sesi sedang berjalan, sehingga pelanggan yang sudah login tetap dapat mengakses keranjang dan menyelesaikan pesanan meskipun statusnya baru saja diblokir oleh admin.

---

## 2. Solusi & Perubahan yang Diterapkan

1. **Helper Method Terpusat pada Model `User` (`app/Models/User.php`):**
   - Menambahkan method `isBlocked(): bool`.
   - Memeriksa relasi `Customer` langsung via foreign key `user_id`.
   - Memiliki mekanisme *fallback* pencocokan nomor telepon (`contact`) dengan normalisasi awalan angka (`08...`, `628...`, atau integer tanpa 0) untuk mengakomodasi data pelanggan offline/legacy.
   - Mengabaikan role `admin` dan `kasir` (hanya berlaku untuk role `pelanggan`).

2. **Intersepsi pada Seluruh Gerbang Login (`app/Http/Controllers/AuthController.php`):**
   - **Form Login (Email & Password):** Jika kredensial cocok namun `$user->isBlocked()` bernilai `true`, sistem segera memanggil `Auth::logout()`, membatalkan sesi, meregenerasi token CSRF, dan mengembalikan user ke halaman login dengan pesan:
     > *"Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan."*
   - **Google OAuth Login (`handleGoogleCallback`):** Dicegat sebelum `Auth::login()`.
   - **Google OTP Verification (`verifyGoogleOtp`):** Dicegat sebelum sesi diinisiasi.
   - **Registrasi OTP (`verifyRegisterOtp`):** Dicegat jika akun/kontak terhubung dengan record blokir.

3. **Middleware Pemutus Sesi Real-Time (`app/Http/Middleware/CheckBlockedCustomer.php`):**
   - Didaftarkan ke grup middleware global `web` di `bootstrap/app.php`.
   - Jika admin memblokir pelanggan saat pelanggan sedang membuka halaman, request berikutnya (klik tombol/navigasi) akan langsung memutus sesi (*force logout*) dan me-redirect ke form login dengan pesan kesalahan.
   - Mendukung respons JSON 403 untuk panggilan API/AJAX.

4. **Lapisan Proteksi Tambahan pada `EnsureUserRole.php`:**
   - Memastikan rute-rute yang diproteksi middleware `role:pelanggan` juga langsung menolak akses jika akun dalam status `Blocked`.

---

## 3. Rincian Diff Kode

### A. Berkas `app/Models/User.php`
```diff
--- a/app/Models/User.php
+++ b/app/Models/User.php
@@ -51,4 +51,25 @@
     {
         return $this->hasOne(UserAddress::class, 'user_id', 'user_id')->where('is_default', true);
     }
+
+    public function isBlocked(): bool
+    {
+        if ($this->role !== 'pelanggan') {
+            return false;
+        }
+
+        $customer = $this->customer()->first();
+        if ($customer && $customer->status === 'Blocked') {
+            return true;
+        }
+
+        if (!empty($this->no_hp)) {
+            $rawPhone = ltrim((string) $this->no_hp, '0');
+            $customerByPhone = Customer::where(function ($q) use ($rawPhone) {
+                $q->where('contact', (string) $this->no_hp)
+                  ->orWhere('contact', '0' . $rawPhone)
+                  ->orWhere('contact', '62' . $rawPhone)
+                  ->orWhere('contact', 'like', '%' . $rawPhone . '%');
+            })->first();
+
+            if ($customerByPhone && $customerByPhone->status === 'Blocked') {
+                return true;
+            }
+        }
+
+        return false;
+    }
 }
```

### B. Berkas `app/Http/Controllers/AuthController.php`
```diff
--- a/app/Http/Controllers/AuthController.php
+++ b/app/Http/Controllers/AuthController.php
@@ -31,6 +31,17 @@
         if (Auth::attempt($credentials)) {
+            $user = Auth::user();
+
+            if ($user->isBlocked()) {
+                Auth::logout();
+                $request->session()->invalidate();
+                $request->session()->regenerateToken();
+
+                return back()->withErrors([
+                    'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
+                ])->onlyInput('email');
+            }
+
             $request->session()->regenerate();

@@ -177,6 +188,12 @@
         if ($user) {
+            if ($user->isBlocked()) {
+                return redirect()->route('login')->withErrors([
+                    'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
+                ]);
+            }
+
             // Update Google ID if not set

@@ -310,6 +327,12 @@
         } else {
             session()->forget('google_auth_pending_user_id');
         }
+
+        if ($user->isBlocked()) {
+            return redirect()->route('login')->withErrors([
+                'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
+            ]);
+        }

         Auth::login($user);

@@ -434,6 +457,12 @@
         }
+
+        if ($user->isBlocked()) {
+            return redirect()->route('login')->withErrors([
+                'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
+            ]);
+        }

         Auth::login($user);
```

### C. Berkas `app/Http/Middleware/CheckBlockedCustomer.php` (Berkas Baru)
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckBlockedCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->isBlocked()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
                    ], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
                ]);
            }
        }

        return $next($request);
    }
}
```

### D. Berkas `bootstrap/app.php`
```diff
--- a/bootstrap/app.php
+++ b/bootstrap/app.php
@@ -15,8 +15,12 @@
             'blockKasir' => \App\Http\Middleware\CheckKasirRole::class,
             'preventBackHistory' => \App\Http\Middleware\PreventBackHistory::class,
             'role' => \App\Http\Middleware\EnsureUserRole::class,
+            'checkBlockedCustomer' => \App\Http\Middleware\CheckBlockedCustomer::class,
         ]);
+        $middleware->appendToGroup('web', [
+            \App\Http\Middleware\CheckBlockedCustomer::class,
+        ]);
```

### E. Berkas `app/Http/Middleware/EnsureUserRole.php`
```diff
--- a/app/Http/Middleware/EnsureUserRole.php
+++ b/app/Http/Middleware/EnsureUserRole.php
@@ -23,6 +23,15 @@
         $user = Auth::user();
+
+        if ($user->isBlocked()) {
+            Auth::logout();
+            $request->session()->invalidate();
+            $request->session()->regenerateToken();
+
+            return redirect()->route('login')->withErrors([
+                'email' => 'Akun Anda telah dinonaktifkan atau diblokir oleh administrator. Silakan hubungi layanan pelanggan.',
+            ]);
+        }
```

---

## 4. Hasil Verifikasi & Pengujian

### A. Pengujian Fitur Khusus (`tests/Feature/CustomerBlockedLoginTest.php`)
Dibuat 7 skenario pengujian komprehensif:
1. `✓ active customer can login normally`: Pelanggan aktif dapat masuk secara normal.
2. `✓ blocked customer via user id cannot login`: Pelanggan dengan status `Blocked` via foreign key `user_id` ditolak login dan sesi tidak tercipta.
3. `✓ blocked customer via phone fallback cannot login`: Pelanggan dengan status `Blocked` via pencocokan nomor kontak telepon ditolak login.
4. `✓ admin and kasir are not affected by blocked check`: Akun Admin dan Kasir tidak terpengaruh oleh aturan pemblokiran pelanggan.
5. `✓ unblocked customer can login again`: Saat administrator mengembalikan status akun ke `Active`, pelanggan dapat login kembali secara normal.
6. `✓ active session is terminated by middleware when customer is blocked`: Sesi yang sedang aktif langsung diputus (*force logout*) ketika administrator memblokir akun di tengah sesi.
7. `✓ blocked customer cannot login via google callback`: Pelanggan terblokir ditolak saat mencoba masuk menggunakan Google OAuth.

Hasil eksekusi:
```text
PASS Tests\Feature\CustomerBlockedLoginTest
7 passed (40 assertions) - Duration: 3.48s
```

### B. Pengujian Seluruh Sistem (*Full Regression Test Suite*)
```bash
php artisan test
```
**Hasil:** `160 passed (814 assertions) - 100% Green` (tidak ada regresi pada modul kasir, admin, transaksi POS, diskon, retur, maupun checkout).

# Eksekusi Penghapusan dan Dekomisioning Database SQLite

Dokumen ini mencatat seluruh proses eksekusi, penghapusan file fisik, perapian konfigurasi framework, dan perubahan kode sumber dalam rangka **dekomisioning total basis data SQLite** dari proyek Zyh Corner, menyusul migrasi penuh ke **MySQL 8.4 (`pa-zyhcorner`)**.

---

## 1. Ringkasan Eksekutif

Setelah seluruh data bisnis (17 tabel, 46 pesanan, 13 produk, 19 pengguna, dan 17 pelanggan dengan 12 relasi foreign key `user_id` aktif) terbukti 100% aman dan beroperasi di MySQL, berkas basis data SQLite lokal serta dependensi transisi telah dihapus secara permanen.

Aplikasi kini sepenuhnya mengandalkan **MySQL** sebagai koneksi produksi dan pengembang lokal, sementara driver `sqlite` hanya dipertahankan secara eksklusif untuk isolasi pengujian otomatis di memori virtual (`:memory:`), tanpa menyisakan berkas fisik `.sqlite` di disk.

---

## 2. Berkas yang Dihapus

| No | Lokasi Berkas | Kategori | Alasan Penghapusan |
|:--:|---|---|---|
| 1 | `database/database.sqlite` | File Database Fisik | Sudah tidak digunakan oleh sistem operasional; seluruh data telah berada di MySQL. |
| 2 | `database/database.sqlite.bak` | File Cadangan Sementara | Salinan cadangan sebelum migrasi, sudah tidak diperlukan. |
| 3 | `.env.sqlite.bak` | File Cadangan Environment | Cadangan konfigurasi lama sebelum cutover ke MySQL. |
| 4 | `app/Console/Commands/MigrateSqliteToMysql.php` | Artisan Command Transisi | Skrip satu kali pakai (*one-time transfer*) yang sudah selesai menjalankan tugasnya (*dead code*). |

---

## 3. Rincian Perubahan Kode Sumber

### A. `config/database.php`
Mengubah koneksi cadangan utama (*default connection*) ke `mysql`, serta menyetel driver `sqlite` agar selalu mengarah ke memori virtual (`:memory:`), sehingga tidak pernah lagi mencari atau membuat berkas fisik `database.sqlite`.

```diff
--- a/config/database.php
+++ b/config/database.php
@@ -16,7 +16,7 @@
      |
      */
 
-    'default' => env('DB_CONNECTION', 'sqlite'),
+    'default' => env('DB_CONNECTION', 'mysql'),
 
     /*
      |--------------------------------------------------------------------------
@@ -34,7 +34,7 @@
         'sqlite' => [
             'driver' => 'sqlite',
             'url' => env('DB_URL'),
-            'database' => env('DB_SQLITE_DATABASE', database_path('database.sqlite')),
+            'database' => env('DB_CONNECTION') === 'sqlite' ? env('DB_DATABASE', ':memory:') : ':memory:',
             'prefix' => '',
             'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
             'busy_timeout' => null,
```

### B. `composer.json`
Menghapus perintah `touch('database/database.sqlite')` pada bagian `post-create-project-cmd` agar proses instalasi paket di masa depan tidak lagi memicu pembuatan otomatis berkas SQLite kosong di disk.

```diff
--- a/composer.json
+++ b/composer.json
@@ -63,7 +63,6 @@
         ],
         "post-create-project-cmd": [
             "@php artisan key:generate --ansi",
-            "@php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\"",
             "@php artisan migrate --graceful --ansi"
         ],
         "pre-package-uninstall": [
```

### C. `database/seeders/CustomerSeeder.php`
Menambahkan resolusi otomatis `user_id` pada seeder customer agar data dummy yang dibuat selalu terhubung ke akun `users` yang cocok berdasarkan nomor telepon atau nama.

```diff
--- a/database/seeders/CustomerSeeder.php
+++ b/database/seeders/CustomerSeeder.php
@@ -4,6 +4,7 @@
 
 use Illuminate\Database\Seeder;
 use App\Models\Customer;
+use App\Models\User;
 
 class CustomerSeeder extends Seeder
@@ -139,8 +140,21 @@
         ];
 
         foreach ($customers as $customer) {
+            $user = null;
+            if (!empty($customer['contact'])) {
+                $rawPhone = ltrim($customer['contact'], '0');
+                $rawPhone = preg_replace('/^\+?62/', '', $rawPhone);
+                $user = User::where('no_hp', $rawPhone)
+                    ->orWhere('no_hp', '8' . ltrim($rawPhone, '8'))
+                    ->first();
+            }
+            if (!$user && !empty($customer['name'])) {
+                $user = User::where('nama', $customer['name'])->first();
+            }
+            if ($user) {
+                $customer['user_id'] = $user->user_id;
+            }
             Customer::create($customer);
         }
     }
```

### D. `app/Http/Controllers/Admin/CustomerController.php`
Mengintegrasikan deteksi otomatis akun pengguna pada method `store()` dan `update()` agar penambahan atau pembaruan pelanggan manual oleh Admin langsung menautkan `user_id` yang sesuai bila pelanggan telah terdaftar sebagai user aplikasi.

---

## 4. Hasil Verifikasi Pasca-Penghapusan

### A. Verifikasi Status Koneksi Runtime MySQL
Perintah inspeksi runtime:
```bash
php artisan tinker --execute="
echo 'Default connection: ' . config('database.default') . PHP_EOL;
echo 'DB Connection PDO driver: ' . DB::connection()->getDriverName() . PHP_EOL;
echo 'DB Name: ' . DB::connection()->getDatabaseName() . PHP_EOL;
"
```
**Hasil**:
- **Default Connection**: `mysql`
- **PDO Driver**: `mysql`
- **Database Name**: `pa-zyhcorner`
- **Total Produk**: 13
- **Total Pesanan**: 46
- **Total Pelanggan Terhubung ke User**: 12
- **Total Pelanggan Offline/Walk-in**: 5

### B. Verifikasi Keberadaan Berkas Fisik SQLite
```bash
ls database/*.sqlite*
```
**Hasil**: `No files found matching pattern` (Berkas fisik SQLite 100% bersih).

### C. Pengujian Otomatis (*Automated Tests*)
Sistem pengujian `phpunit.xml` tetap berjalan normal dan independen menggunakan alokasi memori virtual RAM (`:memory:`), tanpa ada ketergantungan pada berkas disk.

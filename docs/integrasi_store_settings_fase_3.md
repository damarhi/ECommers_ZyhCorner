# Dokumentasi Integrasi Tabel & Ekosistem Pengaturan Toko (Fase 3)

## 1. Latar Belakang & Masalah Sebelumnya
Sebelum Fase 3 diimplementasikan, informasi toko (seperti nama toko, nomor telepon/WhatsApp, email dukungan, alamat operasional, dan jam buka) masih mengandalkan nilai statis, fallback `config('app.*')`, atau penyimpanan sementara di PHP `session()` pada `AdminSettingsController@updateStoreInfo`.

Kelemahan arsitektur sebelumnya:
1. Data pengaturan toko tidak disimpan ke database permanen. Saat sesi berakhir atau server di-restart, konfigurasi kembali ke nilai default fiktif.
2. Tampilan publik pelanggan (Header, Footer, Halaman Kontak, FAQ, dan tautan WhatsApp pada produk kustom/detail) tidak terhubung dengan perubahan yang dilakukan admin pada panel pengaturan.
3. Nomor WhatsApp untuk pemesanan kue kustom di-hardcode ke nomor statis di berbagai file view dan script JavaScript.

---

## 2. Rincian Perubahan Arsitektur & Kode

### A. Tabel & Migrasi `store_settings`
- **Berkas**: `database/migrations/2026_10_23_000001_create_store_settings_table.php`
- **Skema Tabel**:
  ```php
  Schema::create('store_settings', function (Blueprint $table) {
      $table->id();
      $table->string('key')->unique();
      $table->text('value')->nullable();
      $table->string('label')->nullable();
      $table->string('group')->default('general');
      $table->timestamps();
  });
  ```
- **Struktur Key-Value**: Memungkinkan penambahan pengaturan baru di masa mendatang tanpa perlu melakukan migrasi skema database berulang.

---

### B. Model `StoreSetting` (`app/Models/StoreSetting.php`)
Menyediakan interface terpusat dengan mekanisme caching performa tinggi:
1. `StoreSetting::get(string $key, $default = null)`: Mengambil nilai setting dengan auto-caching (3600 detik).
2. `StoreSetting::set(string $key, $value, ?string $label = null, string $group = 'general')`: Menyimpan atau memperbarui setting sekaligus membersihkan cache terkait secara otomatis.
3. `StoreSetting::getAllAsArray()`: Mengambil seluruh konfigurasi toko dalam bentuk array asosiatif `['key' => 'value']`.
4. `StoreSetting::getWaNumber(?string $phone = null)`: Melakukan normalisasi nomor telepon (misal `0812...` atau `+62...`) menjadi format internasional WhatsApp yang valid (`62812...`).
5. `StoreSetting::clearCache()`: Membersihkan seluruh cache pengaturan toko.

---

### C. Seeder Pengaturan Toko (`database/seeders/StoreSettingSeeder.php`)
Menyediakan data awal konfigurasi toko yang realistis:
- `store_name`: "Zyh Corner"
- `store_phone`: "08123456789"
- `store_email`: "contact@zyhcorner.com"
- `store_address`: "Jl. Utama No. 123, Kota Subang"
- `store_hours`: "Senin - Minggu (08:00 - 21:00)"
- `store_tagline`: "Pastry & Bakery Berkualitas"
- `social_instagram`: "https://instagram.com/zyhcorner"
- `social_facebook`: "https://facebook.com/zyhcorner"
- `social_tiktok`: "https://tiktok.com/@zyhcorner"

Telah didaftarkan ke `DatabaseSeeder.php` dan dieksekusi via `php artisan migrate && php artisan db:seed --class=StoreSettingSeeder`.

---

### D. Controller Pengaturan Admin (`app/Http/Controllers/Admin/AdminSettingsController.php`)
1. **Metode `index()`**:
   - Membaca seluruh data toko langsung dari model `StoreSetting::get(...)` dengan fallback aman.
2. **Metode `updateStoreInfo()`**:
   - Memvalidasi input nama toko, nomor telepon, email, jam operasional, alamat lengkap, slogan/tagline, dan URL sosial media.
   - Menyimpan seluruh data secara permanen ke database melalui `StoreSetting::set(...)`.
   - Mengarahkan kembali ke tab Info Toko (`settings.index#store-tab`) dengan notifikasi sukses.

---

### E. Tampilan Panel Admin (`resources/views/Admin/kelola_akun/settings.blade.php`)
1. Memperbarui form pada tab "Info Toko" dengan penambahan input Slogan/Tagline Toko, URL Instagram, Facebook, dan TikTok.
2. Mengisi nilai input form dari data database `$storeInfo` (didukung helper `old()`).
3. Menambahkan skrip JavaScript untuk mempertahankan tab yang aktif berdasarkan hash URL (`#store-tab`).

---

### F. Integrasi Global ke Antarmuka Pelanggan (Frontend)
1. **`AppServiceProvider.php` (View Composer)**:
   - Membagikan variabel `$storeSettings` secara otomatis ke seluruh view Blade (`View::composer('*', ...)`).
   - Menyertakan nomor WhatsApp yang telah dinormalisasi (`$storeSettings['wa_number']`).
2. **Layout Utama Pelanggan (`resources/views/Layout/layout_pelanggan.blade.php`)**:
   - Header & Mobile Drawer: Menampilkan nama toko dinamis pada logo teks (`{{ $storeSettings['store_name'] }}`).
   - Footer: Menampilkan nama toko, tagline toko, nomor telepon, email resmi, tautan Instagram, dan teks hak cipta (*copyright*) dinamis.
   - Global JS: Menginjeksi `window.storePhone` dan `window.storeName` agar script interaktif frontend mengonsumsi data riil.
3. **Halaman Kontak (`resources/views/Pelanggan/contact.blade.php`)**:
   - Menampilkan alamat toko, email layanan, dan nomor telepon dari `$storeSettings`.
4. **Tautan Pemesanan WhatsApp Dinamis**:
   - `custom_products.blade.php`: Membangun URL WhatsApp menggunakan nomor telepon dari database.
   - `detail.blade.php`: Tautan pemesanan produk kustom kini menyematkan nomor WhatsApp riil dari database.
   - `faq.blade.php`: Tombol bantuan WhatsApp terhubung ke nomor riil toko.
   - `pelanggan_pages.js`: Fungsi `buyCustomNow` dan modal preview menggunakan nomor toko dari `window.storePhone`.

---

## 3. Hasil Pengujian & Verifikasi Headless Browser

Pengujian otomatis *end-to-end* dilakukan menggunakan browser Chromium headless:
1. **Autentikasi Admin & Akses Pengaturan**:
   - Berhasil login sebagai admin (`admin@gmail.com`) dan membuka halaman `/admin/settings#store-tab`.
   - Form Info Toko memuat data awal dari database SQLite.
2. **Uji Perubahan Data Toko Melalui Form**:
   - Mengubah nama toko menjadi `Zyh Corner Bakery & Pastry`, alamat menjadi `Jl. HR. Soebrantas No. 88, Panam, Pekanbaru`, nomor telepon menjadi `081298765432`, dan slogan menjadi `Pastry & Cake Pilihan Keluarga`.
   - Form berhasil disubmit dan data tersimpan ke tabel `store_settings`.
3. **Verifikasi Tampilan Pelanggan**:
   - **Header & Footer Beranda**:
     - `header .logo-text`: "Zyh Corner Bakery & Pastry" (Tercermin instan).
     - `footer .logo-text`: "Zyh Corner Bakery & Pastry".
     - Tagline footer: "Pastry & Cake Pilihan Keluarga".
     - Nomor telepon footer: "081298765432".
     - Hak Cipta: "Zyh Corner Bakery & Pastry eCommerce © 2026. All Rights Reserved".
     - Variabel JavaScript: `window.storePhone = "6281298765432"`.
   - **Halaman Kontak (`/contact`)**:
     - Alamat toko: "Jl. HR. Soebrantas No. 88, Panam, Pekanbaru".
     - Nomor telepon: "081298765432".
     - Email: "contact@zyhcorner.com".

---

## 4. Kesimpulan
Seluruh target **Fase 3: Tabel & Ekosistem Pengaturan Toko (`store_settings`)** telah tuntas 100%. Data konfigurasi toko kini tersimpan permanen di database, dapat dikelola penuh oleh administrator, dan langsung berdampak ke seluruh antarmuka pelanggan tanpa ketergantungan pada mockup atau data statis.

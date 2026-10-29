# Rencana Sinkronisasi Nomor Admin & Instagram ke Seluruh Antarmuka Pelanggan

Dokumen ini merinci rencana teknis untuk menyinkronkan nomor WhatsApp dan alamat Instagram yang terdaftar pada akun administrator ke seluruh antarmuka dan titik kontak pelanggan (katalog kue kustom, detail produk, pelacakan pesanan, riwayat, kontak, FAQ, dan footer).

---

## 1. Analisis Kebutuhan & Masalah

1. **Nomor WhatsApp**:
   - Di panel admin, nomor HP admin tersimpan di tabel `users` (`no_hp`) saat admin mengubah profil.
   - Pengaturan toko tersimpan di tabel `store_settings` (`store_phone`) saat admin mengubah Info Toko.
   - Saat ini, pembaruan di salah satu sisi tidak menyinkronkan sisi lainnya, dan jika `store_phone` tidak diisi, tautan pelanggan tidak otomatis mengambil nomor admin di tabel `users`.
   - Masih terdapat nilai fallback hardcode di beberapa view pelanggan (`6281298765432` pada pelacakan pesanan dan riwayat, serta `628123456789` pada produk kustom).
2. **Alamat Instagram**:
   - Alamat Instagram sudah tersimpan di `store_settings` (`social_instagram`) dan sudah tampil di footer (`layout_pelanggan.blade.php`).
   - Namun, halaman kontak (`contact.blade.php`) dan menu mobile drawer belum menampilkan tautan Instagram.
   - Format input admin (misal `@zyhcorner` atau username tanpa https) perlu dinormalisasi agar selalu menghasilkan URL valid.

---

## 2. Rincian Langkah Implementasi (Step-by-Step)

### Step 1: Logika Backend & Sinkronisasi Dua Arah Nomor Admin dan Instagram
- **Tujuan**: Memodifikasi `StoreSetting.php` dan `AdminSettingsController.php` agar nomor WhatsApp dan URL Instagram selalu terhubung secara dinamis dengan data admin.
- **Perubahan Kode**:
  - `StoreSetting.php`:
    - Tambahkan helper `getAdminPhone()` untuk mengambil `no_hp` milik user dengan `role = 'admin'`.
    - Perbarui `getWaNumber()` agar mengambil `store_phone` dari database, atau fallback ke `getAdminPhone()`, lalu membersihkan dan menormalisasi format awalan (`08...`, `+628...`, `8...`) menjadi format internasional WhatsApp (`628...`).
    - Tambahkan helper `getInstagramUrl()` yang menormalisasi format username atau URL menjadi URL Instagram valid (`https://instagram.com/...`).
  - `AdminSettingsController.php`:
    - Pada `updateProfile()`: Setelah memperbarui profil admin di tabel `users`, otomatis sinkronkan nomor HP ke tabel `store_settings` (`store_phone`).
    - Pada `updateStoreInfo()`: Setelah memperbarui info toko di `store_settings`, otomatis sinkronkan `store_phone` ke akun admin yang sedang login (`users.no_hp`).
    - Bersihkan cache pengaturan toko dengan `StoreSetting::clearCache()`.

### Step 2: Standardisasi Tampilan Pelanggan untuk WhatsApp dan Instagram
- **Tujuan**: Mengubah antarmuka pelanggan agar semua tautan WhatsApp dan Instagram mengonsumsi data dinamis admin tanpa hardcode.
- **Perubahan Kode**:
  - `resources/views/Pelanggan/order_detail.blade.php`: Ganti fallback hardcode `'6281298765432'` pada modal bantuan CS dengan `$storeSettings['wa_number'] ?? $storePhone`.
  - `resources/views/Pelanggan/order_history.blade.php`: Ganti fallback hardcode `'6281298765432'` dengan `$storeSettings['wa_number'] ?? $storePhone`.
  - `resources/views/Pelanggan/dashboard.blade.php`: Ganti fallback hardcode `'6281298765432'` dengan `$storeSettings['wa_number'] ?? $storePhone`.
  - `resources/views/Pelanggan/contact.blade.php`: Tambahkan seksi media sosial Instagram resmi di kartu informasi kontak.
  - `resources/views/Layout/layout_pelanggan.blade.php`:
    - Ganti nomor telepon statis pada Mobile Navigation Drawer menjadi dinamis mengonsumsi `$storeSettings['store_phone']` dan tautan `tel:+{{ $storeSettings['wa_number'] }}`.
    - Tambahkan tautan Instagram dinamis pada Mobile Navigation Drawer.
  - `resources/js/pelanggan_pages.js`: Pastikan helper pemesanan WhatsApp memanfaatkan `window.storePhone`.

### Step 3: Pengujian Otomatis Fitur Sinkronisasi Kontak Admin
- **Tujuan**: Menambahkan pengujian integrasi otomatis untuk memvalidasi sinkronisasi nomor admin dan tautan WhatsApp/Instagram.
- **Perubahan Kode**:
  - Buat berkas pengujian `tests/Feature/AdminContactSyncTest.php`.
  - Skenario pengujian:
    1. Verifikasi `StoreSetting::getWaNumber()` berhasil menormalisasi nomor admin dari berbagai format awalan (`08`, `+628`, `8`).
    2. Verifikasi pembaruan profil admin menyinkronkan nomor ke `store_settings`.
    3. Verifikasi pembaruan info toko menyinkronkan nomor ke `users.no_hp` admin.
    4. Verifikasi halaman pelanggan (`/custom-products`, `/contact`, `/order-detail/{id}`) menghasilkan URL WhatsApp dan Instagram yang sesuai dengan data admin.

### Step 4: Dokumentasi Hasil Implementasi
- **Tujuan**: Memperbarui dan menyusun dokumentasi arsitektur sinkronisasi kontak admin ke `docs/laporan_sinkronisasi_nomor_admin_dan_instagram.md`.

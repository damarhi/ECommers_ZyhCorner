# Laporan Implementasi: Sinkronisasi Nomor Admin & Instagram ke Seluruh Antarmuka Pelanggan

Dokumen ini merangkum penyelesaian implementasi sinkronisasi nomor WhatsApp dan tautan Instagram resmi administrator ke seluruh titik kontak dan antarmuka publik pelanggan pada sistem Zyh Corner.

---

## 1. Ringkasan Eksekutif

Sebelum pembaruan ini diimplementasikan:
1. Pembaruan nomor telepon di profil admin (`users.no_hp`) dan pengaturan toko (`store_settings.store_phone`) tidak saling terhubung, sehingga perubahan salah satunya tidak otomatis tercermin pada kontak toko.
2. Tautan WhatsApp pada antarmuka pelanggan menggunakan fallback nomor statis yang bervariasi (`6281298765432` pada modal pelacakan pesanan, `628123456789` pada produk kustom, dsb.).
3. Tautan Instagram resmi toko hanya tampil di footer dan belum hadir pada Halaman Kontak (`/contact`) maupun Menu Navigasi Mobile Drawer. Nomor telepon pada mobile drawer juga masih berupa teks statis (`(+62) 8123456789`).

Setelah pembaruan:
- **Sinkronisasi Dua Arah (*Bi-directional Sync*)**: Pembaruan nomor di Profil Admin otomatis memperbarui `store_settings.store_phone`, dan sebaliknya pembaruan di Info Toko otomatis memperbarui `no_hp` akun admin aktif.
- **Hierarki Resolusi Cerdas (*Smart Fallback Hierarchy*)**: Model `StoreSetting::getWaNumber()` memprioritaskan `store_settings.store_phone`, dan jika kosong otomatis mengambil `users.no_hp` dari user dengan peran `admin`.
- **Normalisasi Nomor WhatsApp**: Input awalan lokal seperti `08...`, internasional `+628...`, atau `8...` dinormalisasi secara otomatis menjadi format standar WhatsApp internasional (`628...`).
- **Normalisasi URL Instagram**: Input username `@zyhcorner`, `zyhcorner`, maupun URL lengkap dinormalisasi menjadi URL valid `https://instagram.com/zyhcorner`.
- **Tampilan Terpadu di Seluruh Frontend**: Seluruh hardcode dihapus. Halaman Kontak dan Mobile Drawer kini menampilkan nomor telepon dinamis dan tautan resmi Instagram.

---

## 2. Rincian Berkas yang Dimodifikasi

### A. Backend & Data Model
1. **`app/Models/StoreSetting.php`**:
   - `getAdminPhone()`: Mengambil nomor HP admin aktif pertama dari tabel `users`.
   - `getWaNumber(?string $phone = null)`: Resolusi pintar dari parameter $\rightarrow$ `store_settings` $\rightarrow$ akun `admin` $\rightarrow$ normalisasi awalan `0` / `8` / `+62` menjadi `62`.
   - `getInstagramUrl(?string $url = null)`: Normalisasi format username dan pemaksaan protokol HTTPS.
2. **`app/Providers/AppServiceProvider.php`**:
   - Menginjeksi `$storeSettings['wa_number']` dan `$storeSettings['social_instagram']` yang ter-normalisasi ke seluruh view Blade melalui `View::composer('*', ...)`.
3. **`app/Http/Controllers/Admin/AdminSettingsController.php`**:
   - `updateProfile()`: Menyinkronkan `no_hp` admin ke `store_settings.store_phone`.
   - `updateStoreInfo()`: Menyinkronkan `store_phone` ke profil admin (`users.no_hp`).
   - `index()`: Menggunakan fallback nomor admin jika `store_phone` belum diatur.

### B. Antarmuka Pelanggan (Views & JS)
1. **`resources/views/Pelanggan/order_detail.blade.php`**:
   - Tombol "Hubungi CS Toko" pada modal pelacakan Biteship kini menggunakan `$storeSettings['wa_number'] ?? ($storePhone ?? StoreSetting::getWaNumber())`.
2. **`resources/views/Pelanggan/order_history.blade.php`**:
   - Tautan bantuan WhatsApp pesanan menggunakan `$storeSettings['wa_number']`.
3. **`resources/views/Pelanggan/dashboard.blade.php`**:
   - Tautan bantuan WhatsApp pada riwayat pesanan dashboard menggunakan `$storeSettings['wa_number']`.
4. **`resources/views/Pelanggan/custom_products.blade.php` & `resources/views/Pelanggan/detail.blade.php`**:
   - Tombol pesanan WhatsApp produk kustom menggunakan nomor dinamis tanpa fallback hardcode.
5. **`resources/views/Pelanggan/faq.blade.php`**:
   - Tombol CTA "WhatsApp" menggunakan `$storeSettings['wa_number']`.
6. **`resources/views/Pelanggan/contact.blade.php`**:
   - Menambahkan kartu informasi **Instagram Kami** yang membuka tautan resmi akun Instagram admin dalam tab baru.
   - Tautan telepon menggunakan `tel:+{{ $storeSettings['wa_number'] }}`.
7. **`resources/views/Layout/layout_pelanggan.blade.php`**:
   - Menghubungkan nomor telepon dan Instagram dinamis pada Mobile Navigation Drawer.
   - Mengisi variabel JavaScript global `window.storePhone` dengan nomor dinamis ter-normalisasi.

---

## 3. Hasil Pengujian Otomatis

Pengujian integrasi baru dibuat di `tests/Feature/AdminContactSyncTest.php`:
1. `test_wa_number_normalizes_various_formats`: **LULUS** (memverifikasi awalan `08`, `+628`, `628`, `81`, dan format strip).
2. `test_wa_number_falls_back_to_registered_admin_phone`: **LULUS** (memverifikasi fallback otomatis ke tabel `users.no_hp` admin).
3. `test_instagram_url_normalizes_correctly`: **LULUS** (memverifikasi normalisasi handle `@` dan HTTP ke HTTPS).
4. `test_updating_admin_profile_syncs_phone_to_store_settings`: **LULUS** (memverifikasi sinkronisasi profil $\rightarrow$ pengaturan toko).
5. `test_updating_store_info_syncs_phone_to_admin_profile`: **LULUS** (memverifikasi sinkronisasi pengaturan toko $\rightarrow$ profil admin).
6. `test_customer_views_render_admin_phone_and_instagram`: **LULUS** (memverifikasi halaman kontak, FAQ, dan layout merender nomor dan URL Instagram admin).

**Hasil Total Pengujian Seluruh Sistem**:
- **137 Tests Passed**
- **688 Assertions Passed**
- **0 Failures**

# Laporan Audit & Distribusi Baris Kode Views dan Controllers
**Aplikasi**: E-Commerce Zyh Corner (Laravel)  
**Tanggal Audit**: 2026-10-31  
**Lingkup Analisis**: Seluruh berkas pada `app/Http/Controllers` (22 file) dan `resources/views` (51 file)  
**Total Berkas**: 73 file  
**Total Baris Kode**: 29.038 baris  
**Berkas CodeTour**: `.tours/architecture-views-and-controllers.tour`

---

## 1. Ringkasan Eksekutif

Audit dilakukan untuk memetakan volume kode, persebaran tanggung jawab, serta dominasi teknologi (PHP, CSS, JavaScript, HTML/Blade) pada layer **HTTP Controller** dan layer **Blade Views**.

| Komponen Layer | Jumlah File | Total Baris Kode | Dominasi Bahasa | Rata-rata Baris/File |
| :--- | :---: | :---: | :--- | :---: |
| **HTTP Controllers** (`app/Http/Controllers`) | 22 | 5.346 | **PHP (100%)** | 243 baris |
| **Blade Views** (`resources/views`) | 51 | 23.692 | **Bervariasi (HTML, CSS, JS, PHP)** | 464 baris |
| **Total Keseluruhan** | **73** | **29.038** | — | **397 baris** |

---

## 2. Rincian File Controller (`app/Http/Controllers`)

Seluruh file controller merupakan script PHP murni berorientasi objek yang menangani routing HTTP, validasi form, query Eloquent ORM, serta otentikasi.

| No | File Controller | Total Baris | Baris PHP (%) | Dominasi | Peran Utama Arsitektur |
| :---: | :--- | :---: | :---: | :---: | :--- |
| 1 | `Admin/AdminDashboardController.php` | 65 | 54 (83,1%) | **PHP** | Agregasi data KPI dashboard admin (omset, produk, order) |
| 2 | `Admin/AdminSettingsController.php` | 147 | 100 (68,0%) | **PHP** | Pengaturan profil admin, info kontak WhatsApp & toko |
| 3 | `Admin/BannerController.php` | 134 | 106 (79,1%) | **PHP** | Manajemen CRUD, toggle status, dan upload gambar banner |
| 4 | `Admin/CassierController.php` | 142 | 121 (85,2%) | **PHP** | Pengelolaan akun staf kasir & sinkronisasi user table |
| 5 | `Admin/ContactMessageController.php` | 97 | 77 (79,4%) | **PHP** | Inbox pesan customer & update status tindak lanjut |
| 6 | `Admin/CustomerController.php` | 138 | 115 (83,3%) | **PHP** | Data master pelanggan & fitur proteksi blokir akun |
| 7 | `Admin/DiskonController.php` | 126 | 101 (80,2%) | **PHP** | Pembuatan dan pengaturan event diskon multi-produk |
| 8 | `Admin/ExpiredProductController.php` | 87 | 69 (79,3%) | **PHP** | Monitoring & sinkronisasi produk mendekati kadaluarsa |
| 9 | `Admin/KategoriController.php` | 103 | 83 (80,6%) | **PHP** | CRUD kategori master produk kue/pastry |
| 10 | `Admin/PesananController.php` | **1.014** | **900 (88,8%)** | **PHP** | **Controller terbesar**: Order lifecycle, invoice, live AJAX search, bukti bayar |
| 11 | `Admin/PosController.php` | 194 | 161 (83,0%) | **PHP** | Terminal Point of Sale (POS) offline kasir & restock |
| 12 | `Admin/ProdukController.php` | 287 | 245 (85,4%) | **PHP** | CRUD master produk, toggle signature & tanggal expired ISO |
| 13 | `Admin/ReturController.php` | 283 | 241 (85,2%) | **PHP** | Verifikasi klaim retur barang & status pengembalian dana |
| 14 | `AuthController.php` | **519** | **437 (84,2%)** | **PHP** | Otentikasi multi-role, Google OAuth 2.0 & OTP email 6-digit |
| 15 | `Controller.php` | 8 | 6 (75,0%) | **PHP** | Base abstract class controller framework |
| 16 | `Pelanggan/CartCheckoutController.php` | 318 | 287 (90,3%) | **PHP** | Validasi stok keranjang, custom teks kue & ongkir Biteship |
| 17 | `Pelanggan/CustomerAddressController.php` | 204 | 177 (86,8%) | **PHP** | Manajemen buku alamat pelanggan & pin koordinat GPS |
| 18 | `Pelanggan/CustomerOrderController.php` | 324 | 283 (87,3%) | **PHP** | Riwayat pesanan pelanggan & pembatalan transaksi |
| 19 | `Pelanggan/CustomerReturnController.php` | 204 | 174 (85,3%) | **PHP** | Pengajuan klaim retur pelanggan & upload foto kendala |
| 20 | `Pelanggan/CustomerSettingsController.php` | 192 | 168 (87,5%) | **PHP** | Pengaturan profil akun pelanggan & upload avatar |
| 21 | `Pelanggan/PelangganController.php` | **455** | **394 (86,6%)** | **PHP** | Storefront katalog: etalase home, shop, dan detail produk |
| 22 | `Pelanggan/PembayaranController.php` | 305 | 265 (86,9%) | **PHP** | Webhook gateway Midtrans Snap & transfer bank manual |

---

## 3. Rincian File View (`resources/views`)

Klasifikasi per baris kode pada file Blade:
- **PHP/Blade**: Direktif Blade (`@if`, `@foreach`, `@extends`, `@section`), ekspresi interpolasi (`{ }`, `{!! !!}`), dan blok `@php`.
- **CSS**: Baris kode di dalam blok `<style>...</style>`.
- **JS**: Baris kode di dalam blok `<script>...</script>`.
- **HTML**: Tag markup struktur halaman (`<div>`, `<table>`, `<form>`, teks statis).

### A. Storefront Pelanggan (`resources/views/Pelanggan/`)

| No | File View (Blade) | Total Baris | PHP/Blade | CSS | JS | HTML | Dominasi Bahasa & Penjelasan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| 1 | `order_detail.blade.php` | **3.400** | 309 (9,1%) | **1.615 (47,5%)** | 621 (18,3%) | 613 (18,0%) | **CSS & JS**: Halaman terpanjang dalam sistem. Berisi layout kartu simetris, stepper pengiriman 5 tahap, live tracker waybill Biteship, dan modal bukti bayar. |
| 2 | `home.blade.php` | **1.771** | 364 (20,6%) | **1.060 (59,9%)** | 1 (0,1%) | 305 (17,2%) | **CSS**: Mengatur layout grid 4-kolom beranda (Zyh Signatures, Hot Deals, Best Seller), slider hero banner, dan responsive breakpoint. |
| 3 | `order_history.blade.php` | **1.204** | 184 (15,3%) | 38 (3,2%) | **625 (51,9%)** | 280 (23,3%) | **JavaScript**: Mengelola tabs filter status pesanan, modal pembatalan mandiri, dan asynchronous tracking payload. |
| 4 | `checkout.blade.php` | **1.165** | 48 (4,1%) | 72 (6,2%) | **612 (52,5%)** | 326 (28,0%) | **JavaScript**: Peta interaktif Leaflet OpenStreetMap, koordinat GPS, autocomplete kurir Biteship, dan switch metode bayar dinamis. |
| 5 | `order_success.blade.php` | **873** | 87 (10,0%) | 0 (0,0%) | **492 (56,4%)** | 234 (26,8%) | **JavaScript**: Efek selebrasi canvas confetti, timer countdown pengalihan halaman, dan polling status konfirmasi kasir. |
| 6 | `dashboard.blade.php` | 841 | 147 (17,5%) | 0 (0,0%) | 85 (10,1%) | **546 (64,9%)** | **HTML/Blade**: Struktur kartu profil ringkasan, widget transaksi terkini, dan modal buku alamat tersimpan. |
| 7 | `settings.blade.php` | 670 | 83 (12,4%) | 0 (0,0%) | 20 (3,0%) | **510 (76,1%)** | **HTML/Blade**: Antarmuka pengaturan akun multi-tab (profil, alamat utama, ganti sandi). |
| 8 | `detail.blade.php` | 420 | **169 (40,2%)** | 13 (3,1%) | 45 (10,7%) | 162 (38,6%) | **PHP/Blade**: Logika kondisional ketersediaan stok, badge mahkota ZYH Signature, baris expired seragam, dan tab spesifikasi. |
| 9 | `return_detail.blade.php` | 317 | 115 (36,3%) | 0 (0,0%) | 0 (0,0%) | **180 (56,8%)** | **HTML/Blade**: Timeline status pengajuan retur barang, inspeksi foto kendala, dan tanggapan admin. |
| 10 | `shop.blade.php` | 266 | **125 (47,0%)** | 0 (0,0%) | 22 (8,3%) | 100 (37,6%) | **PHP/Blade**: Grid etalase katalog, filter kategori, sorting prioritas stok, dan pagination. |
| 11 | `faq.blade.php` | 263 | 10 (3,8%) | 75 (28,5%) | 61 (23,2%) | **94 (35,7%)** | **CSS/HTML**: Accordion interaktif pertanyaan yang sering diajukan pelanggan. |
| 12 | `custom_products.blade.php` | 244 | **109 (44,7%)** | 0 (0,0%) | 22 (9,0%) | 96 (39,3%) | **PHP/Blade**: Etalase produk kustom pre-order dengan direct link WhatsApp generator. |
| 13 | `contact.blade.php` | 227 | 17 (7,5%) | 3 (1,3%) | 88 (38,8%) | **90 (39,6%)** | **HTML / JS**: Form pesan bantuan pelanggan dengan handling AJAX submission. |
| 14 | `about.blade.php` | 144 | 10 (6,9%) | 0 (0,0%) | 21 (14,6%) | **99 (68,8%)** | **HTML**: Profil narasi cerita toko Zyh Corner dan komitmen kualitas bahan. |
| 15 | `return_order.blade.php` | 125 | 12 (9,6%) | 0 (0,0%) | 9 (7,2%) | **80 (64,0%)** | **HTML/Blade**: Formulir input komplain retur barang pesanan. |
| 16 | `partials/multi_banner_grid.blade.php` | 109 | 51 (46,8%) | 0 (0,0%) | 0 (0,0%) | **54 (49,5%)** | **HTML / PHP**: Partial view render grid promo banner beranda. |
| 17 | `userChart.blade.php` | 22 | 6 (27,3%) | 0 (0,0%) | 0 (0,0%) | **7 (31,8%)** | **HTML / PHP**: Komponen visualisasi mini statistik. |

---

### B. Panel Admin & Kasir (`resources/views/Admin/`)

| No | File View (Blade) | Total Baris | PHP/Blade | CSS | JS | HTML | Dominasi Bahasa & Penjelasan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| 18 | `kelola_produk/discounts.blade.php` | 941 | 96 (10,2%) | 0 (0,0%) | 313 (33,3%) | **468 (49,7%)** | **HTML / JS**: Manajemen multi-select produk diskon, toggle aktif, dan modal konfirmasi. |
| 19 | `kelola_carousel/banners.blade.php` | 917 | 153 (16,7%) | 19 (2,1%) | 149 (16,2%) | **538 (58,7%)** | **HTML / Blade**: Grid preview multi-slot banner dan modal upload gambar terisolasi. |
| 20 | `kelola_penjualan/sales_report.blade.php` | 786 | 119 (15,1%) | **332 (42,2%)** | 50 (6,4%) | 218 (27,7%) | **CSS**: Styling tabel print-ready laporan keuangan, kartu omset kotor/net, dan filter tanggal. |
| 21 | `kelola_penjualan/returs.blade.php` | 781 | 139 (17,8%) | 0 (0,0%) | 134 (17,2%) | **462 (59,2%)** | **HTML / Blade**: Tabel pemrosesan retur, filter status tabs, dan modal tindak lanjut admin. |
| 22 | `kelola_penjualan/orders.blade.php` | 573 | 74 (12,9%) | 65 (11,3%) | 185 (32,3%) | **214 (37,3%)** | **HTML / JS**: Tabel master order, modal resi biteship, dan tombol quick confirm. |
| 23 | `kelola_penjualan/pos.blade.php` | 566 | 48 (8,5%) | 44 (7,8%) | **223 (39,4%)** | 215 (38,0%) | **JavaScript**: Antarmuka kasir toko offline, add to cart realtime, dan modal restock produk. |
| 24 | `pesan_kontak/index.blade.php` | 496 | 76 (15,3%) | 0 (0,0%) | 102 (20,6%) | **287 (57,9%)** | **HTML / JS**: Manajemen inbox pesan pelanggan dan modal balasan email. |
| 25 | `kelola_produk/expired_products.blade.php` | 418 | 63 (15,1%) | 0 (0,0%) | 71 (17,0%) | **262 (62,7%)** | **HTML / Blade**: Monitoring produk kadaluarsa dan riwayat penghapusan. |
| 26 | `kelola_penjualan/pos_checkout.blade.php` | 397 | 9 (2,3%) | 0 (0,0%) | **277 (69,8%)** | 79 (19,9%) | **JavaScript**: Kalkulasi kembalian tunai POS, diskon item, dan generate receipt print. |
| 27 | `kelola_produk/products_form.blade.php` | 389 | 54 (13,9%) | 0 (0,0%) | 125 (32,1%) | **181 (46,5%)** | **HTML / JS**: Formulir produk dengan toggle signature dan format standar ISO Y-m-d expired. |
| 28 | `kelola_akun/customers.blade.php` | 342 | 49 (14,3%) | 0 (0,0%) | 65 (19,0%) | **204 (59,6%)** | **HTML / JS**: Master data pelanggan dan modal status blokir akses akun. |
| 29 | `kelola_akun/cassiers.blade.php` | 323 | 35 (10,8%) | 5 (1,5%) | 59 (18,3%) | **204 (63,2%)** | **HTML / JS**: Manajemen akun staf kasir dan perizinan akses sistem. |
| 30 | `dashboard/dashboard.blade.php` | 321 | 36 (11,2%) | 0 (0,0%) | 111 (34,6%) | **165 (51,4%)** | **HTML / JS**: Render grafik penjualan bulanan menggunakan library Chart.js. |
| 31 | `kelola_produk/products.blade.php` | 311 | 57 (18,3%) | 0 (0,0%) | 65 (20,9%) | **172 (55,3%)** | **HTML / JS**: Tabel master katalog etalase, status badge signature, dan stok. |
| 32 | `kelola_produk/custom_products.blade.php` | 294 | 48 (16,3%) | 0 (0,0%) | 65 (22,1%) | **164 (55,8%)** | **HTML / JS**: Tabel master produk kustom pemesanan khusus. |
| 33 | `kelola_akun/settings.blade.php` | 266 | 39 (14,7%) | 0 (0,0%) | 23 (8,6%) | **181 (68,0%)** | **HTML / Blade**: Form pengaturan rekening toko, nomor telepon WhatsApp, dan alamat fisik. |
| 34 | `kelola_penjualan/order_detail_modal.blade.php` | 254 | 76 (29,9%) | 0 (0,0%) | 0 (0,0%) | **162 (63,8%)** | **HTML / Blade**: Modal ringkasan data rincian pesanan pelanggan untuk admin. |
| 35 | `kelola_produk/categories.blade.php` | 245 | 33 (13,5%) | 5 (2,0%) | 59 (24,1%) | **133 (54,3%)** | **HTML / JS**: Tabel master kategori kue dan modal tambah kategori. |
| 36 | `kelola_penjualan/verify_proof_modal.blade.php` | 225 | 36 (16,0%) | 0 (0,0%) | 75 (33,3%) | **101 (44,9%)** | **HTML / JS**: Modal verifikasi transfer bank, zoom foto bukti bayar, dan form alasan tolak. |
| 37 | `kelola_penjualan/partials/all_sales_table.blade.php` | 139 | 57 (41,0%) | 0 (0,0%) | 0 (0,0%) | **80 (57,6%)** | **HTML / Blade**: Partial view baris tabel yang dirender ulang saat live AJAX search berjalan. |
| 38 | `kelola_penjualan/invoice.blade.php` | 127 | 31 (24,4%) | 0 (0,0%) | 7 (5,5%) | **82 (64,6%)** | **HTML / Blade**: Halaman cetak invoice / nota transaksi resmi toko. |
| 39 | `kelola_penjualan/all_sales.blade.php` | 115 | 13 (11,3%) | 0 (0,0%) | 0 (0,0%) | **93 (80,9%)** | **HTML / Blade**: Shell container live AJAX instant search master seluruh penjualan. |

---

### C. Master Layout (`resources/views/Layout/`)

| No | File View (Blade) | Total Baris | PHP/Blade | CSS | JS | HTML | Dominasi Bahasa & Penjelasan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| 40 | `layout_pelanggan.blade.php` | 529 | 91 (17,2%) | 34 (6,4%) | 103 (19,5%) | **262 (49,5%)** | **HTML / JS**: Header navigasi, cart drawer, modal Quick View produk, dan live search bar. |
| 41 | `layout_pAdmin.blade.php` | 496 | 94 (19,0%) | 0 (0,0%) | 127 (25,6%) | **250 (50,4%)** | **HTML / JS**: Sidebar admin/kasir, header notifikasi, dan skrip live instant search listener. |

---

### D. Otentikasi Pengguna (`resources/views/Auth/`)

| No | File View (Blade) | Total Baris | PHP/Blade | CSS | JS | HTML | Dominasi Bahasa & Penjelasan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| 42 | `google_verify.blade.php` | 214 | 12 (5,6%) | **81 (37,9%)** | 73 (34,1%) | 22 (10,3%) | **CSS**: Verifikasi nomor handphone pendaftaran Google dengan styling kartu terisolasi. |
| 43 | `register_verify.blade.php` | 214 | 12 (5,6%) | **81 (37,9%)** | 73 (34,1%) | 22 (10,3%) | **CSS**: Halaman input 6-digit box OTP email dengan auto-focus script. |
| 44 | `register.blade.php` | 135 | 18 (13,3%) | **50 (37,0%)** | 14 (10,4%) | 34 (25,2%) | **CSS**: Halaman registrasi akun baru pelanggan dan validasi visual. |
| 45 | `login.blade.php` | 126 | 14 (11,1%) | **50 (39,7%)** | 18 (14,3%) | 30 (23,8%) | **CSS**: Halaman login multi-role dengan tombol Google OAuth 2.0. |

---

### E. Template Email Notifikasi (`resources/views/emails/`) & Halaman Error (`errors/`)

| No | File View (Blade) | Total Baris | PHP/Blade | CSS | JS | HTML | Dominasi Bahasa & Penjelasan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| 46 | `errors/403.blade.php` | 227 | 29 (12,8%) | **139 (61,2%)** | 0 (0,0%) | 30 (13,2%) | **CSS**: Tampilan larangan hak akses peran dengan styling minimalis modern. |
| 47 | `emails/pesan_kontak_baru_admin.blade.php` | 179 | 7 (3,9%) | **120 (67,0%)** | 0 (0,0%) | 48 (26,8%) | **CSS**: Email template responsive inline styling untuk notifikasi pesan masuk admin. |
| 48 | `emails/pesanan_baru_kasir.blade.php` | 93 | 15 (16,1%) | 19 (20,4%) | 0 (0,0%) | **55 (59,1%)** | **HTML / CSS**: Email pemberitahuan order masuk untuk tim kasir. |
| 49 | `emails/retur_produk_kasir.blade.php` | 73 | 10 (13,7%) | 15 (20,5%) | 0 (0,0%) | **45 (61,6%)** | **HTML / CSS**: Email notifikasi pengajuan komplain retur pelanggan. |
| 50 | `emails/produk_kadaluarsa_kasir.blade.php` | 67 | 7 (10,4%) | 15 (22,4%) | 0 (0,0%) | **42 (62,7%)** | **HTML / CSS**: Email peringatan berkala stok kadaluarsa. |
| 51 | `emails/otp.blade.php` | 52 | 1 (1,9%) | 0 (0,0%) | 0 (0,0%) | **46 (88,5%)** | **HTML**: Email pengiriman kode OTP 6-digit. |

---

## 4. Analisis Mendalam & Peluang Restrukturisasi

### A. Kasus God Controller: `Admin/PesananController.php` (1.014 Baris)
File ini memiliki ukuran 4x lipat rata-rata controller lainnya. Pemetaan tanggung jawabnya:
1. **CRUD & Lifecycle Pesanan**: `index()`, `update()`, `destroy()`, `quickConfirm()`, `printInvoice()` (~180 baris).
2. **Verifikasi Bukti Bayar Bank**: `confirmProof()`, `rejectProof()` (~80 baris).
3. **Pencarian & Laporan Penjualan**: `allSales()`, `salesReport()` (~290 baris).
4. **Generator Export File**: `exportAllSales()`, `exportOrders()` (~465 baris).

**Akar Penyebab Volume**:
- Metode `exportAllSales()` (430 baris) dan `salesReport()` (226 baris) mencakup **65% isi file**.
- Terdapat duplikasi logika query filter (~180 baris) antara `salesReport()` dan `exportAllSales()`.

**Rekomendasi Pemecahan (*Deep Module / Service Layer*)**:
Mengekstrak dua service kelas terdedikasi:
- `app/Services/SalesReportService.php` (menangani filter query & kalkulasi KPI omset/diskon/retur).
- `app/Services/SalesExportService.php` (menangani perakitan file CSV/TSV & Google Sheets redirect).
> **Dampak**: `PesananController.php` menyusut dari **1.014 baris menjadi ~150–180 baris** tanpa mengubah satupun rute di `routes/web.php` dan seluruh automated test suite tetap lulus 100%.

### B. Pola Embedded CSS & JS Masif pada Blade Views
Beberapa file view memiliki proporsi CSS atau JS yang melampaui 50% dari keseluruhan baris:
- **Didominasi CSS**:
  - `Pelanggan/order_detail.blade.php` (1.615 baris CSS / 47,5%)
  - `Pelanggan/home.blade.php` (1.060 baris CSS / 59,9%)
  - `Admin/kelola_penjualan/sales_report.blade.php` (332 baris CSS / 42,2%)
  *Tujuan Desain Saat Ini*: Menjamin performa *self-contained* dan mengeliminasi *Cumulative Layout Shift (CLS)* tanpa bergantung pada file bundler eksternal terpisah.
- **Didominasi JavaScript**:
  - `Pelanggan/order_history.blade.php` (625 baris JS / 51,9%)
  - `Pelanggan/checkout.blade.php` (612 baris JS / 52,5%)
  - `Pelanggan/order_success.blade.php` (492 baris JS / 56,4%)
  - `Admin/kelola_penjualan/pos_checkout.blade.php` (277 baris JS / 69,8%)
  *Tujuan Desain Saat Ini*: Menggerakkan fungsionalitas asinkron dinamis seperti Leaflet Map GPS geocoding, autocomplete Biteship, polling status transaksi, dan POS scanner.

---

## 5. Walkthrough CodeTour Terpandu

Untuk menelusuri secara langsung interaksi antara controller dan view di atas dalam editor VS Code / IDE:
Buka berkas: **`.tours/architecture-views-and-controllers.tour`**
Tour ini mencakup 10 titik penelusuran arsitektur penting:
1. `app/Http/Controllers/Controller.php` (Base Controller Foundation)
2. `app/Http/Controllers/Pelanggan/PelangganController.php` (Katalog Etalase)
3. `resources/views/Pelanggan/home.blade.php` (CSS-Dominated Styling)
4. `app/Http/Controllers/Pelanggan/CartCheckoutController.php` (Checkout Logic & Biteship)
5. `resources/views/Pelanggan/checkout.blade.php` (JS-Dominated Interactivity & Leaflet Map)
6. `app/Http/Controllers/Pelanggan/PembayaranController.php` (Gateway Midtrans & Bank Proof)
7. `resources/views/Pelanggan/order_detail.blade.php` (Order Detail & Tracking Stepper)
8. `app/Http/Controllers/Admin/PesananController.php` (Admin Orders & POS Central Dispatch)
9. `resources/views/Admin/kelola_penjualan/pos.blade.php` (POS Cashier Realtime UI)
10. `app/Http/Controllers/AuthController.php` (Authentication & Security Engine)

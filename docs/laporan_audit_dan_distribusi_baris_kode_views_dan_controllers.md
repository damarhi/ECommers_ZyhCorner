# Laporan Audit & Distribusi Baris Kode Views dan Controllers (Terbaru)

**Aplikasi**: E-Commerce Zyh Corner (Laravel)  
**Tanggal Audit Terbaru**: 2026-11-06  
**Lingkup Analisis**: Seluruh berkas pada `app/Http/Controllers` (25 file) dan `resources/views` (55 file)  
**Total Berkas**: 80 file  
**Total Baris Kode**: 21.952 baris *(turun 7.086 baris / -24,4% dibanding audit awal 29.038 baris)*  

---

## 1. Ringkasan Eksekutif

Audit ulang ini memetakan volume baris kode terkini, distribusi bahasa pemrograman/markup (**HTML**, **CSS**, **JavaScript**, **PHP/Blade**), serta dominasi teknologi pada setiap berkas di dalam `app/Http/Controllers` dan `resources/views` pasca-refaktorisasi modular (pemecahan *God Controller*, ekstraksi aset CSS/JS ke `public/` & `resources/`, pembuatan komponen Blade reusable, serta optimasi tata letak responsif *mobile*).

### A. Ikhtisar Volume Per Layer

| Komponen Layer | Jumlah File (Awal → Kini) | Total Baris (Awal → Kini) | Selisih Baris | Dominasi Bahasa Utama | Rata-rata Baris/File |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **HTTP Controllers** (`app/Http/Controllers`) | 22 → **25 file** | 5.346 → **5.594 baris** | +248 baris (+4,6%) | **PHP (100% di 25 file)** | **224 baris** *(turun dari 243)* |
| **Blade Views** (`resources/views`) | 51 → **55 file** | 23.692 → **16.358 baris** | **-7.334 baris (-31,0%)** | **HTML (57,0% total baris; dominan di 49/55 file)** | **297 baris** *(turun dari 464)* |
| **Total Keseluruhan** | 73 → **80 file** | 29.038 → **21.952 baris** | **-7.086 baris (-24,4%)** | **HTML & PHP** | **274 baris** *(turun dari 397)* |

### B. Komposisi Bahasa Agregat pada `resources/views` (55 File / 16.358 Baris)

| Kategori Bahasa / Baris | Total Baris | Persentase (%) | Jumlah File yang Didominasi | Keterangan |
| :--- | :---: | :---: | :---: | :--- |
| **HTML (Markup Struktur)** | **9.317** | **57,0%** | **49 file** *(termasuk 2 imbang)* | Struktur DOM utama, tabel, kartu, form, dan komponen UI. |
| **PHP / Blade Directives** | **3.816** | **23,3%** | **2 file** *(1 murni, 1 imbang HTML)* | Direktif `@if`, `@foreach`, `@extends`, `@php`, dan interpolasi `{{ }}`. |
| **JavaScript (Inline `<script>`)** | **1.911** | **11,7%** | **1 file** | Logika interaktif halaman yang masih tertanam pada blok `<script>`. |
| **CSS (Inline `<style>`)** | **224** | **1,4%** | **3 file** *(2 murni, 1 imbang HTML)* | Styling spesifik pada blok `<style>` yang belum dipindah ke file eksternal. |
| **Baris Kosong (Blank/Formatting)** | **1.090** | **6,7%** | — | Pemisah antar blok kode untuk keterbacaan (*readability*). |
| **Total `resources/views`** | **16.358** | **100,0%** | **55 file** | — |

---

## 2. Rincian Lengkap File Controller (`app/Http/Controllers` — 25 File)

Seluruh **25 file controller** didominasi mutlak oleh **PHP (100%)**. Dari total **5.594 baris**, terdapat **4.823 baris kode/komentar PHP aktif (86,2%)** dan **771 baris kosong pemisah (13,8%)**.

> **Catatan Perubahan Arsitektur**: `Admin/PesananController.php` yang sebelumnya berukuran **1.014 baris** telah dipecah menjadi 4 controller spesialis: `PesananController.php` (208 baris), `LaporanController.php` (314 baris), `EksporController.php` (560 baris), dan `BuktiBayarController.php` (98 baris).

| No | File Controller (`app/Http/Controllers/`) | Total Baris | Baris PHP Non-Kosong (%) | Baris PHP Murni (Tanpa Komentar) | Baris Kosong | Dominasi | Peran Utama Arsitektur |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| 1 | `Admin/EksporController.php` *(Baru)* | **560** | 512 (91,4%) | 452 (80,7%) | 48 | **PHP** | Generator ekspor penjualan (XML `.xls`, TSV UTF-8, Google Sheets) & CSV pesanan |
| 2 | `AuthController.php` | **519** | 437 (84,2%) | 393 (75,7%) | 82 | **PHP** | Otentikasi multi-role, Google OAuth 2.0 & verifikasi OTP email 6-digit |
| 3 | `Pelanggan/PelangganController.php` | **481** | 418 (86,9%) | 396 (82,3%) | 63 | **PHP** | Storefront katalog pelanggan: beranda, filter katalog shop, detail produk, FAQ |
| 4 | `Pelanggan/CustomerOrderController.php` | **324** | 283 (87,3%) | 261 (80,6%) | 41 | **PHP** | Riwayat pesanan pelanggan, detail pesanan, pelacakan Biteship & pembatalan |
| 5 | `Pelanggan/CartCheckoutController.php` | **318** | 287 (90,3%) | 262 (82,4%) | 31 | **PHP** | Validasi stok keranjang, teks kustom kue & kalkulasi ongkir Biteship |
| 6 | `Admin/LaporanController.php` *(Baru)* | **314** | 282 (89,8%) | 255 (81,2%) | 32 | **PHP** | Master seluruh penjualan, live AJAX search, dan laporan analitik keuangan |
| 7 | `Pelanggan/PembayaranController.php` | **305** | 265 (86,9%) | 233 (76,4%) | 40 | **PHP** | Integrasi pembayaran Midtrans Snap & unggah bukti transfer bank manual |
| 8 | `Admin/ProdukController.php` | **297** | 255 (85,9%) | 255 (85,9%) | 42 | **PHP** | CRUD master produk, produk kustom, toggle signature & stok kadaluarsa |
| 9 | `Admin/ReturController.php` | **283** | 241 (85,2%) | 222 (78,4%) | 42 | **PHP** | Verifikasi klaim retur barang pelanggan & pembaruan status pengembalian |
| 10 | `Admin/PesananController.php` *(Refaktor)* | **208** | 178 (85,6%) | 134 (64,4%) | 30 | **PHP** | Siklus hidup pesanan online, cetak invoice, konfirmasi cepat dapur & bulk delete |
| 11 | `Pelanggan/CustomerAddressController.php` | **204** | 177 (86,8%) | 157 (77,0%) | 27 | **PHP** | Manajemen buku alamat pelanggan, alamat utama & koordinat GPS peta |
| 12 | `Pelanggan/CustomerReturnController.php` | **204** | 174 (85,3%) | 149 (73,0%) | 30 | **PHP** | Pengajuan klaim retur pesanan oleh pelanggan & upload bukti foto/video |
| 13 | `Admin/PosController.php` | **194** | 161 (83,0%) | 157 (80,9%) | 33 | **PHP** | Terminal Point of Sale (POS) kasir toko offline, checkout tunai & restock |
| 14 | `Pelanggan/CustomerSettingsController.php` | **192** | 168 (87,5%) | 158 (82,3%) | 24 | **PHP** | Pengaturan profil akun pelanggan, unggah avatar, alamat & ganti password |
| 15 | `Admin/CustomerController.php` | **184** | 159 (86,4%) | 159 (86,4%) | 25 | **PHP** | Master data pelanggan, sinkronisasi akun & fitur proteksi blokir akses |
| 16 | `Admin/AdminSettingsController.php` | **147** | 129 (87,8%) | 125 (85,0%) | 18 | **PHP** | Pengaturan profil admin, informasi kontak toko, sosial media & rekening bank |
| 17 | `Admin/CassierController.php` | **142** | 121 (85,2%) | 121 (85,2%) | 21 | **PHP** | Pengelolaan akun staf kasir, perizinan akses & bulk delete |
| 18 | `Admin/BannerController.php` | **134** | 106 (79,1%) | 105 (78,4%) | 28 | **PHP** | Manajemen CRUD multi-slot banner karusel & toggle status aktif |
| 19 | `Admin/DiskonController.php` | **126** | 101 (80,2%) | 99 (78,6%) | 25 | **PHP** | Pembuatan dan pengelolaan kampanye diskon multi-produk |
| 20 | `Admin/KategoriController.php` | **103** | 83 (80,6%) | 82 (79,6%) | 20 | **PHP** | CRUD kategori master produk kue dan pastry |
| 21 | `Admin/BuktiBayarController.php` *(Baru)* | **98** | 84 (85,7%) | 70 (71,4%) | 14 | **PHP** | Konfirmasi validasi dan penolakan bukti transfer bank manual |
| 22 | `Admin/ContactMessageController.php` | **97** | 77 (79,4%) | 77 (79,4%) | 20 | **PHP** | Manajemen kotak masuk pesan bantuan pelanggan & status tindak lanjut |
| 23 | `Admin/ExpiredProductController.php` | **87** | 69 (79,3%) | 64 (73,6%) | 18 | **PHP** | Monitoring, sinkronisasi, dan penghapusan produk kadaluarsa |
| 24 | `Admin/AdminDashboardController.php` | **65** | 54 (83,1%) | 50 (76,9%) | 11 | **PHP** | Agregasi metrik KPI dashboard admin (omset, pesanan, grafik bulanan) |
| 25 | `Controller.php` | **8** | 6 (75,0%) | 5 (62,5%) | 2 | **PHP** | Base abstract class controller Laravel |
| — | **Total 25 File Controller** | **5.594** | **4.823 (86,2%)** | **4.441 (79,4%)** | **771 (13,8%)** | **PHP (100%)** | — |

---

## 3. Rincian Lengkap File View (`resources/views` — 55 File)

Klasifikasi per baris kode pada setiap berkas Blade:
- **HTML**: Tag markup struktur halaman (`<div>`, `<section>`, `<table>`, `<form>`, `<input>`, teks statis, serta tag `<link>` / `<script src="...">` eksternal).
- **PHP/Blade**: Direktif Blade (`@if`, `@foreach`, `@extends`, `@section`, `@props`), ekspresi interpolasi (`{{ }}`, `{!! !!}`), serta blok logika `@php ... @endphp`.
- **CSS**: Baris aturan gaya di dalam blok `<style>...</style>` inline.
- **JS**: Baris skrip di dalam blok `<script>...</script>` inline.
- **Kosong**: Baris spasi pemisah antar elemen.

### A. Storefront & Area Akun Pelanggan (`resources/views/Pelanggan/` — 17 File)

| No | File View (`Pelanggan/`) | Total Baris | HTML | PHP/Blade | CSS | JS | Kosong | Didominasi Oleh & Keterangan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| 1 | `order_detail.blade.php` | **1.017** | **612 (60,2%)** | 321 (31,6%) | 0 (0,0%) | 22 (2,2%) | 62 (6,1%) | **HTML (60,2%) & PHP/Blade (31,6%)**: Menyusut 2.641 baris setelah CSS & JS diekstraksi ke `css_order_detail.css` dan `js_order_detail.js`. |
| 2 | `order_success.blade.php` | **873** | 235 (26,9%) | 87 (10,0%) | 0 (0,0%) | **491 (56,2%)** | 60 (6,9%) | **JS (56,2%)**: Didominasi skrip animasi konfeti, unggah bukti bayar instan, dan *polling* status konfirmasi kasir. |
| 3 | `home.blade.php` | **693** | 301 (43,4%) | **365 (52,7%)** | 0 (0,0%) | 0 (0,0%) | 27 (3,9%) | **PHP/Blade (52,7%) & HTML (43,4%)**: Seluruh 1.073 baris CSS inline telah dipindah ke `public/css/css_home.css`; kini fokus pada logika Blade etalase beranda. |
| 4 | `detail.blade.php` | **433** | **173 (40,0%)** | **172 (39,7%)** | 13 (3,0%) | 45 (10,4%) | 30 (6,9%) | **HTML (40,0%) & PHP/Blade (39,7%)**: Markup galeri detail produk, badge ZYH Signature, informasi kadaluarsa, dan produk terkait. |
| 5 | `dashboard.blade.php` | **431** | **180 (41,8%)** | 135 (31,3%) | 0 (0,0%) | 83 (19,3%) | 33 (7,7%) | **HTML (41,8%) & PHP/Blade (31,3%)**: Menyusut 410 baris karena ekstraksi komponen `<x-customer-sidebar>`, `<x-customer-address-modal>`, dan `<x-biteship-tracking-modal>`. |
| 6 | `checkout.blade.php` | **430** | **329 (76,5%)** | 49 (11,4%) | 0 (0,0%) | 13 (3,0%) | 39 (9,1%) | **HTML (76,5%)**: Menyusut 735 baris setelah CSS & JS Leaflet/Biteship dipindah ke `css_checkout.css` dan `js_checkout.js`. |
| 7 | `shop.blade.php` | **417** | **207 (49,6%)** | 162 (38,8%) | 0 (0,0%) | 22 (5,3%) | 26 (6,2%) | **HTML (49,6%) & PHP/Blade (38,8%)**: Katalog produk reguler, filter toolbar responsif, *custom price range*, dan paginasi. |
| 8 | `order_history.blade.php` | **405** | **191 (47,2%)** | 181 (44,7%) | 0 (0,0%) | 5 (1,2%) | 28 (6,9%) | **HTML (47,2%) & PHP/Blade (44,7%)**: Menyusut 701 baris setelah CSS & JS diekstraksi ke `css_order_history.css` dan `js_order_history.js`. |
| 9 | `custom_products.blade.php` | **390** | **202 (51,8%)** | 142 (36,4%) | 0 (0,0%) | 22 (5,6%) | 24 (6,2%) | **HTML (51,8%) & PHP/Blade (36,4%)**: Katalog produk kustom pre-order dengan filter kategori/harga dan tautan pemesanan. |
| 10 | `settings.blade.php` | **369** | **247 (66,9%)** | 71 (19,2%) | 0 (0,0%) | 19 (5,1%) | 32 (8,7%) | **HTML (66,9%)**: Menyusut 301 baris berkat komponen `<x-customer-sidebar>` & `<x-customer-address-modal>`; berisi form profil, avatar, alamat, dan password. |
| 11 | `return_detail.blade.php` | **286** | **153 (53,5%)** | 111 (38,8%) | 0 (0,0%) | 0 (0,0%) | 22 (7,7%) | **HTML (53,5%) & PHP/Blade (38,8%)**: Timeline status pengajuan retur barang, bukti foto/video, dan catatan admin. |
| 12 | `contact.blade.php` | **227** | **90 (39,6%)** | 17 (7,5%) | 3 (1,3%) | **88 (38,8%)** | 29 (12,8%) | **HTML (39,6%) & JS (38,8%)**: Formulir kontak bantuan pelanggan dengan pengiriman AJAX dan notifikasi interaktif. |
| 13 | `about.blade.php` | **174** | **127 (73,0%)** | 10 (5,7%) | 0 (0,0%) | 23 (13,2%) | 14 (8,0%) | **HTML (73,0%)**: Halaman profil cerita brand Zyh Corner, visi kualitas, dan grid keunggulan toko. |
| 14 | `return_order.blade.php` | **125** | **80 (64,0%)** | 12 (9,6%) | 0 (0,0%) | 9 (7,2%) | 24 (19,2%) | **HTML (64,0%)**: Formulir pengajuan klaim retur pesanan dan unggah foto kendala produk. |
| 15 | `faq.blade.php` | **120** | **93 (77,5%)** | 13 (10,8%) | 0 (0,0%) | 0 (0,0%) | 14 (11,7%) | **HTML (77,5%)**: Menyusut 143 baris setelah CSS & JS akordeon dipindah ke `css_faq.css` dan `js_faq.js`. |
| 16 | `partials/multi_banner_grid.blade.php` | **109** | **54 (49,5%)** | **51 (46,8%)** | 0 (0,0%) | 0 (0,0%) | 4 (3,7%) | **HTML (49,5%) & PHP/Blade (46,8%)**: Partial view untuk merender grid promo banner dinamis. |
| 17 | `userChart.blade.php` | **22** | **7 (31,8%)** | 6 (27,3%) | 0 (0,0%) | 0 (0,0%) | 9 (40,9%) | **HTML (31,8%) & PHP/Blade (27,3%)**: Template mini visualisasi grafik pengguna. |
| — | **Subtotal `Pelanggan/` (17 File)** | **6.521** | **3.281 (50,3%)** | **1.905 (29,2%)** | **16 (0,2%)** | **842 (12,9%)** | **477 (7,3%)** | **HTML (15 file), JS (1 file), PHP/Blade (1 file)** |

---

### B. Panel Admin & Kasir (`resources/views/Admin/` — 22 File)

Seluruh **22 file view Admin** kini **100% didominasi oleh HTML** (baik secara tunggal maupun berpasangan dengan PHP/Blade atau JS) karena CSS dan JS seperti laporan penjualan, POS kasir, serta skrip *bulk delete* telah diekstraksi ke berkas eksternal (`css_sales_report.css`, `css_pos.css`, `js_pos.js`, `js_pos_checkout.js`, `admin-bulk-actions.js`, `admin-confirmations.js`).

| No | File View (`Admin/`) | Total Baris | HTML | PHP/Blade | CSS | JS | Kosong | Didominasi Oleh & Keterangan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| 18 | `kelola_produk/discounts.blade.php` | **889** | **474 (53,3%)** | 111 (12,5%) | 0 (0,0%) | 246 (27,7%) | 58 (6,5%) | **HTML (53,3%) & JS (27,7%)**: Tabel manajemen diskon, modal multi-select produk diskon, dan kalkulasi potongan harga. |
| 19 | `kelola_carousel/banners.blade.php` | **761** | **541 (71,1%)** | 156 (20,5%) | 19 (2,5%) | 0 (0,0%) | 45 (5,9%) | **HTML (71,1%)**: Grid manajemen multi-slot banner karusel; JS telah diekstraksi ke `public/js/js_banners.js`. |
| 20 | `kelola_penjualan/returs.blade.php` | **734** | **468 (63,8%)** | 154 (21,0%) | 0 (0,0%) | 73 (9,9%) | 39 (5,3%) | **HTML (63,8%)**: Tabel verifikasi klaim retur pelanggan, filter tab status, dan modal inspeksi bukti retur. |
| 21 | `kelola_penjualan/orders.blade.php` | **494** | **242 (49,0%)** | 96 (19,4%) | 0 (0,0%) | 124 (25,1%) | 32 (6,5%) | **HTML (49,0%) & JS (25,1%)**: Tabel pesanan online aktif, input resi pengiriman, dan tombol konfirmasi cepat. |
| 22 | `pesan_kontak/index.blade.php` | **448** | **296 (66,1%)** | 86 (19,2%) | 0 (0,0%) | 41 (9,2%) | 25 (5,6%) | **HTML (66,1%)**: Manajemen kotak masuk pesan pelanggan dan modal detail/balasan pesan. |
| 23 | `kelola_produk/products_form.blade.php` | **389** | **181 (46,5%)** | 54 (13,9%) | 0 (0,0%) | 125 (32,1%) | 29 (7,5%) | **HTML (46,5%) & JS (32,1%)**: Formulir tambah/edit produk dengan pratinjau gambar, toggle signature, dan tanggal expired. |
| 24 | `kelola_produk/expired_products.blade.php` | **361** | **269 (74,5%)** | 78 (21,6%) | 0 (0,0%) | 0 (0,0%) | 14 (3,9%) | **HTML (74,5%)**: Tabel monitoring stok mendekati/sudah kadaluarsa dan riwayat penghapusan. |
| 25 | `kelola_penjualan/sales_report.blade.php` | **359** | **220 (61,3%)** | 120 (33,4%) | 0 (0,0%) | 5 (1,4%) | 14 (3,9%) | **HTML (61,3%) & PHP/Blade (33,4%)**: Menyusut 427 baris setelah CSS & JS dipindah ke `css_sales_report.css` dan `js_sales_report.js`. |
| 26 | `dashboard/dashboard.blade.php` | **321** | **166 (51,7%)** | 36 (11,2%) | 0 (0,0%) | 110 (34,3%) | 9 (2,8%) | **HTML (51,7%) & JS (34,3%)**: Kartu KPI ringkasan penjualan dan konfigurasi grafik bulanan Chart.js. |
| 27 | `kelola_penjualan/pos.blade.php` | **311** | **224 (72,0%)** | 68 (21,9%) | 0 (0,0%) | 7 (2,3%) | 12 (3,9%) | **HTML (72,0%)**: Menyusut 255 baris setelah CSS & JS kasir dipindah ke `css_pos.css` dan `js_pos.js`. |
| 28 | `kelola_akun/customers.blade.php` | **298** | **216 (72,5%)** | 67 (22,5%) | 0 (0,0%) | 0 (0,0%) | 15 (5,0%) | **HTML (72,5%) & PHP/Blade (22,5%)**: Tabel master pelanggan dan aksi proteksi blokir/buka blokir akun. |
| 29 | `kelola_produk/products.blade.php` | **278** | **186 (66,9%)** | 80 (28,8%) | 0 (0,0%) | 0 (0,0%) | 12 (4,3%) | **HTML (66,9%) & PHP/Blade (28,8%)**: Tabel katalog produk reguler, indikator stok, dan badge signature. |
| 30 | `kelola_akun/cassiers.blade.php` | **276** | **212 (76,8%)** | 36 (13,0%) | 5 (1,8%) | 8 (2,9%) | 15 (5,4%) | **HTML (76,8%)**: Tabel pengelolaan akun staf kasir beserta modal tambah/edit kasir. |
| 31 | `kelola_akun/settings.blade.php` | **266** | **181 (68,0%)** | 39 (14,7%) | 0 (0,0%) | 23 (8,6%) | 23 (8,6%) | **HTML (68,0%)**: Halaman pengaturan profil admin, informasi kontak toko, dan nomor rekening bank. |
| 32 | `kelola_produk/custom_products.blade.php` | **262** | **179 (68,3%)** | 71 (27,1%) | 0 (0,0%) | 0 (0,0%) | 12 (4,6%) | **HTML (68,3%) & PHP/Blade (27,1%)**: Tabel daftar produk kustom pre-order di panel admin. |
| 33 | `kelola_penjualan/order_detail_modal.blade.php` | **254** | **162 (63,8%)** | 76 (29,9%) | 0 (0,0%) | 0 (0,0%) | 16 (6,3%) | **HTML (63,8%) & PHP/Blade (29,9%)**: Komponen modal rincian item pesanan dan informasi pengiriman pelanggan. |
| 34 | `kelola_penjualan/verify_proof_modal.blade.php` | **225** | **101 (44,9%)** | 36 (16,0%) | 0 (0,0%) | 75 (33,3%) | 13 (5,8%) | **HTML (44,9%) & JS (33,3%)**: Modal inspeksi bukti transfer bank, pratinjau gambar, dan form alasan penolakan. |
| 35 | `kelola_produk/categories.blade.php` | **198** | **141 (71,2%)** | 34 (17,2%) | 5 (2,5%) | 8 (4,0%) | 10 (5,1%) | **HTML (71,2%)**: Tabel master kategori produk kue beserta modal CRUD kategori. |
| 36 | `kelola_penjualan/partials/all_sales_table.blade.php` | **146** | **85 (58,2%)** | 59 (40,4%) | 0 (0,0%) | 0 (0,0%) | 2 (1,4%) | **HTML (58,2%) & PHP/Blade (40,4%)**: Partial baris tabel yang dirender pada fitur pencarian AJAX penjualan. |
| 37 | `kelola_penjualan/all_sales.blade.php` | **128** | **95 (74,2%)** | 24 (18,8%) | 0 (0,0%) | 0 (0,0%) | 9 (7,0%) | **HTML (74,2%)**: Halaman utama daftar seluruh penjualan (Online & POS Offline). |
| 38 | `kelola_penjualan/invoice.blade.php` | **127** | **82 (64,6%)** | 31 (24,4%) | 0 (0,0%) | 7 (5,5%) | 7 (5,5%) | **HTML (64,6%) & PHP/Blade (24,4%)**: Template cetak nota / *invoice* resmi pesanan pelanggan. |
| 39 | `kelola_penjualan/pos_checkout.blade.php` | **104** | **80 (76,9%)** | 9 (8,7%) | 0 (0,0%) | 7 (6,7%) | 8 (7,7%) | **HTML (76,9%)**: Menyusut 293 baris setelah seluruh JS kalkulator kembalian dipindah ke `js_pos_checkout.js`. |
| — | **Subtotal `Admin/` (22 File)** | **7.629** | **5.001 (65,6%)** | **1.519 (19,9%)** | **29 (0,4%)** | **859 (11,3%)** | **421 (5,5%)** | **HTML mendominasi seluruh 22 file (100%)** |

---

### C. Master Layout (`resources/views/Layout/` — 2 File)

| No | File View (`Layout/`) | Total Baris | HTML | PHP/Blade | CSS | JS | Kosong | Didominasi Oleh & Keterangan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| 40 | `layout_pelanggan.blade.php` | **570** | **291 (51,1%)** | 107 (18,8%) | 34 (6,0%) | 99 (17,4%) | 39 (6,8%) | **HTML (51,1%)**: Kerangka utama storefront pelanggan (header, navigasi responsif, *cart drawer*, modal *Quick View*, footer). |
| 41 | `layout_pAdmin.blade.php` | **446** | **260 (58,3%)** | 94 (21,1%) | 0 (0,0%) | 67 (15,0%) | 25 (5,6%) | **HTML (58,3%)**: Kerangka utama panel admin & kasir (sidebar, navbar notifikasi, pemuat global `admin-bulk-actions.js`). |
| — | **Subtotal `Layout/` (2 File)** | **1.016** | **551 (54,2%)** | **201 (19,8%)** | **34 (3,3%)** | **166 (16,3%)** | **64 (6,3%)** | **HTML mendominasi seluruh 2 file** |

---

### D. Komponen Blade Reusable (`resources/views/components/` — 3 File Baru)

| No | File View (`components/`) | Total Baris | HTML | PHP/Blade | CSS | JS | Kosong | Didominasi Oleh & Keterangan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| 42 | `customer-address-modal.blade.php` *(Baru)* | **316** | **279 (88,3%)** | 9 (2,8%) | 0 (0,0%) | 0 (0,0%) | 28 (8,9%) | **HTML (88,3%)**: Komponen modal buku alamat pelanggan & pemilih titik koordinat peta Leaflet (digunakan di Dashboard & Settings). |
| 43 | `biteship-tracking-modal.blade.php` *(Baru)* | **71** | **65 (91,5%)** | 2 (2,8%) | 0 (0,0%) | 0 (0,0%) | 4 (5,6%) | **HTML (91,5%)**: Komponen modal pelacakan resi kurir Biteship (digunakan di Dashboard & Riwayat Pesanan). |
| 44 | `customer-sidebar.blade.php` *(Baru)* | **34** | **24 (70,6%)** | 9 (26,5%) | 0 (0,0%) | 0 (0,0%) | 1 (2,9%) | **HTML (70,6%) & PHP/Blade (26,5%)**: Komponen navigasi menu akun pelanggan (`<x-customer-sidebar>`) di 5 halaman profil. |
| — | **Subtotal `components/` (3 File)** | **421** | **368 (87,4%)** | **20 (4,8%)** | **0 (0,0%)** | **0 (0,0%)** | **33 (7,8%)** | **HTML mendominasi seluruh 3 file** |

---

### E. Otentikasi Pengguna (`resources/views/Auth/` — 4 File)

| No | File View (`Auth/`) | Total Baris | HTML | PHP/Blade | CSS | JS | Kosong | Didominasi Oleh & Keterangan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| 45 | `register.blade.php` | **135** | 34 (25,2%) | 18 (13,3%) | **50 (37,0%)** | 14 (10,4%) | 19 (14,1%) | **CSS (37,0%) & HTML (25,2%)**: Formulir pendaftaran akun pelanggan baru dengan kartu terisolasi. |
| 46 | `login.blade.php` | **126** | 30 (23,8%) | 14 (11,1%) | **50 (39,7%)** | 18 (14,3%) | 14 (11,1%) | **CSS (39,7%) & HTML (23,8%)**: Halaman login multi-role dan autentikasi Google OAuth 2.0. |
| 47 | `google_verify.blade.php` | **52** | **23 (44,2%)** | 15 (28,8%) | 0 (0,0%) | 6 (11,5%) | 8 (15,4%) | **HTML (44,2%) & PHP/Blade (28,8%)**: Menyusut 162 baris setelah CSS/JS OTP dipindah ke `auth-otp.css` & `auth-otp.js`. |
| 48 | `register_verify.blade.php` | **52** | **23 (44,2%)** | 15 (28,8%) | 0 (0,0%) | 6 (11,5%) | 8 (15,4%) | **HTML (44,2%) & PHP/Blade (28,8%)**: Menyusut 162 baris berkat pemakaian bersama `auth-otp.css` & `auth-otp.js`. |
| — | **Subtotal `Auth/` (4 File)** | **365** | **110 (30,1%)** | **62 (17,0%)** | **100 (27,4%)** | **44 (12,1%)** | **49 (13,4%)** | **HTML (2 file), CSS (2 file)** |

---

### F. Template Email Notifikasi (`resources/views/emails/` — 6 File) & Error (`errors/` — 1 File)

| No | File View (`emails/` & `errors/`) | Total Baris | HTML | PHP/Blade | CSS | JS | Kosong | Didominasi Oleh & Keterangan |
| :---: | :--- | :---: | :---: | :---: | :---: | :---: | :---: | :--- |
| 49 | `emails/pesan_kontak_baru_admin.blade.php` | **80** | **28 (35,0%)** | 18 (22,5%) | 25 (31,2%) | 0 (0,0%) | 9 (11,2%) | **HTML (35,0%) & CSS (31,2%)**: Menyusut 99 baris setelah mewarisi `@extends('emails.layout')`. |
| 50 | `errors/403.blade.php` | **72** | **30 (41,7%)** | **30 (41,7%)** | 0 (0,0%) | 0 (0,0%) | 12 (16,7%) | **PHP/Blade (41,7%) & HTML (41,7%)**: Menyusut 155 baris setelah CSS animasi gembok dipindah ke `public/css/css_403.css`. |
| 51 | `emails/pesanan_baru_kasir.blade.php` | **66** | **35 (53,0%)** | 23 (34,8%) | 0 (0,0%) | 0 (0,0%) | 8 (12,1%) | **HTML (53,0%) & PHP/Blade (34,8%)**: Template email notifikasi pesanan baru untuk kasir berbasis `emails.layout`. |
| 52 | `emails/otp.blade.php` | **52** | **46 (88,5%)** | 1 (1,9%) | 0 (0,0%) | 0 (0,0%) | 5 (9,6%) | **HTML (88,5%)**: Template email pengiriman kode verifikasi OTP 6-digit. |
| 53 | `emails/retur_produk_kasir.blade.php` | **50** | **25 (50,0%)** | 18 (36,0%) | 0 (0,0%) | 0 (0,0%) | 7 (14,0%) | **HTML (50,0%) & PHP/Blade (36,0%)**: Template email notifikasi pengajuan retur pelanggan berbasis `emails.layout`. |
| 54 | `emails/produk_kadaluarsa_kasir.blade.php` | **44** | **22 (50,0%)** | 15 (34,1%) | 0 (0,0%) | 0 (0,0%) | 7 (15,9%) | **HTML (50,0%) & PHP/Blade (34,1%)**: Template email peringatan stok kadaluarsa berbasis `emails.layout`. |
| 55 | `emails/layout.blade.php` *(Baru)* | **42** | **20 (47,6%)** | 2 (4,8%) | **20 (47,6%)** | 0 (0,0%) | 0 (0,0%) | **CSS (47,6%) & HTML (47,6%)**: Master layout dasar untuk seluruh email notifikasi sistem. |
| — | **Subtotal `emails/` & `errors/` (7 File)** | **406** | **206 (50,7%)** | **107 (26,4%)** | **45 (11,1%)** | **0 (0,0%)** | **48 (11,8%)** | **HTML (5 file), CSS/HTML Imbang (1 file), PHP/HTML Imbang (1 file)** |

---

## 4. Kesimpulan & Temuan Utama Audit

1. **Dominasi pada Layer Controller (`app/Http/Controllers`)**:
   - **100% (25 dari 25 file)** didominasi oleh **PHP** (total **5.594 baris**, dengan **4.823 baris aktif / 86,2%**).
   - Tidak ada lagi *God Controller* yang menembus 1.000 baris. Controller terbesar saat ini adalah `Admin/EksporController.php` (**560 baris**) dan `AuthController.php` (**519 baris**), sementara `Admin/PesananController.php` telah turun dari **1.014 baris menjadi 208 baris (-79,5%)**.

2. **Dominasi pada Layer View (`resources/views`)**:
   - Secara agregat (**16.358 baris** pada **55 file**), kode view kini didominasi oleh **HTML sebesar 57,0% (9.317 baris)**, diikuti oleh **PHP/Blade sebesar 23,3% (3.816 baris)**, **JavaScript sebesar 11,7% (1.911 baris)**, **baris kosong sebesar 6,7% (1.090 baris)**, dan **CSS sebesar 1,4% (224 baris)**.
   - Berdasarkan dominasi per berkas:
     - **Didominasi HTML**: **49 file (89,1%)** — seluruh halaman Admin (22 file), Master Layout (2 file), Blade Components (3 file), mayoritas halaman Pelanggan termasuk `order_detail.blade.php` & `order_history.blade.php` (15 file), halaman OTP Auth (2 file), serta mayoritas template Email (5 file).
     - **Didominasi CSS**: **3 file (5,5%)** — `Auth/login.blade.php` (39,7% CSS), `Auth/register.blade.php` (37,0% CSS), serta `emails/layout.blade.php` (47,6% CSS, imbang dengan HTML).
     - **Didominasi JavaScript**: **1 file (1,8%)** — `Pelanggan/order_success.blade.php` (56,2% JS).
     - **Didominasi PHP/Blade**: **2 file (3,6%)** — `Pelanggan/home.blade.php` (52,7% PHP/Blade) dan `errors/403.blade.php` (41,7% PHP/Blade, imbang dengan HTML).

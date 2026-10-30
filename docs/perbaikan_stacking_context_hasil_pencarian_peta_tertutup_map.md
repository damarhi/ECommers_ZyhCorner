# Dokumentasi Perbaikan Stacking Context Hasil Pencarian Peta Tertutup Kanvas Map

## 1. Masalah yang Ditemukan (Problem Statement)

Pada formulir pengiriman di halaman Checkout (`checkout.blade.php`) dan modal alamat di Dasbor Pelanggan (`dashboard.blade.php`), dropdown hasil pencarian lokasi geocoding (`.map-search-dropdown`) sempat tertimpa dan tertutup oleh kanvas peta Leaflet (`#map` / `.modal-map-canvas`).

Pelanggan yang mengetik nama jalan/gedung dan menekan tombol "Cari" tidak dapat melihat atau mengeklik daftar rekomendasi alamat karena lapisan ubin peta (*tiles*) dan pin marker Leaflet terlukis (*rendered*) di atas kontainer hasil pencarian.

---

## 2. Analisis Akar Masalah (Root Cause Analysis)

Berdasarkan spesifikasi **W3C CSS Stacking Context**:
1. **Stacking Context Trap**:
   Kontainer search bar (`.map-search-container`) awalnya memiliki `position: relative; z-index: 10;`. Hal ini membentuk konteks tumpukan lokal berlevel `10`. Anak elemen di dalamnya (`.map-search-dropdown`), meskipun memiliki `z-index: 1000`, terkunci dan tidak dapat melebihi level `10` dari kontainer induknya.
2. **Leaflet Internal Panes Menembus Lapisan**:
   Pustaka Leaflet CSS mendefinisikan lapisan internal dengan level tumpukan tinggi:
   - `.leaflet-map-pane`: `z-index: 400`
   - `.leaflet-marker-pane`: `z-index: 600`
   - `.leaflet-control`: `z-index: 800`
   - `.leaflet-top, .leaflet-bottom`: `z-index: 1000`
   
   Karena pembungkus peta (`.map-wrapper`) sebelumnya tidak memiliki isolasi tumpukan (`z-index: 1;`), lapisan internal Leaflet tersebut berpartisipasi langsung pada *stacking context* luar (`.form-group-custom`).
3. **DOM Painting Order**:
   Karena elemen `.map-wrapper` muncul **setelah** `.map-search-container` pada urutan DOM, browser memprioritaskan penggambaran elemen berikutnya di atas elemen sebelumnya ketika bersaing pada level stacking yang sama atau lebih tinggi.

---

## 3. Rincian Perubahan Kode (Code Changes)

### A. File `resources/css/pelanggan_custom.css` (Halaman Checkout)

1. **Menaikkan Z-Index Kontainer Pencarian & Fokus State** (Baris 466–473):
   ```css
   .map-search-container {
       position: relative;
       margin-bottom: 8px;
       z-index: 1050;
   }
   .map-search-container:focus-within {
       z-index: 1100;
   }
   ```

2. **Memperkuat Elevasi Dropdown Hasil Pencarian** (Baris 578–590):
   ```css
   .map-search-dropdown {
       position: absolute;
       top: calc(100% + 4px);
       left: 0;
       right: 0;
       background: #ffffff;
       border: 1px solid #cbd5e1;
       border-radius: 8px;
       box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.16), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
       max-height: 220px;
       overflow-y: auto;
       z-index: 1050;
   }
   ```

3. **Mengisolasi Seluruh Lapisan Leaflet di Bawah Search Bar** (Baris 646–658):
   ```css
   .map-wrapper {
       position: relative;
       z-index: 1;
       border-radius: 10px;
       overflow: hidden;
       border: 1px solid #e2e8f0;
       background: #f8fafc;
   }
   #map {
       width: 100%;
       height: 220px;
       z-index: 1;
   }
   ```

---

### B. File `resources/css/dashboard.css` (Modal Alamat Dasbor)

1. **Isolasi Stacking Context pada Pembungkus Peta Modal** (Baris 830–836):
   ```css
   .modal-map-wrapper {
       position: relative;
       z-index: 1;
       border-radius: 12px;
       overflow: hidden;
       border: 1px solid #cbd5e1;
   }
   ```

2. **Menaikkan Z-Index Kontainer & Dropdown Pencarian Modal** (Baris 881–889 & 974–986):
   ```css
   .map-search-container {
       position: relative;
       margin-bottom: 8px;
       z-index: 1050;
   }

   .map-search-container:focus-within {
       z-index: 1100;
   }

   .map-search-dropdown {
       position: absolute;
       top: calc(100% + 4px);
       left: 0;
       right: 0;
       background: #ffffff;
       border: 1px solid #cbd5e1;
       border-radius: 8px;
       box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.16), 0 8px 10px -6px rgba(15, 23, 42, 0.1);
       max-height: 220px;
       overflow-y: auto;
       z-index: 1050;
   }
   ```

---

## 4. Hasil Verifikasi & Pengujian

1. **Build Kompilasi Frontend (Vite)**:
   ```bash
   npm run build
   ```
   *Hasil*: Berhasil dalam 533ms (`pelanggan_pages-Cfalw16J.css` dan `dashboard-CtWo_qUR.css`).

2. **Pengujian Kontrak Desain Checkout & Dasbor (PHPUnit)**:
   ```bash
   php artisan test tests/Feature/CheckoutDesignTest.php tests/Feature/CustomerDashboardTest.php
   ```
   *Hasil*: **7 passed (92 assertions)** 100% Green.

3. **Pengujian Menyeluruh Aplikasi (PHPUnit)**:
   ```bash
   php artisan test
   ```
   *Hasil*: **194 passed (1034 assertions)** 100% Green tanpa ada regresi.

---

## 5. Kesimpulan

Dengan mengisolasi `.map-wrapper` dan `.modal-map-wrapper` pada level `z-index: 1;`, seluruh lapisan internal Leaflet terkurung di lapisan bawah. Kontainer search bar yang ditingkatkan ke `z-index: 1050` (dan `1100` saat aktif) memastikan bahwa dropdown hasil pencarian melayang sempurna di atas peta dengan bayangan kontras yang bersih dan elegan sesuai arahan `/skill:frontend-design-direction`.

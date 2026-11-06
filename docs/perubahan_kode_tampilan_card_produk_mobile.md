# Dokumentasi Perubahan Kode: Perbaikan Tampilan Card Produk Mobile

**Tanggal Eksekusi:** 5 November 2026  
**Target Komponen:** Kartu Produk Pelanggan (`.product-card`) pada Viewport Mobile  
**Halaman Terdampak:**  
1. `resources/views/Pelanggan/detail.blade.php` (Produk Terkait / `.related-grid`)  
2. `resources/views/Pelanggan/home.blade.php` (Featured & Newest Products)  
3. `resources/views/Pelanggan/shop.blade.php` (Katalog Produk / Shop Grid)  
4. `resources/views/Pelanggan/custom_products.blade.php` (Katalog Produk Kustom)  
**Lingkungan:** Windows 10 x64, Laragon, PHP 8.2+, MySQL 8.4 (`pa-zyhcorner`), Laravel 10, Vite 8  

---

## 1. Latar Belakang & Identifikasi Masalah

Berdasarkan laporan visual dari pengguna:
- **Gambar 1 (`image-cae35be339c5a9ff.webp`):** Pada grid produk terkait mobile, tombol icon keranjang (`.cart-btn`) menimpa teks harga (`.product-price`).
- **Gambar 2 (`image-7c8a29c91d5613d0.webp`):** Pada grid produk berdiskon di mobile, teks harga berukuran terlalu besar sehingga teks harga potongan / coret diskon (`.old`) tertutup dan terhimpit.

### Analisis Akar Masalah (Root Cause Analysis)
1. **Ketiadaan Buffer Zona Aman (Safe Zone Gap):**
   - Tombol keranjang (`.cart-btn`) menggunakan posisi absolut:
     ```css
     .cart-btn {
         position: absolute;
         right: 4px;
         bottom: 8px;
         width: 36px;
         height: 36px;
     }
     ```
   - Kontainer informasi produk (`.product-info`) hanya memiliki padding kanan `4px`.
   - Pada layar mobile dengan grid 2-kolom (lebar kartu berkisar $140\text{px} - 165\text{px}$), teks harga yang memanjang ke kanan menembus koordinat absolut `.cart-btn`, menyebabkan lingkaran tombol keranjang menindih angka harga produk secara fisik.
2. **Ukuran Font Desktop Tanpa Media Query Mobile:**
   - `.product-price` diatur secara default dengan `font-size: 17px; font-weight: 700;` dengan susunan horizontal (`display: flex; align-items: center; gap: 8px;`).
   - Teks harga promo `"Rp. 125.000"` memakan lebar $\approx 95\text{px}$, sedangkan harga coret `"Rp. 150.000"` memakan lebar $\approx 72\text{px}$ (total $175\text{px}$).
   - Karena kontainer kartu mobile hanya selebar $145\text{px}$, harga coret terdorong keluar batas atau bertumpuk di bawah tombol keranjang.

---

## 2. Solusi Desain yang Diterapkan (`/skill:frontend-design-direction`)

1. **Penerapan Safe-Zone Buffer 44px (`.product-info`):**
   - Menetapkan `padding: 8px 44px 8px 8px !important;` pada `.product-info` untuk mobile ($\le 576\text{px}$).
   - Memastikan teks judul, harga promo, dan diskon berhenti $44\text{px}$ sebelum tepi kanan kartu.
2. **Vertical Price Stacking:**
   - Mengubah aliran harga dari horizontal menjadi bertumpuk vertikal:
     - **Baris 1:** Harga promo sekarang (`.price-now`) berukuran $14\text{px}$ bold (`#1d2939`).
     - **Baris 2:** Harga coret (`.old`) berukuran $11\text{px}$ abu-abu (`#94a3b8`) dengan `text-decoration: line-through`.
   - Menghilangkan tabrakan horizontal antar harga, sehingga kedua informasi harga terbaca utuh dan jelas.
3. **Penyelarasan Dimensi Tombol Keranjang (`.cart-btn`):**
   - Disesuaikan menjadi $32\text{px} \times 32\text{px}$ pada posisi `right: 6px; bottom: 8px;`.
   - Terdapat jarak bersih (gap fisik) minimal $6\text{px}$ antara karakter harga paling kanan dan sisi kiri lingkaran tombol keranjang.

---

## 3. Rincian Perubahan Kode Sumber

### A. View Blade Pelanggan

#### 1. `resources/views/Pelanggan/detail.blade.php` (Baris 324–331)
Sebelum:
```html
<div class="product-price">
    @if ($rp->has_diskon)
        Rp. {{ number_format($rp->harga_diskon, 0, ',', '.') }}
        <span class="old" style="font-size: 13px; text-decoration: line-through; color: #999; margin-left: 5px;">Rp. {{ number_format($rp->harga, 0, ',', '.') }}</span>
    @else
        Rp. {{ number_format($rp->harga, 0, ',', '.') }}
    @endif
</div>
```
Sesudah:
```html
<div class="product-price">
    @if ($rp->has_diskon)
        <span class="price-now">Rp. {{ number_format($rp->harga_diskon, 0, ',', '.') }}</span>
        <span class="old">Rp. {{ number_format($rp->harga, 0, ',', '.') }}</span>
    @else
        <span class="price-now">Rp. {{ number_format($rp->harga, 0, ',', '.') }}</span>
    @endif
</div>
```

#### 2. `resources/views/Pelanggan/home.blade.php` (Baris 200–208 & 642–650)
Sebelum:
```html
<div class="product-price d-flex align-items-center justify-content-between">
    <div>
        @if ($product->has_diskon)
            Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
            <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
        @else
            Rp. {{ number_format($product->harga, 0, ',', '.') }}
        @endif
    </div>
    @if ($product->stok == 0)
        <span class="stock-status-inline">Habis</span>
    @endif
</div>
```
Sesudah:
```html
<div class="product-price d-flex align-items-center justify-content-between">
    <div class="price-stack">
        @if ($product->has_diskon)
            <span class="price-now">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
            <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
        @else
            <span class="price-now">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
        @endif
    </div>
    @if ($product->stok == 0)
        <span class="stock-status-inline">Habis</span>
    @endif
</div>
```

#### 3. `resources/views/Pelanggan/shop.blade.php` (Baris 294–301)
Sebelum:
```html
<div class="product-price">
    @if ($product->has_diskon)
        Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
        <span class="old" style="font-size: 13px; text-decoration: line-through; color: #999; margin-left: 5px;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
    @else
        Rp. {{ number_format($product->harga, 0, ',', '.') }}
    @endif
</div>
```
Sesudah:
```html
<div class="product-price">
    @if ($product->has_diskon)
        <span class="price-now">Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}</span>
        <span class="old">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
    @else
        <span class="price-now">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
    @endif
</div>
```

#### 4. `resources/views/Pelanggan/custom_products.blade.php` (Baris 277–283)
Sebelum:
```html
<div class="product-price">
    @if ($product->harga_max && $product->harga_max > $product->harga)
        Rp. {{ number_format($product->harga, 0, ',', '.') }} - Rp. {{ number_format($product->harga_max, 0, ',', '.') }}
    @else
        Rp. {{ number_format($product->harga, 0, ',', '.') }}
    @endif
</div>
```
Sesudah:
```html
<div class="product-price">
    @if ($product->harga_max && $product->harga_max > $product->harga)
        <span class="price-now">Rp. {{ number_format($product->harga, 0, ',', '.') }} - Rp. {{ number_format($product->harga_max, 0, ',', '.') }}</span>
    @else
        <span class="price-now">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
    @endif
</div>
```

---

### B. Aturan CSS Terpusat & Sinkronisasi
Ditambahkan pada `resources/css/pelanggan_pages.css`, `public/css/pelanggan.css`, `resources/css/pelanggan.css`, dan `resources/css/pelanggan_custom.css` di dalam media query mobile:

```css
/* Mobile Product Card Polish & Safe Zone */
.product-card {
    border-radius: 12px !important;
    overflow: hidden !important;
}
.product-info {
    position: relative !important;
    padding: 8px 44px 8px 8px !important; /* Safe zone 44px di sisi kanan untuk tombol aksi */
    min-height: 56px !important;
}
.product-name {
    font-size: 13px !important;
    line-height: 1.35 !important;
    margin-bottom: 4px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    display: block !important;
}
.product-price {
    font-size: 14px !important;
    font-weight: 700 !important;
    color: #1d2939 !important;
    display: flex !important;
    flex-direction: column !important; /* Stacking harga vertikal */
    align-items: flex-start !important;
    gap: 2px !important;
    margin-bottom: 0 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
}
.product-price .price-now {
    font-size: 14px !important;
    font-weight: 700 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
}
.product-price .old {
    font-size: 11px !important;
    font-weight: 400 !important;
    color: #94a3b8 !important;
    text-decoration: line-through !important;
    margin-left: 0 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
}
.product-price .price-stack,
.product-price > div {
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 2px !important;
    white-space: nowrap !important;
}
.cart-btn {
    position: absolute !important;
    right: 6px !important;
    bottom: 8px !important;
    width: 32px !important;
    height: 32px !important;
    min-width: 32px !important;
    min-height: 32px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 12.5px !important;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08) !important;
    z-index: 2 !important;
}
```

---

## 4. Hasil Kompilasi & Verifikasi Pengujian

### A. Kompilasi Aset Vite
```bash
npm run build
```
- Status: **Berhasil (✓ built in 582ms)**
- Aset yang diperbarui:
  - `public/build/assets/pelanggan_pages-BoM7LchO.css` (171.13 kB │ gzip: 28.82 kB)
  - `public/build/assets/pelanggan_pages-CzSEwIHz.js` (74.50 kB │ gzip: 15.65 kB)

### B. Suite Pengujian Otomatis Laravel (PHPUnit)
```bash
php artisan test
```
- Hasil: **221 passed (1195 assertions)**
- Status: **100% Lulus (0 kegagalan, 0 regresi)**
- Durasi: **68.17s**

### C. Integritas Database MySQL `pa-zyhcorner`
- `Users`: **16 baris** (utuh)
- `Products`: **13 baris** (utuh)
- `Orders`: **25 baris** (utuh)
- Direktori `public/uploads/` bersih dari berkas dummy pengujian.

---

## 5. Kesimpulan
Seluruh masalah tabrakan visual tombol keranjang dan keterbacaan harga diskon pada kartu produk mobile telah diperbaiki secara tuntas. Komponen kartu produk di seluruh halaman pelanggan kini memiliki struktur hierarki visual yang seimbang, aman dari risiko overlap, serta mematuhi prinsip desain responsif yang modern dan konsisten.

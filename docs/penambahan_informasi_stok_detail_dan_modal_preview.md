# Dokumentasi Teknis: Penambahan Informasi Stok Produk pada Halaman Detail & Modal Preview

## 1. Latar Belakang & Tujuan

Sebelumnya, informasi ketersediaan produk pada toko online **Zyh Corner** hanya ditampilkan secara biner (hanya bertuliskan *"Tersedia"* atau *"Habis"*) tanpa mencantumkan berapa unit fisik yang masih tersisa di etalase:
1. **Halaman Detail Produk (`/product/{id}`)**:
   - Status di bawah judul hanya berupa badge statis `Tersedia` tanpa angka unit.
   - Tabel spesifikasi (`spec-table`) menampilkan label teknis `Status: 10` (mengambil kolom `status_produk` mentah), membingungkan pembeli awam.
   - Peringatan stok maksimum `#qtyStockWarning` tersembunyi dan baru muncul jika pelanggan menaikkan kuantitas hingga mentok.
2. **Modal Preview / Quick View (`#productModal`)**:
   - Badge `<span class="pm-stock-pill" id="pmStock">` hanya menampilkan teks `Tersedia`, meskipun JavaScript sudah memiliki data stok riil melalui `data-stock`.
   - Tidak ada petunjuk sisa stok di sebelah tombol kuantitas modal.

Sesuai arahan panduan **`frontend-design-direction`**, dilakukan peningkatan menyeluruh agar:
1. **Transparansi Stok Nyata**: Menampilkan jumlah stok fisik secara eksplisit (contoh: `Tersedia: 10 unit` atau `Tersisa 2 unit`).
2. **Hierarki Status Bertingkat (*Tiered Stock Pills*)**:
   - **Stok Aman ($> 3$)**: Badge hijau zamrud (`#059669`) dengan teks `Tersedia: X unit`.
   - **Stok Menipis ($1 - 3$)**: Badge amber hangat (`#b45309`) dengan teks `Sisa X unit lagi (Segera Habis!)`.
   - **Stok Habis ($0$)**: Badge abu-abu netral (`#64748b`) dengan teks `Stok Habis`.
   - **Produk Kustom / Pre-Order**: Badge biru muda (`#0284c7`) dengan teks `Pre-Order / Dibuat Sesuai Pesanan`.
3. **Micro-Hint Kuantitas**: Teks indikator stok berdampingan dengan kotak input kuantitas (`qty-box`), sehingga pembeli langsung mengetahui batas maksimum tanpa perlu menebak-nebak.
4. **Tabel Spesifikasi Manusiawi**: Mengubah baris `Status: 10` menjadi `Ketersediaan Stok: 10 unit (Ready Stock / Siap Kirim)`.

---

## 2. Struktur Visual Baru (Mockup)

### A. Halaman Detail Produk (`/product/{id}`)
```
┌────────────────────────────────────────────────────────────────────────┐
│ Black Forest Cake Classic                                              │
│ Rp. 125.000                                                            │
│ ────────────────────────────────────────────────────────────────────── │
│ Kategori : Kue & Cake                                                  │
│ Expired  : 29 Oct 2026                                                 │
│ SKU      : ZYH-0001                                                    │
│ Stok     : Tersedia: 10 unit                                           │
│ ────────────────────────────────────────────────────────────────────── │
│ [ - | 1 | + ]  [📦 Tersisa 10 unit]   [ 🛍️ Beli Sekarang ]            │
├────────────────────────────────────────────────────────────────────────┤
│ SPESIFIKASI:                                                           │
│ - Ketersediaan Stok : 10 unit (Ready Stock / Siap Kirim)               │
│ - SKU               : ZYH-0001                                         │
└────────────────────────────────────────────────────────────────────────┘
```

### B. Modal Quick View (`#productModal`)
```
┌────────────────────────────────────────────────────────────────────────┐
│ Kue & Bakery                                                           │
│ Brownies Fudgy                                                         │
│ [● Sisa 2 unit lagi (Segera Habis!)] • SKU: ZYH-0002                   │
│ Rp. 65.000                                                             │
│ ...                                                                    │
│ [ - | 1 | + ]  [⚠️ Tersisa 2 unit]   [ 🛍️ Beli Sekarang ] [ 🛒 ]      │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Perubahan Kode

### A. Template Detail Produk (`resources/views/Pelanggan/detail.blade.php`)

1. **Badge Galeri Produk (Mendukung 4 Status)**:
```blade
<div class="gallery-main">
    @if ($product->jenis_produk === 'kustom' || request()->query('custom') == 1)
        <div class="gallery-badge custom">Pre-Order</div>
    @elseif ($product->stok == 0)
        <div class="gallery-badge out">Out of Stock</div>
    @elseif ($product->stok <= 3)
        <div class="gallery-badge warning">Sisa {{ $product->stok }} Unit</div>
    @else
        <div class="gallery-badge green">In Stock</div>
    @endif
    <img id="mainProductImage" src="{{ reset($fotos) }}" alt="{{ $product->nama_produk }}">
</div>
```

2. **Informasi Stok Bersih di Bawah SKU (`.pi-info-row`)**:
```blade
<div class="pi-info-row"><strong>SKU</strong><span>:
        ZYH-{{ str_pad($product->id_produk, 4, '0', STR_PAD_LEFT) }}</span></div>
<div class="pi-info-row"><strong>Stok</strong><span>:
        @if ($product->jenis_produk === 'kustom' || request()->query('custom') == 1)
            <span class="pi-stock custom">Pre-Order / Dibuat Sesuai Pesanan</span>
        @elseif ($product->stok <= 0)
            <span class="pi-stock out">Stok Habis</span>
        @elseif ($product->stok <= 3)
            <span class="pi-stock low">Sisa {{ $product->stok }} unit lagi (Segera Habis!)</span>
        @else
            <span class="pi-stock in">Tersedia: {{ $product->stok }} unit</span>
        @endif
    </span></div>
```
*Catatan: Informasi stok dipindahkan ke bawah baris SKU, tanpa latar belakang badge (tanpa background/border) dan tanpa dot status bulat, melainkan teks bersih yang dibedakan dengan warna hijau (#00b207).*

3. **Kontrol Aksi Bersih & Tata Letak Tombol Stok Habis (`.pi-actions`)**:
```blade
<div class="pi-actions {{ $product->stok <= 0 && $product->jenis_produk !== 'kustom' && request()->query('custom') != 1 ? 'is-out-of-stock' : '' }}">
    @if (request()->query('custom') == 1 || $product->jenis_produk === 'kustom')
        <div class="qty-box">
            <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
            <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok > 0 ? $product->stok : 999 }}" oninput="changeQty(0)">
            <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
        </div>
        <button class="btn-buy" onclick="buyCustomNow()" style="background-color: #25d366 !important;">
            <i class="fa-brands fa-whatsapp"></i> Pesan via WhatsApp @if($product->stok <= 0) (Pre-Order) @endif
        </button>
        <div id="qtyStockWarning" class="qty-stock-warning" style="display: none;">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
        </div>
    @elseif ($product->stok > 0)
        <div class="qty-box">
            <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
            <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok }}" oninput="changeQty(0)">
            <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
        </div>
        <button class="btn-buy" onclick="buyNow()">
            <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
        </button>
        <div id="qtyStockWarning" class="qty-stock-warning" style="display: none;">
            <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
        </div>
    @else
        <div class="qty-box" style="opacity: 0.5; pointer-events: none; background-color: #f5f5f5;">
            <button class="qty-btn" disabled><i class="fa-solid fa-minus"></i></button>
            <input class="qty-input" type="text" value="0" id="qtyInput" disabled style="background-color: #f5f5f5;">
            <button class="qty-btn" disabled><i class="fa-solid fa-plus"></i></button>
        </div>
        <button class="btn-buy disabled" disabled style="background-color: #e6e6e6 !important; color: #999999 !important; cursor: not-allowed;">
            <i class="fa-solid fa-ban"></i> Stok Habis
        </button>
    @endif
</div>
```
*Catatan Desain:*
1. Teks petunjuk sisa stok (`.pi-qty-hint`) di samping tombol beli dihapus agar tidak terjadi duplikasi visual dengan baris stok di bawah SKU.
2. Pada produk stok habis (`stok <= 0`), modifier `.is-out-of-stock` diterapkan pada `.pi-actions` (`flex-direction: column; align-items: flex-start; gap: 14px;`) sehingga tombol non-aktif "Stok Habis" tertata rapi persis di bawah kotak kuantitas, dengan lebar proporsional (`max-width: 360px`).

4. **Tabel Spesifikasi (`spec-table`)**:
```blade
<tr>
    <td>Ketersediaan Stok</td>
    <td>
        @if ($product->jenis_produk === 'kustom' || request()->query('custom') == 1)
            <span class="spec-stock-badge custom">Pre-Order (Dibuat Sesuai Pesanan)</span>
        @elseif ($product->stok <= 0)
            <span class="spec-stock-badge out">Habis (Sedang Diproduksi Ulang)</span>
        @elseif ($product->stok <= 3)
            <span class="spec-stock-badge low">Sisa {{ $product->stok }} unit (Segera Habis)</span>
        @else
            <span class="spec-stock-badge in">{{ $product->stok }} unit (Ready Stock / Siap Kirim)</span>
        @endif
    </td>
</tr>
```

---

### B. Template Layout Modal Pelanggan (`resources/views/Layout/layout_pelanggan.blade.php`)

Menambahkan kontainer `#pmQtyHint` di sebelah `.pm-qty`:

```blade
<!-- Action Controls (Aligned 48px Height) -->
<div class="pm-actions" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
    <div class="pm-qty">
        <button type="button" class="pm-qty-btn" onclick="updateModalQty(-1)" aria-label="Kurangi kuantitas">
            <i class="fa-solid fa-minus"></i>
        </button>
        <input type="text" value="1" id="pmQtyInput" class="pm-qty-input" readonly aria-label="Jumlah pesanan">
        <button type="button" class="pm-qty-btn" onclick="updateModalQty(1)" aria-label="Tambah kuantitas">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>
    <span class="pm-qty-hint" id="pmQtyHint" style="display: none;"></span>
```

---

### C. Logika JavaScript Modal Preview (`resources/js/pelanggan_pages.js`)

Memperbarui fungsi `openProductModal(btn)` untuk memformat teks stok dinamis dan mengatur kelas badge:

```javascript
const currentStock = window.currentModalProductStock;
if (pmStock) {
    if (currentStock <= 0 && !isCustom) {
        pmStock.className = 'pm-stock-pill out';
        pmStock.innerHTML = '<span class="pm-status-dot"></span> <span class="pm-stock-text">Stok Habis</span>';
        if (pmQtyHint) {
            pmQtyHint.className = 'pm-qty-hint out';
            pmQtyHint.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Stok Habis';
            pmQtyHint.style.display = 'inline-flex';
        }
        // ... disabled buttons & qty
    } else {
        if (isCustom) {
            pmStock.className = 'pm-stock-pill custom';
            pmStock.innerHTML = '<span class="pm-status-dot"></span> <span class="pm-stock-text">Pre-Order / Dibuat Sesuai Pesanan</span>';
            if (pmQtyHint) {
                pmQtyHint.className = 'pm-qty-hint custom';
                pmQtyHint.innerHTML = '<i class="fa-solid fa-clock-rotate-left"></i> Made by Order';
                pmQtyHint.style.display = 'inline-flex';
            }
        } else if (currentStock <= 3) {
            pmStock.className = 'pm-stock-pill low';
            pmStock.innerHTML = `<span class="pm-status-dot"></span> <span class="pm-stock-text">Sisa ${currentStock} unit lagi (Segera Habis!)</span>`;
            if (pmQtyHint) {
                pmQtyHint.className = 'pm-qty-hint low';
                pmQtyHint.innerHTML = `<i class="fa-solid fa-triangle-exclamation"></i> Tersisa ${currentStock} unit`;
                pmQtyHint.style.display = 'inline-flex';
            }
        } else {
            pmStock.className = 'pm-stock-pill in';
            pmStock.innerHTML = `<span class="pm-status-dot"></span> <span class="pm-stock-text">Tersedia: ${currentStock} unit</span>`;
            if (pmQtyHint) {
                pmQtyHint.className = 'pm-qty-hint in';
                pmQtyHint.innerHTML = `<i class="fa-solid fa-box-archive"></i> Tersedia ${currentStock} unit`;
                pmQtyHint.style.display = 'inline-flex';
            }
        }
    }
}
```

---

### D. Lembar Gaya CSS (`resources/css/pelanggan_custom.css`)

Menambahkan design token warna dan tata letak status:

```css
/* Status Dot */
.stock-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
    background-color: currentColor;
    flex-shrink: 0;
}

/* Detail Page Stock Text (Clean Text Without Background/Dot) */
.pi-stock {
    display: inline;
    font-size: 14px;
    font-weight: 600;
    background: none !important;
    border: none !important;
    padding: 0 !important;
    color: #00b207;
}
.pi-stock.in { color: #00b207; }
.pi-stock.low { color: #00b207; }
.pi-stock.out { color: #dc2626; }
.pi-stock.custom { color: #0284c7; }

/* Quick View Modal Stock Pill */
.pm-stock-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
    line-height: 1.4;
}
.pm-stock-pill.in { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.pm-stock-pill.low { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.pm-stock-pill.out { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.pm-stock-pill.custom { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }

/* Action Buttons & Out-Of-Stock Layout */
.pi-actions { display: flex; align-items: center; gap: 12px; margin-top: 25px; flex-wrap: wrap; }
.pi-actions.is-out-of-stock {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
}
.pi-actions.is-out-of-stock .btn-buy {
    width: 100%;
    max-width: 360px;
    flex: none;
}

/* Quick View Modal Micro-Hint */
.pm-qty-hint {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    color: #059669;
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    border-radius: 8px;
    padding: 6px 12px;
    line-height: 1.3;
}
.pm-qty-hint.low { color: #b45309; background: #fffbeb; border-color: #fde68a; }
.pm-qty-hint.out { color: #64748b; background: #f8fafc; border-color: #e2e8f0; }
.pm-qty-hint.custom { color: #0284c7; background: #f0f9ff; border-color: #bae6fd; }

/* Specification Table Badge */
.spec-stock-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
}
.spec-stock-badge.in { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
.spec-stock-badge.low { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
.spec-stock-badge.out { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.spec-stock-badge.custom { background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; }

/* Gallery Badges */
.gallery-badge.warning { background: #f59e0b; }
.gallery-badge.custom { background: #0284c7; }
.gallery-badge.out { background: #64748b; }
```

---

## 4. Hasil Pengujian & Verifikasi

### A. Automated Unit & Feature Testing
Dibuat test suite baru `tests/Feature/ProductDetailStockInfoTest.php` untuk memvalidasi:
1. `test_detail_page_renders_abundant_stock_information`: Memastikan produk dengan stok $>3$ menampilkan teks `"Tersedia: 8 unit"`, `"8 unit (Ready Stock / Siap Kirim)"`, dan memastikan hint sampingan dihapus (`assertDontSee('Tersisa 8 unit')`).
2. `test_detail_page_renders_low_stock_warning_information`: Memastikan produk dengan stok $\le 3$ menampilkan label `"Sisa 2 unit lagi (Segera Habis!)"`.
3. `test_detail_page_renders_out_of_stock_information`: Memastikan produk dengan stok $0$ menampilkan status `"Stok Habis"`, `"Habis (Sedang Diproduksi Ulang)"`, dan menerapkan modifier layout vertikal `is-out-of-stock`.
4. `test_detail_page_renders_custom_product_preorder_information`: Memastikan produk kustom menampilkan status `"Pre-Order / Dibuat Sesuai Pesanan"` dan spesifikasi pre-order.

Hasil pengujian otomatis:
```
PASS  Tests\Feature\ProductDetailStockInfoTest
✓ detail page renders abundant stock information                                0.38s  
✓ detail page renders low stock warning information                             0.02s  
✓ detail page renders out of stock information                                  0.03s  
✓ detail page renders custom product preorder information                       0.02s  

Tests:    4 passed (17 assertions)
Total Keseluruhan Proyek: 110 passed (522 assertions) - 100% Green
```

### B. Headless Browser Smoke Check
1. **Halaman Detail Produk Tersedia (`/product/1`)**:
   - Baris info produk: `Stok : Tersedia: 10 unit` (teks hijau `#00b207` di bawah baris SKU, tanpa badge/dot).
   - Kontrol aksi: Hanya `[ - | 1 | + ]` dan `[ Beli Sekarang ]` berdampingan secara horizontal dengan gap 12px tanpa teks hint sisa unit yang mengganggu.
2. **Halaman Detail Produk Stok Habis (`/product/3`)**:
   - Baris info produk: `Stok : Stok Habis`.
   - Kontrol aksi: Kotak kuantitas disabled `[ - | 0 | + ]` berada di atas, dan tombol `[ 🚫 Stok Habis ]` berada persis di bawahnya (`top: 747.3px` vs `top: 685.3px`, `flexDirection: column`, `max-width: 360px`).
3. **Modal Quick View (`/shop` -> Klik Quick View)**:
   - `pmStock`: `"Tersedia: 9 unit"`.

---

## 5. Ringkasan File yang Diubah

| No | File | Deskripsi Perubahan |
| :--- | :--- | :--- |
| 1 | `resources/views/Pelanggan/detail.blade.php` | Menghapus elemen `.pi-qty-hint` di samping tombol aksi dan menambahkan modifier `.is-out-of-stock` agar tombol non-aktif tertata di bawah kotak kuantitas. |
| 2 | `resources/views/Layout/layout_pelanggan.blade.php` | Menambahkan elemen `#pmQtyHint` di samping kontrol kuantitas modal quick view. |
| 3 | `resources/js/pelanggan_pages.js` | Mengaktifkan rendering teks stok dinamis dan sinkronisasi hint kuantitas pada fungsi `openProductModal`. |
| 4 | `resources/css/pelanggan_custom.css` | Menambahkan styling teks stok polos hijau (`.pi-stock`), layout vertikal `.pi-actions.is-out-of-stock`, serta lebar proporsional untuk tombol disabled. |
| 5 | `tests/Feature/ProductDetailStockInfoTest.php` | Mengunci kontrak tampilan stok terbaru dan memastikan hint sisa unit tidak lagi muncul di samping tombol beli. |
| 6 | `docs/penambahan_informasi_stok_detail_dan_modal_preview.md` | Dokumentasi teknis lengkap dan catatan perubahan kode. |

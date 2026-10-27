# Dokumentasi Refactoring & Penataan Tampilan Modal Preview Produk (Quick View)

## 1. Ringkasan & Latar Belakang Masalah
Sebelum perbaikan, modal preview produk (`#productModal`) pada antarmuka pelanggan Zyh Corner memiliki beberapa kelemahan visual dan teknis:
1. **Banyak Menggunakan Inline Styles**: Terdapat belasan properti CSS yang disematkan langsung pada elemen HTML Blade (`style="..."`), menyulitkan pemeliharaan, menyebabkan inkonsistensi tata letak, dan memicu isu spesifisitas.
2. **Ketinggian Kontrol Aksi Tidak Sejajar**: Tombol aksi utama (*Beli Sekarang*), *Add to Cart*, dan pemilih kuantitas (*Quantity*) memiliki ukuran tinggi, radius sudut, dan padding yang tidak seragam sehingga terlihat berantakan.
3. **Hirarki Tipografi Kurang Rapi**: Nama produk langsung berhimpitan dengan rating dan harga tanpa adanya *category eyebrow* atau pemisah visual yang teratur.
4. **Metadata Berupa Teks Polos Mentah**: Informasi kategori dan tags ditampilkan sebagai rentetan teks biasa yang dipisahkan koma tanpa representasi visual modern berbentuk chip (*badge pill*).
5. **Tombol Close Kaku**: Tombol penutup modal berupa teks '×' biasa tanpa latar belakang, radius lingkaran, atau *feedback hover* yang jelas.
6. **Thumbnail Tunggal Menampilkan Kontainer Kosong/Redundan**: Jika produk hanya memiliki satu foto, panel thumbnail tetap memakan tempat di sebelah kiri foto utama secara tidak ergonomis.

Melalui refactoring ini, modal ditata ulang mengikuti panduan **Frontend Design Direction** untuk toko roti modern (*warm, appetizing, clean, and trustworthy*), menghilangkan seluruh *inline styling*, memastikan seluruh kontrol aksi memiliki tinggi presisi **48px**, serta melengkapi visual dengan *glassmorphism backdrop* dan *scannable metadata chips*.

---

## 2. Rincian Perubahan Kode

### A. Template Blade (`resources/views/Layout/layout_pelanggan.blade.php`)
- **Menghilangkan seluruh inline styles** pada `#productModal`, `.product-modal`, `.pm-gallery`, `.pm-details`, `.pm-actions`, dan `.pm-info`.
- **Menambahkan elemen struktur visual**:
  - `#pmEyebrow`: Kategori produk sebagai label pelengkap di atas judul.
  - `.pm-meta-row`: Menyatukan status ketersediaan stok (`#pmStock` dengan dot indikator `.pm-status-dot`) dan kode SKU (`#pmSku`).
  - `.pm-price-area`: Area harga dengan warna hijau segar `#00b207` dan badge diskon terstruktur.
  - `.pm-actions`: Baris aksi yang menampung `.pm-qty`, `.pm-buy-btn`, dan `.pm-cart-btn`.
  - `.pm-info-footer`: Menampilkan kategori dan tags dalam bentuk chip badges `.pm-chip`.

```html
<!-- Cuplikan Markup Baru Modal Produk -->
<div class="product-modal-overlay" id="productModal" onclick="if(event.target === this) closeProductModal();">
    <div class="product-modal" role="dialog" aria-modal="true" aria-labelledby="pmName">
        <button type="button" class="close-modal" onclick="closeProductModal()" aria-label="Tutup preview modal">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <div class="product-modal-content">
            <!-- Galeri Foto Produk -->
            <div class="pm-gallery">
                <div class="pm-thumbnails-container" id="pmThumbnailsContainer">
                    <button type="button" class="pm-thumb-nav" id="pmThumbPrev" onclick="scrollModalThumbs(-1)">
                        <i class="fa-solid fa-chevron-up"></i>
                    </button>
                    <div class="pm-thumbnails" id="pmThumbnails"></div>
                    <button type="button" class="pm-thumb-nav" id="pmThumbNext" onclick="scrollModalThumbs(1)">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
                <div class="pm-main-image">
                    <img id="pmMainImage" src="" alt="Product Preview">
                </div>
            </div>

            <!-- Detail Produk -->
            <div class="pm-details">
                <div class="pm-header-group">
                    <span class="pm-eyebrow" id="pmEyebrow">Kategori</span>
                    <h2 class="pm-title" id="pmName">Nama Produk</h2>
                    <div class="pm-meta-row">
                        <span class="pm-stock-pill" id="pmStock">
                            <span class="pm-status-dot"></span>
                            <span class="pm-stock-text">Tersedia</span>
                        </span>
                        <span class="pm-meta-separator">•</span>
                        <span class="pm-sku-text">SKU: <strong id="pmSku">ZYH-0001</strong></span>
                    </div>
                </div>

                <div class="pm-price-area">
                    <span class="pm-new-price" id="pmNewPrice"></span>
                    <span class="pm-old-price" id="pmOldPrice" style="display: none;"></span>
                    <span class="pm-discount-badge" id="pmDiscountBadge" style="display: none;"></span>
                </div>

                <div class="pm-desc" id="pmDesc"></div>

                <div class="pm-actions">
                    <div class="pm-qty">
                        <button type="button" class="pm-qty-btn" onclick="updateModalQty(-1)"><i class="fa-solid fa-minus"></i></button>
                        <input type="text" value="1" id="pmQtyInput" class="pm-qty-input" readonly>
                        <button type="button" class="pm-qty-btn" onclick="updateModalQty(1)"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <button type="button" class="pm-buy-btn" id="pmBuyBtn">
                        Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
                    </button>
                    <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </button>
                </div>

                <div class="pm-info-footer">
                    <div class="pm-info-item">
                        <span class="pm-info-label">Kategori:</span>
                        <span class="pm-chip pm-chip-primary" id="pmCategory">-</span>
                    </div>
                    <div class="pm-info-item pm-tags-item">
                        <span class="pm-info-label">Tags:</span>
                        <div class="pm-tags-list" id="pmTag"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

---

### B. Styling Terpadu (`public/css/pelanggan.css` & `resources/css/pelanggan.css`)
- **Backdrop Blur & Glassmorphism**:
  `.product-modal-overlay` menggunakan `backdrop-filter: blur(6px)` dan background gelap transparan `rgba(15, 23, 42, 0.65)`.
- **Close Button Modern**:
  Lingkaran 36px dengan warna slate lembut, berotasi 90 derajat saat hover dan berubah warna menjadi merah lembut (`#fee2e2` / `#ef4444`).
- **Penyelarasan Kontrol Aksi (48px Uniform Height)**:
  - `.pm-qty`: Tinggi 48px, border radius 24px, tombol bundar 34px untuk `-` dan `+`.
  - `.pm-buy-btn`: Tinggi 48px (`min-height: 48px`), pill radius 24px, bayangan hijau lembut `0 4px 14px rgba(0, 178, 7, 0.25)`.
  - `.pm-cart-btn`: Dimensi 48px × 48px bundar sempurna, border hijau dengan warna aksen lembut.
- **Chips Metadata (`.pm-chip`)**:
  Badge pill yang mudah dipindai dengan radius 6px, latar netral `#f1f5f9`, dan varian aksen `#e8f8ed` untuk kategori.
- **Responsivitas Perangkat Mobile**:
  Pada layar tablet dan smartphone (≤ 768px dan ≤ 576px), galeri beralih ke layout vertikal dan tombol aksi menyesuaikan lebar layar dengan tetap menjaga tinggi sentuh minimum 48px.

---

### C. Logika JavaScript Interaktif (`resources/js/pelanggan_pages.js`)
1. **Binding Kategori ke Eyebrow**: Mengisi `#pmEyebrow` dari `data.cat` untuk memperjelas konteks produk.
2. **Status Indikator Dinamis**:
   - Stok tersedia: `.pm-stock-pill` hijau dengan `.pm-status-dot`.
   - Stok habis: `.pm-stock-pill.out` merah dengan teks *Stok Habis* serta menonaktifkan kontrol kuantitas.
   - Produk custom: `.pm-stock-pill.custom` biru muda dengan teks *Pre-Order / Kustom*.
3. **Smart Gallery & Navigation**:
   - Jika foto produk hanya 1 (`images.length <= 1`), kontainer thumbnail `#pmThumbnailsContainer` disembunyikan otomatis sehingga foto utama melebar secara bersih.
   - Jika foto produk ≤ 3, tombol panah scroll `#pmThumbPrev` dan `#pmThumbNext` disembunyikan karena seluruh thumbnail muat tanpa scrolling.
   - Menambahkan fungsi pembantu `window.scrollModalThumbs(direction)` untuk navigasi thumbnail bertahap.
4. **Rendering Tag Chips Dinamis**: Mengubah array tag menjadi elemen badge individual `.pm-chip` di dalam `#pmTag`.

---

## 3. Hasil Pengujian & Verifikasi Visual

Pengujian dilakukan menggunakan browser headless Playwright pada resolusi Desktop (1706×960) dan Mobile (390×844):

| Parameter Uji | Resolusi Desktop | Resolusi Mobile (390px) | Status |
| :--- | :--- | :--- | :--- |
| **Visibilitas Modal** | Tampil (`display: block / flex`) | Tampil (`width: 333px`) | **Lolos** |
| **Tinggi Selector Qty (`.pm-qty`)** | 48px | 48px | **Lolos** |
| **Tinggi Tombol Beli (`#pmBuyBtn`)** | 48px | 48px | **Lolos** |
| **Tinggi Tombol Keranjang (`#pmCartBtn`)** | 48px | 48px | **Lolos** |
| **Keseragaman Tinggi Kontrol** | 100% Cocok (Semua 48px) | 100% Cocok (Semua 48px) | **Lolos** |
| **Interaksi Quantity Counter** | Nilai bertambah dari 1 ke 2 saat tombol `+` ditekan | Bekerja responsif | **Lolos** |
| **Penutupan Modal (`.close-modal`)** | Modal tertutup sempurna (`display: none`) | Modal tertutup sempurna | **Lolos** |
| **Representasi Visual Tags** | Badge chip `.pm-chip` individual | Badge chip rapi membungkus | **Lolos** |

Tangkapan layar hasil verifikasi disimpan pada:
- `docs/product_modal_preview_desktop.png` (Tampilan Desktop)
- `docs/product_modal_preview_mobile.png` (Tampilan Mobile)

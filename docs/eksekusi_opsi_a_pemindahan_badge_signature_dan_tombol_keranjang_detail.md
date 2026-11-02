# Eksekusi Opsi A: Pemindahan Badge Signature ke Floating Tag Foto & Penambahan Tombol Keranjang Halaman Detail

**Tanggal Eksekusi:** 31 Oktober 2026  
**Status:** Berhasil Diimplementasikan & Diverifikasi Penuh (211 Tests Passing / 1138 Assertions)  
**Tujuan Desain:**  
1. Menata ulang penempatan badge **Signature** agar menyatu estetis secara visual tanpa memotong padding modal preview atau terlempar keluar dari alur informasi.
2. Menambahkan tombol **Keranjang Belanja** (`.btn-cart-detail`) pada halaman detail produk (`detail.blade.php`) berdampingan dengan tombol beli sekarang / stok habis dalam satu baris horizontal yang selaras.

---

## 1. Analisis Masalah & Keputusan Arsitektur Desain (Option A)

### A. Masalah Badge Signature Sebelumnya
* **Akar Masalah CSS:**  
  Pada `public/css/home.css` baris 594, terdapat aturan:
  ```css
  .signature-badge:not(.eyebrow-signature) {
      position: absolute;
      top: 10px;
      left: 10px;
      ...
  }
  ```
  Pada modal preview sebelumnya, `#pmSignatureBadge` diletakkan di dalam `.pm-eyebrow-row` dengan class `signature-badge pm-signature-badge`. Karena tidak memiliki class `.eyebrow-signature`, aturan global `home.css` memaksa badge tersebut memiliki `position: absolute; top: 10px; left: 10px;` relatif terhadap container modal (`.product-modal`). Akibatnya, badge oranye tersebut terlempar keluar ke pojok kiri atas modal, menabrak padding, dan memotong foto produk.
* **Solusi Desain (Option A — Floating Luxury Tag):**  
  Badge Signature dipindahkan langsung ke dalam bingkai foto utama (`.pm-main-image` pada modal dan `.gallery-main` pada halaman detail). Menggunakan styling *frosted glass* mewah dengan gradasi Amber emas (`rgba(217, 119, 6, 0.95)` ke `rgba(180, 83, 9, 0.95)`), ikon mahkota emas cerah (`#fef08a`), border semi-transparan, dan bayangan lembut. Pada galeri halaman detail, posisinya diletakkan di pojok kanan atas (`top: 15px; right: 15px;`) sehingga seimbang dengan badge stok (`.gallery-badge`) di pojok kiri atas (`top: 15px; left: 15px;`).

### B. Ketiadaan Tombol Keranjang di Halaman Detail Produk
* **Akar Masalah UI/UX:**  
  Pada `resources/views/Pelanggan/detail.blade.php`, pengguna hanya disajikan tombol `[ Beli Sekarang ]` (yang langsung mengarahkan ke halaman cart/checkout) atau `[ Stok Habis ]`. Pengguna tidak dapat menambahkan produk ke keranjang sambil tetap berada di halaman detail untuk melihat-lihat produk lainnya.
* **Solusi Desain:**  
  Menambahkan tombol keranjang bulat 48px (`.btn-cart-detail`) di samping tombol beli sekarang / stok habis:
  * **Stok Tersedia:** Tombol hijau muda elegan (`#e8f8ed` latar, border `1.5px solid #00b207`, ikon `#00b207`) yang memicu fungsi `addToCartFromDetail()` dan otomatis membuka drawer keranjang belanja.
  * **Stok Habis:** Tombol abu-abu dinonaktifkan (`disabled`, `#f1f5f9` latar, border `#e2e8f0`, ikon `#94a3b8`) berdampingan dalam satu baris horizontal dengan tombol `[ Stok Habis 🚫 ]`.
  * **Produk Kustom:** Dikecualikan karena alur pemesanan khusus langsung dialihkan ke WhatsApp.

---

## 2. Rincian File dan Kode yang Diubah

### A. Template Blade Modal Preview (`resources/views/Layout/layout_pelanggan.blade.php`)
* **Perubahan:** Memindahkan `#pmSignatureBadge` dari `.pm-eyebrow-row` ke dalam `.pm-main-image` menggunakan class `.floating-signature-tag`.
* **Cuplikan Kode:**
  ```html
  <div class="pm-gallery">
      <div class="pm-thumbnails-container" id="pmThumbnailsContainer">
          ...
      </div>
      <div class="pm-main-image">
          <span class="floating-signature-tag" id="pmSignatureBadge" style="display: none;">
              <i class="fa-solid fa-crown"></i> Signature
          </span>
          <img id="pmMainImage" src="" alt="Product Preview">
      </div>
  </div>

  <div class="pm-details">
      <div class="pm-header-group">
          <div class="pm-eyebrow-row">
              <span class="pm-eyebrow" id="pmEyebrow">Kategori Produk</span>
          </div>
          <h2 class="pm-title" id="pmName">Product Name</h2>
  ```

---

### B. Template Blade Detail Produk (`resources/views/Pelanggan/detail.blade.php`)
* **Perubahan 1:** Menambahkan `.floating-signature-tag` ke dalam `.gallery-main`.
* **Perubahan 2:** Menambahkan `.btn-cart-detail` di samping tombol `[ Beli Sekarang ]` dan `[ Stok Habis ]` pada `.pi-actions`.
* **Cuplikan Kode:**
  ```html
  <div class="gallery-main">
      @if ($product->is_signature)
          <span class="floating-signature-tag">
              <i class="fa-solid fa-crown"></i> Signature
          </span>
      @endif
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
  ```html
  <div class="pi-actions {{ $product->stok <= 0 && $product->jenis_produk !== 'kustom' && request()->query('custom') != 1 ? 'is-out-of-stock' : '' }}">
      @if (request()->query('custom') == 1 || $product->jenis_produk === 'kustom')
          ...
      @elseif ($product->stok > 0)
          <div class="qty-box">
              <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
              <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok }}" oninput="changeQty(0)">
              <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
          </div>
          <button class="btn-buy" onclick="buyNow()">
              <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
          </button>
          <button type="button" class="btn-cart-detail" onclick="addToCartFromDetail()" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
              <i class="fa-solid fa-cart-shopping"></i>
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
          <button type="button" class="btn-cart-detail disabled" disabled title="Stok Habis" aria-label="Stok habis">
              <i class="fa-solid fa-cart-shopping"></i>
          </button>
      @endif
  </div>
  ```

---

### C. Logika JavaScript Halaman Detail (`resources/js/pelanggan_pages.js`)
* **Perubahan:** Menambahkan fungsi global `window.addToCartFromDetail()` yang mengambil data dari `window.detailConfig` dan nilai input kuantiti, lalu memanggil `window.addToCart(..., true)` untuk menambahkan item ke keranjang dan membuka drawer keranjang.
* **Cuplikan Kode:**
  ```javascript
  window.addToCartFromDetail = function() {
      if (['admin', 'kasir'].includes(window.layoutConfig?.userRole)) {
          window.showAdminShoppingNotice();
          return false;
      }
      if (!window.isLoggedIn) {
          window.location.href = window.detailConfig?.loginRoute || "/login";
          return;
      }

      if (window.detailConfig) {
          const conf = window.detailConfig;
          const qty = parseInt(document.getElementById('qtyInput')?.value || 1);
          window.addToCart({
              id: conf.productId,
              name: conf.productName,
              price: conf.productPrice,
              bisa_custom_teks: conf.bisaCustomTeks || false,
              img: conf.productImg,
              qty: qty
          }, true);
      }
  };
  ```

---

### D. CSS Styling Terintegrasi
Diperbarui secara tersinkronisasi pada `resources/css/pelanggan_pages.css`, `resources/css/pelanggan.css`, `resources/css/pelanggan_custom.css`, dan `public/css/pelanggan.css`:

```css
/* ==========================================================================
   FLOATING SIGNATURE TAG & DETAIL PAGE CART BUTTON (OPTION A)
   ========================================================================== */
.floating-signature-tag {
    position: absolute !important;
    top: 14px !important;
    left: 14px !important;
    right: auto !important;
    background: linear-gradient(135deg, rgba(217, 119, 6, 0.95), rgba(180, 83, 9, 0.95)) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    color: #ffffff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    letter-spacing: 0.6px !important;
    text-transform: uppercase !important;
    padding: 5px 12px !important;
    border-radius: 20px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    box-shadow: 0 4px 14px rgba(217, 119, 6, 0.35) !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    z-index: 5 !important;
    pointer-events: none !important;
    line-height: 1 !important;
}

.floating-signature-tag i {
    font-size: 10px !important;
    color: #fef08a !important;
}

/* Detail page gallery positioning: top-right to balance with top-left stock badge */
.gallery-main .floating-signature-tag {
    top: 15px !important;
    right: 15px !important;
    left: auto !important;
}

/* Detail Page Cart Button */
.btn-cart-detail {
    width: 48px !important;
    height: 48px !important;
    min-width: 48px !important;
    min-height: 48px !important;
    border-radius: 50% !important;
    border: 1.5px solid #00b207 !important;
    background: #e8f8ed !important;
    color: #00b207 !important;
    cursor: pointer !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 18px !important;
    transition: all 0.25s ease !important;
    flex-shrink: 0 !important;
    padding: 0 !important;
    box-shadow: 0 2px 6px rgba(0, 178, 7, 0.12) !important;
}

.btn-cart-detail:hover {
    background: #00b207 !important;
    color: #ffffff !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0, 178, 7, 0.28) !important;
}

.btn-cart-detail.disabled,
.btn-cart-detail:disabled {
    background: #f1f5f9 !important;
    color: #94a3b8 !important;
    border: 1.5px solid #e2e8f0 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    transform: none !important;
}

/* Out of stock 1-row layout on detail page */
.pi-actions.is-out-of-stock {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 12px !important;
}

.pi-actions.is-out-of-stock .btn-buy {
    flex: 1 !important;
    width: auto !important;
    max-width: none !important;
}

.pi-actions.is-out-of-stock .btn-cart-detail {
    display: inline-flex !important;
}
```

### E. Perlindungan Selektor CSS Global (`public/css/home.css` & `public/css/css_home.css`)
* Menambahkan pengecualian `:not(.floating-signature-tag)` pada selektor `.signature-badge:not(.eyebrow-signature)` agar badge signature floating di dalam foto tidak pernah tertarik keluar oleh stylesheet beranda.

---

## 3. Hasil Pengujian & Verifikasi Mutu

1. **Vite Build (`npm run build`):**
   * Berhasil dikompilasi sempurna dalam 634ms tanpa peringatan syntax error.
2. **Automated Test Suite (`php artisan test`):**
   * **Total Tests:** 211 tests lulus 100% (1138 assertions).
   * **Uji Signature:** `SignatureProductsTest` lulus penuh (termasuk verifikasi badge detail page dan `#pmSignatureBadge`).
3. **Pembersihan Artefak:**
   * `git clean -fd public/uploads/` dijalankan segera setelah pengujian untuk menjaga direktori publik tetap bersih.

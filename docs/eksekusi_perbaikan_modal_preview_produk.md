# Dokumentasi Eksekusi: Perbaikan Tata Letak Modal Preview Produk (Opsi A — 2-Tier Action Dock)

**Tanggal Eksekusi:** 6 November 2026  
**Target Berkas:**
1. `resources/views/Layout/layout_pelanggan.blade.php` (Restrukturisasi Grup Tombol Aksi Modal `.pm-btn-group`)
2. `resources/css/pelanggan_pages.css` (Tata Letak Opsi A 2-Tier Action Dock & Perbaikan Kotak Harga)
3. `public/css/pelanggan.css` & `resources/css/pelanggan.css` (Sinkronisasi CSS Statis)
4. `resources/css/pelanggan_custom.css` (Sinkronisasi Flexbox `.pm-actions` & `.pm-btn-group`)
**Metodologi:** `skill://frontend-design-direction`  
**Status Pengujian:** 221 Tests Passed (1196 Assertions) — 100% Lulus  
**Integritas Database MySQL:** Utuh & Tidak Terdampak (Users: 16, Products: 13, Orders: 25)

---

## 1. Audit Visual & Identifikasi Masalah (`image-c06a61958b050fef.png`)

Berdasarkan inspeksi piksel tangkapan layar mobile (`383×835`) dan pengukuran geometri DOM pada `#productModal`:

1. **Tombol Keranjang Terpotong (*Clipped*) & Overflow Horizontal pada `.pm-actions`:**
   - Pada layar ponsel (`383px`), lebar modal adalah `326px–343px` dan lebar bersih dalam `.pm-details` hanya `284px–311px`.
   - Sebelumnya, `.pm-actions` memaksa 3 elemen dalam 1 baris tanpa *wrapping* (`flex-wrap: nowrap; gap: 12px;`):
     - `.pm-qty` (`125px`, `flex-shrink: 0`)
     - `.pm-buy-btn` (`141px`, dengan `padding: 0 24px`)
     - `.pm-cart-btn` (`48px`, `flex-shrink: 0`)
   - Total kebutuhan lebar minimal mencapai `338px` (`> 299px`), sehingga `.pm-cart-btn` terdorong ke koordinat `x=330..378` melewati batas kanan modal (`x=363`) dan **terpotong lebih dari 75%**.
2. **Teks Tombol "Beli Sekarang" Terjepit Menjadi 2 Baris:**
   - Akibat himpitan horizontal dan `padding: 0 24px`, ruang teks dalam `.pm-buy-btn` hanya tersisa `93px`. Kata `"Beli"` dan `"Sekarang"` terlipat menjadi 2 baris di dalam tombol setinggi `48px`.
3. **Harga & Diskon di `.pm-price-area` Terbelah Menjadi 2 Baris:**
   - Ketiadaan `white-space: nowrap` dan `flex-wrap: wrap` pada `.pm-price-area` menyebabkan `"Rp."` dan `"135.000"`, harga coret `"Rp."` dan `"150.000"`, serta badge `"-10%"` dan `"OFF"` masing-masing patah menjadi 2 baris vertikal.

---

## 2. Arsitektur Solusi (Opsi A — Unified 2-Tier Action Dock)

### A. Restrukturisasi DOM `.pm-actions` (`layout_pelanggan.blade.php`)
Membungkus `#pmCartBtn` dan `#pmBuyBtn` ke dalam `<div class="pm-btn-group">` agar konsisten dengan komponen `.pi-btn-group` pada halaman Detail Produk (`detail.blade.php`):

```html
<div class="pm-actions">
    <div class="pm-qty">
        <button type="button" class="pm-qty-btn" onclick="updateModalQty(-1)" aria-label="Kurangi kuantitas">
            <i class="fa-solid fa-minus"></i>
        </button>
        <input type="text" value="1" id="pmQtyInput" class="pm-qty-input" readonly aria-label="Jumlah pesanan">
        <button type="button" class="pm-qty-btn" onclick="updateModalQty(1)" aria-label="Tambah kuantitas">
            <i class="fa-solid fa-plus"></i>
        </button>
    </div>
    <div class="pm-btn-group">
        <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
            <i class="fa-solid fa-cart-shopping"></i>
        </button>
        <button type="button" class="pm-buy-btn" id="pmBuyBtn" style="{{ $isCustomContext ? 'background-color: #25d366;' : '' }}">
            @if ($isCustomContext)
                Pesan via WhatsApp <i class="fa-brands fa-whatsapp"></i>
            @else
                Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
            @endif
        </button>
    </div>
    <div id="pmStockWarning" class="pm-stock-warning" style="display: none;">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok produk tercapai
    </div>
</div>
```

### B. Tata Letak Responsif Desktop & Mobile
1. **Desktop (`> 576px`):**
   - `.pm-actions` merender `.pm-qty` (`125px × 48px`) dan `.pm-btn-group` (`flex: 1`) dalam **1 baris sejajar** setinggi `48px`.
   - `.pm-buy-btn` menggunakan `white-space: nowrap; padding: 0 20px;` sehingga teks tombol selalu utuh 1 baris.
2. **Mobile (`<= 576px` — 2-Tier Action Dock):**
   - **Baris 1 (`.pm-qty`):** Pill pemilih kuantitas rata kiri berukuran `126px × 42px` (`background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 50px;`) dengan tombol minus/plus bulat `32px × 32px` berbayang halus.
   - **Baris 2 (`.pm-btn-group`):** Membentang `100%` lebar kontainer (`display: flex; gap: 10px;`):
     - `#pmCartBtn`: Squircle `46px × 46px` (`border-radius: 12px`), latar `#e8f8ed`, ikon hijau `#00b207`.
     - `#pmBuyBtn`: `flex: 1`, tinggi `46px` (`border-radius: 12px`), `white-space: nowrap`, mengisi penuh sisa lebar baris (`255px` pada layar `383px`).
   - **Kotak Harga (`.pm-price-area`):** Menggunakan `flex-wrap: wrap; align-items: center; gap: 6px 8px;` dan `white-space: nowrap` pada `.pm-new-price` (`18px`), `.pm-old-price` (`13px`), dan `.pm-discount-badge` (`10.5px`) sehingga ketiga elemen tampil utuh dalam 1 baris.

---

## 3. Hasil Verifikasi Geometri DOM (Headless Chrome CDP)

| Viewport | `modalScrollW` vs `modalClientW` | Geometri `.pm-price-area` | Geometri Baris 1 (`.pm-qty`) | Geometri Baris 2 (`.pm-btn-group`) | Overflow Kanan |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Mobile `383×835`** | `343px === 343px` | `width: 311px, height: 45px` (Harga, harga coret & diskon utuh 1 baris) | `left: 36, width: 126, height: 42` | `cartBtn`: `46×46` (`x=36..82`)<br>`buyBtn`: `255×46` (`x=92..347`) | **`0px`** |
| **Mobile `375×812`** | `335px === 335px` | `width: 303px, height: 45px` (1 baris utuh) | `left: 36, width: 126, height: 42` | `cartBtn`: `46×46` (`x=36..82`)<br>`buyBtn`: `247×46` (`x=92..339`) | **`0px`** |
| **Desktop `1440×900`** | `960px === 960px` | `width: 428px, height: 48px` (1 baris utuh) | `left: 731, width: 125, height: 48` (Sejajar 1 baris `top: 524`) | `cartBtn`: `48×48` (`x=868..916`)<br>`buyBtn`: `231×48` (`x=928..1159`) | **`0px`** |

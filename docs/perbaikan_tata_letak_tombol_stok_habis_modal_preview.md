# Dokumentasi Perbaikan: Tata Letak Tombol Nonaktif Stok Habis pada Modal Preview Produk

## 1. Latar Belakang & Identifikasi Masalah (*Problem Statement*)

Berdasarkan inspeksi antarmuka pada fitur *Quick View* (Modal Preview Produk `#productModal`), ketika membuka produk yang berstatus **Stok Habis** (`stok <= 0`):
1. **Pemisahan Tombol Canggung (*Awkward Wrapping*)**:
   - Area aksi `.pm-actions` memuat input kuantitas disabled (`.pm-qty`), badge petunjuk sisa stok (`#pmQtyHint`), tombol disabled *"Stok Habis"* (`#pmBuyBtn`), dan tombol keranjang disabled (`#pmCartBtn`).
   - Karena keterbatasan lebar container (~380px), tombol keranjang (`#pmCartBtn`) terdorong sendirian ke baris kedua di pojok kiri bawah.
2. **Redundansi Teks "Stok Habis"**:
   - Status stok sudah ditampilkan dengan jelas dan elegan di bagian atas deskripsi produk melalui badge `.pm-stock-pill.out` bertuliskan `● Stok Habis`.
   - Menampilkan kembali badge `#pmQtyHint` di samping input kuantitas hanya menduplikasi teks yang sama dan mempersempit tombol aksi utama.
3. **Ketidaksinkronan dengan Halaman Detail Produk**:
   - Pada halaman detail produk (`/product/{id}`), saat stok habis tombol disabled *"Stok Habis"* diposisikan rapi di bawah kotak kuantitas, dan opsi tambah ke keranjang disembunyikan karena produk tidak dapat dibeli.

---

## 2. Rencana & Arahan Desain Antarmuka (*Frontend Design Direction*)

### A. Kontrak State Antarmuka

| Status Produk | Kontrol Kuantitas | Tombol Beli / Aksi Utama | Tombol Keranjang | Tata Letak (`.pm-actions`) |
| :--- | :--- | :--- | :--- | :--- |
| **Tersedia ($> 3$)** | Aktif (`[ - | 1 | + ]`) | Hijau `[ Beli Sekarang 🛍️ ]` | Aktif `[ 🛒 ]` | Horizontal 1 baris (`row`, gap 12px) |
| **Menipis ($1 - 3$)** | Aktif (`[ - | 1 | + ]`) | Hijau `[ Beli Sekarang 🛍️ ]` | Aktif `[ 🛒 ]` | Horizontal 1 baris (`row`, gap 12px) |
| **Kustom / PO** | Aktif (`[ - | 1 | + ]`) | Hijau WA `[ Pesan via WA 💬 ]` | Tersembunyi (`display: none`) | Horizontal 1 baris (`row`, gap 12px) |
| **Stok Habis ($0$)** | Non-aktif (`[ - | 0 | + ]`, grey) | Abu-abu `[ 🚫 Stok Habis ]` (disabled) | **Tersembunyi (`display: none`)** | **Vertikal 2 tingkat (`column`, gap 14px)** |

---

### B. Perbandingan Visual (Sebelum vs Sesudah)

#### 1. Kondisi Produk Stok Habis (`stok <= 0`):
```
[SEBELUM]
[ - | 0 | + ]   [❌ Stok Habis]   [ 🚫 Stok Habis ]
[ 🛒 ]  <-- Sendirian di baris 2 (Canggung & Terputus)

[SESUDAH]
┌─────────────┐
│ [ - | 0 | + ] │ (Disabled / Greyed Out, 125px)
└─────────────┘
┌───────────────────────────────────────┐
│ 🚫 Stok Habis                          │ (Disabled, 100% max 360px, tepat di bawah)
└───────────────────────────────────────┘
(Tombol keranjang disembunyikan karena produk habis)
```

#### 2. Kondisi Produk Tersedia (`stok > 0`):
```
[SEBELUM]
[ - | 1 | + ]   [📦 Tersedia 9 unit]
[ 🛍️ Beli Sekarang ]   [ 🛒 ]  <-- Terdorong ke baris 2

[SESUDAH]
[ - | 1 | + ]   [ 🛍️ Beli Sekarang ]   [ 🛒 ]  <-- 1 Baris Sejajar Rapi (Tinggi 48px)
```

---

## 3. Rincian Perubahan Kode

### A. Template Blade (`resources/views/Layout/layout_pelanggan.blade.php`)
- Menghapus style inline `style="display: flex; flex-wrap: wrap; ..."` dari pembungkus `.pm-actions` dan menghapus elemen `#pmQtyHint`.
```blade
<!-- Action Controls (Aligned 48px Height) -->
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
    @php
        $isCustomContext = request()->query('custom') == 1 || request()->routeIs('custom-products') || (isset($product) && isset($product->jenis_produk) && $product->jenis_produk === 'kustom');
    @endphp
    <button type="button" class="pm-buy-btn" id="pmBuyBtn" style="{{ $isCustomContext ? 'background-color: #25d366;' : '' }}">
        @if ($isCustomContext)
            Pesan via WhatsApp <i class="fa-brands fa-whatsapp"></i>
        @else
            Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
        @endif
    </button>
    <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
        <i class="fa-solid fa-cart-shopping"></i>
    </button>
    <div id="pmStockWarning" class="pm-stock-warning" style="display: none;">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok produk tercapai
    </div>
</div>
```

### B. JavaScript Interaksi Modal (`resources/js/pelanggan_pages.js`)
- Mengatur kelas modifier `.is-out-of-stock` pada `.pm-actions`.
- Menyembunyikan `#pmCartBtn` saat stok habis (`pmCartBtn.style.display = 'none'`) dan menonaktifkan stepper kuantitas.
- Mengembalikan susunan normal 1 baris saat produk tersedia.

```javascript
    const pmActions = document.querySelector('.pm-actions');
    const currentStock = window.currentModalProductStock;
    if (pmStock) {
        if (currentStock <= 0 && !isCustom) {
            pmStock.className = 'pm-stock-pill out';
            pmStock.innerHTML = '<span class="pm-status-dot"></span> <span class="pm-stock-text">Stok Habis</span>';
            if (pmActions) {
                pmActions.classList.add('is-out-of-stock');
            }

            if (pmBuyBtn) {
                pmBuyBtn.style.backgroundColor = '';
                pmBuyBtn.classList.add('disabled');
                pmBuyBtn.disabled = true;
                pmBuyBtn.innerHTML = 'Stok Habis <i class="fa-solid fa-ban"></i>';
                pmBuyBtn.onclick = null;
            }
            if (pmCartBtn) {
                pmCartBtn.style.display = 'none';
                pmCartBtn.classList.add('disabled');
                pmCartBtn.disabled = true;
                pmCartBtn.onclick = null;
            }
            const pmQtyContainer = document.querySelector('.pm-qty');
            if (pmQtyContainer) {
                pmQtyContainer.style.opacity = '0.5';
                pmQtyContainer.style.pointerEvents = 'none';
            }
            const pmQtyInputVal = document.getElementById('pmQtyInput');
            if (pmQtyInputVal) {
                pmQtyInputVal.value = 0;
                pmQtyInputVal.disabled = true;
            }
        } else {
            if (pmActions) {
                pmActions.classList.remove('is-out-of-stock');
            }
            const pmQtyContainer = document.querySelector('.pm-qty');
            if (pmQtyContainer) {
                pmQtyContainer.style.opacity = '';
                pmQtyContainer.style.pointerEvents = '';
            }
            const pmQtyInputVal = document.getElementById('pmQtyInput');
            if (pmQtyInputVal) {
                pmQtyInputVal.disabled = false;
                if (parseInt(pmQtyInputVal.value) <= 0) {
                    pmQtyInputVal.value = 1;
                }
            }
            if (pmCartBtn) {
                pmCartBtn.style.display = isCustom ? 'none' : 'inline-flex';
                pmCartBtn.classList.remove('disabled');
                pmCartBtn.disabled = false;
            }
            // ...
```

### C. Lembar Gaya CSS (`resources/css/pelanggan_custom.css` & `resources/css/pelanggan.css`)
```css
/* Modal Quick View Actions */
.pm-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: nowrap;
}

.pm-actions.is-out-of-stock {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 14px;
}

.pm-actions.is-out-of-stock .pm-buy-btn {
    width: 100%;
    max-width: 360px;
    flex: none;
    background-color: #e2e8f0 !important;
    color: #94a3b8 !important;
    cursor: not-allowed !important;
    box-shadow: none !important;
    transform: none !important;
}

.pm-actions.is-out-of-stock .pm-cart-btn {
    display: none !important;
}
```

---

## 4. Hasil Verifikasi & Pengujian

1. **Automated Test Suite**:
   - `php artisan test`: **110 passed (522 assertions)** tanpa kegagalan.
2. **Kompilasi Asset Frontend**:
   - `npm run build`: Berhasil tanpa error (`pelanggan_pages.js` dan CSS ter-bundle rapi).
3. **Headless Browser Measurement**:
   - **Kondisi Stok Habis**:
     - `actionsClass`: `"pm-actions is-out-of-stock"`
     - `flexDirection`: `"column"`
     - `.pm-qty`: `top: 402.58px`, `left: 762.62px`, `width: 125px`, `height: 48px`, `val: 0`
     - `#pmBuyBtn`: `top: 464.58px`, `left: 762.62px`, `width: 360px`, `height: 48px`, `disabled: true`
     - `#pmCartBtn`: `display: none`
   - **Kondisi Stok Tersedia**:
     - `actionsClass`: `"pm-actions"`
     - `flexDirection`: `"row"`
     - `.pm-qty`: `top: 427.23px`, `width: 125px`, `height: 48px`
     - `#pmBuyBtn`: `top: 427.23px`, `width: 180.87px`, `height: 48px`
     - `#pmCartBtn`: `top: 427.23px`, `width: 48px`, `height: 48px`, `display: flex`
     - Ketiga kontrol sejajar presisi pada sumbu vertikal (`top: 427.23px`) dalam satu baris tunggal.

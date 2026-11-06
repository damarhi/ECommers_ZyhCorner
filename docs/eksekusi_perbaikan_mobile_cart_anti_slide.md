# Dokumentasi Eksekusi Perbaikan Card Produk Halaman Cart Mode Mobile (Anti-Slide) — Opsi 2

**Tanggal Eksekusi:** 6 November 2026  
**Pendekatan yang Dipilih:** **Opsi 2 — Two-Section Mobile Card (Info Produk di Atas + Baris Kontrol di Bawah, Tanpa Slide Horizontal, Desain Website 100% Tetap Utuh)**  
**Berkas yang Diperbarui:**
- `resources/js/pelanggan_pages.js` (Fungsi `renderCartPage()`)
- `resources/css/pelanggan_custom.css` (Aturan responsif `@media (max-width: 768px)`)
- `public/build/assets/pelanggan_pages-CznGvmBr.css` & `public/build/assets/pelanggan_pages-B9klK_3h.js` (Hasil Kompilasi Vite)

---

## 1. Masalah Awal pada Mode Mobile (`<= 768px`)

1. **Tabel Produk Melebar Hingga `648px` di Dalam Kartu Selebar `343px`:**
   - Pada `renderCartPage()` di `resources/js/pelanggan_pages.js`, elemen `<table class="cart-table">` memiliki inline style `min-width: 580px` dan dibungkus `<div style="overflow-x: auto;">`.
   - Di layar mobile (`375×812`), tabel melebar hingga **`648px`** (`scrollWidth: 648px` vs `clientWidth: 343px`), sehingga menciptakan **area geser (*slide* horizontal) sepanjang `305px`**.
2. **Kontrol Quantity (`- 1 +`) & Tombol Hapus (`✕`) Terlempar ke Luar Layar:**
   - Kolom Quantity berada di koordinat `left: 376px` dan tombol hapus di `left: 520px`, memaksa pengguna menggeser kartu ke kanan setiap kali ingin mengubah jumlah pesanan atau menghapus produk.

---

## 2. Solusi Teknis yang Diterapkan (Opsi 2 — Khusus `@media (max-width: 768px)`)

Seluruh transformasi hanya aktif pada `@media (max-width: 768px)` sehingga **tampilan Mode Website / Desktop (`> 768px`) tetap 100% utuh hingga ke satuan piksel**:

1. **Penambahan Class Semantik & Elemen Metadata Mobile di `resources/js/pelanggan_pages.js`:**
   - Menambahkan class `.cart-table-scroll`, `.cart-item-row`, `.cart-td-product`, `.cart-td-price`, `.cart-td-qty`, dan `.cart-td-remove`.
   - Menambahkan elemen `.cart-mobile-unit-price` dan `.cart-mobile-qty-meta` (*Jumlah Pesanan* & *Subtotal Item*) yang memiliki `display: none` pada layar desktop (`> 768px`) dan hanya ditampilkan pada layar mobile (`<= 768px`).
2. **Transformasi Tabel ke Two-Section Mobile Card di `resources/css/pelanggan_custom.css` (`@media (max-width: 768px)`):**
   - **Kunci Anti-Slide:** `.cart-table-wrapper, .cart-table-scroll { overflow-x: hidden !important; width: 100% !important; }` dan `.cart-table { min-width: 0 !important; width: 100% !important; display: block !important; }`.
   - **Bagian Atas Kartu (`.cart-td-product` & `.cart-td-remove`):**
     - Menampilkan foto produk (`64px × 64px`), nama produk yang dapat *wrap* hingga 2 baris (`white-space: normal`, `-webkit-line-clamp: 2`), teks custom (jika ada), harga satuan (`.cart-mobile-unit-price`), serta tombol hapus (`✕`) di pojok kanan atas (`position: absolute; top: 15px; right: 15px;`).
   - **Bagian Bawah Kartu (`.cart-td-qty` dengan `border-top: 1px dashed #e5e7eb`):**
     - Sisi kiri menampilkan label **`Jumlah Pesanan`** dan **`Subtotal: Rp. ...`**, sedangkan sisi kanan menampilkan kontrol **`- 1 +`** (`.cart-qty-box` berukuran `96px × 34px`).

---

## 3. Bukti Pengukuran Geometri DOM (Headless Chrome CDP)

| Metrik / Komponen | Mobile Sebelum (`375×812`) | Mobile Sesudah (`375×812`) | Hasil Mobile | Desktop Sebelum (`1440×900`) | Desktop Sesudah (`1440×900`) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **`scrollDiv.clientWidth`** | `343px` | **`343px`** | Pas 100% | `838px` | **`838px`** *(100% Tetap)* |
| **`scrollDiv.scrollWidth`** | `648px` *(Melebar 305px)* | **`343px`** | **0px Geser (Anti-Slide)** | `838px` | **`838px`** *(100% Tetap)* |
| **`isHorizontallyScrollable`** | `true` *(Bisa Di-Slide)* | **`false` *(Terkunci)*** | **Tidak Bisa Di-Slide** | `false` | **`false`** *(100% Tetap)* |
| **`.cart-table` `min-width`** | `580px` | **`0px`** | Mengikuti Lebar Kartu | `580px` | **`580px`** *(100% Tetap)* |
| **Lebar Baris/Kartu (`firstRow`)** | `648px` | **`343px`** | Pas di Dalam Layar HP | `838px × 109px` | **`838px × 109px`** *(100% Tetap)* |
| **Posisi Kontrol `- 1 +` (`qtyBoxRight`)** | `left: 376px` *(Di Luar Layar)* | **`right: 343px` *(Di Dalam Kartu)*** | Langsung Terlihat Utuh | `right: 731px` (`104×36px`) | **`right: 731px` (`104×36px`)** *(100% Tetap)* |
| **Posisi Tombol `✕` (`removeBtnRight`)** | `left: 520px` *(Di Luar Layar)* | **`right: 344px` *(Pojok Kanan Atas)*** | Langsung Terlihat Utuh | `right: 883px` (`28×28px`) | **`right: 883px` (`28×28px`)** *(100% Tetap)* |
| **Ukuran `.cart-table-wrapper`** | `345px` | **`345px × 406px`** | Proporsional | `840px × 350px` | **`840px × 350px`** *(100% Tetap)* |
| **Ukuran `.cart-total-card`** | `345px × 410px` | **`345px × 410px`** | Proporsional | `360px × 410px` | **`360px × 410px`** *(100% Tetap)* |

---

## 4. Verifikasi Quality Gate, Pengujian & Database
- **Vite Production Build (`npm run build`):** Berhasil dikompilasi (`pelanggan_pages-CznGvmBr.css` & `pelanggan_pages-B9klK_3h.js`).
- **Frontend Design Direction Evidence Gate (`check-evidence.js`):** **PASS (`EXIT: 0`)** — bukti tangkapan layar tersimpan di `docs/evidence/cart-mobile-375x812.png` dan `docs/evidence/cart-desktop-1440x900.png`.
- **PHPUnit Test Suite (`php artisan test`):** **221 passed (1196 assertions)** — 100% lulus.
- **Database MySQL (`pa-zyhcorner`):** Utuh (*Users: 16, Products: 13, Orders: 25*).

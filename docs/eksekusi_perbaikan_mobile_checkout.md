# Dokumentasi Eksekusi Perbaikan Tata Letak Mobile Halaman Checkout (`/checkout`) — Paket Lengkap A

**Tanggal Eksekusi:** 6 November 2026  
**Pendekatan yang Dipilih:** **Paket Lengkap A — Kurir 1 Kolom Rapi (Perbaikan 6 Titik Tata Letak Mobile `@media (max-width: 768px)`, Tampilan Website `> 768px` 100% Tetap Utuh)**  
**Berkas yang Diperbarui:**
- `resources/views/Pelanggan/checkout.blade.php`
- `resources/css/pelanggan_custom.css`
- `public/css/css_checkout.css`
- `public/build/assets/pelanggan_pages-BHzSwlBy.css` (Hasil Kompilasi Vite)

---

## 1. Masalah Awal pada Mode Mobile (`<= 768px`)

1. **Kartu Metode Pembayaran ("Transfer Bank & Virtual Account") Terjepit & Membengkak (`height: 154px`):**
   - `<label>` di dalam `.payment-option-header` menggunakan `display: flex; justify-content: space-between` tanpa *wrap*. Deretan 3 *pill* (`BCA`, `BRI`, `Mandiri`) memakan lebar `134px` di sisi kanan, sehingga judul & subjudul di sisi kiri terjepit menjadi hanya **`107px`** (terpecah menjadi 7 baris vertikal!).
2. **Padding Berlapis (*Nested Card Padding*) & Grid Overflow pada Langkah 1–3:**
   - Kombinasi padding `.checkout-container` (`15px`), `.checkout-card` (`20px`), `.summary-card` (`24px`), dan `.checkout-faq-a-inner` (`18px`) menyempitkan lebar bersih peta (`#map`) menjadi hanya `263px`. Selain itu, label ucapan kue tanpa `flex-wrap` dan `.checkout-grid` tanpa `minmax(0, 1fr)` berpotensi mendorong lebar kolom melebihi layar saat kartu Langkah 3 aktif.
3. **Header Peta & Tombol `Gunakan Lokasi Saya` Tertekan (`height: 60px`):**
   - Label *"Titik Lokasi Pengantaran di Peta"* terlipat menjadi 3 baris (`153px × 60px`) dan tombol `Gunakan Lokasi Saya` terlipat 2 baris (`112px × 44px`).
4. **Header Pemilih Alamat Tersimpan & Ringkasan Accordion Sesak:**
   - Alamat pada `.address-summary-pill` terpotong pendek (`max-width: 180px`) dan pemisah bullet (`•`) menggantung saat pindah baris.
5. **Tombol `Cek Ongkir` Menggantung di Header Langkah 2:**
   - Tombol `#btnRefreshShipping` (`width: 116px`) jatuh tanggung di bawah subjudul Langkah 2.
6. **Nama Produk di Ringkasan Pesanan Terpotong (`max-width: 140px`):**
   - `.order-item-name` pada `@media (max-width: 576px)` dibatasi `max-width: 140px; white-space: nowrap; text-overflow: ellipsis;` sehingga nama kue terpotong sebelum pelanggan membayar.

---

## 2. Solusi Teknis yang Diterapkan (Khusus `@media (max-width: 768px)`)

Seluruh aturan CSS ditempatkan di dalam `@media (max-width: 768px)` sehingga **Mode Website / Desktop (`> 768px`) tetap 100% identik hingga ke satuan piksel**:

1. **Kartu Metode Pembayaran (`.payment-option-label`):**
   - Mengubah `<label>` opsi pembayaran di mobile menjadi `flex-direction: column !important; align-items: flex-start !important; gap: 7px !important;`.
   - Lebar teks judul & subjudul naik dari **`107px` → `257px`**, dan tinggi kartu *Transfer Bank* turun dari **`154px` → `110px`** (seimbang dengan kartu COD `111px`).
2. **Perampingan Padding Berlapis & Kunci Lebar Grid (`minmax(0, 1fr)`):**
   - `.checkout-grid { grid-template-columns: minmax(0, 1fr); gap: 18px; }` serta `.checkout-main-col, .checkout-sidebar-col { min-width: 0; width: 100%; }`.
   - Merampingkan padding `.checkout-card` & `.sticky-summary-card` menjadi `16px` serta `.checkout-faq-a-inner` menjadi `14px 12px 6px`, sehingga seluruh kartu terkunci rapi pada lebar **`347px`** (`pageOverflow: 0px`) dan lebar peta bertambah dari **`263px` → `273px`**.
3. **Header Peta & Tombol `Gunakan Lokasi Saya` (`.checkout-map-label-row`):**
   - Disusun vertikal (`flex-direction: column; align-items: stretch; gap: 8px;`) sehingga label tampil **1 baris utuh (`height: 20px`)** dan tombol `Gunakan Lokasi Saya` tampil *full-width* (`275px × 36px`).
4. **Pemilih Alamat Tersimpan & Ringkasan Accordion (`.address-summary-pill`):**
   - Menyembunyikan `.summary-sep` (`•`) di mobile dan menampilkan `.summary-address` hingga 2 baris penuh (`max-width: 100%`, `-webkit-line-clamp: 2`).
5. **Header Langkah 2 & Tombol `Cek Ongkir` (`.checkout-step-header-shipping`):**
   - Tombol `#btnRefreshShipping` dijadikan *full-width* (`313px × 32px`, `justify-content: center`) di bawah judul Langkah 2, dengan daftar 4 kurir tetap 1 kolom rapi (`gap: 8px`).
6. **Nama Produk 2 Baris di Ringkasan Pesanan (`.order-item-name`):**
   - Mengganti batasan `max-width: 140px; white-space: nowrap` dengan `max-width: 100%; white-space: normal; -webkit-line-clamp: 2;` agar nama produk terbaca utuh.

---

## 3. Bukti Pengukuran Geometri DOM (Headless Chrome CDP)

| Komponen / Metrik | Mobile Sebelum (`375×812`) | Mobile Sesudah (`375×812`) | Hasil Mobile | Desktop Sebelum (`1440×900`) | Desktop Sesudah (`1440×900`) |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Horizontal Overflow** | `0px` | **`0px`** (`gridScrollWidth: 347px`) | Terkunci Rapi | `0px` | **`0px`** *(100% Tetap)* |
| **Lebar Teks Kartu Transfer Bank** | `107px` *(Terjepit 7 Baris)* | **`257px` *(Lebar Penuh)*** | **`+150px` Lebih Lega** | `279px` | **`279px`** *(100% Tetap)* |
| **Tinggi Kartu Transfer Bank** | `154px` | **`110px`** | **Hemat `44px` & Proporsional** | `80px` (`467×80px`) | **`80px` (`467×80px`)** *(100% Tetap)* |
| **Label Header Peta** | `153px × 60px` *(3 Baris)* | **`275px × 20px` *(1 Baris Utuh)*** | Tidak Lagi Terjepit | `241px × 20px` | **`241px × 20px`** *(100% Tetap)* |
| **Tombol `Gunakan Lokasi Saya`** | `112px × 44px` *(2 Baris Sempit)* | **`275px × 36px` *(Full-Width)*** | Nyaman Disentuh | `164px × 27px` | **`164px × 27px`** *(100% Tetap)* |
| **Lebar Peta Leaflet (`#map`)** | `263px × 200px` | **`273px × 200px`** | Lebih Lebar `+10px` | `531px × 220px` | **`531px × 220px`** *(100% Tetap)* |
| **Tombol `Cek Ongkir`** | `116px × 28px` *(Menggantung)* | **`313px × 32px` *(Full-Width)*** | Rata & Proporsional | `116px × 28px` | **`116px × 28px`** *(100% Tetap)* |
| **Nama Produk Ringkasan Pesanan** | `white-space: nowrap` *(Terpotong)* | **`white-space: normal` *(2 Baris)*** | Terbaca Utuh | `170px × 20px` (`nowrap`) | **`170px × 20px` (`nowrap`)** *(100% Tetap)* |
| **Kartu Langkah 1 (`.checkout-card`)** | `345px` (`padding: 20px`) | **`347px` (`padding: 16px`)** | Lebih Lega | `621px × 1146px` | **`621px × 1146px`** *(100% Tetap)* |

---

## 4. Verifikasi Quality Gate, Pengujian & Database
- **Vite Production Build (`npm run build`):** Berhasil dikompilasi (`pelanggan_pages-BHzSwlBy.css`).
- **Frontend Design Direction Evidence Gate (`check-evidence.js`):** **PASS (`EXIT: 0`)** — bukti laporan tersimpan di `docs/evidence/checkout-evidence.json`.
- **PHPUnit Test Suite (`php artisan test`):** **221 passed (1196 assertions)** — 100% lulus (termasuk `CheckoutDesignTest`).
- **Database MySQL (`pa-zyhcorner`):** Utuh (*Users: 16, Products: 13, Orders: 25*).

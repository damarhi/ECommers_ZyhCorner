# Dokumentasi Eksekusi Perbaikan Tata Letak Mobile Halaman FAQ & Warna Solid Kartu CTA

**Tanggal Eksekusi:** 6 November 2026  
**Pilihan Desain:**
- **Warna Solid Kartu CTA Bawah:** **Pilihan 1 — Deep Botanical Forest (`#14532D` / `rgb(20, 83, 45)`)**
- **Tata Letak Tab Kategori Mobile:** **Pilihan A — Grid Simetris Rata Penuh (3 Tab Atas + 2 Tab Bawah)**

**Berkas yang Diperbarui:**
- `resources/views/Pelanggan/faq.blade.php`
- `public/css/css_faq.css`
- `public/js/js_faq.js`

---

## 1. Akar Masalah Sebelum Perbaikan

1. **Tab Kategori (`.faq-tab`) Tidak Rata Kanan & Posisi Bergeser Akibat Bentrok CSS + GSAP:**
   - Di layar mobile (`375×812`), Baris 1 (`Semua`, `Pemesanan`, `Pengiriman`) menyisakan celah `12px` di kanan, sedangkan Baris 2 (`Pembayaran`, `Produk`) menyisakan **ruang kosong `128px`** di sisi kanan (`right: 232px` dari `360px`).
   - Properti `transition: all 0.2s ease` pada `.faq-tab` berbenturan dengan animasi `gsap.from('#faqSearchInput, .faq-tab', { y: -20, opacity: 0 })` di `public/js/js_faq.js`, sehingga beberapa tab tertahan pada posisi `translateY` dan `opacity` yang tidak seragam.
2. **Kartu CTA Bawah Menggunakan Gradasi Hijau & Tombol Bertumpuk Tidak Seukuran (`270px`):**
   - Menggunakan inline style `background: linear-gradient(135deg, #00b207, #15803d)`.
   - Di layar mobile, tombol `Hubungi Kami` (`169px × 41px`) dan `WhatsApp` (`145px × 45px`) jatuh menjadi 2 baris vertikal dengan lebar dan tinggi yang berbeda.
3. **Inline Style Kaku & Absennya `@media (max-width: 768px)` di `public/css/css_faq.css`:**
   - Seluruh elemen di `faq.blade.php` menggunakan inline style desktop (`padding: 40px 0 60px`, `margin-top: 40px`), serta terdapat duplikasi `@section('styles')` kosong pada akhir `faq.blade.php`.

---

## 2. Solusi Teknis yang Diterapkan

1. **Refaktor Struktur Class Semantik di `resources/views/Pelanggan/faq.blade.php`:**
   - Memindahkan inline style ke class semantik (`.faq-page`, `.faq-container`, `.faq-controls`, `.faq-search-wrapper`, `.faq-search-input`, `.faq-tabs`, `.faq-no-results`, `.faq-cta-card`, `.faq-cta-title`, `.faq-cta-desc`, `.faq-cta-actions`, `.faq-cta-btn-primary`, `.faq-cta-btn-secondary`).
   - Menghapus duplikasi `@section('styles')` kosong di bagian bawah file.
2. **Perbaikan Warna Solid Kartu CTA & Responsivitas Mobile di `public/css/css_faq.css`:**
   - **Kartu CTA Solid Deep Botanical Forest (`#14532D`):**
     - `background-color: #14532d; border: 1px solid #166534; box-shadow: 0 10px 26px rgba(20, 83, 45, 0.16);` tanpa `linear-gradient`.
     - Tombol utama (`Hubungi Kami`): putih solid (`#ffffff`) dengan teks `#14532d` dan `border: 2px solid #ffffff`.
     - Tombol sekunder (`WhatsApp`): `background-color: rgba(255, 255, 255, 0.1); color: #ffffff; border: 2px solid rgba(255, 255, 255, 0.65);`.
   - **Tab Kategori Mobile Simetris Rata Penuh (`@media (max-width: 768px)`):**
     - `.faq-tab { flex: 1 1 calc(33.333% - 6px); min-height: 36px; padding: 8px 10px; font-size: 12.5px; }` sehingga 3 tab baris atas (`110px` × 3) dan 2 tab baris bawah (`169px` × 2) merentang presisi dari `x = 15px` hingga `x = 360px`.
   - **Kartu Pertanyaan & Tombol CTA Mobile:**
     - `.faq-q`: `padding: 13px 15px`, `.faq-title`: `font-size: 13.5px`, `.faq-icon`: `28px × 28px`, `.faq-a-inner`: `text-align: justify; font-size: 13px;`.
     - `.faq-cta-actions`: `display: grid; grid-template-columns: 1fr 1fr; gap: 10px;` sehingga kedua tombol berdampingan 1 baris dengan ukuran identik (`151px × 40px`).
3. **Eliminasi Konflik Animasi GSAP (`public/css/css_faq.css` & `public/js/js_faq.js`):**
   - Mengganti `transition: all 0.2s ease` pada `.faq-tab` menjadi `transition: background-color 0.2s ease, color 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease`.
   - Menambahkan `clearProps: 'transform,opacity'` pada animasi GSAP di `public/js/js_faq.js`.

---

## 3. Bukti Pengukuran Geometri DOM (Headless Chrome CDP)

| Komponen / Metrik | Mobile Sebelum (`375×812`) | Mobile Sesudah (`375×812`) | Perubahan / Penghematan | Desktop (`1440×900`) |
| :--- | :--- | :--- | :--- | :--- |
| **Total Tinggi `.faq-page`** | `1.145px` | **`811px`** | **`-334px` (-29,2% — Muat 1 Layar HP)** | `946px` |
| **Total Tinggi Halaman (`scrollHeight`)** | `2.228px` | **`1.894px`** | **`-334px`** | `1.617px` |
| **Tab Baris 1 (`Semua`, `Pemesanan`, `Pengiriman`)** | Lebar beda (`85/117/115px`), `top` selisih `2px`, sisa `12px` kanan | **Simetris `110px × 3`**, `top: 287px` seragam, rata `x=15..360px` | **Rata Penuh 100%** | 1 Baris (`top: 392px`) |
| **Tab Baris 2 (`Pembayaran`, `Produk`)** | Lebar `125px` & `84px`, **sisa ruang kosong `128px` di kanan** | **Simetris `169px × 2`**, `top: 331px` seragam, rata `x=15..360px` | **Celah 128px Hilang 100%** | 1 Baris (`top: 392px`) |
| **Kartu Pertanyaan (`.faq-card`)** | Tinggi `83px` (`padding: 18px 20px`) | **Tinggi `56px–67px`** (`padding: 13px 15px`, jawaban `justify`) | **`-16px s/d -27px` per kartu** | Tinggi `70px` |
| **Warna Latar Kartu CTA (`.faq-cta-card`)** | `linear-gradient(135deg, #00b207, #15803d)` | **Solid `#14532D` (`rgb(20, 83, 45)`)** | **Bebas Gradasi (Solid Elegan)** | **Solid `#14532D`** |
| **Tinggi Kartu CTA (`.faq-cta-card`)** | `270px` | **`150px`** | **`-120px` (-44,4%)** | `195px` |
| **Tombol CTA (`Hubungi Kami` & `WhatsApp`)** | Tumpuk 2 baris, ukuran beda (`169×41px` vs `145×45px`) | **Sejajar 1 baris (`top: 925px`), ukuran identik `151×40px`** | **Simetris & Sejajar** | Sejajar 1 baris (`height: 44px`) |
| **Horizontal Overflow** | `0px` | **`0px`** | Aman | `0px` |

---

## 4. Verifikasi Quality Gate, Pengujian & Database
- **Frontend Design Direction Evidence Gate (`check-evidence.js`):** **PASS (`EXIT: 0`)** dengan tangkapan layar terverifikasi di `docs/evidence/faq-mobile-375x812.png` dan `docs/evidence/faq-desktop-1440x900.png`.
- **PHPUnit Test Suite (`php artisan test`):** **221 passed (1196 assertions)** — 100% lulus.
- **Database MySQL (`pa-zyhcorner`):** Utuh (*Users: 16, Products: 13, Orders: 25*).

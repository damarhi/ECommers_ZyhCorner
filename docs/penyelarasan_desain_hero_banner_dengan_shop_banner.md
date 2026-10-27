# Dokumentasi Penyelarasan Desain Hero Banner Beranda dengan Banner Shop & Custom Products

## 1. Latar Belakang & Tujuan
Tujuan dari pembaruan ini adalah menyelaraskan kualitas visual, estetika, dan kedalaman desain pada **Hero Banner Beranda** (`resources/views/Pelanggan/home.blade.php`) agar mengadopsi kemewahan (*premium feel*), kehalusan, dan integrasi elemen yang sama dengan banner di **Halaman Shop** (`shop.blade.php`) dan **Produk Kustom** (`custom_products.blade.php`), dengan **tiga batasan mutlak**:
1. **Warna Tetap Sama**: Mempertahankan palet sage terang asli (`#edf2ee`), teks *deep charcoal* (`#1a1a1a`), aksen diskon oranye/amber (`#ff8a00`), badge hijau (`#008a05`), dan tombol hijau Zyh Corner (`#00b207`).
2. **Ukuran Tetap Sama**: Mempertahankan dimensi ukuran tinggi desktop `min-height: 520px` (mobile `210px`), layout proporsi `65fr 35fr`, kelengkungan sudut `border-radius: 24px`, serta jarak vertikal `margin-top: 28px` & `margin-bottom: 36px` (desktop) / `16px` & `24px` (mobile).
3. **Teks Tetap Sama**: Mempertahankan seluruh teks dinamis database (`judul_banner`, `$badgeText`, `$calloutHighlight`, `$calloutPrefix`, `$descText`, dan tombol `Shop Now`).

---

## 2. Rincian Perubahan Desain & Kode

### A. Elevated Card Shell & Multi-Layer Shadow
Sebelumnya kartu hero hanya memiliki bayangan datar dan border standar. Kini kartu hero mengadopsi elevasi melayang (*elevated floating card*) yang halus:
- **Border**: Diperbarui dari `#e2eae3` menjadi `#dbe8dd` (saat hover berubah ke `#b9dac0`).
- **Box Shadow**: Multi-layer ambient shadow `0 16px 36px rgba(0, 0, 0, 0.04), 0 4px 16px rgba(0, 178, 7, 0.06)` yang memberi kesan kartu berbobot dan melayang lembut di atas kanvas latar belakang.
- **Transisi Hover**: Efek kedalaman halus saat kursor melayang di atas banner.

### B. Glassmorphic Micro-Pill Badge
Badge subjudul `.hero-badge-pill` ditingkatkan dengan perlakuan kaca mikro (*glassmorphism*):
- **Backdrop Filter**: `backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);`
- **Background**: `rgba(0, 178, 7, 0.09)` semi-transparan.
- **Border**: `1px solid rgba(0, 178, 7, 0.28)`.
- **Bayangan Mikro**: `box-shadow: 0 2px 8px rgba(0, 178, 7, 0.08)`.

### C. Ambient Daylight Spotlight Architecture
Pendaran latar belakang di balik area teks (`.hero-text::before`) ditingkatkan menjadi *dual daylight ambient spotlight*:
- **Pendaran Sudut (Emerald Glow)**: `radial-gradient(circle at 8% 18%, rgba(0, 178, 7, 0.08) 0%, transparent 50%)`.
- **Pendaran Teks (Daylight Wash)**: `radial-gradient(ellipse 135% 105% at 25% 48%, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.65) 45%, rgba(237, 242, 238, 0) 85%)`.
- Memberikan perlindungan kontras tinggi bagi teks tanpa memerlukan lapisan overlay gelap kusam.

### D. Seamless Blending Showcase Produk
Sambungan pendar pada sisi foto kue (`.hero-image::before`) diperlebar menjadi `width: 200px` dengan gradasi radial melengkung asimetris (`ellipse 95% 120% at 0% 50%`) sehingga foto produk roti/kue berpadu organik dengan latar sage banner tanpa garis potong vertikal yang kaku.

### E. Tombol Interaktif dengan Glowing Shadow
Tombol `.hero-btn` diselaraskan dengan estetika `.spb-btn`:
- **Dimensi & Padding**: `padding: 13px 32px`, `font-size: 14.5px`.
- **Bayangan Pendar**: `box-shadow: 0 6px 20px rgba(0, 178, 7, 0.28)`.
- **Interaktivitas Hover**: Elevasi halus `transform: translateY(-2px)`, penguatan pendaran `box-shadow: 0 10px 28px rgba(0, 178, 7, 0.40)`, dan pergeseran ikon panah halus `transform: translateX(4px)`.

---

## 3. Daftar Berkas yang Diperbarui

| No | Berkas | Deskripsi Perubahan |
| :--- | :--- | :--- |
| 1 | `resources/views/Pelanggan/home.blade.php` | Pembaruan aturan CSS `.hero-section`, `.hero-text::before`, `.hero-badge-pill`, `.hero-btn`, dan `.hero-image::before`. |
| 2 | `public/css/home.css` | Sinkronisasi aturan CSS kartu hero, badge glassmorphic, dan tombol CTA. |
| 3 | `resources/css/pelanggan_pages.css` | Penyelarasan styling hero banner pada bundel CSS utama halaman pelanggan. |
| 4 | `resources/css/pelanggan_custom.css` | Penyelarasan styling hero banner pada bundel CSS kustom pelanggan. |
| 5 | `public/build/*` | Aset terkompilasi dari eksekusi `npm run build`. |

---

## 4. Hasil Verifikasi Browser Headless

Pengujian dilakukan menggunakan browser headless Chromium pada dua viewport:

### A. Desktop (1440 × 900 px)
- **Dimensi Lebar & Tinggi**: `1164px × 519px` (memenuhi `min-height: 520px`).
- **Margin Vertikal**: `margin-top: 28px`, `margin-bottom: 36px`.
- **Border**: `1px solid rgb(219, 232, 221)` (`#dbe8dd`).
- **Box Shadow**: `rgba(0, 0, 0, 0.04) 0px 16px 36px 0px, rgba(0, 178, 7, 0.06) 0px 4px 16px 0px`.
- **Badge Pill**: `backdrop-filter: blur(8px)`, font `12.5px`, warna teks `rgb(0, 138, 5)`, border `1px solid rgba(0, 178, 7, 0.28)`.
- **Tombol CTA**: Warna `#00b207`, padding `13px 32px`, pendaran shadow `rgba(0, 178, 7, 0.28) 0px 6px 20px 0px`.
- **Keterbacaan Teks**: Judul `#1a1a1a` dengan font 48px berbobot 700 sangat tajam dan kontras.

### B. Mobile (390 × 844 px)
- **Margin Vertikal**: `margin-top: 16px`, `margin-bottom: 24px`.
- **Tinggi Gambar Produk**: `210px` (`object-fit: cover`).
- **Layout Orientasi**: `flex-direction: column-reverse` (gambar produk di atas, teks judul di bawah, terpusat rapi).
- **Badge Pill Mobile**: Font `11.5px`, padding `5px 14px`.
- **Tombol CTA Mobile**: Font `13.5px`, padding `12px 28px`.

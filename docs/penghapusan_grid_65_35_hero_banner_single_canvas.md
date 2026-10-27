# Dokumentasi Penghapusan Grid 65/35 Hero Banner Beranda Menjadi Single Unified Canvas

## 1. Latar Belakang & Masalah Desain Sebelumnya

Sebelumnya, Hero Banner Beranda (`resources/views/Pelanggan/home.blade.php`) menggunakan pembagian layout CSS Grid dua kolom kaku:
```css
display: grid !important;
grid-template-columns: 65fr 35fr !important;
```

Struktur tersebut membatasi foto produk (kue/roti) hanya berada di kolom kanan sebesar 35%, sementara area teks memakan 65% kolom kiri. Akibatnya:
1. Terdapat batas potongan vertikal yang terasa kaku (*boxy*) antara area teks dan area foto.
2. Foto produk kue tidak dapat membentang secara leluasa (*full-bleed*), padahal desain banner promo pada halaman Shop dan Custom Products (`.shop-promo-banner`) telah mengadopsi arsitektur *Single Unified Canvas* di mana foto produk membentang lebar dari kanan dengan gradasi asimetris lembut (*feathered melt*).
3. Efek visual keseluruhan kurang berbobot dan kurang menyatu dengan palet kanvas sage terang.

Pembaruan ini mengeliminasi pemisahan kolom 65/35 secara total dan menerapkan arsitektur **Single Unified Canvas** yang identik dengan banner halaman Shop/Custom Products, sembari menjaga seluruh batasan sistem tetap utuh.

---

## 2. Batasan Mutlak yang Dipatuhi (Strict Constraints)

1. **Warna Tetap Sama**:
   - Background canvas: Sage terang `#edf2ee`
   - Border: `#dbe8dd` (hover: `#b9dac0`)
   - Teks utama & heading: Deep charcoal `#1a1a1a`
   - Teks deskripsi: Netral abu `#555555`
   - Aksen diskon & highlight: Amber/oranye `#ff8a00`
   - Badge subjudul: Hijau brand `#008a05` dengan background semi-transparan `rgba(0, 178, 7, 0.09)`
   - Tombol CTA: Hijau Zyh Corner `#00b207` (hover: `#009906`)

2. **Ukuran Tetap Sama**:
   - Tinggi desktop: `min-height: 520px`
   - Tinggi foto produk mobile: `height: 210px`
   - Sudut kartu: `border-radius: 24px`
   - Jeda vertikal desktop: `margin-top: 28px` dan `margin-bottom: 36px`
   - Jeda vertikal mobile: `margin-top: 16px` dan `margin-bottom: 24px`

3. **Teks Dinamis Tetap Sama**:
   - Seluruh teks bersumber 100% dari database melalui model `Banner` (`$banner->judul_banner`, `$badgeText`, `$calloutHighlight`, `$calloutPrefix`, `$descText`, dan tombol `Shop Now`).

4. **Kompatibilitas Animasi GSAP Slider**:
   - Script slider GSAP pada `public/js/home.js` mengincar selektor `.hero-image img` dan `.hero-text *`.
   - Menggunakan selektor kelas ganda `.hero-image.hero-showcase-bg` dan `.hero-text.hero-content` sehingga styling kanvas modern terpasang sempurna tanpa merusak mekanisme slider GSAP yang ada.

---

## 3. Rincian Perubahan Arsitektur & Kode

### A. Rekonstruksi Markup HTML (`home.blade.php`)

**Sebelum (Struktur Grid 2 Kolom Kaku):**
```html
<div class="hero-section hero-slide-item ...">
    <!-- Kolom 1 (Kiri 65%): Teks -->
    <div class="hero-text">
        <span class="hero-badge-pill">...</span>
        <h1>...</h1>
        ...
    </div>

    <!-- Kolom 2 (Kanan 35%): Foto Terjepit -->
    <div class="hero-image">
        <div class="hero-image-frame">
            <img src="..." alt="...">
        </div>
    </div>
</div>
```

**Sesudah (Single Unified Canvas):**
```html
<div class="hero-section hero-slide-item ...">
    <!-- Layer 1 (Z-Index 1): Full-Bleed Showcase Image (58% Lebar Sisi Kanan) -->
    <div class="hero-image hero-showcase-bg">
        <div class="hero-image-frame">
            <img src="..." alt="...">
        </div>
    </div>

    <!-- Layer 2 (Z-Index 3): Floating Content Teks Bebas Hambatan di Sisi Kiri -->
    <div class="hero-text hero-content">
        <span class="hero-badge-pill">...</span>
        <h1>...</h1>
        ...
    </div>
</div>
```

### B. Penghapusan Grid & Penerapan Flex Canvas Modern

Aturan `grid-template-columns: 65fr 35fr;` dihapus seluruhnya dan digantikan dengan kanvas flex berposisi relatif:
```css
.hero-section {
    background-color: #edf2ee;
    border: 1px solid #dbe8dd;
    border-radius: 24px;
    display: flex !important;
    align-items: center !important;
    min-height: 520px !important;
    position: relative !important;
    overflow: hidden !important;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.04), 0 4px 16px rgba(0, 178, 7, 0.06);
    transition: border-color 0.35s ease, box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 0 !important;
    margin: 0 !important;
}
```

### C. Layer Gradasi Asimetris (*Feathered Spotlight Melt*)

Diletakkan pada pseudo-element `.hero-section::before` dengan `z-index: 2`, berada persis di antara foto kue (`z-index: 1`) dan teks (`z-index: 3`):
```css
.hero-section::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 2;
    background: 
        radial-gradient(circle at 10% 25%, rgba(0, 178, 7, 0.08) 0%, transparent 45%),
        linear-gradient(90deg, 
            #edf2ee 0%, 
            rgba(237, 242, 238, 0.98) 42%, 
            rgba(237, 242, 238, 0.85) 54%, 
            rgba(237, 242, 238, 0.35) 75%, 
            rgba(237, 242, 238, 0) 100%
        );
    pointer-events: none;
    transition: opacity 0.35s ease;
}
```
**Efek visual yang dihasilkan:**
- Sisi kiri teks 100% bersih, jernih, dan memiliki kontras maksimal di atas warna sage `#edf2ee`.
- Area tengah banner (42% – 75%) meleleh secara organik (*seamless melt*), menghilangkan garis potong vertikal.
- Sisi kanan (75% – 100%) menampilkan foto produk kue secara tajam, bersih, dan memikat.

### D. Full-Bleed Showcase Image (58% Lebar Kanvas)

Foto produk diperluas dari sebelumnya terjepit di 35% menjadi 58% lebar kanvas:
```css
.hero-image,
.hero-showcase-bg {
    position: absolute !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 58% !important;
    height: 100% !important;
    min-height: 520px !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 1 !important;
    overflow: hidden !important;
    display: block !important;
}

.hero-image img,
.hero-image-frame img {
    position: absolute !important;
    inset: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    object-position: center right !important;
    display: block !important;
    filter: saturate(1.05) contrast(1.02);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.hero-section:hover .hero-image img,
.hero-section:hover .hero-image-frame img {
    transform: scale(1.03);
}
```

### E. Area Konten Mengambang (*Floating Content*)

Teks diletakkan di lapisan paling depan (`z-index: 3`) dengan pembatasan lebar elegan `max-width: 620px`:
```css
.hero-text,
.hero-content {
    position: relative !important;
    z-index: 3 !important;
    max-width: 620px !important;
    width: 100% !important;
    padding: 55px 45px 55px 75px !important;
    box-sizing: border-box !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: flex-start !important;
}
```

### F. Penyesuaian Responsif Mobile (< 768px)

Pada layar smartphone, tata letak otomatis berubah menjadi susunan bertumpuk vertikal:
- Foto produk berada di atas setinggi `210px` (`position: relative; width: 100%`).
- Lapisan spotlight overlay dinonaktifkan (`display: none !important`) agar foto tampil terang tanpa tertutup gradasi.
- Konten teks berada di bawah foto dengan padding `24px 20px 30px`, teks rata tengah, dan elemen tombol berada di posisi yang nyaman disentuh.

```css
@media (max-width: 768px) {
    .hero-section {
        display: flex !important;
        flex-direction: column !important;
        padding: 0 !important;
        margin: 0 !important;
        text-align: center;
        min-height: auto !important;
    }
    .hero-section::before {
        display: none !important;
    }
    .hero-image,
    .hero-showcase-bg {
        position: relative !important;
        top: auto !important;
        right: auto !important;
        bottom: auto !important;
        width: 100% !important;
        height: 210px !important;
        min-height: 210px !important;
        padding: 0 !important;
        margin-bottom: 0 !important;
        z-index: 1 !important;
    }
    .hero-text,
    .hero-content {
        position: relative !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 24px 20px 30px !important;
        align-items: center !important;
        text-align: center !important;
        z-index: 2 !important;
    }
}
```

---

## 4. Daftar Berkas yang Diperbarui

| No | Berkas | Lokasi / Baris | Deskripsi Perubahan |
| :--- | :--- | :--- | :--- |
| 1 | `resources/views/Pelanggan/home.blade.php` | Baris 110–180 & 260–290 | Merekonstruksi urutan DOM (`.hero-image` di latar belakang, `.hero-text` di depan), menghapus CSS grid `65fr 35fr`, menambahkan flex canvas, full-bleed showcase 58%, dan feathered melt layer. |
| 2 | `public/css/home.css` | Baris 99–268 & 785–855 | Menyinkronkan aturan `.hero-section`, `.hero-section::before`, `.hero-showcase-bg`, serta aturan breakpoint mobile ke CSS publik. |
| 3 | `resources/css/pelanggan_pages.css` | Baris 216–387 & 3955–4045 | Menghapus aturan grid 65/35 dan menerapkan arsitektur single canvas pada file sumber aset Vite utama. |
| 4 | `resources/css/pelanggan_custom.css` | Baris 1121–1225 & 1420–1495 | Menghapus aturan grid 65/35 dan menyelaraskan aturan single unified canvas pada file sumber kustom pelanggan. |
| 5 | `public/build/*` | Manifest & Chunks | Hasil kompilasi ulang seluruh bundle frontend melalui `npm run build`. |

---

## 5. Hasil Verifikasi Browser Headless

Pengujian visual dan komputasi CSS dilakukan menggunakan browser headless Chromium pada dua resolusi standar:

### A. Pengujian Desktop (1440 × 900 px)
- **Komputasi Display `.hero-section`**: `flex` (Grid 65/35 terbukti 100% tereliminasi).
- **Dimensi Lebar & Tinggi Banner**: `1170px × 520px` (Konsisten dengan `min-height: 520px`).
- **Dimensi Showcase Gambar (`.hero-image`)**:
  - `position`: `absolute`
  - `width`: `677.438px` (Tepat ~58% dari lebar kanvas 1170px)
  - `height`: `520px` (Membentang penuh dari atas ke bawah)
  - `top`: `0px`, `right`: `0px`
- **Dimensi Area Konten (`.hero-text`)**:
  - `position`: `relative`
  - `z-index`: `3`
  - `max-width`: `620px`
  - `padding`: `55px 45px 55px 75px`
- **Efek Gradasi**: Dual-tone spotlight & feathered melt layer aktif pada `z-index: 2` menyatukan kanvas dan foto kue tanpa garis potong.
- **Konsol JavaScript**: 0 error, 0 warning kritis. Animasi GSAP slider berputar mulus.

### B. Pengujian Mobile (390 × 844 px)
- **Komputasi Display `.hero-section`**: `flex`, `flex-direction: column`.
- **Dimensi Showcase Gambar**:
  - `position`: `relative`
  - `width`: `358px` (100% container)
  - `height`: `210px`
- **Dimensi Area Konten**:
  - `position`: `relative`
  - `height`: `300px` (Teks berada di bawah foto, tanpa ada elemen yang saling menumpuk/terpotong).
  - `padding`: `24px 20px 30px`
- **Responsivitas**: Responsif, proporsional, dan nyaman dibaca pada perangkat mobile.

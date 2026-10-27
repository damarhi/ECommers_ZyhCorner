# Dokumentasi Perubahan Desain: Hero Banner Foto Full-Bleed 35% & Gradasi Warna 65% Membayangi Foto

Dokumentasi ini mencatat implementasi penyempurnaan desain visual pada komponen **Hero Banner Carousel** di beranda (`resources/views/Pelanggan/home.blade.php`) dan modal pratinjau admin (`resources/views/Admin/kelola_carousel/banners.blade.php`), di mana foto produk kini **memenuhi penuh (*full-bleed edge-to-edge*)** kolom 35% dan warna kanvas kolom 65% (`#edf2ee`) membentuk **gradasi bayangan lembut (*atmospheric gradient shadow veil*) selebar 160px** yang menaungi sisi kiri foto.

---

## 1. Analisis Masalah & Konsep Perubahan

### A. Masalah Sebelumnya
Pada implementasi sebelumnya, foto kue masih dirender di dalam kontainer ber-padding dengan batasan dimensi tertentu (`height: 390px; object-fit: contain; max-width: 480px;`). Akibatnya, foto terlihat seperti "kartu/stiker yang melayang di tengah kolom 35%" dengan ruang kosong di sisi atas, kanan, dan bawahnya.

### B. Konsep Solusi (*Editorial Magazine Full-Bleed Layout*)
```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│ <  [65% CONTENT AREA - Canvas #edf2ee]                     ░▒▓ [35% FULL-BLEED PHOTO]          │
│                                                            ░▒▓                                 │
│    [Pill Badge: ✨ WELCOME TO ZYH CORNER]                  ░▒▓ ╭─────────────────────────────╮ │
│                                                            ░▒▓ │ (Image Fills 100% Height    │ │
│    Delicious & Fresh                                       ░▒▓ │  & Width with Object-Cover) │ │
│    Cakes and Desserts                                      ░▒▓ │                             │ │
│                                                            ░▒▓ │      🎂 Table of Cakes      │ │
│    Sale up to [ 30% OFF ]                                  ░▒▓ │         & Desserts          │ │
│                                                            ░▒▓ │                             │ │
│    [ Shop Now  → ]                                         ░▒▓ │ (Flush to Right/Top/Bottom) │ │
│                                                            ░▒▓ ╰─────────────────────────────╯ │
│                                                                                                │
│                              ◄─── 160px Atmospheric Blend ───►                                 │
│                                  ●  ○  ○  ○ (Indicator Dots)                                   │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

1. **Foto Memenuhi 100% Kolom 35%**:
   - `padding: 0;` dan `margin: 0;` pada kontainer `.hero-image`.
   - Gambar kue disetel `position: absolute; width: 100%; height: 100%; object-fit: cover;` sehingga gambar mengisi penuh tinggi banner (520px) dan menempel rapat ke sudut kanan atas dan kanan bawah kontainer banner (`border-radius: 24px; overflow: hidden;`).
2. **Gradasi Warna 65% Membayangi / Menutupi Foto 35%**:
   - Lapisan gradasi bayangan atmosferik (*atmospheric gradient shadow veil*) menggunakan pseudo-element `::before` pada `.hero-image` selebar **160px** dengan *multi-stop linear gradient* warna `#edf2ee`:
     ```css
     background: linear-gradient(
         to right,
         #edf2ee 0%,
         rgba(237, 242, 238, 0.95) 20%,
         rgba(237, 242, 238, 0.65) 50%,
         rgba(237, 242, 238, 0.20) 80%,
         rgba(237, 242, 238, 0) 100%
     );
     ```
   - Disempurnakan dengan *gradient alpha mask* pada gambar:
     ```css
     -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.25) 12%, rgba(0, 0, 0, 0.8) 35%, rgba(0, 0, 0, 1) 60%);
     mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.25) 12%, rgba(0, 0, 0, 0.8) 35%, rgba(0, 0, 0, 1) 60%);
     ```

---

## 2. Rincian Perubahan Kode Berkas

### A. `resources/views/Pelanggan/home.blade.php`

#### 1. CSS Kolom 35% Full-Bleed & Gradient Shadow Veil
```css
/* 35% Full-Bleed Showcase Area with 160px Atmospheric Gradient Shadow Veil */
.hero-image { 
    position: relative; 
    height: 100%; 
    min-height: 520px;
    width: 100%;
    display: flex; 
    align-items: stretch;
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    overflow: hidden;
    z-index: 1;
}
/* Atmospheric Gradient Shadow Veil from 65% area over 35% */
.hero-image::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 160px;
    background: linear-gradient(
        to right,
        #edf2ee 0%,
        rgba(237, 242, 238, 0.95) 20%,
        rgba(237, 242, 238, 0.65) 50%,
        rgba(237, 242, 238, 0.20) 80%,
        rgba(237, 242, 238, 0) 100%
    );
    z-index: 3;
    pointer-events: none;
}
.hero-image-frame { 
    position: relative; 
    width: 100%; 
    height: 100%; 
    display: block; 
    overflow: hidden; 
}
.hero-image-frame img { 
    position: absolute;
    top: 0;
    right: 0;
    width: 100%; 
    height: 100%; 
    object-fit: cover; 
    object-position: center center;
    display: block; 
    filter: saturate(1.05) contrast(1.02);
    -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.25) 12%, rgba(0, 0, 0, 0.8) 35%, rgba(0, 0, 0, 1) 60%);
    mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.25) 12%, rgba(0, 0, 0, 0.8) 35%, rgba(0, 0, 0, 1) 60%);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); 
}
.hero-image-frame:hover img { 
    transform: scale(1.04); 
}
```

#### 2. Aturan Media Queries Responsif
```css
@media (max-width: 1100px) {
    .hero-section { grid-template-columns: 60fr 40fr; min-height: 460px; }
    .hero-text { padding: 45px 30px 45px 45px; }
    .hero-text h1 { font-size: 40px; }
    .hero-image { min-height: 460px; padding: 0; }
    .hero-image::before { width: 130px; }
    .product-grid { grid-template-columns: repeat(3, 1fr) !important; }
    .four-cols { grid-template-columns: repeat(2, 1fr) !important; }
    .category-grid { grid-template-columns: repeat(3, 1fr) !important; }
}
@media (max-width: 768px) {
    .hero-section { display: flex; flex-direction: column-reverse; padding: 0; text-align: center; min-height: auto; }
    .hero-text { max-width: 100%; padding: 30px 20px 40px 20px; align-items: center; }
    .hero-sale-callout { justify-content: center; }
    .hero-text p.desc { margin-left: auto; margin-right: auto; }
    .hero-image { height: 280px; min-height: 280px; padding: 0; width: 100%; }
    .hero-image::before { 
        left: 0; 
        right: 0; 
        bottom: 0; 
        top: auto; 
        width: 100%; 
        height: 80px; 
        background: linear-gradient(to bottom, rgba(237, 242, 238, 0) 0%, rgba(237, 242, 238, 0.75) 50%, #edf2ee 100%); 
    }
    .hero-image-frame img { 
        object-position: center; 
        -webkit-mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%); 
        mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 60%, rgba(0,0,0,0) 100%); 
    }
    .hero-slider-prev { left: 10px !important; }
    .hero-slider-next { right: 10px !important; }
    .features-bar { grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 20px; }
    .product-grid { grid-template-columns: repeat(2, 1fr) !important; }
    .four-cols { grid-template-columns: 1fr !important; }
    .category-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
```

---

### B. `resources/views/Admin/kelola_carousel/banners.blade.php`

Pada modal pratinjau admin, layout diselaraskan dengan etalase full-bleed:
```blade
<div class="position-relative p-0" style="background: transparent; z-index: 2; height: 100%; min-height: 220px; overflow: hidden;">
    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 60px; background: linear-gradient(to right, #edf2ee 0%, rgba(237, 242, 238, 0.9) 25%, rgba(237, 242, 238, 0) 100%); z-index: 3; pointer-events: none;"></div>
    <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="Preview" style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; border-radius: 0 18px 18px 0; display: block; -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 15%, rgba(0,0,0,1) 45%); mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 15%, rgba(0,0,0,1) 45%);">
</div>
```

---

## 3. Hasil Pengujian & Verifikasi

1. **Kompilasi Cache View**:
   Perintah `php artisan view:clear` dijalankan dengan status `Compiled views cleared successfully`.
2. **Verifikasi Render HTML**:
   - `PelangganController::home()` berhasil merender 125.638 karakter HTML tanpa error Blade.
   - Aturan `object-fit: cover;` dan `width: 160px;` (veil) terverifikasi aktif pada HTML.
   - Gradasi multi-stop `rgba(237, 242, 238, 0.95) 20%` terverifikasi aktif.
   - Modal admin `banners.blade.php` berhasil terkompilasi dan memuat thumbnail cover full-bleed.

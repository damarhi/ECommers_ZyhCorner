# Dokumentasi Perubahan Desain: Hero Banner Rasio 65/35 & Titik Temu Bergradasi Membayangi (*Gradient Shadow Seam*)

Dokumentasi ini mencatat implementasi restrukturisasi tata letak komponen **Hero Banner Carousel** di beranda (`resources/views/Pelanggan/home.blade.php`) dan modal pratinjau admin (`resources/views/Admin/kelola_carousel/banners.blade.php`) menggunakan proporsi asimetris **65% (Teks & Tombol CTA) : 35% (Foto Produk)** dengan efek **titik temu vertikal bergradasi membayangi (*atmospheric gradient shadow seam*)**.

---

## 1. Latar Belakang & Konsep Desain

### A. Latar Belakang
Sebelumnya Hero Banner menggunakan layout flexbox simetris dengan alokasi ruang fleksibel. Untuk memberikan ruang nafas yang lebih lega bagi tipografi promo (judul besar, callout diskon, deskripsi, dan tombol CTA) sekaligus membuat foto kue menyatu secara organik (*blended*) ke dalam kanvas latar belakang tanpa tepi terpotong kaku (*no harsh edges*), diterapkan pembagian rasio **65 / 35**.

### B. Konsep Visual (*Visual Blueprint*)
```
┌──────────────────────────────────────────────────────────────────────────────────────────────────┐
│ <  [65% CONTENT AREA]                                     ░▒▓ [35% PHOTO SHOWCASE]             │
│                                                           ░▒▓                                  │
│    [Pill Badge: ✨ WELCOME TO ZYH CORNER]                 ░▒▓    ╭──────────────────────╮      │
│                                                           ░▒▓    │                      │      │
│    Delicious & Fresh                                      ░▒▓    │     🎂 Cake Photo    │    > │
│    Cakes and Desserts                                     ░▒▓    │     (Height: 390px)  │      │
│                                                           ░▒▓    │     (Organic Blend)  │      │
│    Sale up to [ 30% OFF ]                                 ░▒▓    ╰──────────────────────╯      │
│                                                           ░▒▓                                  │
│    [ Shop Now  → ]                                        ░▒▓                                  │
│                                                                                                │
│                             ◄─── Gradient Shadow Seam ───►                                     │
│                                  ●  ○  ○  ○ (Indicator Dots)                                   │
└──────────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## 2. Arsitektur Teknis 65/35 & Gradient Shadow Seam

### A. CSS Grid 65fr 35fr
Mengunci rasio secara presisi di layar desktop menggunakan fraksi CSS Grid:
```css
.hero-section {
    display: grid;
    grid-template-columns: 65fr 35fr;
    align-items: center;
    padding: 0;
    margin: 0;
    min-height: 520px;
    background: #edf2ee;
    border: 1px solid #e2eae3;
    border-radius: 24px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}
```

### B. Pemisahan Padding Antar Kolom
- **Kolom 65% (`.hero-text`)**: `padding: 60px 45px 60px 80px; width: 100%; box-sizing: border-box;`
  Memberi ruang horizontal hingga 680px bagi judul promosi dan tombol CTA tanpa terdesak.
- **Kolom 35% (`.hero-image`)**: `padding: 30px 40px 30px 10px; width: 100%; height: 100%; min-height: 520px;`
  Menjadi kontainer etalase foto kue setinggi 390px.

### C. Efek Titik Temu Bergradasi Membayangi (*Dual-Layer Seam Blend*)
1. **Atmospheric Gradient Seam Overlay**:
   Pseudo-element `.hero-image::before` ditempatkan di sisi kiri kolom 35% tepat di garis batas temu:
   ```css
   .hero-image::before {
       content: '';
       position: absolute;
       left: 0;
       top: 0;
       bottom: 0;
       width: 110px;
       background: linear-gradient(to right, #edf2ee 0%, rgba(237, 242, 238, 0.6) 45%, rgba(237, 242, 238, 0) 100%);
       z-index: 4;
       pointer-events: none;
   }
   ```
2. **Soft Edge Fade Masking**:
   Lapisan *gradient mask* pada elemen gambar kue agar sisi kirinya memudar transparan ke arah teks:
   ```css
   -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.80) 15%, rgba(0, 0, 0, 1) 32%);
   mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.80) 15%, rgba(0, 0, 0, 1) 32%);
   ```

---

## 3. Rincian Perubahan Kode Berkas

### A. `resources/views/Pelanggan/home.blade.php`

#### 1. Aturan CSS Utama Hero Banner
```css
/* Hero Section (65 / 35 Asymmetric Layout with Atmospheric Gradient Shadow Seam) */
.hero-section {
    background: #edf2ee;
    border: 1px solid #e2eae3;
    border-radius: 24px;
    display: grid;
    grid-template-columns: 65fr 35fr;
    align-items: center;
    padding: 0;
    margin: 0;
    min-height: 520px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}
.hero-text { 
    z-index: 2; 
    padding: 60px 45px 60px 80px; 
    width: 100%; 
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
}
.hero-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0, 178, 7, 0.10);
    color: #008a05;
    border: 1px solid rgba(0, 178, 7, 0.25);
    padding: 7px 18px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 18px;
}
.hero-text h1 { 
    font-size: 52px; 
    font-weight: 700; 
    color: #1a1a1a; 
    line-height: 1.15; 
    margin-bottom: 16px; 
    letter-spacing: -0.8px; 
    max-width: 680px;
}
.hero-sale-callout {
    font-size: 22px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}
.hero-sale-highlight {
    color: #ff8a00;
    font-weight: 700;
}
.hero-text p.desc { 
    color: #555555; 
    font-size: 15px; 
    margin-top: 10px; 
    margin-bottom: 0; 
    max-width: 540px; 
    line-height: 1.6; 
}
.hero-btn { 
    margin-top: 28px; 
    background: #00b207; 
    color: #ffffff !important; 
    border: none; 
    padding: 15px 38px; 
    border-radius: 50px; 
    font-weight: 600; 
    font-size: 15px; 
    cursor: pointer; 
    display: inline-flex; 
    align-items: center; 
    gap: 12px; 
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); 
    text-decoration: none; 
    box-shadow: 0 6px 20px rgba(0, 178, 7, 0.28); 
}
.hero-btn:hover { 
    background: #009906; 
    transform: translateY(-3px); 
    box-shadow: 0 10px 28px rgba(0, 178, 7, 0.40); 
}
.hero-btn i { transition: transform 0.25s ease; }
.hero-btn:hover i { transform: translateX(5px); }

/* 35% Showcase Area with Atmospheric Gradient Shadow Seam */
.hero-image { 
    position: relative; 
    height: 100%; 
    min-height: 520px;
    display: flex; 
    justify-content: center; 
    align-items: center; 
    padding: 30px 40px 30px 10px;
    box-sizing: border-box;
    overflow: hidden;
    z-index: 1;
}
/* Gradient Shadow Seam at Meeting Point between 65% and 35% */
.hero-image::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 110px;
    background: linear-gradient(to right, #edf2ee 0%, rgba(237, 242, 238, 0.6) 45%, rgba(237, 242, 238, 0) 100%);
    z-index: 4;
    pointer-events: none;
}
.hero-image-frame { 
    position: relative; 
    border-radius: 24px; 
    max-width: 480px; 
    width: 100%; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    transition: transform 0.4s ease; 
}
.hero-image-frame::before {
    content: '';
    position: absolute;
    width: 380px;
    height: 380px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(0, 178, 7, 0.08) 0%, rgba(255, 255, 255, 0.5) 45%, rgba(255, 255, 255, 0) 72%);
    z-index: 1;
    pointer-events: none;
    filter: blur(10px);
}
.hero-image-frame:hover { transform: translateY(-5px) scale(1.02); }
.hero-image-frame img { 
    position: relative;
    z-index: 2;
    width: 100%; 
    height: 390px; 
    object-fit: contain; 
    border-radius: 16px; 
    display: block; 
    filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.12)) drop-shadow(0 6px 12px rgba(0, 0, 0, 0.06)); 
    -webkit-mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.80) 15%, rgba(0, 0, 0, 1) 32%);
    mask-image: linear-gradient(to right, rgba(0, 0, 0, 0) 0%, rgba(0, 0, 0, 0.80) 15%, rgba(0, 0, 0, 1) 32%);
    transition: all 0.4s ease;
}
```

#### 2. Media Queries Responsif
```css
@media (max-width: 1100px) {
    .hero-section { grid-template-columns: 60fr 40fr; min-height: 460px; }
    .hero-text { padding: 45px 30px 45px 45px; }
    .hero-text h1 { font-size: 40px; }
    .hero-image { min-height: 460px; padding: 25px 25px 25px 0; }
    .hero-image-frame img { height: 320px; }
}
@media (max-width: 768px) {
    .hero-section { display: flex; flex-direction: column-reverse; padding: 0; text-align: center; min-height: auto; }
    .hero-text { max-width: 100%; padding: 30px 20px 40px 20px; align-items: center; }
    .hero-sale-callout { justify-content: center; }
    .hero-text p.desc { margin-left: auto; margin-right: auto; }
    .hero-image { justify-content: center; min-height: auto; padding: 35px 20px 10px 20px; width: 100%; }
    .hero-image::before { left: 0; right: 0; bottom: 0; top: auto; width: 100%; height: 50px; background: linear-gradient(to bottom, rgba(237, 242, 238, 0) 0%, #edf2ee 100%); }
    .hero-image-frame { max-width: 320px; }
    .hero-image-frame::before { width: 260px; height: 260px; }
    .hero-image-frame img { height: 240px; -webkit-mask-image: none; mask-image: none; }
    .hero-slider-prev { left: 10px !important; }
    .hero-slider-next { right: 10px !important; }
}
```

---

### B. `resources/views/Admin/kelola_carousel/banners.blade.php`

Pada modal pratinjau admin, layout diselaraskan dengan arsitektur 65/35 yang sama:
```blade
<div class="shadow-sm position-relative overflow-hidden"
    style="background: #edf2ee; border-radius: 18px; border: 1px solid #e2eae3; min-height: 220px; display: grid; grid-template-columns: 65fr 35fr; align-items: center;">
    <div class="p-3 ps-4" style="z-index: 2;">
        <span class="badge mb-2 d-inline-block" style="background: rgba(0, 178, 7, 0.10); color: #008a05; border: 1px solid rgba(0, 178, 7, 0.25); border-radius: 30px; font-weight: 700; padding: 5px 14px; font-size: 11px; text-transform: uppercase;">
            <i class="mdi mdi-sparkles me-1"></i> {{ $badgeTextPreview }}
        </span>
        <h4 class="font-weight-bold mb-1" style="color: #1a1a1a; letter-spacing: -0.3px;">{{ $b->judul_banner }}</h4>
        @if($calloutHighlightPreview)
            <div style="font-size: 13.5px; font-weight: 600; color: #1a1a1a; margin-bottom: 6px;">
                @if($calloutPrefixPreview)
                    <span>{{ $calloutPrefixPreview }}</span>
                @endif
                <span style="color: #ff8a00; font-weight: 700;">{{ $calloutHighlightPreview }}</span>
            </div>
        @endif
        @if($descTextPreview)
            <p class="text-muted mb-3" style="font-size: 12px; line-height: 1.5;">{{ $descTextPreview }}</p>
        @endif
        <span class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background: #00b207; font-weight: 600; font-size: 12.5px;">Shop Now <i class="mdi mdi-arrow-right ms-1"></i></span>
    </div>
    <div class="position-relative d-flex align-items-center justify-content-center p-2" style="background: transparent; z-index: 2; height: 100%;">
        <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 40px; background: linear-gradient(to right, #edf2ee 0%, rgba(237, 242, 238, 0) 100%); z-index: 3; pointer-events: none;"></div>
        <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="Preview" style="max-width: 170px; height: 125px; object-fit: contain; border-radius: 12px; display: block; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.10)); -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,1) 25%); mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,1) 25%);">
    </div>
</div>
```

---

## 4. Hasil Verifikasi & Uji Coba

1. **Pembersihan Cache Tampilan**:
   `php artisan view:clear` dieksekusi dengan hasil `Compiled views cleared successfully`.
2. **Kompilasi & Render View**:
   - `PelangganController::home()` berhasil merender 125.755 karakter HTML.
   - Aturan CSS `grid-template-columns: 65fr 35fr;` terverifikasi aktif pada berkas render.
   - Efek gradasi `mask-image: linear-gradient` dan `hero-image::before` terverifikasi aktif.
   - Tampilan modal Admin `banners.blade.php` berhasil terkompilasi dan memuat grid 65/35 pratinjau.

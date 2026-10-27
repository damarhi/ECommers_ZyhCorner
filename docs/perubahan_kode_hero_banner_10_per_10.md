# Dokumentasi Peningkatan Desain Hero Banner 10/10 (Frontend Design Direction)

Dokumentasi ini mencatat implementasi penyempurnaan desain visual pada komponen **Hero Banner Carousel** di beranda (`/`) dari status fungsional dasar menjadi kualitas visual prima (**10/10**) yang mengikuti kaidah estetika *Ecobazar* (Figma), proporsi simetri vertikal murni, serta *ambient lighting* fotografi produk bakery & pastry.

---

## 1. Analisis Latar Belakang & Masalah Sebelumnya

Sebelum penyempurnaan, Hero Banner telah berhasil dibuat 100% dinamis dari database tanpa teks hardcoded, namun masih memiliki kekurangan ergonomi visual:
1. **Asimetri Vertikal & Tombol Terpotong**:
   - `.hero-section` memiliki `margin-top: 15px; margin-bottom: 35px;` di dalam kontainer `.hero-slider-wrapper` yang berstatus `overflow: hidden`.
   - Hal ini menyebabkan konten teks kiri dan tombol `Shop Now` terdesak ke tepi bawah, menyisakan jarak bawah yang sangat sempit (< 10px) sementara jarak atas terlalu lapang (> 110px).
2. **Skala Produk Terlalu Kecil di Layar Lebar**:
   - Gambar kue di desktop 1920px hanya dirender dengan tinggi 320px dan lebar visual ~300px, menyisakan ruang kosong yang terlalu lebar (~200px) di sisi kanan banner.
3. **Tombol CTA Kurang Bernafas**:
   - Tombol `Shop Now` memiliki jarak yang terlalu dekat dengan teks promo/callout di atasnya, sehingga hierarki aksinya kurang menonjol (*lack of focal prominence*).
4. **Kontrol Navigasi Masih Standar**:
   - Tombol panah slider dan indikator dot belum memiliki kedalaman material (*glassmorphism depth*) yang harmonis dengan palet natural.

---

## 2. Rincian 4 Pilar Peningkatan Visual (Pencapaian 10/10)

```
┌────────────────────────────────────────────────────────────────────────────────────────┐
│ <  [Pill Badge: ✨ WELCOME TO ZYH CORNER]                                              │
│    Delicious & Fresh                                    ╭─────────────────────────╮    │
│    Cakes and Desserts                                   │  [Ambient Radial Glow]  │    │
│                                                         │       🎂 Cake Hero       │  > │
│    Sale up to [ 30% OFF ]                               │     (Height: 380px)     │    │
│                                                         │    (Drop Shadow 3D)     │    │
│    [ Shop Now  → ]                                      ╰─────────────────────────╯    │
│                                                                                        │
│                                  ●  ○  ○  ○ (Indicator Dots)                           │
└────────────────────────────────────────────────────────────────────────────────────────┘
```

### A. Simetri Vertikal Seimbang (*True Vertical Symmetry*)
- Mengatur `margin: 0;` pada `.hero-section` agar sepenuhnya menyatu dengan wrapper slider tanpa offset liar.
- Mengatur padding atas-bawah dan kiri-kanan yang presisi: `padding: 60px 80px;` dengan `min-height: 520px;`.
- Teks dan gambar kini berada tepat pada sumbu optik tengah kontainer (*vertical optical center*).

### B. Ambient Food Spotlight & Pembesaran Skala Gambar
- Menambahkan lingkaran pendaran lembut (*soft ambient radial glow halo*) di belakang gambar kue menggunakan pseudo-element `::before`:
  ```css
  .hero-image-frame::before {
      content: '';
      position: absolute;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(0, 178, 7, 0.08) 0%, rgba(255, 255, 255, 0.5) 45%, rgba(255, 255, 255, 0) 72%);
      z-index: 1;
      pointer-events: none;
      filter: blur(10px);
  }
  ```
- Memperbesar dimensi gambar kue dari `320px` menjadi `380px` (`max-width: 520px;`) dengan *multi-layered drop shadow*:
  `filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.12)) drop-shadow(0 6px 12px rgba(0, 0, 0, 0.06));`
- Memberi efek hover terangkat halus: `transform: translateY(-5px) scale(1.02);`.

### C. Jarak Nafas Tombol & Hierarki Tipografi
- Menambahkan jarak nafas mandiri pada tombol CTA: `margin-top: 28px;`.
- Mempertegas tombol `Shop Now` dengan padding `15px 38px`, font size `15px`, border-radius `50px`, dan bayangan hijau brand: `box-shadow: 0 6px 20px rgba(0, 178, 7, 0.28);`.
- Memperbesar judul utama `<h1>` menjadi `font-size: 52px; line-height: 1.15; letter-spacing: -0.8px;`.

### D. Navigasi Frosted Glass & Pill Dots Slider
- Panah kiri/kanan: Ukuran lingkaran `48px × 48px` dengan efek *frosted glass* nyata (`backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.75); border: 1.5px solid rgba(255, 255, 255, 0.95);`).
- Titik indikator: Kapsul aktif (*pill active dot*) melebar hingga `24px` berwarna hijau brand `#00b207` pada posisi presisi `bottom: 22px;`.

---

## 3. Perubahan Kode Berkas

### A. `resources/views/Pelanggan/home.blade.php`
```css
/* Hero Section (10/10 Artisanal Refinement & Symmetrical Layout) */
.hero-section {
    background: #edf2ee;
    border: 1px solid #e2eae3;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 60px 80px;
    margin: 0;
    min-height: 520px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}
.hero-text { flex: 1.15; z-index: 2; max-width: 620px; }
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
    max-width: 480px; 
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
.hero-image { flex: 1; display: flex; justify-content: flex-end; align-items: center; position: relative; z-index: 2; }
.hero-image-frame { 
    position: relative; 
    border-radius: 24px; 
    max-width: 520px; 
    width: 100%; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    transition: transform 0.4s ease; 
}
.hero-image-frame::before {
    content: '';
    position: absolute;
    width: 400px;
    height: 400px;
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
    height: 380px; 
    object-fit: contain; 
    border-radius: 16px; 
    display: block; 
    filter: drop-shadow(0 20px 35px rgba(0, 0, 0, 0.12)) drop-shadow(0 6px 12px rgba(0, 0, 0, 0.06)); 
}

/* Frosted Glass Slider Arrows (48px Circle with Ambient Blur) */
.hero-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.75) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    border: 1.5px solid rgba(255, 255, 255, 0.95) !important;
    color: #1a1a1a !important;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    opacity: 0.75;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08) !important;
}
.hero-slider-wrapper:hover .hero-slider-arrow {
    opacity: 0.95;
}
.hero-slider-arrow:hover {
    background: #ffffff !important;
    color: #00b207 !important;
    border-color: #00b207 !important;
    opacity: 1 !important;
    transform: translateY(-50%) scale(1.12) !important;
    box-shadow: 0 8px 26px rgba(0, 178, 7, 0.20) !important;
}
.hero-slider-prev { left: 24px !important; }
.hero-slider-next { right: 24px !important; }

/* Indicator Dots (Pill Active Style) */
.hero-slider-dots {
    position: absolute;
    bottom: 22px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 8px;
    z-index: 10;
    background: rgba(0, 0, 0, 0.16);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    padding: 6px 14px;
    border-radius: 20px;
}
.hero-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.55);
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.hero-dot.active {
    width: 24px;
    border-radius: 10px;
    background: #00b207;
    box-shadow: 0 2px 8px rgba(0, 178, 7, 0.4);
}
```

### B. `public/css/home.css`
Menyelaraskan nilai ukuran panah (48px), frosted blur (12px), offset (left/right: 24px), serta pill active indicator dots agar seragam di semua stylesheet.

---

## 4. Hasil Verifikasi

1. **Kompilasi Blade**: `php artisan view:clear` berhasil dijalankan.
2. **Kesesuaian CSS**:
   - `hero-section padding: 60px 80px`: Terverifikasi aktif.
   - `hero-text h1 font-size: 52px`: Terverifikasi aktif.
   - `hero-btn margin-top: 28px`: Terverifikasi aktif.
   - `hero-image-frame img height: 380px`: Terverifikasi aktif.
   - `ambient spotlight radial-gradient`: Terverifikasi aktif.
   - `frosted arrows 48px`: Terverifikasi aktif.
   - `pill dots bottom: 22px`: Terverifikasi aktif.

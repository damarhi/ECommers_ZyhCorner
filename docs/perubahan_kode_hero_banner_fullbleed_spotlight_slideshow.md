# Dokumentasi Perubahan: Hero Banner Full-Bleed 35% dengan Radial Spotlight Seam & Slideshow Multi-Banner

**Tanggal**: 22 Oktober 2026  
**Fitur / Komponen**: Hero Banner Beranda (`.hero-slider-wrapper`, `.hero-section`, `.hero-image`)  
**Tujuan**: 
1. Menambahkan efek *atmospheric radial spotlight* di antara bagian 65% (konten teks dan tombol CTA) dan 35% (foto produk), serta pendaran spotlight di belakang teks utama agar judul penawaran terbaca dengan kontras maksimal.
2. Memastikan foto produk di kolom 35% kanan membentang 100% penuh (*edge-to-edge / full-bleed cover*) tanpa terpotong kaku oleh masking berlebih atau runtuh akibat benturan flexbox.
3. Mengaktifkan dan memverifikasi fitur perputaran *slideshow* otomatis (carousel) dari database untuk semua banner dengan `posisi_banner = 'home_hero'` yang berstatus `Aktif`.

---

## 1. Analisis Masalah Tampilan Lama (Root Cause)

1. **Runtuhnya Tinggi Gambar (*Height Collapse* menjadi 0px)**:
   - Bundel Vite `pelanggan_pages.css` menerapkan deklarasi lama `.hero-section { display: flex; align-items: center; }` dan `.hero-image img { max-height: 400px; object-fit: contain; }`.
   - Di `home.blade.php`, `.hero-image-frame` diatur `height: 100%`, dan `img` diatur `position: absolute; height: 100%`.
   - Di dalam container Flexbox tanpa `height: 100%` eksplisit pada parent, tinggi relatif `height: 100%` terhitung sebagai `0px` oleh mesin render browser sehingga foto produk sama sekali tidak muncul (*blank*).
2. **Masking Terlalu Agresif**:
   - Aturan `-webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, ..., rgba(0,0,0,1) 60%)` memotong transparansi foto secara drastis dari tepi kiri hingga 60% lebar gambar, membuat separuh foto pastry hilang.
3. **Slideshow Statis / Banner Tunggal**:
   - Di database sebelumnya hanya terdapat 1 banner untuk `home_hero`. Akibatnya logika Blade `@if($heroBanners->count() > 1)` menyembunyikan tombol navigasi panah (*prev/next*) dan dot navigasi, serta skrip slideshow tidak memutar banner secara berkala.

---

## 2. Solusi Desain Visual (*Frontend Design Direction*)

1. **Struktur Grid Asimetris 65% : 35%**:
   - `.hero-section` dikunci menggunakan `display: grid !important; grid-template-columns: 65fr 35fr !important; align-items: stretch !important; min-height: 520px !important;`.
   - Kolom 35% (`.hero-image`) dialokasikan lebar dan tinggi penuh 520px secara presisi.
2. **Pendaran Dual Radial Spotlight**:
   - **Spotlight Latar Teks (Bagian 65%)**:
     Pseudo-element `.hero-text::before` memberikan pendaran cahaya putih lembut (`#ffffff` 95% memudar ke transparansi `#edf2ee`) terpusat di belakang judul dan tombol CTA, memperkuat keterbacaan teks tanpa bayangan gelap.
   - **Spotlight Seam (Batas Antara 65% dan 35%)**:
     Pseudo-element `.hero-image::before` diposisikan di sepanjang batas vertikal (`left: -50px; width: 200px;`):
     ```css
     background: radial-gradient(
         ellipse 100% 120% at 0% 50%,
         rgba(237, 242, 238, 0.98) 0%,
         rgba(237, 242, 238, 0.85) 30%,
         rgba(237, 242, 238, 0.35) 65%,
         rgba(237, 242, 238, 0) 100%
     );
     ```
     Cahaya hijau pastel mengalir halus dari kiri ke atas foto kue tanpa garis potong kaku, membiarkan 80%+ foto pastry tampil cerah dan tajam.
3. **Pengisian 100% Full-Bleed Edge-to-Edge pada Bagian 35%**:
   - `.hero-image-frame` dan elemen `img` diatur `position: absolute !important; inset: 0 !important; width: 100% !important; height: 100% !important; object-fit: cover !important; object-position: center !important;`.
   - Masking yang memotong gambar dihapus dari desktop sehingga seluruh area 35% terisi penuh oleh foto kue/pastry.
4. **Perputaran Slideshow Otomatis & Kontrol Navigasi**:
   - Ditambahkan entri banner aktif kedua untuk posisi `home_hero` di database dan seeder.
   - Tombol panah navigasi *frosted glass* (`.hero-slider-prev/next`) dan dot indikator aktif otomatis tampil.
   - Durasi interval autoplay 5 detik dengan transisi animasi halus dan fitur *Pause on Hover*.

---

## 3. Rincian Perubahan Kode per Berkas

### A. `resources/views/Pelanggan/home.blade.php`

1. **Pembaruan Gaya CSS Hero Banner**:
```css
/* Hero Section (65 / 35 Asymmetric Layout with Dual Radial Spotlight) */
.hero-section {
    background: #edf2ee;
    border: 1px solid #e2eae3;
    border-radius: 24px;
    display: grid !important;
    grid-template-columns: 65fr 35fr !important;
    align-items: stretch !important;
    padding: 0 !important;
    margin: 0 !important;
    min-height: 520px !important;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}
.hero-text { 
    position: relative;
    z-index: 2; 
    padding: 60px 45px 60px 80px; 
    width: 100%; 
    box-sizing: border-box;
    display: flex; 
    flex-direction: column; 
    justify-content: center; 
    align-items: flex-start; 
}
.hero-text::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 130% 100% at 35% 45%, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.55) 45%, rgba(237, 242, 238, 0) 80%);
    z-index: 1;
    pointer-events: none;
}
.hero-text > * {
    position: relative;
    z-index: 2;
}

/* 35% Full-Bleed Showcase Area with Radial Spotlight Seam */
.hero-image { 
    position: relative !important; 
    height: 100% !important; 
    min-height: 520px !important; 
    width: 100% !important; 
    display: block !important; 
    padding: 0 !important; 
    margin: 0 !important; 
    box-sizing: border-box !important; 
    overflow: hidden !important; 
    z-index: 1 !important; 
}
/* Radial Spotlight Seam between 65% text area and 35% image area */
.hero-image::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: -50px;
    width: 200px;
    background: radial-gradient(
        ellipse 100% 120% at 0% 50%,
        rgba(237, 242, 238, 0.98) 0%,
        rgba(237, 242, 238, 0.85) 30%,
        rgba(237, 242, 238, 0.35) 65%,
        rgba(237, 242, 238, 0) 100%
    );
    z-index: 2;
    pointer-events: none;
}
.hero-image-frame { 
    position: absolute !important; 
    inset: 0 !important;
    width: 100% !important; 
    height: 100% !important; 
    display: block !important; 
    overflow: hidden !important; 
}
.hero-image-frame img { 
    position: absolute !important; 
    inset: 0 !important;
    width: 100% !important; 
    height: 100% !important; 
    max-width: none !important;
    max-height: none !important;
    object-fit: cover !important; 
    object-position: center center !important; 
    display: block !important; 
    filter: saturate(1.05) contrast(1.02);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); 
}
.hero-section:hover .hero-image-frame img { 
    transform: scale(1.04); 
}
```

2. **Media Query Responsif Layar Mobile**:
```css
@media (max-width: 768px) {
    .hero-section { display: flex !important; flex-direction: column-reverse !important; padding: 0 !important; text-align: center !important; min-height: auto !important; }
    .hero-text { max-width: 100% !important; padding: 30px 20px 40px 20px !important; align-items: center !important; }
    .hero-sale-callout { justify-content: center !important; }
    .hero-text p.desc { margin-left: auto !important; margin-right: auto !important; }
    .hero-image { height: 260px !important; min-height: 260px !important; padding: 0 !important; width: 100% !important; margin-bottom: 0 !important; }
    .hero-image-frame { position: relative !important; width: 100% !important; height: 100% !important; }
    .hero-image-frame img { 
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: center !important; 
        -webkit-mask-image: none !important; 
        mask-image: none !important; 
    }
    .hero-image::before { 
        display: none !important; 
    }
    .hero-slider-prev { left: 10px !important; }
    .hero-slider-next { right: 10px !important; }
    .features-bar { grid-template-columns: repeat(2, 1fr); gap: 16px; padding: 20px; }
}
```

3. **Fallback Gambar pada Template Blade**:
```blade
<div class="hero-image">
    <div class="hero-image-frame">
        @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
            <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
        @else
            <img src="{{ asset('storage/banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png') }}" alt="{{ $banner->judul_banner }}">
        @endif
    </div>
</div>
```

---

### B. `resources/css/pelanggan_pages.css` & `resources/css/pelanggan_custom.css` & `public/css/home.css`

- Menghapus deklarasi usang `display: flex;` dan `object-fit: contain; max-height: 400px;` yang menimpa tampilan Blade setelah kompilasi Vite.
- Menambahkan aturan baku yang sama untuk `.hero-section`, `.hero-text`, `.hero-image`, `.hero-image-frame`, dan `img` (grid 65fr 35fr, radial spotlight seam, dan cover 100%).

---

### C. `public/js/home.js`

- Memperhalus animasi masuk slide pada container gambar `.hero-image`:
  - Mengubah dari animasi rotasi yang kasar (`scale: 0.84, rotation: 5deg`) menjadi transisi lembut:
    ```javascript
    if (img) {
        tl.fromTo(img,
            { x: direction > 0 ? 30 : -30, opacity: 0, scale: 0.98 },
            { x: 0, opacity: 1, scale: 1, duration: 0.75, ease: 'power2.out' },
            '-=0.55'
        );
    }
    ```
- Menjamin fungsi tombol panah (*prev/next*), navigasi dot, dan jeda saat kursor diarahkan (*pause on hover*).

---

### D. `database/seeders/BannerSeeder.php` & Database

- Menambahkan banner kedua untuk posisi `home_hero` di database:
  - **Banner 1**: "Delicious & Fresh Cakes and Desserts" (`banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png`) - Urutan 1.
  - **Banner 2**: "Special Artisanal Pastry & Bakery" (`banners/FwCnRdyIVKpLvKe5xCLVEujVcIgcvPxmU4LfZisj.png`) - Urutan 2.
- Data ini memastikan slideshow otomatis aktif dan dapat memutar 2 slide berbeda secara mulus.

---

## 4. Hasil Kompilasi & Verifikasi Nyata (Browser Smoke Test)

1. **Build Vite**:
   - `npm run build` sukses mengompilasi bundel `pelanggan_pages-B1N5tvjs.css` (86.91 kB).
   - `php artisan view:clear` sukses membersihkan cache Blade.
2. **Pengukuran Computed Styles Nyata di Browser**:
   - `totalSlides`: 2, `totalDots`: 2, `hasPrev`: true, `hasNext`: true.
   - `.hero-section`: `display: grid; width: 1170px; height: 522px; gridTemplateColumns: 759.188px 408.812px;` (Presisi 65% : 35%).
   - `.hero-text`: `width: 759.188px; height: 520px;`
   - `.hero-image`: `width: 408.812px; height: 520px;`
   - `.hero-image-frame img`: `position: absolute; inset: 0; width: 408.812px; height: 520px; object-fit: cover;` (**100% Full Bleed**).
3. **Uji Navigasi & Slideshow**:
   - Klik tombol Next $\rightarrow$ Slide beralih dari Slide 0 ke Slide 1 dengan animasi GSAP yang halus.
   - Klik tombol Prev $\rightarrow$ Slide kembali ke Slide 0.
   - Klik Dot 1 $\rightarrow$ Langsung beralih ke Slide 1.
   - Autoplay berjalan otomatis bergantian setiap ~5 detik saat kursor berada di luar area banner, dan terjeda saat kursor berada di atas banner (*hover*).
4. **Uji Responsivitas Perangkat**:
   - **Desktop (1463px / 1170px container)**: Layout 65/35 berdampingan, tinggi 522px, spotlight seam memusat di perbatasan.
   - **Tablet (768px)**: Lebar 738px, tinggi 552px, gambar di atas dan teks di bawah dengan rapi.
   - **Mobile (390px)**: Lebar 360px, tinggi 589px, gambar berdimensi 260px cover penuh, teks dan tombol tersorot jelas di tengah layar ponsel.

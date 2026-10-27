# Dokumentasi Perubahan: Promo Banner Full-Bleed Edge-to-Edge dengan Radial Spotlight Gradient

**Tanggal**: 22 Oktober 2026  
**Fitur / Komponen**: Promo Banner Diskon Kolom ke-4 (Bagian 4 Kolom di Beranda)  
**Tujuan**: Memperbaiki kartu Promo Banner Figma (`.promo-banner-figma`) agar foto banner memenuhi kartu secara utuh 100% (*edge-to-edge / full-bleed*: kiri, kanan, atas, bawah) sekaligus menjaga efek *gradient spotlight* putih/radial di area teks agar judul diskon (`75% OFF`), badge (`SUMMER SALE`), tagline, dan tombol CTA (`Shop Now`) tetap terbaca jelas, kontras tinggi, dan elegan tanpa terpotong atau canggung.

---

## 1. Analisis Akar Masalah (Root Cause)

Berdasarkan investigasi menyeluruh pada tangkapan layar pengguna dan kode sumber:
1. **Timpaan CSS Eksternal (*CSS Cascading Precedence*)**:
   - Di `layout_pelanggan.blade.php`, stylesheet Vite `@vite(['resources/css/pelanggan_pages.css'])` dimuat **setelah** `@yield('styles')`.
   - Berkas `resources/css/pelanggan_pages.css`, `public/css/home.css`, dan `resources/css/pelanggan_custom.css` masih menyimpan aturan lama:
     - `background: radial-gradient(130% 90% at 50% 0%, #ffffff 0%, #fbf9f5 55%, #f4ede3 100%);` (warna latar krem/peach datar).
     - `.promo-figma-image-wrap { position: relative; ... flex: 1; min-height: 180px; }`
     - `.promo-figma-img { max-height: 200px; width: 100%; object-fit: contain; }`
     - `.promo-floating-tag { position: absolute; top: 6px; }`
   - Hal tersebut menyebabkan gambar produk tertekan menjadi *thumbnail* kecil di bagian bawah tombol dengan batasan `max-height: 200px` dan `object-fit: contain`, sementara kartu tetap berlatar peach/krem.
2. **Ketinggian Kolom ke-4 pada Grid**:
   - Kolom ke-4 pada `.four-cols` belum memiliki instruksi `display: flex; flex-direction: column; height: 100%;` untuk meneruskan peregangan grid `align-items: stretch` ke kartu `.promo-banner-figma`.

---

## 2. Solusi Desain (*Frontend Design Direction*)

1. **Latar Belakang Gambar Full-Bleed (Layer 1, Z-Index: 1)**:
   - `.promo-figma-image-wrap` dialihkan ke `position: absolute; inset: 0; width: 100%; height: 100%;`.
   - `.promo-figma-img` diatur `position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; object-position: center bottom;` sehingga gambar kue/pastry membentang penuh 100% dari ujung atas ke bawah dan kiri ke kanan kartu.
2. **Lapisan Radial Spotlight Gradient (Layer 2, Z-Index: 2)**:
   - Menggunakan pseudo-element `::before` dengan *radial spotlight gradient* lembut yang berpusat di belakang teks diskon:
     ```css
     background: radial-gradient(
         ellipse 150% 115% at 50% 12%,
         rgba(255, 255, 255, 0.98) 0%,
         rgba(255, 255, 255, 0.92) 34%,
         rgba(255, 255, 255, 0.65) 60%,
         rgba(255, 255, 255, 0.15) 82%,
         rgba(255, 255, 255, 0) 100%
     );
     ```
   - Cahaya putih memusat di area teks dan tombol, lalu memudar secara transparan (*fade to transparent*) ke arah foto produk di bawah dan tepian kartu, menjaga saturasi warna asli kue tetap menggugah selera.
3. **Konten Teks & Lencana Mengambang (Layer 3, Z-Index: 3)**:
   - Header teks (`.promo-figma-header`) diposisikan di atas spotlight dengan kontras tajam.
   - Lencana mengambang `.promo-floating-tag` diposisikan di bagian bawah kartu (`bottom: 16px; left: 50%; transform: translateX(-50%);`) dengan efek kaca buram (*glassmorphism backdrop blur*).

---

## 3. Rincian Perubahan Berkas

### A. `resources/views/Pelanggan/home.blade.php`

1. **Menambahkan penguncian tinggi penuh pada kolom ke-4 di `.four-cols`**:
   ```css
   .four-cols {
       display: grid !important;
       grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(0, 1.35fr) !important;
       gap: 22px !important;
       margin-bottom: 60px !important;
       align-items: stretch !important;
   }
   .four-cols > div:last-child {
       display: flex !important;
       flex-direction: column !important;
       height: 100% !important;
   }
   ```

2. **Memperbarui CSS `.promo-banner-figma` & Radial Spotlight**:
   ```css
   .promo-banner-figma,
   .promo-banner {
       position: relative;
       border-radius: 20px;
       overflow: hidden;
       width: 100%;
       height: 100%;
       min-height: 420px;
       display: flex;
       flex-direction: column;
       justify-content: flex-start;
       align-items: center;
       text-align: center;
       padding: 28px 20px 20px 20px;
       background: #ffffff;
       border: 1.5px solid #dbe8dd;
       box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 4px rgba(0, 178, 7, 0.06);
       transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
       box-sizing: border-box;
       flex: 1;
   }

   .promo-banner-figma::before,
   .promo-banner::before {
       content: '';
       position: absolute;
       inset: 0;
       background: radial-gradient(
           ellipse 150% 115% at 50% 12%,
           rgba(255, 255, 255, 0.98) 0%,
           rgba(255, 255, 255, 0.92) 34%,
           rgba(255, 255, 255, 0.65) 60%,
           rgba(255, 255, 255, 0.15) 82%,
           rgba(255, 255, 255, 0) 100%
       );
       z-index: 2;
       pointer-events: none;
   }
   ```

3. **Memperbarui CSS `.promo-figma-image-wrap`, `.promo-figma-img`, dan `.promo-floating-tag`**:
   ```css
   .promo-figma-image-wrap {
       position: absolute;
       inset: 0;
       width: 100%;
       height: 100%;
       margin: 0;
       padding: 0;
       overflow: hidden;
       z-index: 1;
       border-radius: 20px;
   }

   .promo-floating-tag {
       position: absolute;
       bottom: 16px;
       left: 50%;
       transform: translateX(-50%);
       top: auto;
       z-index: 3;
       background: rgba(255, 255, 255, 0.92);
       backdrop-filter: blur(10px);
       -webkit-backdrop-filter: blur(10px);
       border: 1px solid rgba(0, 178, 7, 0.25);
       color: #00b207;
       font-size: 11px;
       font-weight: 700;
       padding: 6px 16px;
       border-radius: 20px;
       box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
       display: inline-flex;
       align-items: center;
       gap: 6px;
       white-space: nowrap;
       pointer-events: none;
   }

   .promo-figma-img {
       position: absolute;
       inset: 0;
       width: 100%;
       height: 100%;
       max-height: none;
       object-fit: cover;
       object-position: center bottom;
       display: block;
       filter: saturate(1.05) contrast(1.02);
       transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
   }

   .promo-banner-figma:hover .promo-figma-img,
   .promo-banner:hover .promo-figma-img {
       transform: scale(1.05);
   }
   ```

4. **Penataan Ulang Struktur Layer Blade**:
   ```blade
   <div class="promo-banner-figma">
       <!-- Layer 1: Background Edge-to-Edge Imagery Area -->
       <div class="promo-figma-image-wrap">
           @if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner))
               <img src="{{ asset('storage/' . $popupBanner->gambar_banner) }}" alt="{{ $popupBanner->judul_banner ?? 'Summer Sale' }}" class="promo-figma-img">
           @else
               <img src="{{ asset('storage/banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png') }}" alt="Summer Sale" class="promo-figma-img">
           @endif
       </div>

       <!-- Layer 3: Top Content Area (Badge, Title, Tagline, CTA Button) -->
       <div class="promo-figma-header">
           <span class="promo-figma-badge-pill">
               <i class="fa-solid fa-sparkles"></i> {{ $promoSub }}
           </span>
           <h2 class="promo-figma-title">{{ $promoTitle }}</h2>
           <p class="promo-figma-tagline">Aneka Pastry & Cake Pilihan Terbaik</p>
           <a href="{{ isset($popupBanner) && $popupBanner && $popupBanner->link_url ? $popupBanner->link_url : route('shop') }}" class="promo-figma-btn">
               Shop Now <i class="fa-solid fa-arrow-right"></i>
           </a>
       </div>

       <!-- Layer 3: Floating Tag at Bottom -->
       <div class="promo-floating-tag">
           <i class="fa-regular fa-clock"></i> Limited Time Offer
       </div>
   </div>
   ```

---

### B. `public/css/home.css` & `resources/css/pelanggan_pages.css` & `resources/css/pelanggan_custom.css`

Menghapus aturan lama (`max-height: 200px`, `object-fit: contain`, dan `background: radial-gradient(130% 90% ... #f4ede3)`) serta menyelaraskan aturan dengan cover penuh, `position: absolute; inset: 0`, dan `radial-gradient` spotlight putih.

---

### C. `resources/views/Admin/kelola_carousel/banners.blade.php`

Menyelaraskan pratinjau banner promo posisi `popup_promo` di dasbor admin agar menggunakan *radial spotlight gradient* dan penempatan gambar cover 100%:
```blade
@elseif($b->posisi_banner === 'popup_promo')
    <div class="p-3 text-center d-flex flex-column align-items-center justify-content-between position-relative overflow-hidden"
        style="border-radius: 18px; min-height: 250px; background: #ffffff; border: 1.5px solid #dbe8dd;">
        <!-- Radial Spotlight Gradient Overlay -->
        <div style="position: absolute; inset: 0; background: radial-gradient(ellipse 150% 115% at 50% 15%, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.92) 34%, rgba(255, 255, 255, 0.65) 60%, rgba(255, 255, 255, 0.15) 82%, rgba(255, 255, 255, 0) 100%); z-index: 2; pointer-events: none;"></div>
        <div class="d-flex flex-column align-items-center w-100" style="position: relative; z-index: 3;">
            <span class="badge mb-1 text-uppercase fw-bold" style="background: rgba(255, 138, 0, 0.14); color: #ff8a00; border: 1px solid rgba(255, 138, 0, 0.32); border-radius: 20px; font-size: 10px; letter-spacing: 1.4px; padding: 4px 10px;">
                <i class="mdi mdi-creation me-1"></i> {{ $b->subjudul ?? 'SUMMER SALE' }}
            </span>
            <h3 class="fw-bold mb-1" style="color: #00b207; font-size: 26px; letter-spacing: -0.5px;">{{ $b->judul_banner }}</h3>
            <span class="btn btn-sm text-white fw-bold rounded-pill px-3 shadow-sm mb-1" style="background: linear-gradient(135deg, #00b207, #009906); font-size: 11.5px;">
                Shop Now <i class="mdi mdi-arrow-right ms-1"></i>
            </span>
        </div>
        <!-- Full-Bleed Image Wrap -->
        <div class="w-100 position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden;">
            <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="{{ $b->judul_banner }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center bottom;">
        </div>
    </div>
```

---

## 4. Hasil Kompilasi & Verifikasi Nyata (Browser Smoke Test)

1. **Build Asset Vite**:
   - `npm run build` dijalankan sukses (`built in 660ms`).
   - Berkas `public/build/assets/pelanggan_pages-Cu2PqDNy.css` (84.15 kB) terbarui.
2. **Pembersihan Cache Blade**:
   - `php artisan view:clear` dijalankan sukses.
3. **Pemeriksaan Computed Styles di Browser**:
   - `.promo-banner-figma`: `height: 420px; width: 342.6px;`
   - `.promo-figma-image-wrap`: `position: absolute; width: 340.6px; height: 418px; z-index: 1;`
   - `.promo-figma-img`: `position: absolute; width: 340.6px; height: 418px; object-fit: cover;` (**100% Full Bleed Edge-to-Edge**)
   - `.promo-figma-header`: `position: relative; z-index: 3;` (**Di atas spotlight**)
   - `.promo-floating-tag`: `position: absolute; bottom: 16px; z-index: 3;`
4. **Uji Responsivitas**:
   - **Desktop (1463px)**: 4 kolom sejajar, tinggi serasi dengan kolom `Hot Deals`, `Best Seller`, dan `Top Rated`.
   - **Tablet (768px)**: Lebar banner 738px, tinggi stabil 420px, gambar memenuhi kartu secara proporsional.
   - **Mobile (390px)**: Lebar banner 360px, tinggi stabil 420px, teks dan tombol tersorot sempurna di tengah.

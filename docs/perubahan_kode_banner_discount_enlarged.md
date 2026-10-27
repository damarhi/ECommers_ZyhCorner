# Dokumentasi Perubahan Kode: Pelebaran Ukuran & Peningkatan Visual Banner Diskon

## 1. Ringkasan Perubahan
Berdasarkan arahan desain **/skill:frontend-design-direction** untuk membuat banner promosi diskon pada kolom ke-4 widget produk beranda menjadi lebih lebar (+24% hingga +30%) dan memiliki daya tarik visual yang tinggi (*high conversion & appetizing*), perubahan berikut telah diterapkan:

1. **Pelebaran Ukuran Kolom Banner**:
   - Struktur grid `.four-cols` diubah dari pembagian sama rata `repeat(4, 1fr)` menjadi **`repeat(3, minmax(0, 1fr)) minmax(0, 1.35fr)`**.
   - Kolom banner bertambah lebar dari ~277px menjadi **~345px**, memberikan ruang napas (*breathing room*) yang lebih lapang tanpa mengorbankan keterbacaan 3 kolom produk di sampingnya (~255px).
2. **Peningkatan Estetika Visual Banner**:
   - **Latar Belakang Berkedalaman**: Menggunakan gradien halus bernuansa krim roti artisanal dan pendaran hijau sangat lembut:
     `radial-gradient(130% 90% at 50% 0%, #ffffff 0%, #fbf9f5 55%, #f4ede3 100%)` dengan border halus `#dbe8dd` dan sudut lengkung `border-radius: 20px`.
   - **Badge Micro-Pill Oranye**: Teks diganti menjadi pill badge elegan dengan ikon sparkle:
     `<span class="promo-figma-badge-pill"><i class="fa-solid fa-sparkles"></i> SUMMER SALE</span>`
   - **Tipografi Diskon Bold 44px**: Ukuran teks diskon dinaikkan menjadi `44px` font-weight `900` dengan warna `#00b207` dan efek pendaran halus.
   - **Sub-tagline Menggugah Selera**: Menambahkan keterangan mikro `"Aneka Pastry & Cake Pilihan Terbaik"`.
   - **Tombol CTA Interaktif**: Menggunakan gradien hijau cerah, bayangan berpendar (*glow shadow*), dan mikro-interaksi saat kursor melayang (`translateY(-2px)` dan panah bergeser `translateX(5px)`).
   - **Soft Fade Masking & Floating Glass Badge**:
     - Menerapkan `-webkit-mask-image: linear-gradient(to top, rgba(0,0,0,1) 70%, rgba(0,0,0,0) 100%)` sehingga bagian atas foto produk tidak terpotong kaku, melainkan memudar menyatu secara organik dengan warna kartu.
     - Menambahkan lencana melayang bernuansa kaca buram (*frosted glass*):
       `<div class="promo-floating-tag"><i class="fa-regular fa-clock"></i> Limited Time Offer</div>`

---

## 2. File yang Diubah

| No | File | Deskripsi Perubahan |
|---|---|---|
| 1 | `resources/views/Pelanggan/home.blade.php` | - Mengubah `.four-cols` ke rasio `repeat(3, minmax(0, 1fr)) minmax(0, 1.35fr)`.<br>- Memperbarui CSS `.promo-banner-figma` (gradien, mask, pill, floating tag).<br>- Menambahkan elemen HTML baru: badge-pill, tagline, dan floating tag. |
| 2 | `public/css/home.css` | - Menambahkan rule `.four-cols` rasio `1.35fr`.<br>- Menyelaraskan seluruh selector `.promo-banner-figma` dan komponen pendukungnya. |
| 3 | `resources/css/pelanggan_pages.css` | - Menyelaraskan `.four-cols` dan `.promo-banner-figma` pada stylesheet Vite. |
| 4 | `resources/css/pelanggan_custom.css` | - Menyelaraskan `.four-cols` dan `.promo-banner-figma` pada stylesheet custom. |
| 5 | `resources/views/Admin/kelola_carousel/banners.blade.php` | - Menyesuaikan kartu preview `popup_promo` di panel admin dengan tema krim gradien, mask fade, dan badge oranye baru. |
| 6 | `public/build/assets/pelanggan_pages-*.css` | - Dikompilasi ulang dengan `npm run build`. |

---

## 3. Rincian Kode yang Diubah

### A. Grid Pelebaran Kolom (`.four-cols`)
```css
/* resources/views/Pelanggan/home.blade.php & public/css/home.css */
.four-cols {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr)) minmax(0, 1.35fr) !important;
    gap: 22px !important;
    margin-bottom: 60px !important;
    align-items: stretch !important;
}
```

### B. Styling CSS Banner yang Ditingkatkan
```css
.promo-banner-figma,
.promo-banner {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    height: 100%;
    min-height: 430px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    text-align: center;
    padding: 32px 22px 0 22px;
    background: radial-gradient(130% 90% at 50% 0%, #ffffff 0%, #fbf9f5 55%, #f4ede3 100%);
    border: 1.5px solid #dbe8dd;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 4px rgba(0, 178, 7, 0.06);
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    box-sizing: border-box;
}

.promo-banner-figma:hover,
.promo-banner:hover {
    transform: translateY(-4px);
    border-color: #b9dac0;
    box-shadow: 0 16px 36px rgba(0, 178, 7, 0.12), 0 4px 12px rgba(0, 0, 0, 0.04);
}

.promo-figma-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    z-index: 2;
}

.promo-figma-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 138, 0, 0.12);
    color: #ff8a00;
    border: 1px solid rgba(255, 138, 0, 0.28);
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    margin-bottom: 8px;
    box-shadow: 0 2px 6px rgba(255, 138, 0, 0.08);
}

.promo-figma-title {
    font-size: 44px;
    font-weight: 900;
    color: #00b207;
    line-height: 1.05;
    margin-bottom: 6px;
    letter-spacing: -1px;
    text-shadow: 0 2px 8px rgba(0, 178, 7, 0.10);
}

.promo-figma-tagline {
    font-size: 12.5px;
    color: #666666;
    font-weight: 500;
    margin-bottom: 16px;
    line-height: 1.35;
    max-width: 260px;
}

.promo-figma-btn,
.promo-btn {
    background: linear-gradient(135deg, #00b207 0%, #009906 100%);
    color: #ffffff !important;
    font-size: 14px;
    font-weight: 700;
    padding: 11px 28px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 6px 18px rgba(0, 178, 7, 0.32);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
    border: none;
}

.promo-figma-btn:hover,
.promo-btn:hover {
    background: linear-gradient(135deg, #009e06 0%, #008505 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(0, 178, 7, 0.42);
    color: #ffffff !important;
}

.promo-figma-btn:hover i,
.promo-btn:hover i {
    transform: translateX(5px);
}

.promo-figma-image-wrap {
    position: relative;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    margin-top: 14px;
    overflow: hidden;
    flex: 1;
    min-height: 180px;
    -webkit-mask-image: linear-gradient(to top, rgba(0,0,0,1) 70%, rgba(0,0,0,0) 100%);
    mask-image: linear-gradient(to top, rgba(0,0,0,1) 70%, rgba(0,0,0,0) 100%);
}

.promo-floating-tag {
    position: absolute;
    top: 6px;
    z-index: 3;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(0, 178, 7, 0.22);
    color: #00b207;
    font-size: 11px;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    pointer-events: none;
}

.promo-figma-img {
    max-height: 200px;
    width: 100%;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.08));
    transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
}

.promo-banner-figma:hover .promo-figma-img,
.promo-banner:hover .promo-figma-img {
    transform: scale(1.06) translateY(-4px);
}
```

### C. Markup HTML Blade Kolom ke-4
```blade
<!-- 4. Promotional Banner (Ecobazar Figma Replica - Enlarged & Enhanced) -->
<div>
    @php
        $promoSub = 'SUMMER SALE';
        $promoTitle = '75% OFF';
        if (isset($popupBanner) && $popupBanner) {
            if (!empty($popupBanner->judul_banner)) {
                if (preg_match('/(.*?)\s*(\d+%\s*OFF)/i', $popupBanner->judul_banner, $matches)) {
                    $promoSub = strtoupper(trim($matches[1])) ?: 'SUMMER SALE';
                    $promoTitle = strtoupper(trim($matches[2]));
                } elseif (preg_match('/(\d+%\s*OFF)/i', $popupBanner->judul_banner, $matches)) {
                    $promoTitle = strtoupper(trim($matches[1]));
                    $promoSub = strtoupper($popupBanner->subjudul ?? 'SUMMER SALE');
                } else {
                    $promoTitle = $popupBanner->judul_banner;
                    $promoSub = strtoupper($popupBanner->subjudul ?? 'SPECIAL PROMO');
                }
            } elseif (!empty($popupBanner->subjudul)) {
                $promoSub = strtoupper($popupBanner->subjudul);
            }
        }
    @endphp
    <div class="promo-banner-figma">
        <!-- Top Content Area: Badge, Title, Tagline, CTA Button -->
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

        <!-- Bottom Product Imagery Area -->
        <div class="promo-figma-image-wrap">
            <div class="promo-floating-tag">
                <i class="fa-regular fa-clock"></i> Limited Time Offer
            </div>
            @if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner))
                <img src="{{ asset('storage/' . $popupBanner->gambar_banner) }}" alt="{{ $popupBanner->judul_banner ?? 'Summer Sale' }}" class="promo-figma-img">
            @else
                <img src="{{ asset('storage/banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png') }}" alt="Summer Sale" class="promo-figma-img">
            @endif
        </div>
    </div>
</div>
```

---

## 4. Hasil Verifikasi
1. **Kompilasi Aset Vite**: `npm run build` sukses dalam 469ms (`pelanggan_pages-B3YgXddX.css`).
2. **Pembersihan Cache Tampilan**: `php artisan view:clear` sukses.
3. **Uji Render HTTP**: Rute `/` berstatus `200 OK` dengan elemen DOM terverifikasi:
   - Grid `.four-cols` menggunakan proporsi `repeat(3, minmax(0, 1fr)) minmax(0, 1.35fr)`.
   - Lencana pill `<span class="promo-figma-badge-pill">` aktif.
   - Angka diskon 44px `<h2 class="promo-figma-title">75% OFF</h2>` aktif.
   - Tagline `<p class="promo-figma-tagline">` aktif.
   - Tombol CTA `<a class="promo-figma-btn">` aktif.
   - Floating glass tag `<div class="promo-floating-tag">` aktif.
   - Soft fade masking terpasang pada pembungkus gambar foto produk.

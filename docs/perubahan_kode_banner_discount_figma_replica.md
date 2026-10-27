# Dokumentasi Perubahan Kode: Redesain Banner Diskon Menjadi Replika 1:1 Figma Ecobazar

## 1. Latar Belakang & Masalah
Sebelumnya, banner promosi diskon pada kolom ke-4 widget produk beranda (`Hot Deals`, `Best Seller`, `Top Rated`, `Banner Diskon`) memiliki beberapa masalah visual jika dibandingkan dengan desain referensi **Figma Ecobazar (Image #1)**:
- **Latar Belakang Gelap / Muram**: Menggunakan gradien cokelat pekat (`linear-gradient(160deg, #26140c 0%, #120a06 100%)`) yang terkesan kotor dan tidak selaras dengan 3 kolom produk berlatar putih bersih di sampingnya.
- **Hierarki & Urutan Elemen Terbalik**: Pada implementasi lama (Image #2), elemen menumpuk dari atas ke bawah: lencana promo panjang &rarr; font script oranye &rarr; diskon hijau &rarr; teks diskon duplikat &rarr; paragraf panjang &rarr; foto kotak terisolasi &rarr; tombol di paling dasar.
- **Desain Figma Ecobazar Asli (Image #1)** menerapkan urutan yang jauh lebih bersih, modern, dan minimalis:
  1. **Atas**: Tipografi terfokus (`SUMMER SALE` oranye &rarr; `75% OFF` hijau bold).
  2. **Tengah-Atas**: Tombol CTA hijau pill (`Shop Now →`) tepat di bawah angka diskon.
  3. **Bawah**: Foto produk artisanal/bakery mengisi separuh bawah kartu secara natural hingga menyentuh dasar kartu.
  4. **Latar Belakang**: Off-white lembut (`#fbfaf9`), border halus `#e2eae3`, dan border-radius `16px`.

---

## 2. File yang Diubah

| No | Lokasi File | Cakupan Perubahan |
|---|---|---|
| 1 | `resources/views/Pelanggan/home.blade.php` | Mengganti token styling CSS `.promo-banner-figma` & refactor hierarki markup HTML banner kolom ke-4. |
| 2 | `public/css/home.css` | Menyelaraskan selector `.promo-banner-figma` dan `.promo-banner` dengan spesifikasi Figma. |
| 3 | `resources/css/pelanggan_pages.css` | Memperbarui style banner lama yang bergradien gelap di stylesheet Vite. |
| 4 | `resources/css/pelanggan_custom.css` | Membersihkan override lama bergradien gelap. |
| 5 | `resources/views/Admin/kelola_carousel/banners.blade.php` | Menyesuaikan kartu preview `popup_promo` di panel Admin agar selaras dengan tampilan Figma. |
| 6 | `public/build/assets/pelanggan_pages-*.css` | Dikompilasi ulang menggunakan `npm run build`. |

---

## 3. Rincian Perubahan Kode

### A. `resources/views/Pelanggan/home.blade.php`

#### 1. Perubahan CSS (Baris ~436 - 541):
```css
/* ==========================================================================
   Ecobazar Figma Replica: Promotional / Discount Banner (4th Column)
   ========================================================================== */
.promo-banner-figma,
.promo-banner {
    position: relative;
    border-radius: 16px;
    overflow: hidden;
    height: 100%;
    min-height: 420px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    align-items: center;
    text-align: center;
    padding: 34px 22px 0 22px;
    background: #fbfaf9;
    border: 1px solid #e2eae3;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.02);
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    box-sizing: border-box;
}

.promo-banner-figma:hover,
.promo-banner:hover {
    transform: translateY(-4px);
    border-color: #cce2d2;
    box-shadow: 0 14px 30px rgba(0, 178, 7, 0.09);
}

.promo-figma-header {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    z-index: 2;
}

.promo-figma-badge {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.6px;
    color: #ff8a00;
    margin-bottom: 6px;
    display: block;
}

.promo-figma-title {
    font-size: 38px;
    font-weight: 800;
    color: #00b207;
    line-height: 1.1;
    margin-bottom: 18px;
    letter-spacing: -0.5px;
    text-shadow: none;
}

.promo-figma-btn,
.promo-btn {
    background-color: #00b207;
    color: #ffffff !important;
    font-size: 13.5px;
    font-weight: 600;
    padding: 10px 26px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(0, 178, 7, 0.25);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}

.promo-figma-btn:hover,
.promo-btn:hover {
    background-color: #009906;
    transform: translateY(-2px);
    box-shadow: 0 8px 22px rgba(0, 178, 7, 0.35);
    color: #ffffff !important;
}

.promo-figma-btn i,
.promo-btn i {
    transition: transform 0.25s ease;
}

.promo-figma-btn:hover i,
.promo-btn:hover i {
    transform: translateX(4px);
}

.promo-figma-image-wrap {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: flex-end;
    margin-top: 15px;
    overflow: hidden;
    position: relative;
    flex: 1;
}

.promo-figma-img {
    max-height: 190px;
    width: 100%;
    object-fit: contain;
    object-position: bottom center;
    filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.08));
    transition: transform 0.4s ease;
}

.promo-banner-figma:hover .promo-figma-img,
.promo-banner:hover .promo-figma-img {
    transform: scale(1.05) translateY(-3px);
}
```

#### 2. Perubahan Markup HTML Blade (Baris ~1075 - 1115):
```blade
<!-- 4. Promotional Banner (Ecobazar Figma Replica) -->
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
        <!-- Top Content Area: Badge, Title, CTA Button -->
        <div class="promo-figma-header">
            <span class="promo-figma-badge">{{ $promoSub }}</span>
            <h2 class="promo-figma-title">{{ $promoTitle }}</h2>
            <a href="{{ isset($popupBanner) && $popupBanner && $popupBanner->link_url ? $popupBanner->link_url : route('shop') }}" class="promo-figma-btn">
                Shop Now <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <!-- Bottom Product Imagery Area -->
        <div class="promo-figma-image-wrap">
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

### B. `resources/views/Admin/kelola_carousel/banners.blade.php`

Menyesuaikan kartu preview posisi `popup_promo` agar mencerminkan latar off-white `#fbfaf9` dan susunan teks yang sama persis:
```blade
@elseif($b->posisi_banner === 'popup_promo')
    <div class="p-3 text-center d-flex flex-column align-items-center justify-content-between position-relative overflow-hidden"
        style="border-radius: 16px; min-height: 240px; background: #fbfaf9; border: 1px solid #e2eae3;">
        <div class="d-flex flex-column align-items-center w-100 z-index-2">
            <span class="text-uppercase fw-bold mb-1" style="color: #ff8a00; font-size: 11px; letter-spacing: 1.4px;">
                {{ $b->subjudul ?? 'SUMMER SALE' }}
            </span>
            <h3 class="fw-bold mb-2" style="color: #00b207; font-size: 24px; letter-spacing: -0.5px;">{{ $b->judul_banner }}</h3>
            <span class="btn btn-sm text-white fw-bold rounded-pill px-3 shadow-sm" style="background: #00b207; font-size: 11.5px;">
                Shop Now <i class="mdi mdi-arrow-right ms-1"></i>
            </span>
        </div>
        <div class="w-100 d-flex justify-content-center align-items-end mt-2" style="max-height: 110px; overflow: hidden;">
            <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="{{ $b->judul_banner }}" style="max-height: 100px; width: 100%; object-fit: contain; object-position: bottom center;">
        </div>
    </div>
@endif
```

---

## 4. Hasil Verifikasi & Uji Render

1. **Vite Build**: Berhasil dijalankan dengan `npm run build` dalam 493ms, menghasilkan bundle terkompilasi `pelanggan_pages-Bx5TL2sx.css`.
2. **Laravel View Cache**: Dibersihkan menggunakan `php artisan view:clear`.
3. **HTTP 200 & Rendered DOM Check**:
   - Status: `HTTP 200 OK`
   - Output HTML:
     ```html
     <div class="promo-banner-figma">
         <!-- Top Content Area: Badge, Title, CTA Button -->
         <div class="promo-figma-header">
             <span class="promo-figma-badge">SUMMER SALE</span>
             <h2 class="promo-figma-title">75% OFF</h2>
             <a href="/shop" class="promo-figma-btn">
                 Shop Now <i class="fa-solid fa-arrow-right"></i>
             </a>
         </div>

         <!-- Bottom Product Imagery Area -->
         <div class="promo-figma-image-wrap">
             <img src="http://localhost/storage/banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png" alt="Summer Sale 75% OFF" class="promo-figma-img">
         </div>
     </div>
     ```
4. **Kesesuaian dengan Referensi**:
   - Latar belakang cokelat pekat kusam sepenuhnya dihilangkan.
   - Urutan teks dan tombol CTA sekarang berada di bagian atas.
   - Gambar produk bakery/pastry tersaji di area bawah banner dan menyatu secara mulus dengan layout 4 kolom halaman utama.

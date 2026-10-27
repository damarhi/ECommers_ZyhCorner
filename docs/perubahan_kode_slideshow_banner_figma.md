# Dokumentasi Perubahan Kode: Unified Hero Banner Slideshow & Panah Minimalis Semi-Transparan

## 1. Ringkasan Perubahan

Berdasarkan klarifikasi pengguna, seluruh banner promosi aktif (**Hero Utama**, **Discount Promo**, **Shop Header**, dan **Custom Header**) diintegrasikan langsung ke dalam **satu Hero Banner Slideshow (Carousel)** di bagian atas halaman beranda (`/`). Slideshow berputar secara otomatis dan berurutan:
1. **Slide 1 — Hero Utama (`home_hero`)**: *"Delicious & Fresh Cakes and Desserts"* &rarr; CTA `Shop Now` (`/shop`).
2. **Slide 2 — Discount Promo (`popup_promo`)**: *"Summer Sale 75% OFF"* &rarr; CTA `Klaim Promo` (`/shop`).
3. **Slide 3 — Shop Header (`shop_header`)**: *"Sale of the Month - Potongan hingga 50%"* &rarr; CTA `Belanja Sekarang` (`/shop`).
4. **Slide 4 — Custom Header (`custom_header`)**: *"Kue Kustom Spesial Acara Anda"* &rarr; CTA `Pesan Sekarang` (`/custom-products`).

Selain itu, navigasi panah kiri/kanan diperbarui dengan gaya **minimalis semi-transparan (*frosted glass*)** agar tidak mengganggu visual produk dan menyatu harmonis dengan tema Ecobazar Figma (`#edf2ee`). Seksi grid statis 3-kolom di bawah bilah fitur yang sebelumnya redundant telah dihapus.

---

## 2. File-File yang Diubah

### A. Backend Controller: `app/Http/Controllers/Pelanggan/PelangganController.php`
- **Tujuan**: Mengambil seluruh banner berstatus `Aktif` dari 4 posisi (`home_hero`, `popup_promo`, `shop_header`, `custom_header`) dan mengurutkannya secara terstruktur menggunakan SQL `CASE`.
- **Kode**:
```php
// Unified Hero Slideshow: home_hero -> popup_promo (discount) -> shop_header -> custom_header
$heroBanners = Banner::whereIn('posisi_banner', ['home_hero', 'popup_promo', 'shop_header', 'custom_header'])
    ->where('status', 'Aktif')
    ->orderByRaw("CASE 
        WHEN posisi_banner = 'home_hero' THEN 1 
        WHEN posisi_banner = 'popup_promo' THEN 2 
        WHEN posisi_banner = 'shop_header' THEN 3 
        WHEN posisi_banner = 'custom_header' THEN 4 
        ELSE 5 END")
    ->orderBy('urutan', 'asc')
    ->get();

$promoBanners = Banner::where('posisi_banner', 'popup_promo')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
$popupBanner = $promoBanners->first();

return view('Pelanggan.home', compact(
    'featuredProducts',
    'hotDeals',
    'bestSellers',
    'topRated',
    'categories',
    'newestProducts',
    'heroBanners',
    'promoBanners',
    'popupBanner'
));
```

---

### B. Stylesheet: `public/css/home.css`
- **Tujuan**: Menerapkan gaya panah minimalis semi-transparan dengan efek *frosted glass* (`backdrop-filter: blur(10px)`).
- **Kode**:
```css
/* Minimalist Semi-Transparent Frosted Glass Slider Arrows */
.hero-slider-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.65);
    color: rgba(26, 26, 26, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 10;
    opacity: 0.65;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.hero-slider-wrapper:hover .hero-slider-arrow {
    opacity: 0.92;
}

.hero-slider-arrow:hover {
    background: rgba(255, 255, 255, 0.90);
    color: #00b207;
    border-color: rgba(0, 178, 7, 0.4);
    opacity: 1 !important;
    transform: translateY(-50%) scale(1.08);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.hero-slider-prev {
    left: 20px;
}

.hero-slider-next {
    right: 20px;
}
```

---

### C. Blade Template: `resources/views/Pelanggan/home.blade.php`
- **Tujuan**:
  1. Menambahkan CSS panah *frosted glass* di blok `<style>` lokal halaman.
  2. Mengadaptasi isi tiap slide (`@forelse($heroBanners as $index => $banner)`) secara dinamis sesuai posisi banner (`posisi_banner`).
  3. Menghapus seksi grid `.multi-banner-section` yang redundant.
- **Logika Adaptif Slide**:
```blade
@forelse($heroBanners as $index => $banner)
    @php
        $pos = $banner->posisi_banner ?? 'home_hero';
        if ($pos === 'popup_promo') {
            $badgeIcon = 'fa-solid fa-fire text-danger';
            $badgeText = $banner->subjudul ?? 'Special Promo';
            $calloutPrefix = 'Limited Time:';
            $calloutHighlight = 'Diskon Spesial 75% OFF';
            $descText = 'Penawaran diskon spesial aneka kue & pastry lezat pilihan terbaik Zyh Corner dengan harga istimewa.';
            $btnText = 'Klaim Promo';
            $btnUrl = $banner->link_url ?? route('shop');
            $tagTop = '🔥 Hot Deal';
            $tagBottom = 'Promo Terbatas';
        } elseif ($pos === 'shop_header') {
            $badgeIcon = 'fa-solid fa-tag text-success';
            $badgeText = $banner->subjudul ?? 'Exclusive Pastry Collection';
            $calloutPrefix = 'Fresh Daily:';
            $calloutHighlight = 'Koleksi Menu Spesial';
            $descText = 'Jelajahi aneka roti lembut, viennoiserie, dan dessert istimewa khas Zyh Corner yang dipanggang fresh setiap hari.';
            $btnText = 'Belanja Sekarang';
            $btnUrl = $banner->link_url ?? route('shop');
            $tagTop = '⭐ Top Choice';
            $tagBottom = 'Fresh from Oven';
        } elseif ($pos === 'custom_header') {
            $badgeIcon = 'fa-solid fa-cake-candles text-warning';
            $badgeText = $banner->subjudul ?? 'Custom Cake Studio';
            $calloutPrefix = 'Personalized:';
            $calloutHighlight = 'Desain Kue Impian';
            $descText = 'Kreasikan desain, rasa, dan ukuran kue impian Anda untuk setiap momen perayaan spesial.';
            $btnText = 'Pesan Sekarang';
            $btnUrl = $banner->link_url ?? route('custom-products');
            $tagTop = '🎂 100% Custom';
            $tagBottom = 'Made to Order';
        } else {
            $badgeIcon = 'fa-solid fa-sparkles text-success';
            $badgeText = $banner->subjudul ?? 'Artisanal Bakery & Pastry';
            $calloutPrefix = 'Sale up to';
            $calloutHighlight = '30% OFF';
            $descText = 'Free shipping on all your order. Nikmati kelezatan aneka kue dan pastry berkualitas tinggi khas Zyh Corner dengan bahan premium pilihan.';
            $btnText = 'Shop Now';
            $btnUrl = $banner->link_url ?? route('shop');
            $tagTop = '⭐ 4.9/5';
            $tagBottom = '100% Fresh Daily';
        }
    @endphp
    <div class="hero-slide-item {{ $loop->first ? 'active' : '' }}" data-slide="{{ $index }}">
        <div class="hero-section">
            <div class="hero-text">
                <span class="hero-badge-pill"><i class="{{ $badgeIcon }}"></i> {{ $badgeText }}</span>
                <h1>{{ $banner->judul_banner }}</h1>
                <div class="hero-sale-callout">
                    <span>{{ $calloutPrefix }}</span> <span class="hero-sale-highlight">{{ $calloutHighlight }}</span>
                </div>
                <p class="desc">{{ $descText }}</p>
                <a href="{{ $btnUrl }}" class="hero-btn">{{ $btnText }} <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="hero-image">
                @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
                    <div class="hero-image-frame">
                        <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
                        <div class="hero-rating-tag">
                            {{ $tagTop }}
                        </div>
                        <div class="hero-fresh-tag">
                            <i class="fa-solid fa-certificate text-success"></i> {{ $tagBottom }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
...
@endforelse
```

---

## 3. Hasil Pengujian & Verifikasi

Pengujian dilakukan melalui direct application runner Laravel:
- **Status Respon HTTP**: `200 OK` (Ukuran DOM: ~125 KB, tanpa error atau peringatan variabel undefined).
- **Jumlah Slide Aktif**: `4 Slide`
  - Slide 0 (`home_hero`): *Delicious & Fresh Cakes and Desserts* (Callout: *Sale up to 30% OFF*, Tombol: *Shop Now* &rarr; `/shop`)
  - Slide 1 (`popup_promo`): *Summer Sale 75% OFF* (Callout: *Limited Time: Diskon Spesial 75% OFF*, Tombol: *Klaim Promo* &rarr; `/shop`)
  - Slide 2 (`shop_header`): *Sale of the Month - Potongan hingga 50%* (Callout: *Fresh Daily: Koleksi Menu Spesial*, Tombol: *Belanja Sekarang* &rarr; `/shop`)
  - Slide 3 (`custom_header`): *Kue Kustom Spesial Acara Anda* (Callout: *Personalized: Desain Kue Impian*, Tombol: *Pesan Sekarang* &rarr; `/custom-products`)
- **Indikator Titik (*Dots*)**: 4 dots terpasang dan sinkron dengan `data-slide-to`.
- **Panah Navigasi Minimalis**: `.hero-slider-arrow` dengan kelas `.hero-slider-prev` dan `.hero-slider-next` terpasang dengan efek *frosted glass* semi-transparan.
- **Pembersihan Grid Bawah**: `.multi-banner-section` berhasil dihilangkan dari DOM sehingga tidak ada konten kembar di halaman beranda.

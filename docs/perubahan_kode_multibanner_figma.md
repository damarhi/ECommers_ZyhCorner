# Dokumentasi Perubahan Kode: Redesain Banner Figma & Multi-Banner Triad (Shop, Custom, Discount)

Dokumentasi ini mencatat secara lengkap seluruh perubahan kode, analisis spesifikasi desain Figma (*Ecobazar Template*), penerapan prinsip **Frontend Design Direction**, implementasi komponen **Multi-Banner Triad (Shop, Custom Cake Studio, dan Discount Promo)**, sinkronisasi Admin Live Preview Modal, serta bukti verifikasi sistem di aplikasi **Zyh Corner**.

---

## 1. Analisis Spesifikasi Desain Figma (Reverse-Engineering)

Berdasarkan analisis visual dan ekstraksi nilai piksel dari tangkapan layar desain Figma (`figma_banner_source.png`):

| Elemen Desain | Spesifikasi Figma Asli | Implementasi di Zyh Corner |
|---|---|---|
| **Latar Belakang Hero** | Soft mint/neutral cream `#edf2ee` | `background: #edf2ee; border: 1px solid #e2eae3; border-radius: 24px;` |
| **Dimensi Hero** | Desktop: ~500px tinggi, padding: 50px 65px | `min-height: 480px; padding: 50px 65px;` (responsif di mobile: `padding: 35px 25px;`) |
| **Judul Utama (H1)** | 48px – 52px, Bold 700, deep charcoal `#1a1a1a`, line-height: 1.18 | `font-size: 48px; font-weight: 700; color: #1a1a1a; line-height: 1.18;` |
| **Callout Promo** | `"Sale up to "` + `"[30% OFF]"` (Aksen oranye amber) | `<div class="hero-sale-callout"><span>Sale up to</span> <span class="hero-sale-highlight">30% OFF</span></div>` (`#ff8a00`) |
| **Deskripsi** | 14.5px, neutral gray `#666666`, max-width 460px | `color: #666666; font-size: 14.5px; line-height: 1.6; max-width: 460px;` |
| **Tombol Aksi (CTA)** | Pill shape (`border-radius: 50px`), hijau `#00b207`, teks putih, panah ke kanan | `background: #00b207; color: #ffffff; border-radius: 50px; padding: 14px 34px; font-weight: 600; font-size: 14.5px;` |
| **Foto Produk Hero** | Tampil natural di sisi kanan dengan soft drop shadow | `max-width: 440px; height: 320px; object-fit: contain; filter: drop-shadow(0 15px 30px rgba(0,0,0,0.10));` |

---

## 2. Arsitektur Multi-Banner Triad (Shop, Custom, & Discount)

Untuk memenuhi kebutuhan menampilkan banner dari 3 pilar layanan utama dalam format multibanner:
1. **Shop Banner** (`shop_header`): Menampilkan promo pastry harian dan mengarahkan ke halaman katalog (`/shop`).
2. **Custom Produk Banner** (`custom_header`): Menampilkan layanan pembuatan kue kustom dan mengarahkan ke formulir pemesanan kue kustom (`/custom-products`).
3. **Discount Promo Banner** (`popup_promo`): Menampilkan diskon spesial / summer sale dan mengarahkan ke penawaran terbaik.

Bagian ini disematkan tepat di antara **Features Bar** dan **Featured Products** dengan tata letak 3 kolom sejajar (`col-12 col-md-4`) yang responsif, berlatar gambar produk dari storage lokal, dan diberi lapisan *dark roasted overlay* agar teks terbaca kontras tinggi.

---

## 3. Rincian Perubahan Kode Berdasarkan File

### A. Controller Pelanggan
**File**: `app/Http/Controllers/Pelanggan/PelangganController.php`

**Perubahan**:
Memperbarui metode `home()` untuk mengambil banner aktif dari ketiga posisi (`shop_header`, `custom_header`, `popup_promo`) dan menyalurkannya ke view via `compact()`.

```php
// app/Http/Controllers/Pelanggan/PelangganController.php

// Multi-banner Triad (Shop, Custom Products, and Discount Promo)
$shopBanner = Banner::where('posisi_banner', 'shop_header')->where('status', 'Aktif')->orderBy('urutan', 'asc')->first();
$customBanner = Banner::where('posisi_banner', 'custom_header')->where('status', 'Aktif')->orderBy('urutan', 'asc')->first();
$discountBanner = Banner::where('posisi_banner', 'popup_promo')->where('status', 'Aktif')->orderBy('urutan', 'asc')->first();
$multiBanners = collect([$shopBanner, $customBanner, $discountBanner])->filter();

return view('Pelanggan.home', compact(
    'featuredProducts',
    'hotDeals',
    'bestSellers',
    'topRated',
    'categories',
    'newestProducts',
    'heroBanners',
    'promoBanners',
    'popupBanner',
    'shopBanner',
    'customBanner',
    'discountBanner',
    'multiBanners'
));
```

---

### B. Halaman Depan Pelanggan (Homepage)
**File**: `resources/views/Pelanggan/home.blade.php`

**Perubahan CSS**:
1. Menyelaraskan `.hero-section` ke palet Figma Ecobazar (`#edf2ee`, `#1a1a1a`, `#00b207`).
2. Menambahkan kelas `.hero-sale-callout` dan `.hero-sale-highlight` untuk lencana diskon `"Sale up to 30% OFF"`.
3. Menambahkan gaya kartu `.multi-banner-section`, `.mb-card`, `.mb-badge`, `.mb-title`, `.mb-desc`, dan `.mb-btn`.

```css
/* Hero Section (Figma Ecobazar Redesign) */
.hero-section {
    background: #edf2ee;
    border: 1px solid #e2eae3;
    border-radius: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 50px 65px;
    margin-bottom: 35px;
    margin-top: 15px;
    min-height: 480px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
}
.hero-text { flex: 1.15; z-index: 2; max-width: 580px; }
.hero-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(0, 178, 7, 0.10);
    color: #008a05;
    border: 1px solid rgba(0, 178, 7, 0.25);
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 12.5px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-bottom: 16px;
}
.hero-text h1 { font-size: 48px; font-weight: 700; color: #1a1a1a; line-height: 1.18; margin-bottom: 14px; letter-spacing: -0.5px; }
.hero-sale-callout {
    font-size: 20px;
    font-weight: 600;
    color: #1a1a1a;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.hero-sale-highlight {
    color: #ff8a00;
    font-weight: 700;
}
.hero-text p.desc { color: #666666; font-size: 14.5px; margin-bottom: 28px; max-width: 460px; line-height: 1.6; }
.hero-btn { background: #00b207; color: #ffffff !important; border: none; padding: 14px 34px; border-radius: 50px; font-weight: 600; font-size: 14.5px; cursor: pointer; display: inline-flex; align-items: center; gap: 10px; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); text-decoration: none; box-shadow: 0 4px 14px rgba(0, 178, 7, 0.25); }
.hero-btn:hover { background: #009906; transform: translateY(-2px); box-shadow: 0 8px 22px rgba(0, 178, 7, 0.35); }
.hero-image-frame img { width: 100%; height: 320px; object-fit: contain; border-radius: 16px; display: block; filter: drop-shadow(0 15px 30px rgba(0, 0, 0, 0.10)); }

/* Multi Banner Triad Grid (Shop, Custom Product, Discount Promo) */
.multi-banner-section { margin-bottom: 48px; }
.mb-card {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    min-height: 250px;
    padding: 30px 26px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    background-size: cover;
    background-position: center;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.06);
    text-decoration: none;
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.08);
}
.mb-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(26, 13, 8, 0.84) 0%, rgba(26, 13, 8, 0.65) 60%, rgba(26, 13, 8, 0.38) 100%);
    z-index: 1;
    transition: background 0.35s ease;
}
.mb-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(26, 13, 8, 0.18);
    color: #ffffff;
}
.mb-badge-shop { background: rgba(0, 178, 7, 0.22); color: #4ade80; border: 1px solid rgba(74, 222, 128, 0.35); }
.mb-badge-custom { background: rgba(245, 158, 11, 0.22); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.35); }
.mb-badge-promo { background: rgba(239, 68, 68, 0.25); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.35); }
.mb-title { font-size: 21px; font-weight: 700; line-height: 1.28; margin-bottom: 8px; color: #ffffff; }
.mb-desc { font-size: 13px; color: rgba(255, 255, 255, 0.82); line-height: 1.45; margin-bottom: 18px; max-width: 280px; }
.mb-btn { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600; color: #ffffff; background: rgba(255, 255, 255, 0.16); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.35); padding: 8px 18px; border-radius: 30px; transition: all 0.25s ease; width: fit-content; }
.mb-card:hover .mb-btn { background: #00b207; border-color: #00b207; color: #ffffff; transform: translateX(3px); }
```

**Perubahan Markup Blade**:
Menyematkan grid multibanner 3 kolom di antara `.features-bar` dan `Featured Products`:

```blade
<!-- Multi Banner Triad Grid (Shop, Custom Products, Discount) -->
<div class="multi-banner-section scroll-reveal">
    <div class="row g-4">
        {{-- 1. Shop Banner --}}
        <div class="col-12 col-md-4">
            <a href="{{ isset($shopBanner) && $shopBanner && $shopBanner->link_url ? $shopBanner->link_url : route('shop') }}" class="mb-card h-100"
                style="@if(isset($shopBanner) && $shopBanner && $shopBanner->gambar_banner && Storage::disk('public')->exists($shopBanner->gambar_banner)) background-image: url('{{ asset('storage/' . $shopBanner->gambar_banner) }}'); @else background-color: #26140c; @endif">
                <div class="mb-content">
                    <span class="mb-badge mb-badge-shop"><i class="fa-solid fa-tag"></i> {{ isset($shopBanner) && $shopBanner && $shopBanner->subjudul ? $shopBanner->subjudul : 'Exclusive Pastry' }}</span>
                    <h3 class="mb-title">{{ isset($shopBanner) && $shopBanner && $shopBanner->judul_banner ? $shopBanner->judul_banner : 'Sale of the Month' }}</h3>
                    <p class="mb-desc">Koleksi roti & pastry fresh from the oven setiap hari untuk Anda.</p>
                </div>
                <div class="mb-content">
                    <span class="mb-btn">Belanja Sekarang <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
        </div>

        {{-- 2. Custom Product Banner --}}
        <div class="col-12 col-md-4">
            <a href="{{ isset($customBanner) && $customBanner && $customBanner->link_url ? $customBanner->link_url : route('custom-products') }}" class="mb-card h-100"
                style="@if(isset($customBanner) && $customBanner && $customBanner->gambar_banner && Storage::disk('public')->exists($customBanner->gambar_banner)) background-image: url('{{ asset('storage/' . $customBanner->gambar_banner) }}'); @else background-color: #21130d; @endif">
                <div class="mb-content">
                    <span class="mb-badge mb-badge-custom"><i class="fa-solid fa-cake-candles"></i> {{ isset($customBanner) && $customBanner && $customBanner->subjudul ? $customBanner->subjudul : 'Custom Cake Studio' }}</span>
                    <h3 class="mb-title">{{ isset($customBanner) && $customBanner && $customBanner->judul_banner ? $customBanner->judul_banner : 'Kue Kustom Spesial Acara' }}</h3>
                    <p class="mb-desc">Wujudkan desain kue impian untuk momen perayaan terbaik Anda.</p>
                </div>
                <div class="mb-content">
                    <span class="mb-btn">Pesan Kustom <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
        </div>

        {{-- 3. Discount Promo Banner --}}
        <div class="col-12 col-md-4">
            <a href="{{ isset($discountBanner) && $discountBanner && $discountBanner->link_url ? $discountBanner->link_url : route('shop') }}" class="mb-card h-100"
                style="@if(isset($discountBanner) && $discountBanner && $discountBanner->gambar_banner && Storage::disk('public')->exists($discountBanner->gambar_banner)) background-image: url('{{ asset('storage/' . $discountBanner->gambar_banner) }}'); @else background-color: #1a0d08; @endif">
                <div class="mb-content">
                    <span class="mb-badge mb-badge-promo"><i class="fa-solid fa-fire"></i> {{ isset($discountBanner) && $discountBanner && $discountBanner->subjudul ? $discountBanner->subjudul : 'Special Promo' }}</span>
                    <h3 class="mb-title">{{ isset($discountBanner) && $discountBanner && $discountBanner->judul_banner ? $discountBanner->judul_banner : 'Summer Sale Up to 75%' }}</h3>
                    <p class="mb-desc">Penawaran diskon terbatas aneka varian menu pilihan lezat.</p>
                </div>
                <div class="mb-content">
                    <span class="mb-btn">Klaim Promo <i class="fa-solid fa-arrow-right"></i></span>
                </div>
            </a>
        </div>
    </div>
</div>
```

---

### C. Komponen Partial Multi Banner Grid
**File**: `resources/views/Pelanggan/partials/multi_banner_grid.blade.php`

**Perubahan**:
Memperbaiki struktur tag penutup HTML yang sebelumnya tidak berimbang pada kartu 2 kolom, 3 kolom, dan 4 kolom, membungkus konten dalam kontainer semantik (`banner-top-content` dan `banner-bottom-content`), serta menerapkan palet degradasi gelap khas butik bakery.

---

### D. Modal Admin Live Preview
**File**: `resources/views/Admin/kelola_carousel/banners.blade.php`

**Perubahan**:
Menyesuaikan pratinjau live `#previewModal{{ $b->id_banner }}` untuk posisi `home_hero` agar mengadopsi latar `#edf2ee`, teks `#1a1a1a`, baris promo `"Sale up to 30% OFF"`, dan tombol hijau `#00b207` secara 1:1 dengan front-end.

---

## 4. Hasil Verifikasi Sistem

1. **Pembersihan Cache View**:
   - Perintah: `php artisan view:clear`
   - Hasil: `Compiled views cleared successfully`.
2. **Pengujian Response HTTP & Struktur DOM Front-End**:
   - URL: `http://127.0.0.1:8000/`
   - Status: **200 OK**
   - Hasil pemeriksaan otomatis:
     - `has_hero`: **True**
     - `has_hero_bg (#edf2ee)`: **True**
     - `has_sale_callout`: **True**
     - `has_multi_banner`: **True**
     - `has_exclusive_pastry (Shop Banner)`: **True**
     - `has_custom_studio (Custom Cake Banner)`: **True**
     - `has_special_promo (Discount Banner)`: **True**
3. **Pengujian Kompilasi View Admin**:
   - Status: Berhasil dikompilasi tanpa exception Blade.
   - Marker `#edf2ee` pada pratinjau live `home_hero`: **Terkonfirmasi aktif (YES)**.

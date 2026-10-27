# Dokumentasi Perubahan Kode: Redesain Banner Halaman Shop & Custom Products

## 1. Ringkasan & Latar Belakang

Berdasarkan pemeriksaan visual terhadap dua halaman pelanggan Zyh Corner:
1. **Halaman Katalog Belanja / Shop** (`/shop` via `resources/views/Pelanggan/shop.blade.php`)
2. **Halaman Produk Kustom / Custom Products** (`/custom-products` via `resources/views/Pelanggan/custom_products.blade.php`)

Ditemukan beberapa kekurangan desain pada banner sebelumnya:
- **Overlay Muddy Brown**: Banner menggunakan gradasi linier cokelat tua pekat (`rgba(26, 13, 8, 0.92)` hingga `0.70`) yang menutupi lebih dari 55% area foto produk roti/kue, membuat visual makanan terlihat kusam, gelap, dan kurang menggugah selera (*unappetizing*).
- **Badge Kuning Kaku**: Menggunakan badge bawaan Bootstrap `<span class="badge bg-warning text-dark">` yang kontrasnya murahan dan tidak selaras dengan identitas butik pastry artisanal.
- **Keterbacaan Teks Rendah**: Teks deskripsi menggunakan `text-white-50` (`rgba(255,255,255,0.5)`) yang redup dan sulit dibaca di atas gradasi foto.
- **Ketiadaan Jarak Ritmis (Spacing)**: `margin-top: 0px`, menempel langsung di bawah breadcrumb sehingga antarmuka terasa sesak.
- **Tombol CTA Kurang Interaktif**: Tombol hijau tanpa bayangan pendar (*glow shadow*) atau transisi panah animasi saat kursor diarahkan (*hover*).
- **Multi-Banner Grid**: File `multi_banner_grid.blade.php` mewarisi styling linier cokelat pekat dan badge kuning kaku yang sama.

Perbaikan telah dieksekusi secara terpadu mengacu pada standar skill **`frontend-design-direction`** dengan konsep **Cinematic Gourmet Showcase**.

---

## 2. Arah Desain & Spesifikasi Baru (*Frontend Design Direction*)

- **Tone & Karakter**: *Refined Artisanal Pastry Boutique* — bersih, hangat, lezat, kontras tinggi, dan modern.
- **Dual-Tone Ambient Spotlight**: Latar belakang banner menggunakan lapisan pendar ganda (`radial-gradient` hijau emerald Zyh Corner di sisi kiri dan gradasi asimetris transparan ke sisi kanan). Teks terlindungi dengan kontras tajam di sisi kiri, sementara foto produk kue/roti di sisi kanan tampil jernih, tajam, dan memikat tanpa tertutup kabut gelap.
- **Glassmorphic Micro-Pill Badge**:
  - Halaman Shop: Emas hangat lembut (`background: rgba(255, 138, 0, 0.18); color: #ffab38; border: 1px solid rgba(255, 138, 0, 0.38)`).
  - Halaman Custom Products: Hijau mint segar (`background: rgba(0, 178, 7, 0.18); color: #3cd96b; border: 1px solid rgba(0, 178, 7, 0.38)`).
- **Tipografi Berbobot**: Judul `font-size: 34px` (desktop) / `24px` (mobile), `font-weight: 800`, `letter-spacing: -0.6px`, dan deskripsi menggunakan warna `#e2e8f0` (90% luminance) dengan `line-height: 1.6`.
- **Interactive Glowing CTA Button**: Tombol `.spb-btn` warna `#00b207` dengan pendaran bayangan `box-shadow: 0 6px 20px rgba(0, 178, 7, 0.32)`, elevasi saat di-hover (`translateY(-2px)`), dan panah beranimasi geser ke kanan (`translateX(4px)`).
- **Sistem Jarak Terukur (Spacing System)**:
  - Desktop: `margin-top: 24px`, `margin-bottom: 36px`, `border-radius: 20px`.
  - Mobile: `margin-top: 16px`, `margin-bottom: 24px`, `border-radius: 16px`.

---

## 3. Daftar Berkas yang Diubah

| No | Berkas | Peran / Perubahan |
| :--- | :--- | :--- |
| 1 | `resources/views/Pelanggan/shop.blade.php` | Mengganti inline muddy gradient dengan clean background image, mengadopsi `.spb-pill-badge badge-shop`, `.spb-title`, `.spb-desc`, dan `.spb-btn`. |
| 2 | `resources/views/Pelanggan/custom_products.blade.php` | Mengganti inline muddy gradient dengan clean background image, mengadopsi `.spb-pill-badge badge-custom`, `.spb-title`, `.spb-desc`, dan `.spb-btn`. |
| 3 | `resources/views/Pelanggan/partials/multi_banner_grid.blade.php` | Memodernisasi layout multi-banner (1, 2, 3, dan 4 kolom) menggunakan micro-pill glassmorphic, ambient spotlight backdrop, dan tombol interaktif. |
| 4 | `resources/css/pelanggan_pages.css` | Menambahkan aturan styling terpadu `.shop-promo-banner`, `.spb-pill-badge`, `.spb-btn`, `.multi-banner-container`, `single-banner-card`, `.banner-grid-card`, dan media query responsif. |
| 5 | `resources/css/pelanggan_custom.css` | Menyelaraskan aturan CSS agar identik dengan `pelanggan_pages.css` guna mencegah konflik cascade. |

---

## 4. Rincian Perubahan Kode (Before & After)

### 4.1. `resources/views/Pelanggan/shop.blade.php`

#### Sebelum:
```blade
<div class="shop-promo-banner" @if($shopBanner->gambar_banner && Storage::disk('public')->exists($shopBanner->gambar_banner)) style="background-image: linear-gradient(90deg, rgba(26, 13, 8, 0.92) 0%, rgba(26, 13, 8, 0.70) 55%, rgba(26, 13, 8, 0.20) 100%), url('{{ asset('storage/' . $shopBanner->gambar_banner) }}'); background-size: cover; background-position: center;" @endif>
    <div class="spb-content" style="max-width: 560px; z-index: 2;">
        <span class="badge bg-warning text-dark font-weight-bold px-3 py-1 mb-2 rounded-pill shadow-sm" style="font-size: 11.5px; letter-spacing: 0.8px;">
            <i class="fa-solid fa-tags me-1"></i> {{ $shopBanner->subjudul ?? 'Exclusive Pastry' }}
        </span>
        <h2 style="font-size: 34px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; margin-bottom: 8px;">{{ $shopBanner->judul_banner }}</h2>
        <p class="text-white-50 mb-3" style="font-size: 13.5px; line-height: 1.5;">Pilihan produk roti segar, kue ulang tahun premium, dan pastry berkualitas tinggi langsung dari oven.</p>
        @if($shopBanner->link_url)
            <a href="{{ $shopBanner->link_url }}" class="spb-btn text-decoration-none d-inline-flex align-items-center">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
        @else
            <button class="spb-btn">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></button>
        @endif
    </div>
</div>
```

#### Sesudah:
```blade
<div class="shop-promo-banner" @if($shopBanner->gambar_banner && Storage::disk('public')->exists($shopBanner->gambar_banner)) style="background-image: url('{{ asset('storage/' . $shopBanner->gambar_banner) }}'); background-size: cover; background-position: center right;" @endif>
    <div class="spb-content">
        <span class="spb-pill-badge badge-shop">
            <i class="fa-solid fa-tags"></i> {{ $shopBanner->subjudul ?? 'Exclusive Pastry' }}
        </span>
        <h2 class="spb-title">{{ $shopBanner->judul_banner }}</h2>
        <p class="spb-desc">Pilihan produk roti segar, kue ulang tahun premium, dan pastry berkualitas tinggi langsung dari oven.</p>
        @if($shopBanner->link_url)
            <a href="{{ $shopBanner->link_url }}" class="spb-btn">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
        @else
            <button class="spb-btn" type="button">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></button>
        @endif
    </div>
</div>
```

---

### 4.2. `resources/views/Pelanggan/custom_products.blade.php`

#### Sebelum:
```blade
<div class="shop-promo-banner" @if($customBanner->gambar_banner && Storage::disk('public')->exists($customBanner->gambar_banner)) style="background-image: linear-gradient(90deg, rgba(26, 13, 8, 0.92) 0%, rgba(26, 13, 8, 0.70) 55%, rgba(26, 13, 8, 0.20) 100%), url('{{ asset('storage/' . $customBanner->gambar_banner) }}'); background-size: cover; background-position: center;" @endif>
    <div class="spb-content" style="max-width: 560px; z-index: 2;">
        <span class="badge bg-warning text-dark font-weight-bold px-3 py-1 mb-2 rounded-pill shadow-sm" style="font-size: 11.5px; letter-spacing: 0.8px;">
            <i class="fa-solid fa-cake-candles me-1"></i> {{ $customBanner->subjudul ?? 'Custom Cake Studio' }}
        </span>
        <h2 style="font-size: 34px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px; margin-bottom: 8px;">{{ $customBanner->judul_banner }}</h2>
        <p class="text-white-50 mb-3" style="font-size: 13.5px; line-height: 1.5;">Wujudkan kue impian untuk perayaan spesial Anda. Bebas tentukan tema, desain, rasa, dan ukuran.</p>
        @if($customBanner->link_url)
            <a href="{{ $customBanner->link_url }}" class="spb-btn text-decoration-none d-inline-flex align-items-center">Pesan Sekarang <i class="fa-solid fa-arrow-right ms-2"></i></a>
        @else
            <button class="spb-btn">Pesan Sekarang <i class="fa-solid fa-arrow-right ms-2"></i></button>
        @endif
    </div>
</div>
```

#### Sesudah:
```blade
<div class="shop-promo-banner" @if($customBanner->gambar_banner && Storage::disk('public')->exists($customBanner->gambar_banner)) style="background-image: url('{{ asset('storage/' . $customBanner->gambar_banner) }}'); background-size: cover; background-position: center right;" @endif>
    <div class="spb-content">
        <span class="spb-pill-badge badge-custom">
            <i class="fa-solid fa-cake-candles"></i> {{ $customBanner->subjudul ?? 'Custom Cake Studio' }}
        </span>
        <h2 class="spb-title">{{ $customBanner->judul_banner }}</h2>
        <p class="spb-desc">Wujudkan kue impian untuk perayaan spesial Anda. Bebas tentukan tema, desain, rasa, dan ukuran.</p>
        @if($customBanner->link_url)
            <a href="{{ $customBanner->link_url }}" class="spb-btn">Pesan Sekarang <i class="fa-solid fa-arrow-right"></i></a>
        @else
            <button class="spb-btn" type="button">Pesan Sekarang <i class="fa-solid fa-arrow-right"></i></button>
        @endif
    </div>
</div>
```

---

### 4.3. `resources/views/Pelanggan/partials/multi_banner_grid.blade.php`

- Menghapus semua hardcoded inline style `linear-gradient(135deg, rgba(26, 13, 8, 0.84) ...)` dan `<span class="badge bg-warning text-dark ...">`.
- Mengganti layout kartu 1 kolom (`.single-banner-card`) dan multi-kolom (`.banner-grid-card`) agar menggunakan class `.spb-pill-badge badge-shop` serta tombol interaktif `.spb-btn`.
- Memberikan struktur visual seragam untuk tampilan 1, 2, 3, dan 4 banner grid.

---

### 4.4. `resources/css/pelanggan_pages.css` & `resources/css/pelanggan_custom.css`

```css
/* ==========================================================================
   Zyh Corner: Premium Banner Redesign for Shop & Custom Products
   ========================================================================== */
.shop-promo-banner {
    position: relative;
    border-radius: 20px;
    padding: 42px 48px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    margin-bottom: 36px;
    overflow: hidden;
    min-height: 250px;
    background-color: #0d1710;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12), 0 4px 16px rgba(0, 178, 7, 0.08);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.35s ease;
}

.shop-promo-banner:hover {
    transform: translateY(-2px);
    border-color: rgba(0, 178, 7, 0.35);
    box-shadow: 0 20px 42px rgba(0, 0, 0, 0.16), 0 8px 24px rgba(0, 178, 7, 0.14);
}

/* Dual-Tone Ambient Spotlight for Left Reading Protection & Crisp Product View on Right */
.shop-promo-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(circle at 12% 45%, rgba(0, 178, 7, 0.22) 0%, rgba(0, 178, 7, 0.05) 50%, transparent 75%),
        linear-gradient(98deg, rgba(12, 22, 15, 0.96) 0%, rgba(12, 22, 15, 0.90) 38%, rgba(12, 22, 15, 0.65) 60%, rgba(12, 22, 15, 0.12) 85%, rgba(12, 22, 15, 0.02) 100%);
    z-index: 1;
    pointer-events: none;
    transition: opacity 0.35s ease;
}

.shop-promo-banner .spb-content {
    position: relative;
    max-width: 580px;
    z-index: 2;
}

/* Glassmorphic Micro-Pill Badge */
.spb-pill-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 16px;
    border-radius: 50px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 14px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    transition: all 0.3s ease;
}

.spb-pill-badge.badge-shop {
    background: rgba(255, 138, 0, 0.18);
    color: #ffab38;
    border: 1px solid rgba(255, 138, 0, 0.38);
}

.spb-pill-badge.badge-custom {
    background: rgba(0, 178, 7, 0.18);
    color: #3cd96b;
    border: 1px solid rgba(0, 178, 7, 0.38);
}

.spb-pill-badge i {
    font-size: 11px;
}

/* Banner Title */
.shop-promo-banner h2,
.spb-content h2,
.spb-title {
    font-size: 34px;
    font-weight: 800;
    color: #ffffff;
    letter-spacing: -0.6px;
    line-height: 1.22;
    margin-bottom: 12px;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
}

/* Banner Subtitle / Description */
.shop-promo-banner p,
.spb-content p,
.spb-desc {
    color: #e2e8f0;
    font-size: 14.5px;
    line-height: 1.6;
    margin-bottom: 22px;
    font-weight: 400;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.25);
}

/* Interactive CTA Button */
.spb-btn {
    background-color: #00b207;
    color: #ffffff !important;
    border: none;
    padding: 13px 32px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 14.5px;
    letter-spacing: 0.2px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    box-shadow: 0 6px 20px rgba(0, 178, 7, 0.32);
}

.spb-btn:hover {
    background-color: #009906;
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(0, 178, 7, 0.45);
    color: #ffffff !important;
}

.spb-btn i {
    transition: transform 0.25s ease;
}

.spb-btn:hover i {
    transform: translateX(4px);
}

/* Multi-Banner Grid Polish */
.multi-banner-container {
    margin-top: 24px;
    margin-bottom: 36px;
}

.single-banner-card {
    border-radius: 20px !important;
    min-height: 250px !important;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.12), 0 4px 16px rgba(0, 178, 7, 0.08) !important;
    position: relative;
    overflow: hidden;
}

.single-banner-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: 
        radial-gradient(circle at 12% 45%, rgba(0, 178, 7, 0.22) 0%, rgba(0, 178, 7, 0.05) 50%, transparent 75%),
        linear-gradient(98deg, rgba(12, 22, 15, 0.96) 0%, rgba(12, 22, 15, 0.88) 40%, rgba(12, 22, 15, 0.55) 65%, rgba(12, 22, 15, 0.10) 90%);
    z-index: 1;
    pointer-events: none;
}

.banner-grid-card {
    border-radius: 18px !important;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08) !important;
    position: relative;
    overflow: hidden;
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.banner-grid-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(12, 22, 15, 0.94) 0%, rgba(12, 22, 15, 0.72) 58%, rgba(12, 22, 15, 0.20) 100%);
    z-index: 1;
    pointer-events: none;
}

.banner-grid-card:hover {
    transform: translateY(-4px) !important;
    border-color: rgba(0, 178, 7, 0.35);
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.14), 0 6px 20px rgba(0, 178, 7, 0.12) !important;
}

/* Media Query Responsif Mobile (max-width: 768px) */
@media (max-width: 768px) {
    .shop-promo-banner {
        flex-direction: column;
        align-items: flex-start;
        padding: 28px 24px;
        min-height: 200px;
        margin-top: 16px;
        margin-bottom: 24px;
        border-radius: 16px;
    }

    .shop-promo-banner::before {
        background: linear-gradient(180deg, rgba(12, 22, 15, 0.94) 0%, rgba(12, 22, 15, 0.82) 60%, rgba(12, 22, 15, 0.45) 100%);
    }

    .shop-promo-banner h2,
    .spb-content h2,
    .spb-title {
        font-size: 24px !important;
        margin-bottom: 8px;
    }

    .shop-promo-banner p,
    .spb-content p,
    .spb-desc {
        font-size: 13px !important;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .spb-pill-badge {
        font-size: 11px;
        padding: 5px 12px;
        margin-bottom: 10px;
    }

    .spb-btn {
        padding: 10px 24px;
        font-size: 13.5px;
    }

    .multi-banner-container {
        margin-top: 16px;
        margin-bottom: 24px;
    }
}
```

---

## 5. Hasil Verifikasi Rendered Headless Browser

Pengujian dilakukan langsung menggunakan Chromium headless pada viewport Desktop (1440×900) dan Mobile (390×844):

### A. Desktop (1440×900 px)

| Parameter | Halaman Shop (`/shop`) | Halaman Custom Products (`/custom-products`) | Status |
| :--- | :--- | :--- | :--- |
| **`marginTop`** | `24px` (ideal breathing room) | `24px` (ideal breathing room) | **PASS** |
| **`marginBottom`** | `36px` (proporsional ke toolbar) | `36px` (proporsional ke toolbar) | **PASS** |
| **`borderRadius`** | `20px` (modern soft corner) | `20px` (modern soft corner) | **PASS** |
| **Background Color** | `rgb(13, 23, 16)` | `rgb(13, 23, 16)` | **PASS** |
| **Badge Class** | `.spb-pill-badge.badge-shop` | `.spb-pill-badge.badge-custom` | **PASS** |
| **Badge Colors** | BG: `rgba(255, 138, 0, 0.18)`, Teks: `#ffab38` | BG: `rgba(0, 178, 7, 0.18)`, Teks: `#3cd96b` | **PASS** |
| **Heading Typography** | `34px`, font-weight: 800, color: `#ffffff` | `34px`, font-weight: 800, color: `#ffffff` | **PASS** |
| **Description Color** | `rgb(226, 232, 240)` (high contrast) | `rgb(226, 232, 240)` (high contrast) | **PASS** |
| **CTA Button Shadow** | `rgba(0, 178, 7, 0.32) 0px 6px 20px 0px` | `rgba(0, 178, 7, 0.32) 0px 6px 20px 0px` | **PASS** |

### B. Mobile (390×844 px)

| Parameter | Hasil Computed Style | Evaluasi Responsif |
| :--- | :--- | :--- |
| **Container Padding** | `28px 24px` | Nyaman di layar kecil tanpa terpotong |
| **`marginTop`** | `16px` | Rapi di bawah breadcrumb |
| **`marginBottom`** | `24px` | Ruang jeda proporsional ke filter |
| **Judul Heading** | `24px` | Skala proporsional tidak membentur batas layar |
| **Badge Subjudul** | `11px`, padding `5px 12px` | Micro-pill proporsional |
| **CTA Button** | `13.5px`, padding `10px 24px` | Tombol touch target mudah ditekan jari |

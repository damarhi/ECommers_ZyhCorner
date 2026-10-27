# Dokumentasi Perubahan Kode Redesain Banner & Perbaikan Animasi Scroll

Dokumentasi ini mencatat secara lengkap seluruh pembaruan desain, investigasi mendalam penyebab hilangnya animasi saat scroll, perbaikan logika animasi progresif, penerapan prinsip desain **Frontend Design Direction** pada seluruh komponen banner (Hero Slider, Middle Promo/Discount Banner, Header Banner Shop & Custom Products, dan Multi Banner Grid), pembersihan URL gambar eksternal (Unsplash), serta sinkronisasi modal **Admin Live Preview** di aplikasi **Zyh Corner**.

---

## 1. Analisis Masalah: Kenapa Animasi Saat Halaman Scroll Sempat Hilang?

### Akar Penyebab:
1. **Absennya Kelas Pengaktif Animasi (`js-animate`)**:
   - Di `resources/views/Pelanggan/home.blade.php`, aturan CSS untuk menyembunyikan elemen sebelum masuk viewport didefinisikan dengan selektor induk:
     ```css
     .js-animate .scroll-reveal:not(.is-visible) {
         opacity: 0;
         transform: translateY(24px) scale(0.98);
     }
     ```
   - Namun, pada file JavaScript (`public/js/home.js`), kode `document.body.classList.add('js-animate')` **tidak pernah dipanggil**.
   - Akibatnya, selektor `.js-animate` tidak pernah cocok (*match*). Elemen kartu produk tetap menggunakan aturan dasar `.scroll-reveal`:
     ```css
     .scroll-reveal {
         opacity: 1;
         transform: translateY(0);
     }
     ```
   - Karena sejak awal elemen sudah memiliki nilai `opacity: 1` dan `transform: translateY(0)`, ketika pengguna menggulir halaman (*scrolling*) dan `IntersectionObserver` menambahkan kelas `.is-visible`, **tidak ada perubahan visual yang terjadi** (tidak ada perpindahan dari transparan ke terlihat, atau dari bawah ke atas). Animasi scroll seolah-olah "hilang".

2. **Perbaikan Logika Progresif (*Progressive Enhancement*)**:
   - Di `public/js/home.js`, ditambahkan eksekusi `document.body.classList.add('js-animate')` segera setelah DOM siap.
   - Di `resources/views/Pelanggan/home.blade.php`, aturan animasi diperbarui:
     - Elemen di luar viewport akan di-*offset* ke bawah sebesar `32px` dengan `opacity: 0` dan `scale(0.97)` hanya jika JavaScript aktif.
     - Saat kartu masuk ke dalam viewport, `IntersectionObserver` menambahkan kelas `.is-visible`, memicu transisi halus berdurasi `0.65s cubic-bezier(0.16, 1, 0.3, 1)` dengan efek *staggered delay* (`--reveal-delay: index * 0.06s`).
     - Jika JavaScript dimatikan/gagal dimuat, fallback CSS tetap aman: elemen langsung terlihat (`opacity: 1`), mencegah masalah produk hilang.

---

## 2. Rencana & Arahan Desain (Frontend Design Direction)

Mengacu pada `/skill:frontend-design-direction`:
- **Domain & Brand Identity**: Artisanal Bakery & Pastry Boutique ("Zyh Corner") mengutamakan kesegaran harian, kehangatan oven, kemewahan bahan premium, dan kustomisasi kue ulang tahun.
- **Palet Warna**:
  - *Warm Dough & Butter Creams*: `#fdfbf7`, `#fbf6ef`, `#f4ece1`.
  - *Dark Cocoa & Roasted Crust*: `#21130d`, `#26140c`, `#150a05`.
  - *Artisanal Fresh Emerald*: `#00b207` (hover: `#009906`).
  - *Golden Caramel Embers*: `#f59e0b`, `#d97706`.
- **Hierarki & Detail Memorable**:
  1. **Hero Banner**:
     - Latar belakang gradasi artisanal hangat dengan aura radial ganda.
     - Wadah foto produk berbingkai kartu mewah (`border-radius: 24px`, bayangan ganda halus).
     - Dua lencana mengambang (*floating trust chips*): `⭐ 4.9/5 Rating` di kanan atas dan `100% Fresh Daily` di kiri bawah.
     - Tombol CTA gradien hijau zamrud dengan animasi micro-interaction panah geser saat disentuh kursor.
  2. **Shop Header Banner**:
     - Asymmetric gradient mesh 90° (`linear-gradient(90deg, rgba(26,13,8,0.92) 0%, rgba(26,13,8,0.70) 55%, rgba(26,13,8,0.20) 100%)`).
     - Lencana koleksi emas `🏷️ Exclusive Pastry` dan subteks penjelasan kualitas bahan langsung dari oven.
  3. **Custom Product Header Banner**:
     - Lencana studio kustom `🎂 Custom Cake Studio` dengan teks inspirasi personalisasi pesanan kue.
     - Tombol berdaya panggil kuat: `Pesan Sekarang`.
  4. **Middle Discount / Promo Banner**:
     - Latar belakang *roasted dark chocolate* berpadu pendaran emas di sudut kanan atas.
     - Lencana api emas `🔥 Special Promo`.
     - Teks diskon bertipografi tajam dengan tombol putih kontras tinggi (`.promo-btn`).
  5. **Admin Live Preview Modal**:
     - Sinkron 1:1 dengan tampilan pelanggan untuk seluruh posisi (`home_hero`, `shop_header`, `custom_header`, `popup_promo`).

---

## 3. Rincian Perubahan Kode Berdasarkan File

### A. Perbaikan Animasi Scroll & Logika JS
**File**: `public/js/home.js`
- Menambahkan aktivasi kelas `document.body.classList.add('js-animate')`.
- Mengatur ambang batas (*threshold*) `IntersectionObserver` ke `0.08` dan `rootMargin: '0px 0px -30px 0px'` agar animasi transisi kartu terpicu tepat saat kartu mulai memasuki bidang pandang pengguna.

```javascript
// public/js/home.js

const revealElements = document.querySelectorAll('.scroll-reveal, .safe-reveal');
if (revealElements.length > 0) {
    // Aktifkan kelas animasi hanya jika browser mendukung JS
    document.body.classList.add('js-animate');

    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.08,
            rootMargin: '0px 0px -30px 0px'
        });
        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        revealElements.forEach(el => el.classList.add('is-visible'));
    }
}
```

---

### B. Redesain Hero Banner & Animasi Kartu Produk
**File**: `resources/views/Pelanggan/home.blade.php`

- **CSS Animasi Scroll**:
```css
/* Scroll-Reveal Animations for Cards */
.scroll-reveal,
.safe-reveal {
    opacity: 1;
    transform: translateY(0);
    transition: opacity 0.65s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.65s cubic-bezier(0.16, 1, 0.3, 1);
    transition-delay: var(--reveal-delay, 0s);
    will-change: transform, opacity;
}

.js-animate .scroll-reveal:not(.is-visible),
.js-animate .safe-reveal:not(.is-visible) {
    opacity: 0;
    transform: translateY(32px) scale(0.97);
    pointer-events: none;
}

.scroll-reveal.is-visible,
.safe-reveal.is-visible {
    opacity: 1 !important;
    transform: translateY(0) scale(1) !important;
    pointer-events: auto;
}
```

- **CSS Hero Section**:
```css
.hero-section {
    background: radial-gradient(circle at 85% 30%, rgba(245, 158, 11, 0.08) 0%, transparent 60%),
                radial-gradient(circle at 15% 80%, rgba(0, 178, 7, 0.06) 0%, transparent 50%),
                linear-gradient(135deg, #fdfbf7 0%, #fbf6ef 50%, #f4ece1 100%);
    border: 1px solid rgba(226, 215, 201, 0.65);
    border-radius: 26px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 55px 65px;
    margin-bottom: 35px;
    margin-top: 15px;
    min-height: 450px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(60, 36, 21, 0.05);
}

.hero-image-frame {
    position: relative;
    border-radius: 24px;
    padding: 7px;
    background: linear-gradient(145deg, #ffffff, rgba(255, 255, 255, 0.7));
    box-shadow: 0 24px 55px rgba(44, 24, 16, 0.14), 0 6px 16px rgba(44, 24, 16, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.95);
    max-width: 430px;
    width: 100%;
    transition: transform 0.4s ease;
}

.hero-rating-tag {
    position: absolute;
    top: 16px;
    right: 16px;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(8px);
    color: #21130d;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 11.5px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.8);
    z-index: 3;
}

.hero-fresh-tag {
    position: absolute;
    bottom: -12px;
    left: 20px;
    background: #ffffff;
    color: #26140c;
    padding: 8px 18px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 8px 22px rgba(44, 24, 16, 0.12);
    border: 1px solid #f1eae0;
    z-index: 3;
}
```

- **Markup Hero Slider & Promo Banner**:
```blade
<!-- Hero Image Frame with Double Trust Chips -->
<div class="hero-image-frame">
    <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
    <div class="hero-rating-tag">
        <i class="fa-solid fa-star text-warning"></i> 4.9/5
    </div>
    <div class="hero-fresh-tag">
        <i class="fa-solid fa-certificate text-warning"></i> 100% Fresh Daily
    </div>
</div>

<!-- Middle Promo Banner with High-Contrast Typography & Slogan -->
<div class="promo-banner"
    style="@if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner)) background-image: linear-gradient(160deg, rgba(38, 20, 12, 0.78) 0%, rgba(18, 10, 6, 0.94) 100%), url('{{ asset('storage/' . $popupBanner->gambar_banner) }}'); @endif">
    <div class="promo-banner-content">
        <span class="promo-tag-pill"><i class="fa-solid fa-fire text-warning"></i> {{ isset($popupBanner) && $popupBanner && $popupBanner->subjudul ? $popupBanner->subjudul : 'Special Promo' }}</span>
        <h2>{{ isset($popupBanner) && $popupBanner && $popupBanner->judul_banner ? $popupBanner->judul_banner : 'Summer Sale' }}</h2>
        <p class="text-white-50 mb-3" style="font-size: 13.5px; max-width: 260px; line-height: 1.45;">Diskon spesial aneka kue & pastry lezat pilihan terbaik.</p>
        <a href="{{ isset($popupBanner) && $popupBanner && $popupBanner->link_url ? $popupBanner->link_url : route('shop') }}" class="promo-btn">
            Shop Now <i class="fa-solid fa-arrow-right ms-2"></i>
        </a>
    </div>
</div>
```

---

### C. Redesain Header Banner Shop & Custom Products
**Files**:
- `resources/views/Pelanggan/shop.blade.php`
- `resources/views/Pelanggan/custom_products.blade.php`

```blade
<!-- Header Banner Shop -->
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

```blade
<!-- Header Banner Custom Products -->
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

---

### D. Sinkronisasi Modal Admin Live Preview
**File**: `resources/views/Admin/kelola_carousel/banners.blade.php`
- Menyelaraskan modal `#previewModal{{ $b->id_banner }}` dengan elemen visual front-end terbaru (chip rating `4.9/5`, lencana `100% Fresh Daily`, tombol hijau gradien, serta gradasi hangat).

---

## 4. Hasil Verifikasi Sistem

1. **Pembersihan Cache Blade**:
   `php artisan view:clear` dieksekusi dengan hasil: `Compiled views cleared successfully`.
2. **Pengujian Halaman Homepage (`/`)**:
   - Status `200 OK`.
   - Seluruh kontainer hero (`.hero-section`, `.hero-badge-pill`, `.hero-rating-tag`, `.hero-fresh-tag`, `.hero-image-frame`) terverifikasi aktif.
   - Aturan CSS `.js-animate .scroll-reveal:not(.is-visible)` aktif.
   - Logika `document.body.classList.add('js-animate')` terverifikasi di `public/js/home.js`.
3. **Pengujian Halaman Shop (`/shop`) & Custom Products (`/custom-products`)**:
   - Status `200 OK`.
   - Banner header terverifikasi memuat gradasi asimetris 90° berpadu aset storage lokal, lencana emas, tipografi deskripsi, dan tombol aksi.

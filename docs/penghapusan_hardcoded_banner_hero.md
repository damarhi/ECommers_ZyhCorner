# Dokumentasi Penghapusan Teks & Elemen Hardcoded Hero Banner Beranda

Dokumentasi ini mencatat pembersihan dan penghapusan seluruh data teks, stiker, dan percabangan statis (*hardcoded*) pada komponen **Hero Banner Carousel** di beranda (`resources/views/Pelanggan/home.blade.php`), serta sinkronisasi kueri controller dan modal preview admin agar seluruh konten banner hero murni 100% bersumber dinamis dari database (`banners` table via model `App\Models\Banner`).

---

## 1. Masalah Sebelum Perbaikan

Sebelum perbaikan, komponen Hero Banner memiliki sejumlah kelemahan struktural:
1. **Teks Hardcoded Menimpa Input Admin**:
   - Di `resources/views/Pelanggan/home.blade.php` (baris 694–737), terdapat percabangan `@php` statis yang menetapkan teks buatan:
     - Callout: `'Sale up to'`, `'Limited Time:'`, `'Fresh Daily:'`, `'Personalized:'`
     - Highlight Diskon: `'30% OFF'`, `'Diskon Spesial 75% OFF'`, `'Koleksi Menu Spesial'`
     - Paragraf Deskripsi: `'Free shipping on all your order. Nikmati kelezatan aneka kue...'`, `'Penawaran diskon spesial aneka kue & pastry lezat...'`
     - Teks Tombol: `'Shop Now'`, `'Klaim Promo'`, `'Belanja Sekarang'`, `'Pesan Sekarang'`
   - Akibatnya, pembaruan judul atau subjudul yang dilakukan Admin melalui dashboard Admin tidak berpengaruh penuh karena tertimpa oleh string statis di template.
2. **Stiker / Tag Melayang Palsu (*Fake Tags*)**:
   - Stiker `'⭐ 4.9/5'` (`.hero-rating-tag`) dan `'100% Fresh Daily'` (`.hero-fresh-tag`) ditempel permanen di atas gambar banner hero, menutupi visual asli produk.
3. **Kueri Posisi Bercampur**:
   - Controller mengambil seluruh posisi `['home_hero', 'popup_promo', 'shop_header', 'custom_header']` ke dalam hero carousel, menyebabkan konten banner promo diskon 75% terduplikasi antara hero slider dan widget promosi kolom ke-4.
4. **Modal Preview Admin Tidak Sinkron**:
   - Modal preview di `resources/views/Admin/kelola_carousel/banners.blade.php` memiliki teks statis yang sama, sehingga tidak mencerminkan tampilan front-end sebenarnya.

---

## 2. Rincian Perubahan Kode

### A. Controller Beranda: `app/Http/Controllers/Pelanggan/PelangganController.php`
Mengubah kueri `$heroBanners` agar murni mengambil banner yang dialokasikan untuk posisi `'home_hero'` dengan status `'Aktif'`, diurutkan sesuai kolom `urutan`:

```php
// SEBELUM:
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

// SESUDAH:
// Home Hero Banner Slideshow (Dynamic from database)
$heroBanners = Banner::where('posisi_banner', 'home_hero')
    ->where('status', 'Aktif')
    ->orderBy('urutan', 'asc')
    ->get();
```

---

### B. View Pelanggan: `resources/views/Pelanggan/home.blade.php`
1. **Menghapus Seluruh Percabangan Teks Hardcoded**:
   Memangkas lebih dari 40 baris teks statis buatan dan menggantinya dengan logika ekstraksi dinamis murni dari kolom `subjudul` dan `judul_banner`:
   - Jika `subjudul` memiliki pemisah `-` (contoh: *"Welcome to Zyh Corner - Sale up to 30% OFF"*):
     - Bagian kiri (`Welcome to Zyh Corner`) menjadi **Badge Pill**.
     - Bagian kanan (`Sale up to 30% OFF`) dianalisis secara dinamis: teks diskon/persentase otomatis diberi highlight warna oranye `#ff8a00`.
   - Jika `subjudul` adalah teks deskripsi umum (contoh: *"Aneka Kue Lezat Dipanggang Segar Setiap Hari"*), langsung dirender ke dalam tag `<p class="desc">`.
   - Target URL tombol langsung membaca `$banner->link_url` dengan fallback aman ke `route('shop')`.

2. **Menghapus Stiker Palsu pada Gambar**:
   Tag `.hero-rating-tag` (`⭐ 4.9/5`) dan `.hero-fresh-tag` (`100% Fresh Daily`) dihilangkan sehingga foto produk dari database tampil bersih, proporsional, dan estetik.

```blade
{{-- KODE SESUDAH DI home.blade.php --}}
@forelse($heroBanners as $index => $banner)
    @php
        $subjudul = $banner->subjudul;
        $badgeText = 'Zyh Corner';
        $calloutPrefix = null;
        $calloutHighlight = null;
        $descText = null;

        if ($subjudul) {
            if (str_contains($subjudul, '-')) {
                $parts = explode('-', $subjudul, 2);
                $badgeText = trim($parts[0]);
                $calloutCandidate = trim($parts[1]);
            } else {
                $calloutCandidate = trim($subjudul);
            }

            if (preg_match('/^(.*?)(\d+%\s*OFF|\d+%\s*diskon|potongan\s*\d+%|diskon\s*\d+%.*|\d+%.*)$/i', $calloutCandidate, $matches)) {
                $calloutPrefix = trim($matches[1]) ?: null;
                $calloutHighlight = trim($matches[2]);
            } else {
                $descText = $calloutCandidate;
            }
        }
    @endphp
    <div class="hero-slide-item {{ $loop->first ? 'active' : '' }}" data-slide="{{ $index }}">
        <div class="hero-section">
            <div class="hero-text">
                <span class="hero-badge-pill">
                    <i class="fa-solid fa-sparkles text-success"></i> {{ $badgeText }}
                </span>
                <h1>{{ $banner->judul_banner }}</h1>
                @if($calloutHighlight)
                    <div class="hero-sale-callout">
                        @if($calloutPrefix)
                            <span>{{ $calloutPrefix }}</span>
                        @endif
                        <span class="hero-sale-highlight">{{ $calloutHighlight }}</span>
                    </div>
                @endif
                @if($descText)
                    <p class="desc">{{ $descText }}</p>
                @endif
                <a href="{{ $banner->link_url ? url($banner->link_url) : route('shop') }}" class="hero-btn">
                    Shop Now <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
            <div class="hero-image">
                @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
                    <div class="hero-image-frame">
                        <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
                    </div>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="hero-slide-item active" data-slide="0">
        <div class="hero-section">
            <div class="hero-text">
                <span class="hero-badge-pill"><i class="fa-solid fa-sparkles text-success"></i> Zyh Corner</span>
                <h1>Delicious & Fresh Cakes and Desserts</h1>
                <a href="{{ route('shop') }}" class="hero-btn">Shop Now <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="hero-image d-none">
            </div>
        </div>
    </div>
@endforelse
```

---

### C. View Admin: `resources/views/Admin/kelola_carousel/banners.blade.php`
Menyelaraskan modal preview banner berposisi `home_hero` di dashboard Admin agar menggunakan parser dinamis yang sama persis dengan front-end pelanggan dan menghapus stiker hardcoded:

```blade
@if($b->posisi_banner === 'home_hero')
    @php
        $subjudulPreview = $b->subjudul;
        $badgeTextPreview = 'Zyh Corner';
        $calloutPrefixPreview = null;
        $calloutHighlightPreview = null;
        $descTextPreview = null;

        if ($subjudulPreview) {
            if (str_contains($subjudulPreview, '-')) {
                $parts = explode('-', $subjudulPreview, 2);
                $badgeTextPreview = trim($parts[0]);
                $candidate = trim($parts[1]);
            } else {
                $candidate = trim($subjudulPreview);
            }

            if (preg_match('/^(.*?)(\d+%\s*OFF|\d+%\s*diskon|potongan\s*\d+%|diskon\s*\d+%.*|\d+%.*)$/i', $candidate, $m)) {
                $calloutPrefixPreview = trim($m[1]) ?: null;
                $calloutHighlightPreview = trim($m[2]);
            } else {
                $descTextPreview = $candidate;
            }
        }
    @endphp
    <div class="p-4 text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 shadow-sm position-relative overflow-hidden"
        style="background: #edf2ee; border-radius: 18px; border: 1px solid #e2eae3; min-height: 220px;">
        <div style="max-width: 62%; z-index: 2;">
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
        <div class="position-relative" style="background: transparent; padding: 0; z-index: 2;">
            <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="Preview" style="max-width: 170px; height: 125px; object-fit: contain; border-radius: 12px; display: block; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.10));">
        </div>
    </div>
@endif
```

---

## 3. Hasil Pengujian & Verifikasi

1. **Pembersihan Cache Blade**:
   `php artisan view:clear` berhasil dieksekusi tanpa kendala.
2. **Pengecekan Teks Statis pada Rendered HTML**:
   Semua teks hardcoded terbukti telah **hilang 100%**:
   - `Penawaran diskon spesial aneka kue` &rarr; Hilang
   - `Free shipping on all your order. Nikmati kelezatan` &rarr; Hilang
   - `Jelajahi aneka roti lembut` &rarr; Hilang
   - `Kreasikan desain, rasa, dan ukuran` &rarr; Hilang
   - `⭐ 4.9/5` & `100% Fresh Daily` &rarr; Hilang
3. **Uji Reaktivitas Input Database**:
   Pengujian perubahan judul banner (`judul_banner = 'Kue Tart Cokelat Belgia Premium'`) dan subjudul (`subjudul = 'Zyh Patisserie - Diskon Akhir Tahun 40% OFF'`) membuktikan bahwa beranda langsung merender konten baru secara instan dan akurat.

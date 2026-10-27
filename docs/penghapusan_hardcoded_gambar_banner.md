# Dokumentasi Penghapusan Fallback Hardcoded Gambar Banner

Dokumentasi ini mencatat pembersihan dan penghapusan seluruh gambar banner hardcoded (baik file statis lokal `image/about_cake_...` maupun URL eksternal `unsplash.com`) sehingga seluruh banner di aplikasi Zyh Corner murni 100% memuat data dari database dan penyimpanan storage.

---

## 1. Masalah yang Ditemukan (Sebelum Perbaikan)

1. **Fallback Hardcoded di Template Beranda (`home.blade.php`)**:
   - Pada bagian **Hero Banner Slider** (`.hero-image`), terdapat kondisi fallback ke file `image/about_cake_coffee.png` dan URL Unsplash:
     ```blade
     @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
         <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
     @elseif(file_exists(public_path('image/about_cake_coffee.png')))
         <img src="{{ asset('image/about_cake_coffee.png') }}" alt="{{ $banner->judul_banner }}">
     @else
         <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587..." alt="{{ $banner->judul_banner }}">
     @endif
     ```
   - Pada bagian **Promotional Banner** (`.promo-banner`), terdapat fallback ke `image/about_cake_showcase.png` dan URL Unsplash.
2. **Kueri Banner Hero Tidak Terfilter Berdasarkan Posisi**:
   Di `PelangganController.php`, kueri `$heroBanners` mengambil seluruh banner aktif tanpa filter `posisi_banner = 'home_hero'`:
   ```php
   $heroBanners = Banner::where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
   ```
   Akibatnya, banner untuk *shop header*, *custom header*, dan *popup promo* ikut muncul di slider beranda utama.
3. **Data Dummy Seeder Menggunakan File Non-Existent**:
   Di `BannerSeeder.php`, nama file gambar diset dummy seperti `'banners/hero_banner_1.jpg'` yang tidak ada secara fisik di storage.

---

## 2. Perubahan Kode yang Diterapkan

### A. `resources/views/Pelanggan/home.blade.php`
- Menghapus seluruh percabangan `@elseif(file_exists(...))` dan `@else (unsplash...)` pada `.hero-image`:
  ```blade
  {{-- SESUDAH --}}
  <div class="hero-image">
      @if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner))
          <img src="{{ asset('storage/' . $banner->gambar_banner) }}" alt="{{ $banner->judul_banner }}">
      @endif
  </div>
  ```
- Menghapus fallback hardcoded pada `.promo-banner`:
  ```blade
  {{-- SESUDAH --}}
  <div class="promo-banner scroll-reveal"
      style="--reveal-delay: 0.1s; @if(isset($popupBanner) && $popupBanner && $popupBanner->gambar_banner && Storage::disk('public')->exists($popupBanner->gambar_banner)) background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.65)), url('{{ asset('storage/' . $popupBanner->gambar_banner) }}'); background-size: cover; background-position: center; @endif">
  ```

### B. `app/Http/Controllers/Pelanggan/PelangganController.php`
- Menambahkan filter posisi eksplisit untuk `$heroBanners`:
  ```php
  // SESUDAH
  $heroBanners = Banner::where('posisi_banner', 'home_hero')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
  ```

### C. `database/seeders/BannerSeeder.php`
- Mengaitkan data seeder banner dengan 4 file banner aktual yang sudah tersedia di storage:
  - `home_hero` -> `banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png`
  - `shop_header` -> `banners/FwCnRdyIVKpLvKe5xCLVEujVcIgcvPxmU4LfZisj.png`
  - `custom_header` -> `banners/4KCToSkGBZDsUvU9Z8oBu13EhAQtABOn1oJhmOOa.png`
  - `popup_promo` -> `banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png`

---

## 3. Hasil Pengujian Verifikasi (Automated Headless Browser)

| Lokasi Banner | Gambar yang Dirender | Sumber Gambar | Status |
| :--- | :--- | :---: | :---: |
| **Home Hero Slider** | `banners/JMrYmKE9l5JHfJWWfbf5k0zNjjGZQZzNDKVWsG9N.png` | Database Storage | **Lolos (Murni Database)** |
| **Home Promo Banner** | `banners/SZFG52k6XY9FraW8KrM9ZT1zpBSrurmlFdXxV2NI.png` | Database Storage | **Lolos (Murni Database)** |
| **Shop Header Banner** | `banners/FwCnRdyIVKpLvKe5xCLVEujVcIgcvPxmU4LfZisj.png` | Database Storage | **Lolos (Murni Database)** |
| **Custom Header Banner** | `banners/4KCToSkGBZDsUvU9Z8oBu13EhAQtABOn1oJhmOOa.png` | Database Storage | **Lolos (Murni Database)** |

Tidak ada lagi file gambar statis hardcoded maupun link Unsplash eksternal pada seluruh komponen banner.

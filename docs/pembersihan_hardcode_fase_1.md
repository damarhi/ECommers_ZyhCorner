# Dokumentasi Pembersihan Hardcoded & Mockup Palsu (Fase 1)

## 1. Latar Belakang & Tujuan
Pada implementasi awal aplikasi Zyh Corner, terdapat beberapa elemen data fiktif (mockup) dan ketergantungan pada aset gambar eksternal (Unsplash CDN) yang tidak mencerminkan data riil dari database. Keberadaan data mockup ini berpotensi menyesatkan pengguna (seperti diskon fiktif 50% dan rating bintang 5 seragam tanpa ulasan), serta menyebabkan kegagalan rendering jika koneksi internet terganggu atau URL CDN eksternal kedaluwarsa.

Fase 1 berfokus pada **Eliminasi Mockup & Dead Weight (Quick Wins Tanpa Perubahan Skema Database)** untuk membersihkan seluruh elemen fiktif dan mengembalikan integritas tampilan aplikasi secara transparan, bersih, dan 100% mandiri secara lokal.

---

## 2. Batasan Khusus yang Dipatuhi
- **Pengecualian Profil Tim**: Bagian kartu profil tim pada `resources/views/Pelanggan/about.blade.php` (L88–107: Jenny Wilson, Jane Cooper, Cody Fisher, Robert Fox) **dipertahankan seutuhnya tanpa perubahan** sesuai instruksi eksplisit pengguna (*"eksekusi fase 1 kecuali profile tim"*).
- **Estetika Warna Tetap Konsisten**: Penggantian background breadcrumb Unsplash menggunakan gradasi warna dark sage organik khas brand Zyh Corner (`linear-gradient(135deg, #1b382b 0%, #244837 100%)`) sehingga nuansa elegan tetap terjaga.
- **Aset Fallback Lokal**: Fallback gambar produk menggunakan aset lokal internal `asset('image/logo_circle.png')` yang dijamin selalu tersedia di direktori `public/image/`.

---

## 3. Rincian Perubahan Berkas & Kode

### A. Penghapusan Rumus Diskon Palsu di Beranda
- **Berkas**: `resources/views/Pelanggan/home.blade.php`
- **Sebelum**:
  - Terdapat kondisi penambahan badge diskon palsu pada produk pertama di daftar Featured Products:
    ```blade
    @elseif($loop->first)
        <div class="sale-badge">Sale 50%</div>
    @endif
    ```
  - Terdapat rumus manipulasi harga coret palsu yang menggandakan harga asli:
    ```blade
    @if($loop->first)
        <span class="old">Rp. {{ number_format($product->harga * 2, 0, ',', '.') }}</span>
    @endif
    ```
  - Atribut `data-sale` diisi nilai statis berdasarkan urutan loop:
    ```blade
    data-sale="{{ $loop->first ? '1' : '0' }}"
    ```
- **Sesudah**:
  - Seluruh rumus pengali diskon `harga * 2` dan badge fiktif `@elseif($loop->first)` dihapus.
  - Atribut `data-sale` di-reset ke nilai aman `data-sale="0"`.
  - Harga yang ditampilkan pada kartu produk murni adalah harga satuan asli dari kolom `harga` database.

---

### B. Perbaikan Logika Modal Pratinjau Produk
- **Berkas**: `resources/js/pelanggan_pages.js`
- **Sebelum**:
  - Modal pratinjau produk memaksakan teks diskon dan menghitung harga coret fiktif:
    ```javascript
    if (data.sale === '1') {
        if (pmOldPrice) {
            pmOldPrice.style.display = 'inline-block';
            pmOldPrice.textContent = 'Rp. ' + new Intl.NumberFormat('id-ID').format(price * 2);
        }
        if (pmDiscountBadge) {
            pmDiscountBadge.style.display = 'inline-block';
            pmDiscountBadge.textContent = '50% Off';
        }
    } else {
        if (pmOldPrice) pmOldPrice.style.display = 'none';
        if (pmDiscountBadge) pmDiscountBadge.style.display = 'none';
    }
    ```
- **Sesudah**:
  - Logika diganti agar hanya menampilkan harga coret dan badge jika data diskon riil disertakan (`data.oldPrice`):
    ```javascript
    if (data.sale === '1' && data.oldPrice) {
        if (pmOldPrice) {
            pmOldPrice.style.display = 'inline-block';
            pmOldPrice.textContent = data.oldPrice.startsWith('Rp') ? data.oldPrice : ('Rp. ' + new Intl.NumberFormat('id-ID').format(parseInt(data.oldPrice)));
        }
        if (pmDiscountBadge) {
            pmDiscountBadge.style.display = 'inline-block';
            pmDiscountBadge.textContent = data.discountBadge || 'Promo';
        }
    } else {
        if (pmOldPrice) pmOldPrice.style.display = 'none';
        if (pmDiscountBadge) pmDiscountBadge.style.display = 'none';
    }
    ```

---

### C. Penghapusan Rating Bintang 5 Seragam Palsu
- **Berkas**: `resources/views/Pelanggan/home.blade.php`
- **Sebelum**:
  - Kartu produk Featured Products memiliki elemen statis 5 bintang:
    ```html
    <div class="product-rating">
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
        <i class="fa-solid fa-star"></i>
    </div>
    ```
  - Kolom Hot Deals, Best Seller, dan Top Rated masing-masing memiliki elemen statis:
    ```html
    <div class="sp-rating" style="margin-bottom: 5px;">
        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i>
    </div>
    ```
- **Sesudah**:
  - Seluruh elemen rating statis fiktif tersebut dihapus dari `home.blade.php`. Tampilan kartu produk kini bersih, jujur, dan seragam dengan layout bagian produk terbaru (*Newest Products*).

---

### D. Penggantian Background Breadcrumb Unsplash dengan CSS Gradient
- **Berkas yang Diperbarui**:
  1. `resources/css/dashboard.css` (L9)
  2. `resources/css/pelanggan_pages.css` (L11)
  3. `resources/css/pelanggan.css` (L215)
  4. `resources/css/pelanggan_custom.css` (L372, L1574, L2159, L2766, L3332, L3816)
  5. `public/css/pelanggan.css` (L215)
  6. `resources/views/Pelanggan/order_detail.blade.php` (L49)
- **Sebelum**:
  ```css
  background: linear-gradient(rgba(45, 78, 55, 0.7), rgba(45, 78, 55, 0.7)), url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=2042&auto=format&fit=crop') center/cover !important;
  ```
- **Sesudah**:
  ```css
  background: linear-gradient(135deg, #1b382b 0%, #244837 100%) !important;
  ```
- **Manfaat**: Menghilangkan ketergantungan pada server pihak ketiga, mempercepat First Contentful Paint (FCP), dan menghadirkan latar belakang gradasi sage gelap yang konsisten dan elegan.

---

### E. Penggantian Fallback Gambar Produk Unsplash ke Aset Lokal
- **Berkas yang Diperbarui**:
  1. `resources/views/Pelanggan/dashboard.blade.php` (L151)
  2. `resources/views/Pelanggan/order_history.blade.php` (L170)
  3. `resources/views/Pelanggan/order_detail.blade.php` (L952)
  4. `resources/views/Pelanggan/return_detail.blade.php` (L216)
- **Sebelum**:
  ```php
  $displayImg = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop';
  ```
- **Sesudah**:
  ```php
  $displayImg = asset('image/logo_circle.png');
  ```
- **Manfaat**: Jika produk belum memiliki foto atau data riwayat lama tidak menyertakan foto, aplikasi menggunakan logo resmi Zyh Corner sebagai placeholder yang rapi dan profesional.

---

## 4. Hasil Kompilasi & Verifikasi Otomatis

### A. Kompilasi Aset Vite & Cache View
- Eksekusi: `npm run build && php artisan view:clear`
- Hasil:
  ```text
  vite v8.0.9 building client environment for production...
  ✓ 58 modules transformed.
  public/build/assets/dashboard-BujXhvA_.css         9.56 kB
  public/build/assets/app-C96nGsXD.css              21.83 kB
  public/build/assets/pelanggan_pages-CHNaROOj.css  94.87 kB
  public/build/assets/dashboard-DrVLxMtf.js          5.12 kB
  public/build/assets/app-DRPeDlVi.js               37.25 kB
  public/build/assets/pelanggan_pages-DHhPeGcR.js   54.91 kB
  ✓ built in 2.33s
  INFO  Compiled views cleared successfully.
  ```

### B. Uji Verifikasi Headless Browser
Pengujian otomatis dilakukan pada endpoint live aplikasi:
1. **Beranda (`/`)**:
   - Jumlah kartu Featured Products: 8 kartu ter-render normal.
   - Deteksi rumus diskon fiktif: 0 (bersih).
   - Deteksi rating bintang palsu: 0 (bersih).
   - Interaksi Quick View Modal: Modal terbuka normal, harga coret fiktif dan badge `50% Off` disembunyikan secara otomatis.
2. **Halaman Tentang Kami (`/about`)**:
   - Header Breadcrumb: Menampilkan `linear-gradient(135deg, rgb(27, 56, 43) 0%, rgb(36, 72, 55) 100%)`.
   - Profil Tim: Tetap lengkap mempertahankan profil tim asli (Jenny Wilson dkk).
3. **Pemeriksaan CDN Unsplash**:
   - Zero Unsplash URL di seluruh modul produk, pesanan, dan layout breadcrumb.

---

## 5. Kesimpulan & Langkah Selanjutnya
Seluruh target **Fase 1** telah selesai dikerjakan dengan sempurna tanpa menimbulkan regresi visual maupun fungsional. Aplikasi kini siap untuk melangkah ke **Fase 2: Integrasi Relasi Diskon & Best Seller Riil Menggunakan Database yang Ada**.

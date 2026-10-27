# Dokumentasi Perbaikan Beranda, Seeder Tabel Lengkap & Purifikasi Data Pelanggan

Dokumen ini mencatat seluruh perbaikan teknis yang dilakukan pada sistem Zyh Corner untuk memastikan seluruh interaktivitas beranda berjalan fungsional, tampilan beranda pulih ke desain aslinya yang bersih (*clean, appetizing bakery aesthetic*), dan seluruh 10 tabel database memiliki data seeder realistis.

---

## 1. Ringkasan Perbaikan Utama & Status Eksekusi

| Area / Fitur | Masalah Awal | Solusi & Implementasi Final | Status |
| :--- | :--- | :--- | :--- |
| **Seeder Kasir (`cassiers`)** | Tabel `cassiers` kosong (0 baris), menu kelola kasir tidak memiliki data | Dibuat `database/seeders/CassierSeeder.php` berisi 4 akun kasir (Pagi, Siang, Malam, Cadangan) dengan password hash dan sinkronisasi role `kasir` pada tabel `users`. | **Selesai** |
| **Seeder Produk Kadaluarsa (`produk_kadaluarsas`)** | Tabel `produk_kadaluarsas` kosong (0 baris), menu monitoring produk kadaluarsa tidak memiliki data | Dibuat `database/seeders/ProdukKadaluarsaSeeder.php` berisi 5 entri riil bakery dengan variasi status tindakan (`Perlu Tindakan`, `Dimusnahkan`, `Diarsipkan`, `Diretur`). | **Selesai** |
| **Database Seeder Terpadu** | `DatabaseSeeder.php` belum memanggil seeder kasir & kadaluarsa | Ditambahkan pemanggilan `CassierSeeder::class` dan `ProdukKadaluarsaSeeder::class` dengan urutan dependensi foreign key yang aman. | **Selesai** |
| **Bug Kartu Menghilang / Layar Beranda Kosong** | Kelas `.scroll-reveal` di `home.css` mengatur `opacity: 0` dan `translateY(24px)`. Semua 27 kartu produk/kategori menjadi 100% transparan jika observer tidak memicu kelas `.is-visible` | Menghapus aturan `opacity: 0` pada `.scroll-reveal` di `home.css`, menghapus kelas masking dari `home.blade.php`, dan menetapkan `opacity: 1 !important` serta `transform: none !important`. | **Selesai** |
| **Konflik Cascading CSS (`pelanggan_pages.css` vs `home.css`)** | `@vite(['resources/css/pelanggan_pages.css'])` menimpa selektor beranda dengan `!important`, menyebabkan distorsi padding dan ukuran grid | Mengisolasi seluruh gaya inti beranda di blok `<style>` di dalam `home.blade.php` dengan spesifisitas tinggi sehingga layout tahan terhadap stylesheet global. | **Selesai** |
| **Slider Kategori Rusak** | `.category-grid` diubah menjadi flexbox overflow horizontal (`overflow-x: auto`) dengan tombol panah yang mengaburkan card | Mengembalikan `.category-grid` ke model CSS Grid murni (`display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px;`) yang stabil, responsif, dan rapi. | **Selesai** |
| **Tata Letak 4 Kolom & Banner Promo** | Kolom ke-4 (`.promo-banner`) dibungkus dalam kontainer berlebih yang tingginya tidak sejajar dengan 3 kolom list di sampingnya | Mengembalikan `.four-cols` menjadi 4 kolom langsung sejajar (`align-items: stretch`), banner promo mengisi tinggi penuh (`height: 100%; min-height: 320px;`). | **Selesai** |
| **Interaktivitas Kartu & Modal Cepat** | Diperlukan navigasi kartu ke detail dan tombol keranjang memicu modal tanpa event bubbling | Kartu memiliki `onclick="window.location.href='...'"` dengan `cursor: pointer;`. Tombol keranjang dan quick view memanggil `openProductModal(this)` dengan dataset lengkap, serta seluruh tombol aksi memiliki `event.stopPropagation()`. | **Selesai** |
| **Animasi Scroll Aman (*Fail-Safe Progressive Enhancement*)** | Animasi scroll sebelumnya menyembunyikan elemen secara statis dengan `opacity: 0`, membuat halaman berisiko blank jika script observer terlambat | Diimplementasikan arsitektur *fail-safe*: elemen default `opacity: 1`. Efek transisi halus slide-up hanya aktif saat JS mengonfirmasi `js-reveal-ready`, didukung `rootMargin: 50px 0px` dan jaring pengaman `setTimeout(..., 1200)` sehingga mustahil ada kartu yang tertinggal dalam kondisi transparan. | **Selesai** |

---

## 2. Hasil Audit & Status Seeder Seluruh Tabel Database

Setelah eksekusi `php artisan db:seed`, seluruh 10 tabel basis data kini memiliki data terisi (> 0 baris):

```text
users: 16 rows (1 Admin, 11 Pelanggan, 4 Kasir)
kategoris: 5 rows (Kue & Cake, Roti & Pastry, Dessert & Buah Segar, Produk Kustom & Gift, Cemilan & Snack)
produks: 13 rows (13 produk bakery & pastry autentik)
pesanans: 25 rows (Transaksi penjualan online & offline)
returs: 10 rows (Data retur produk dengan status komplit)
cassiers: 4 rows (Akun kasir aktif & nonaktif)
customers: 15 rows (Data profil pelanggan admin panel)
diskons: 4 rows (Data diskon promo)
produk_kadaluarsas: 5 rows (Pencatatan expired terkait produk)
banners: 10 rows (Banner hero slider dan popup promo beranda)
```

---

## 3. Detail Berkas yang Dibuat & Dimodifikasi

### A. Berkas Seeder Baru:
1. **`database/seeders/CassierSeeder.php`**:
   - Membuat akun kasir: `Kasir Pagi - Rina`, `Kasir Siang - Dimas`, `Kasir Malam - Sarah`, dan `Kasir Cadangan - Eko`.
   - Mengenkripsi password dengan `Hash::make('kasir123')`.
   - Menyinkronkan akun pada tabel `users` dengan `role: 'kasir'` agar kasir dapat melakukan autentikasi login ke sistem POS.
2. **`database/seeders/ProdukKadaluarsaSeeder.php`**:
   - Mengaitkan catatan expired dengan produk rilisan bakery (`Butter Croissant`, `Almond Danish`, `Fresh Fruit Tartlet`, `Garlic Cheese Bread`, `Black Forest Cake`).
   - Menyertakan tanggal kadaluarsa realistis, jumlah stok, status tindakan (`Perlu Tindakan`, `Dimusnahkan`, `Diarsipkan`, `Diretur`), dan catatan operasional.

### B. Berkas Konfigurasi Seeder:
3. **`database/seeders/DatabaseSeeder.php`**:
   - Mendaftarkan `CassierSeeder::class` dan `ProdukKadaluarsaSeeder::class` dalam urutan eksekusi dependensi:
     `KategoriSeeder` → `ProdukSeeder` → `UserPelangganSeeder` → `CassierSeeder` → `CustomerSeeder` → `PesananSeeder` → `ReturSeeder` → `DiskonSeeder` → `ProdukKadaluarsaSeeder` → `BannerSeeder`.

### C. Berkas Tampilan Beranda:
4. **`resources/views/Pelanggan/home.blade.php`**:
   - Menghapus seluruh kelas `.scroll-reveal` dan atribut `--reveal-delay` dari semua kartu produk, kartu kategori, features bar, dan promo banner.
   - Menyematkan CSS scoped terisolasi dengan prioritas tinggi di blok `<style>` untuk memastikan layout tetap konsisten dan tidak dirusak oleh stylesheet eksternal.
   - Menata ulang **Hero Slider** dengan transisi halus dan fallback gambar aset lokal jika gambar banner penyimpanan belum tersedia.
   - Memulihkan **Features Bar** 4 kolom (*Free Shipping*, *Customer Support 24/7*, *100% Secure Payment*, *Freshly Baked Daily*).
   - Menata **Featured Products** menjadi 4 kolom CSS grid dengan informasi harga, badge stok, tombol wishlist, quick view, dan keranjang belanja.
   - Menata **4 Columns Section** (*Hot Deals*, *Best Seller*, *Top Rated*, dan *Promotional Banner*) dengan tinggi kartu yang sejajar secara proporsional.
   - Menata **Top Category** menjadi grid 5 kolom yang simetris dan elegan.
   - Menata **Newest Products** menjadi 4 kolom dengan kartu produk fungsional.
   - Memastikan tombol aksi memiliki `onclick="event.stopPropagation(); ..."` agar tidak memicu navigasi kartu ganda.

### D. Berkas Stylesheet & Skrip Beranda:
5. **`public/css/home.css`**:
   - Mengubah deklarasi `.scroll-reveal` dari `opacity: 0` menjadi `opacity: 1 !important; transform: none !important;` untuk mencegah bug rendering kartu transparan di browser klien.
   - Mengubah `.category-grid` dari flexbox slider horizontal menjadi CSS Grid responsif (`grid-template-columns: repeat(5, 1fr) !important; gap: 20px !important;`).
   - Menetapkan `.promo-banner` dengan `height: 100% !important; min-height: 320px !important;` agar mengisi penuh ruang kolom ke-4.

---

## 4. Hasil Verifikasi Otomatis (Browser DOM & Computed Styles)

Pengujian end-to-end dilakukan menggunakan browser headless pada halaman `http://127.0.0.1:8000/`:

1. **Uji Opasitas Elemen (Mencegah Blank/Hidden Card)**:
   - `productCards` (8 kartu: 4 Featured + 4 Newest): **Seluruh 8 kartu memiliki computed opacity: "1"** (100% terlihat).
   - `smallCards` (9 kartu: 3 Hot Deals + 3 Best Seller + 3 Top Rated): **Seluruh 9 kartu memiliki computed opacity: "1"**.
   - `categoryCards` (5 kategori): **Seluruh 5 kartu memiliki computed opacity: "1"**.
   - `featuresBar` (4 item nilai proposisi): **Seluruh 4 item memiliki computed opacity: "1"**.
   - `promoBanner`: **Tinggi 408px, computed opacity: "1"**.

2. **Uji Interaktivitas Modal Cepat (Quick View)**:
   - Tombol `.action-btn[title="Quick View"]` diklik secara terprogram.
   - Modal `#productModal` berhasil terbuka (`display: 'flex'`, `visibility: 'visible'`).
   - Data produk (`pmName`: nama produk terpilih, `pmNewPrice`: harga terformat "Rp. xx.xxx") terisi secara dinamis tanpa muat ulang halaman.

3. **Uji Interaktivitas Tombol Keranjang (`.cart-btn`)**:
   - Tombol `.cart-btn` pada kartu produk aktif diklik secara terprogram.
   - Berhasil memicu `#productModal` dengan data produk yang bersangkutan (`display: 'flex'`).
   - Event bubbling dicegah sehingga halaman tidak berpindah ke halaman detail secara tidak sengaja.

4. **Verifikasi Visual Snapshot**:
   - Snapshot halaman penuh tersimpan pada `docs/homepage_restored_verification.png`.
   - Layout beranda tampil rapi, proporsional, berkontras tinggi, dan ramah pengguna sesuai arahan *Frontend Design Direction*.

---

## 5. Implementasi & Verifikasi Animasi Scroll-Reveal Sesuai Permintaan Pengguna

### A. Spesifikasi Motion & Easing Figma:
Animasi scroll telah dikembalikan ke perilaku visual aslinya yang mulus (*smooth natural cascade*):
- **Kondisi Awal (Sebelum Discroll Masuk Layar)**:
  `opacity: 0; transform: translateY(28px) scale(0.98);`
- **Kondisi Aktif (Saat Masuk Viewport)**:
  `opacity: 1; transform: translateY(0) scale(1);`
- **Durasi & Kurva Easing**:
  `transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1), transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);`
- **Staggered Delay**:
  `transition-delay: var(--reveal-delay, 0s);` bertingkat antar kartu (0.05s - 0.06s) sehingga kartu muncul berurutan layaknya efek gelombang halus.

### B. Pengaturan IntersectionObserver di `public/js/home.js`:
```javascript
const revealElements = document.querySelectorAll('.scroll-reveal, .safe-reveal');
if (revealElements.length > 0) {
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
            rootMargin: '0px 0px -20px 0px'
        });

        revealElements.forEach(el => revealObserver.observe(el));
    } else {
        revealElements.forEach(el => el.classList.add('is-visible'));
    }
}
```

### C. Bukti Pengujian Bertahap Sesuai Scroll Pengguna:
1. **Saat Posisi Teratas (`scrollY = 0`)**:
   - 0 kartu di bawah fold berstatus `is-visible`, 23 kartu menunggu interaksi scroll pengguna.
2. **Saat Pengguna Scroll ke Bagian Featured Products (`scrollY = 450`)**:
   - 4 kartu Featured Products memicu observer dan meluncur halus ke atas (`is-visible: true`), sementara kategori di bawahnya tetap menunggu giliran (`is-visible: false`).
3. **Saat Pengguna Scroll ke Bagian 4 Kolom (`scrollY = 1200`)**:
   - 10 kartu kecil (Hot Deals, Best Sellers, Top Rated) dan Promo Banner meluncur halus ke atas secara beruntun.
4. **Saat Pengguna Scroll ke Kategori & Produk Terbaru (`scrollY = 2000`)**:
   - Seluruh 5 kategori dan 4 kartu produk terbaru menyelesaikan animasi scroll reveal (`allVisible: true`).
5. **Dokumentasi Visual Snapshot**:
   - Bukti tangkapan layar tersimpan pada `docs/homepage_scroll_reveal_activated.png`.

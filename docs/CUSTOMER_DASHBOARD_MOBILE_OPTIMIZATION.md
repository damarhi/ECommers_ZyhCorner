# Optimasi Tata Letak Mobile Halaman Customer Dashboard (`/dashboard`) & Komponen `<x-customer-sidebar>`

## 1. Ringkasan Eksekutif
Dokumentasi ini merinci evaluasi dan perbaikan tata letak (*mobile layout optimization*) pada halaman **Customer Dashboard** (`/dashboard`) serta penyembunyian komponen navigasi samping pelanggan (`<x-customer-sidebar>`) pada mode mobile (`@media (max-width: 768px)`). Seluruh perubahan diisolasi pada media query `@media (max-width: 768px)` sehingga **tampilan Website / Desktop (`> 768px`) tetap 100% utuh tanpa perubahan satu piksel pun**.

---

## 2. Masalah yang Ditemukan Sebelum Perbaikan (Baseline `375×812`)
1. **Sidebar Menu `"Navigation"` (`<x-customer-sidebar>`) Memakan `366px` di Bagian Atas Layar Mobile:**
   - Pada viewport `375×812`, kartu `.sidebar-navigation` tampil di paling atas (`top: 229px`, `height: 366px` + `margin-bottom: 24px` = `390px`), memaksa pelanggan melakukan *scroll* jauh ke bawah (`top: 619px`) sebelum melihat profil, alamat pengiriman, dan riwayat pesanan. Padahal seluruh menu akun sudah tersedia di drawer hamburger header mobile dan footer.
2. **Header Kartu *"Buku Alamat Pengiriman"* (`.billing-card`) Patah Menjadi 2 Baris (`height: 40px`):**
   - Judul `BUKU ALAMAT PENGIRIMAN` (`width: 181px`, `height: 36px`) dan tombol `+ Tambah Alamat` (`width: 122px`, `height: 40px`) saling berhimpitan hingga keduanya terlipat menjadi 2 baris.
3. **Baris *"Recent Order History"* (`.order-row-item`) Pincang & Terpotong (`rowHeight: 190px`):**
   - Ukuran gambar `.order-img-wrapper` dipaksa `80×80px !important`, menyisakan `178px` untuk kolom teks sehingga `Total: Rp. 375.000 (3 Products)` terpotong menjadi 2 baris (`height: 40px`).
   - Elemen `.tracking-action-wrapper` membungkus status badge (`Menunggu Konfirmasi`) sekaligus tombol `Tracking Order` di kolom kanan (`width: 130px`), sedangkan tombol `Cancel Order` berada sendirian di kolom kiri (`width: 133px`). Akibatnya, status `Menunggu Konfirmasi` terlipat 2 baris (`height: 40px`), timbul ruang kosong di atas `Cancel Order`, dan posisi vertikal tombol `Cancel Order` (`top: 1555px`) tidak sejajar dengan `Tracking Order` (`top: 1579px`, selisih `24px`).
4. **Celah Vertikal Ganda Antara Kartu Alamat & Recent Order History (`54px`):**
   - Kombinasi `.mb-4` (`24px`) pada kolom kartu alamat dan `margin-top: 30px` pada `.order-history-card` menciptakan jarak kosong `54px`.

---

## 3. Solusi & Perubahan Kode (`@media (max-width: 768px)`)

### A. File yang Diperbarui
- `resources/views/components/customer-sidebar.blade.php`
  - Menambahkan class `customer-sidebar-col` pada elemen pembungkus `<div class="col-lg-3 col-md-4 mb-4 customer-sidebar-col">`.
- `resources/views/Pelanggan/dashboard.blade.php`
  - Menambahkan class `billing-card-header` pada header kartu Buku Alamat Pengiriman dan class `btn-add-address-inline` pada tombol `+ Tambah Alamat`.
- `resources/css/dashboard.css`, `resources/css/pelanggan_custom.css`, dan `resources/css/pelanggan_pages.css`
  - Menyembunyikan `.customer-sidebar-col` (`display: none !important;`) pada `@media (max-width: 768px)` di seluruh halaman akun pelanggan (`Dashboard`, `Order History`, `Order Detail`, `Return Detail`, `Settings`) serta memastikan kolom konten utama `.col-lg-9.col-md-8` mengambil lebar penuh `100%`.
  - Merampingkan proporsi `.profile-card` pada mobile (avatar `88×88px` terpusat, `padding: 18px 16px`, tinggi kartu turun dari `272px` menjadi `220px`).
  - Menyelaraskan `.billing-card-header` agar judul `BUKU ALAMAT PENGIRIMAN` dan tombol `+ Tambah Alamat` tampil 1 baris utuh (`height: 18px`, turun dari `40px`).
  - Mengubah ukuran `.order-img-wrapper` di mobile menjadi `64×64px` sehingga teks `Total: Rp. 375.000 (3 Products)` muat dalam 1 baris utuh (`width: 238px`, `height: 18px`).
  - Mengatur `.tracking-action-wrapper { display: contents !important; }` pada mobile dan menampilkan `.order-status-badge` (`Status: Menunggu Konfirmasi`) sebagai *pill banner* lebar penuh (`order: -1; flex: 0 0 100%; height: 28px`) di atas baris tombol, sehingga tombol `Cancel Order` dan `Tracking Order` berdampingan sejajar (`width: 155px`, `height: 38px`, `top: 975px` identik).

- `resources/views/Layout/layout_pelanggan.blade.php`, `public/css/pelanggan.css`, dan `resources/css/pelanggan.css`
  - Menambahkan menu lengkap navigasi akun pelanggan (`Dashboard`, `Order History`, `Shopping Cart`, `Settings`, `Log-out`) beserta ikon dan *active state* ke dalam **Dropdown Foto Profil Header (`#profileDropdownMenu`)** khusus mode mobile (`<= 768px`, `195×265px`) serta ke dalam bagian **"Navigasi Akun"** pada **Mobile Hamburger Drawer (`#mobileNavDrawer`)** (`240×43px` per item).
  - Memperbaiki bug *off-canvas overflow* pada **Shopping Cart Sidebar (`.cart-sidebar`)** di layar mobile yang sedikit lebih lebar (`401px–576px`), di mana aturan `@media (max-width: 576px)` mengubah `width: 100% !important` tanpa memperbarui `right: -400px` sehingga sebagian panel `.cart-sidebar` (`(100vw - 400px)`) bocor dan menutupi sisi kanan layar (`Search`, `+ Tambah Alamat`, `View All`, `Tracking Order`). Kini `.cart-sidebar` dan `.mobile-nav-drawer` menggunakan `right: -100% !important; transform: translateX(100%); visibility: hidden; pointer-events: none; box-shadow: none;` saat tertutup dan `right: 0 !important; transform: translateX(0) !important; visibility: visible !important; pointer-events: auto !important;` saat dibuka (`.open`).
---

## 4. Hasil Pengukuran Geometri DOM (Headless Chrome CDP)

### Tabel Perbandingan Mobile (`375×812`) & Website (`1440×900`)

| Metrik / Komponen | Mobile Sebelum (`375×812`) | Mobile Sesudah (`375×812`) | Website / Desktop (`1440×900`) |
| :--- | :--- | :--- | :--- |
| **Horizontal Overflow (`pageOverflow`)** | `0px` | **`0px`** | **`0px`** |
| **Tinggi Total `.dashboard-wrapper`** | `1405px` | **`807px` *(Hemat `-598px` / `-42.6%`)* ** | `1200×649px` *(100% Tetap)* |
| **Sidebar `<x-customer-sidebar>`** | Tampil (`345×366px`, `top: 229px`) | **Tersembunyi (`display: none`)** | Tampil `263×366px` *(100% Tetap)* |
| **Kartu Profil (`.profile-card`)** | `345×272px` (`top: 619px`, avatar `120px`) | **`351×220px` (`top: 225px`, avatar `88px`)** | `414×368px` *(100% Tetap)* |
| **Header Buku Alamat (`.billing-card-header`)** | `height: 40px` (Judul & Tombol 2 baris) | **`height: 18px` (Judul `17px` & Tombol `18px`, 1 baris)** | `352×20px` *(100% Tetap)* |
| **Celah Kartu Alamat ke Recent Order** | `54px` | **`16px`** | `30px` *(100% Tetap)* |
| **Teks Ringkasan Produk (`.order-summary`)** | `178×40px` (Terlipat 2 baris) | **`238×18px` (Utuh 1 baris)** | `198×20px` *(100% Tetap)* |
| **Badge Status (`.order-status-badge`)** | `130×40px` (Terlipat 2 baris di kanan) | **`317×28px` (Pill 1 baris penuh di atas tombol)** | `147×20px` *(100% Tetap)* |
| **Tombol `Cancel Order` vs `Tracking Order`** | `top: 1555px` vs `top: 1579px` (Miring `24px`) | **`top: 975px` vs `top: 975px` (`155×38px` Sejajar Rata)** | `top: 896px` / `910px` *(100% Tetap)* |

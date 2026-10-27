# Rencana Perbaikan: Alur Tombol "Beli Sekarang" Langsung ke Halaman Keranjang Tanpa Membuka Sidebar Cart

## Ringkasan Eksekutif
Dokumen ini merinci rencana perbaikan alur interaksi tombol **"Beli Sekarang"** (*Buy Now*) pada halaman detail produk dan modal *Quick View*. Saat ini, tombol "Beli Sekarang" memicu efek samping berupa pembukaan drawer *sidebar cart* sebelum mengalihkan halaman. Perbaikan ini memisahkan tanggung jawab fungsi `addToCart()` dengan parameter kontrol `openDrawer`, menonaktifkan pembukaan sidebar saat pembelian kilat, dan mengarahkan pengguna langsung ke **Halaman Keranjang Belanja (`/cart`)**.

---

## 1. Analisis Masalah & Kebutuhan
- **Gejala Masalah**: Saat pengunjung/pelanggan mengklik tombol *"Beli Sekarang"*, drawer keranjang samping (*sidebar cart*) sempat muncul/beranimasi keluar sesaat sebelum halaman berpindah.
- **Akar Masalah**: Fungsi `window.buyNow()` memanfaatkan `window.addToCart()` untuk menyimpan produk ke memori (*localStorage*), namun di dalam `window.addToCart()` baris terakhir selalu mengeksekusi `window.toggleCart(true)`.
- **Target Perilaku yang Diharapkan**:
  1. **Tombol "Tambah ke Keranjang"** (*Add to Cart*): Tetap membuka drawer sidebar cart agar pelanggan dapat mengintip ringkasan belanja dan lanjut memilih produk lain di halaman yang sama.
  2. **Tombol "Beli Sekarang"** (*Buy Now*): **Tidak membuka drawer sidebar sama sekali**, melainkan langsung menambahkan item ke memori dan mengalihkan browser langsung ke **Halaman Keranjang (`/cart`)**.

---

## 2. Langkah-Langkah Perbaikan Terstruktur (Step-by-Step)

### Step 1: Penambahan Konfigurasi Rute Keranjang Global & Detail
- **Target File**:
  - `resources/views/Layout/layout_pelanggan.blade.php`
  - `resources/views/Pelanggan/detail.blade.php`
- **Tindakan**:
  - Menambahkan properti `cartRoute: "{{ route('userChart') }}"` pada `window.layoutConfig` dan `window.detailConfig`.
- **Kriteria Penerimaan**:
  - Nilai rute `/cart` tersedia secara konsisten di seluruh skrip frontend tanpa *hardcode* URL.

### Step 2: Refactoring Fungsi `window.addToCart()` dengan Parameter Kontrol `openDrawer`
- **Target File**:
  - `resources/js/pelanggan_pages.js`
- **Tindakan**:
  - Ubah tanda tangan fungsi menjadi:
    `window.addToCart = function(product, openDrawer = true)`
  - Bungkus pemanggilan `window.toggleCart(true)` dengan kondisi:
    ```javascript
    window.saveCart();
    if (openDrawer) {
        window.toggleCart(true);
    }
    ```
- **Kriteria Penerimaan**:
  - Pemanggilan biasa `addToCart(item)` tetap membuka sidebar (nilai default `true`).
  - Pemanggilan dengan `addToCart(item, false)` memasukkan barang ke keranjang tanpa membuka sidebar.

### Step 3: Penyesuaian Alur `window.buyNow()` dan Tombol Modal Beli
- **Target File**:
  - `resources/js/pelanggan_pages.js`
- **Tindakan**:
  - Di dalam `window.buyNow()`:
    1. Panggil `window.addToCart(item, false)`.
    2. Arahkan browser langsung ke `window.detailConfig?.cartRoute || window.layoutConfig?.cartRoute || "/cart"`.
  - Di dalam `pmBuyBtn.onclick` (Modal *Quick View*):
    1. Panggil `window.addToCart(item, false)`.
    2. Arahkan browser langsung ke `window.layoutConfig?.cartRoute || "/cart"`.
- **Kriteria Penerimaan**:
  - Klik "Beli Sekarang" tidak memicu class aktif atau pembukaan `#cartSidebar`.
  - Pengguna langsung tiba di halaman `/cart` dengan item sudah terisi di tabel keranjang.

### Step 4: Kompilasi Aset Vite & Pembersihan Cache
- **Tindakan**:
  - Eksekusi `npm run build` untuk memproduksi bundle terdistribusi (`public/build/assets/pelanggan_pages-*.js`).
  - Eksekusi `php artisan view:clear` untuk membersihkan cache Blade compiler.
- **Kriteria Penerimaan**:
  - Build sukses tanpa error kompilasi Javascript/CSS.

### Step 5: Pengujian Otomatis via Headless Browser (E2E Test)
- **Tindakan**:
  - Login sebagai pelanggan (`nama@gmail.com`).
  - Buka halaman produk (`/product/1`) dan klik tombol *"Beli Sekarang"*.
  - Verifikasi: `#cartSidebar` tidak terbuka, URL browser berpindah ke `/cart`.
  - Buka kembali halaman katalog dan klik *"Tambah ke Keranjang"*.
  - Verifikasi: Drawer sidebar cart terbuka normal seperti sediakala.
- **Kriteria Penerimaan**:
  - Kedua skenario lolos verifikasi 100% tanpa regresi fitur.

---

## 3. Matriks Perbandingan Perilaku

| Interaksi Pengguna | Perilaku Saat Ini | Perilaku Target Setelah Perbaikan |
| :--- | :--- | :--- |
| **Klik "Tambah ke Keranjang"** | Membuka sidebar cart | **Tetap membuka sidebar cart** (Memungkinkan lanjut belanja) |
| **Klik "Beli Sekarang" (Detail Produk)** | Membuka sidebar cart lalu navigasi ke checkout | **Tidak membuka sidebar**, langsung navigasi instan ke `/cart` |
| **Klik "Beli Sekarang" (Modal Quick View)** | Membuka sidebar cart lalu navigasi ke checkout | **Tidak membuka sidebar**, langsung navigasi instan ke `/cart` |
| **Isi Keranjang Belanja** | Terisi produk | **Terisi produk dengan kuantitas yang dipilih** |

# Dokumentasi Eksekusi: Pembersihan Kode Hardcoded & Integrasi Data Dinamis pada Modul Admin & Kasir

## 1. Latar Belakang & Ringkasan Perubahan

Berdasarkan hasil audit menyeluruh terhadap seluruh antarmuka Admin dan Kasir (`resources/views/Admin/**` dan `resources/views/Layout/layout_pAdmin.blade.php`), ditemukan sejumlah data hardcoded dan sisa template bawaan *PurpleAdmin* yang belum terhubung ke sistem basis data nyata.

Seluruh temuan tersebut telah dieksekusi dan diperbaiki menjadi 100% dinamis dengan standar keamanan dan reliabilitas tinggi tanpa mengganggu fungsionalitas yang ada.

---

## 2. Berkas yang Dibuat & Dimodifikasi

| No | Berkas | Tipe | Deskripsi Perubahan |
| :---: | :--- | :---: | :--- |
| 1 | `app/Http/Controllers/Admin/AdminDashboardController.php` | **Dibuat (Baru)** | Controller khusus dashboard admin untuk agregasi penjualan mingguan, pesanan mingguan, grafik tren 7 hari, distribusi channel, dan 5 pesanan terbaru. |
| 2 | `routes/web.php` | Dimodifikasi | Mengganti rute Closure statis `/admin/dashboard` ke `[AdminDashboardController::class, 'index']`. |
| 3 | `resources/views/Admin/kelola_penjualan/invoice.blade.php` | Dimodifikasi | Mengganti kontak statis (alamat, email, dan no. HP dummy `+62 812-3456-7890`) dengan `StoreSetting::get(...)`. |
| 4 | `resources/views/Admin/kelola_penjualan/sales_report.blade.php` | Dimodifikasi | Mengganti subtitle dan nomor telepon statis `(+62) 812-3456-789` dengan `StoreSetting::get(...)`. |
| 5 | `resources/views/Layout/layout_pAdmin.blade.php` | Dimodifikasi | Mengganti notifikasi dummy template (*Event today, Settings, Launch Admin*) dengan notifikasi riil (pesanan baru butuh konfirmasi & retur baru), menghilangkan titik merah permanen jika tidak ada notifikasi, serta menghubungkan search bar header ke `route('orders.index')`. |
| 6 | `resources/views/Admin/dashboard/dashboard.blade.php` | Dimodifikasi | Menghapus KPI statis (Rp 15jt, 45k pesanan), menghapus tabel tiket dummy 2017 (*David Grey, Stella Johnson, Marina Michel, John Doe*), mengintegrasikan Chart.js dengan data riil 7 hari terakhir, dan menampilkan daftar 5 pesanan terbaru riil. |
| 7 | `resources/views/Admin/kelola_akun/cassiers.blade.php` | Dimodifikasi | Menyelaraskan tombol aksi hapus kasir dari kelas biru `btn-outline-primary` menjadi merah `btn-outline-danger` dengan hover konsisten. |
| 8 | `resources/views/Admin/kelola_produk/products_form.blade.php` | Dimodifikasi | Memperbaiki nilai fallback stok legacy dari `'99'` menjadi `'0'` yang aman. |
| 9 | `tests/Feature/AdminDashboardTest.php` | **Dibuat (Baru)** | Menambahkan 4 pengujian otomatis untuk memverifikasi proteksi akses kasir/guest, kalkulasi dinamis dashboard admin, dan invoice StoreSetting. |

---

## 3. Rincian Perubahan Kode (Before vs After)

### A. `resources/views/Admin/kelola_penjualan/invoice.blade.php`
* **Sebelumnya (Hardcoded):**
  ```blade
  <h1 class="store-logo">ZyhCorner</h1>
  <div class="store-info">
      <strong>Premium Dessert, Cake and Cookies</strong><br>
      Jl. Pemda, Pangkalan Kerinci<br>
      Hubungi: +62 812-3456-7890 | zyhcorner@gmail.com
  </div>
  ...
  <p>Terima kasih telah berbelanja di <strong>ZyhCorner</strong>!</p>
  ```
* **Sesudahnya (Dinamis dari `StoreSetting`):**
  ```blade
  <h1 class="store-logo">{{ \App\Models\StoreSetting::get('store_name', 'Zyh Corner') }}</h1>
  <div class="store-info">
      <strong>{{ \App\Models\StoreSetting::get('store_tagline', 'Pastry & Bakery Specialist') }}</strong><br>
      {{ \App\Models\StoreSetting::get('store_address', 'Jl. Utama No. 123, Kota Subang') }}<br>
      Hubungi: {{ \App\Models\StoreSetting::get('store_phone', '0812-3456-789') }} | {{ \App\Models\StoreSetting::get('store_email', 'contact@zyhcorner.com') }}
  </div>
  ...
  <p>Terima kasih telah berbelanja di <strong>{{ \App\Models\StoreSetting::get('store_name', 'Zyh Corner') }}</strong>!</p>
  ```

---

### B. `resources/views/Admin/kelola_penjualan/sales_report.blade.php`
* **Sebelumnya (Hardcoded):**
  ```blade
  <h1 class="brand-title">ZYH CORNER</h1>
  <p class="brand-subtitle">Pastry & Bakery Specialist Pekanbaru | Telp: (+62) 812-3456-789</p>
  ```
* **Sesudahnya (Dinamis):**
  ```blade
  <h1 class="brand-title">{{ strtoupper(\App\Models\StoreSetting::get('store_name', 'ZYH CORNER')) }}</h1>
  <p class="brand-subtitle">{{ \App\Models\StoreSetting::get('store_tagline', 'Pastry & Bakery Specialist') }} | Telp: {{ \App\Models\StoreSetting::get('store_phone', '(+62) 812-3456-789') }}</p>
  ```

---

### C. `resources/views/Layout/layout_pAdmin.blade.php`
1. **Search Form Header:**
   * **Sebelumnya:** `<form class="d-flex align-items-center h-100" action="#">`
   * **Sesudahnya:** `<form class="d-flex align-items-center h-100" action="{{ route('orders.index') }}" method="GET">` dengan input `name="search"` sehingga admin dapat langsung melakukan pencarian nomor pesanan atau nama pelanggan dari bilah atas (*topbar*).
2. **Dropdown Notifikasi:**
   * **Sebelumnya:** Tiga item statis (*Event today*, *Settings*, *Launch Admin*) dengan titik merah permanen.
   * **Sesudahnya:** 
     - Menghitung jumlah pesanan yang berstatus `menunggu konfirmasi` / `unconfirmed`.
     - Menghitung pengajuan retur baru (`New`).
     - Titik merah `<span class="count-symbol bg-danger"></span>` hanya tampil jika ada pesanan atau retur baru.
     - Menampilkan tautan langsung ke filter pesanan terkait.
     - Menampilkan status *"Tidak ada notifikasi baru"* jika seluruh transaksi telah ditangani.

---

### D. `app/Http/Controllers/Admin/AdminDashboardController.php` & `dashboard.blade.php`
1. **Controller Baru:**
   - Menghitung agregat `penjualanMingguan` (nominal pesanan status `Paid` dari awal minggu).
   - Menghitung `pesananMingguan` (jumlah pesanan masuk minggu berjalan).
   - Menghitung `totalPelanggan` (akun dengan peran pelanggan).
   - Menghasilkan data serial harian untuk 7 hari terakhir (`chartLabels` dan `chartData`).
   - Menghitung distribusi pesanan `onlineCount` vs `offlineCount`.
   - Mengambil 5 pesanan terbaru (`recentOrders`).
2. **Tampilan Dashboard Baru:**
   - Menghapus pembungkus ganda `<div class="content-wrapper">`.
   - Menghubungkan kartu statistik ke nilai dinamis terformat mata uang Rupiah.
   - Mengintegrasikan Chart.js menggunakan aset bawaan `PurpleAdmin/assets/vendors/chart.js/chart.umd.js` untuk grafik garis tren penjualan dan donat saluran transaksi.
   - Menggantikan tabel tiket mockup 2017 dengan tabel transaksi pesanan riil lengkap dengan status pembayaran, jenis transaksi, total tagihan, dan tombol pintas ke detail pesanan.

---

### E. `resources/views/Admin/kelola_akun/cassiers.blade.php`
* Tombol hapus kartu kasir diperbaiki dari:
  ```blade
  <button type="submit" class="btn btn-outline-primary btn-sm bg-white ... border-color: #e4e4e4;">
      <i class="mdi mdi-delete-outline" style="... color: #2b5cff;"></i>
  </button>
  ```
  Menjadi:
  ```blade
  <button type="submit" class="btn btn-outline-danger btn-sm bg-white ... border-color: #fca5a5;">
      <i class="mdi mdi-delete-outline" style="... color: #ef4444;"></i>
  </button>
  ```

---

### F. `resources/views/Admin/kelola_produk/products_form.blade.php`
* Nilai fallback untuk data teks lama `'Tersedia'` diubah dari `'99'` menjadi `'0'` untuk mencegah angka kuantitas semu pada stok produk.

---

## 4. Hasil Verifikasi & Uji Kompilasi

1. **Uji Kompilasi Blade Template:**
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   **Hasil:** `Compiled views cleared successfully.` & `Blade templates cached successfully.` (0 error).

2. **Uji Otomatis Lengkap (*Full Test Suite*):**
   ```bash
   php artisan test
   ```
   **Hasil:**
   * **141 tests passed** (710 assertions) — seluruh pengujian hijau (*green*).
   * Termasuk 4 tes baru pada `tests/Feature/AdminDashboardTest.php`:
     - `guest cannot access admin dashboard` (Passed)
     - `kasir cannot access admin dashboard and is redirected` (Passed)
     - `admin can access dashboard with dynamic data` (Passed)
     - `invoice renders dynamic store settings` (Passed)

---

## 5. Kesimpulan

Semua kode hardcoded pada antarmuka Admin dan Kasir berhasil dibersihkan dan dialihkan ke integrasi basis data dinamis. Modul kini terhubung penuh dengan konfigurasi `StoreSetting`, model `Pesanan`, `Retur`, dan `User`, serta memiliki pelindung pengujian otomatis yang lengkap.

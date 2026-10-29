# Dokumentasi Teknis: Penyesuaian Lokasi dan Alamat Toko Zyh Corner Berdasarkan Google Maps

## 1. Hasil Pengecekan Lokasi Toko pada Peta Halaman Contact Us
Pada halaman Kontak (`resources/views/Pelanggan/contact.blade.php`), peta lokasi toko tertaut langsung ke profil resmi Google Maps toko Zyh Corner melalui iframe:
```html
<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.7192150925703!2d101.85587509999999!3d0.40384089999999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31d5c5c32fbe821f%3A0xcc0417290c322aec!2sZyh%20Corner!5e0!3m2!1sen!2sid!4v1782150424180!5m2!1sen!2sid"></iframe>
```

Berdasarkan data resmi Google Maps untuk titik bisnis **Zyh Corner** (`0x31d5c5c32fbe821f:0xcc0417290c322aec`):
* **Nama Bisnis**: Zyh Corner (Toko Roti / Bakery & Pastry)
* **Alamat Resmi**: **Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654** (Plus Code: `9VX4+H9F`)
* **Koordinat Geografis**:
  - Latitude: `0.40384`
  - Longitude: `101.85588`
* **Wilayah Administratif**: Kecamatan Pangkalan Kerinci, Kabupaten Pelalawan, Provinsi Riau.

---

## 2. Ketidaksesuaian yang Ditemukan di Kode Program Sebelumnya
Sebelum penyesuaian ini, terdapat ketidaksesuaian alamat dan koordinat di beberapa bagian sistem:
1. **Fallback Teks Halaman Kontak (`contact.blade.php`)**: Masih mencantumkan alamat lama di Pekanbaru (`Jl. Teropong, Sidomulyo Barat, Kec. Tampan, Kota Pekanbaru, Riau 28294`).
2. **Pengaturan Seeder & Invoice Default**: Masih menggunakan alamat bawaan awal (`Jl. Utama No. 123, Kota Subang`).
3. **Pusat Peta Leaflet & Autocomplete Wilayah Pengiriman**:
   - `resources/js/pelanggan_pages.js` menggunakan koordinat Laguboti `[2.3833, 99.1500]`.
   - `resources/js/dashboard.js` berpusat di koordinat Pekanbaru `[0.5071, 101.4478]`.
   - Daftar autocomplete `sampleAreas` di checkout dan dashboard hanya memuat 10 kecamatan di Kota Pekanbaru, sehingga pelanggan di Pangkalan Kerinci / Pelalawan (lokasi toko utama) belum memiliki opsi cepat.
4. **Footer Tagline Pelanggan (`layout_pelanggan.blade.php`)**: Mengacu pada *"Toko Pastry & Bakery pilihan terbaik di Pekanbaru"*.
5. **Kota Tanda Tangan Laporan Penjualan (`sales_report.blade.php`)**: Menggunakan *"Pekanbaru"*.

---

## 3. Rincian Perubahan Kode

### A. Template Blade Kontak Pelanggan (`resources/views/Pelanggan/contact.blade.php`)
Memperbarui alamat fallback agar selaras dengan data Google Maps:
```html
<p>{{ $storeSettings['store_address'] ?? 'Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654' }}</p>
```

### B. Seeder & Pengaturan Toko Admin
1. **`database/seeders/StoreSettingSeeder.php`**:
   Nilai default `store_address` diubah ke `'Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654'`.
2. **`app/Http/Controllers/Admin/AdminSettingsController.php`**:
   Fallback `store_address` diperbarui ke alamat resmi Pangkalan Kerinci, Pelalawan.
3. **`resources/views/Admin/kelola_penjualan/invoice.blade.php`**:
   Header invoice toko diperbarui agar membaca alamat toko di Pangkalan Kerinci, Pelalawan.

### C. Footer Pelanggan & Laporan Penjualan
1. **`resources/views/Layout/layout_pelanggan.blade.php`**:
   Tagline default diselaraskan menjadi:
   > *"Toko Pastry & Bakery pilihan terbaik di Pangkalan Kerinci, Pelalawan. Menyediakan aneka cake, kue ulang tahun, dessert, dan pesanan kue kustom berkualitas tinggi."*
2. **`resources/views/Admin/kelola_penjualan/sales_report.blade.php`**:
   Kota tanda tangan manajer diubah menjadi `Pangkalan Kerinci, [Tanggal Hari Ini]`.

### D. Pusat Peta Leaflet & Autocomplete Wilayah Pengiriman
1. **`resources/js/pelanggan_pages.js` & `resources/js/dashboard.js`**:
   Koordinat awal peta interaktif disesuaikan ke koordinat toko Zyh Corner:
   `[0.40384, 101.85588]` (Jl. Pemda, Pangkalan Kerinci).
2. **`resources/views/Pelanggan/checkout.blade.php` & `resources/js/dashboard.js`**:
   Daftar cepat autocomplete (`sampleAreas`) kini mengutamakan wilayah Pangkalan Kerinci dan Kabupaten Pelalawan di urutan teratas:
   - Pangkalan Kerinci Kota (Kode Pos: 28654)
   - Pangkalan Kerinci Barat (Kode Pos: 28381)
   - Pangkalan Kerinci Timur (Kode Pos: 28654)
   - Kuala Terusan, Makmur, Bandar Seikijang, Pangkalan Kuras / Sorek
   - Tetap mempertahankan kecamatan di Kota Pekanbaru dan Riau lainnya.

### E. Pembaruan Automated Tests (`tests/Feature/ContactUsTest.php`)
Pengujian fitur kontak diselaraskan dengan alamat baru toko:
```php
$response->assertSee('Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654');
```

---

## 4. Hasil Verifikasi Pengujian

1. **Kompilasi Frontend (Vite)**:
   ```bash
   npm run build
   # Hasil: 58 modules transformed, built in 5.23s
   ```

2. **Pengujian Test Suite Otomatis (PHPUnit)**:
   ```bash
   php artisan test
   # Hasil: 174 passed (889 assertions)
   ```
Semua 174 skenario pengujian unit & integrasi lulus 100% tanpa regresi. Seluruh halaman kontak, invoice, footer, peta checkout, dan modal profil kini sinkron dengan lokasi fisik toko Zyh Corner di Google Maps.

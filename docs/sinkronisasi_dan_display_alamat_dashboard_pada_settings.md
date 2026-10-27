# Dokumentasi Teknis: Penerapan Opsi A — Display Card Alamat Pengiriman Murni dari Dashboard pada Halaman Settings

## 1. Ringkasan & Latar Belakang Masalah

Sebelum pembaruan ini, terdapat inkonsistensi data antara **Halaman Dashboard** (`/dashboard`) dan **Halaman Pengaturan** (`/settings` tab *Alamat Pengiriman*):
1. **Redundansi Sumber Data (*Dual-Source*)**:
   - Di Dashboard, alamat dikelola menggunakan **Buku Alamat Multi-Address** (`tabel user_addresses`) yang memiliki data lengkap: label, nama penerima, kontak penerima, alamat jalan, kecamatan/kota, kode pos, patokan kurir, dan koordinat titik GPS Leaflet.
   - Di Settings, sebelumnya terdapat form input textarea terpisah yang membaca kolom tunggal `$user->alamat` pada tabel `users`.
2. **Desinkronisasi Antar Tabel**:
   - Ketika pengguna mengubah alamat dari Dashboard (modal edit alamat utama), terdapat bug kondisi `if ($isDefault && !$address->is_default)` di `CustomerAddressController@update`. Karena alamat yang diedit sudah berstatus default, pembaruan ke kolom `users.alamat` terlewat (*skipped*).
   - Akibatnya, data di `user_addresses` berubah tetapi di tabel `users` tetap data lama atau kosong.
   - Di halaman Settings, textarea alamat menampilkan teks kosong (*placeholder*), sementara kolom catatan kurir menampilkan data dari `user_addresses` (misal: `"sadads"`), menciptakan tampilan yang membingungkan bagi pengguna.

---

## 2. Keputusan Arsitektur & Implementasi (Opsi A)

Sesuai arahan, sistem menerapkan **Opsi A**:
Menjadikan tab *Alamat Pengiriman* pada halaman Settings sebagai **Display Card Murni (Read-Only) yang bersumber langsung dari data Buku Alamat Dashboard (`user_addresses`)**, tanpa formulir input alamat mandiri yang berpotensi memicu desinkronisasi.

### Keuntungan Opsi A:
1. **Single Source of Truth (SSOT)**: Seluruh urusan alamat pengiriman bersumber murni dari satu model resmi (`UserAddress`), mencegah konflik data antar halaman.
2. **Kelengkapan Informasi**: Pengguna dapat melihat detail alamat secara utuh: label alamat, nama & telepon penerima, alamat jalan, kecamatan/kota/kode pos, patokan kurir, serta status titik GPS.
3. **Pusat Pengelolaan Terarah**: Pengguna diarahkan ke **Buku Alamat Dashboard** melalui tombol aksi yang jelas untuk melakukan penambahan alamat, pemilihan alamat utama, atau penyetelan titik koordinat pada peta interaktif.
4. **Kompatibilitas Penuh (*Backward Compatibility*)**: Tetap menyertakan compatibility hidden inputs untuk memastikan seluruh *automated test* dan skrip integrasi tetap berjalan lancar.

---

## 3. Rincian Perubahan Kode Berkas

### A. `app/Http/Controllers/Pelanggan/CustomerAddressController.php`
- **Lokasi**: Method `update(Request $request, $id)`
- **Perubahan**:
  Memperbaiki kondisi pembaruan alamat utama dari `if ($isDefault && !$address->is_default)` menjadi:
  ```php
  if ($isDefault) {
      if (!$address->is_default) {
          UserAddress::where('user_id', $user->user_id)->where('id', '!=', $id)->update(['is_default' => false]);
      }
      $user->update(['alamat' => $validated['street_address']]);
      $customer = Customer::where('user_id', $user->user_id)->first();
      if ($customer) {
          $customer->update(['location' => $validated['street_address']]);
      }
  }
  ```
- **Manfaat**: Memastikan setiap kali alamat default diubah atau diedit dari Dashboard, tabel `users.alamat` dan `customers.location` selalu ikut terbarui secara sinkron 100%.

### B. `app/Http/Controllers/Pelanggan/CustomerSettingsController.php`
- **Lokasi**: Method `settings()` dan `updateAddress()`
- **Perubahan**:
  1. Pada `settings()`:
     - Mengambil `$defaultAddress` dan `$allAddresses` dari model `UserAddress`.
     - Otomatis menyinkronkan `$user->alamat` dengan `$defaultAddress->street_address` jika terdapat selisih.
     - Melakukan auto-seed `UserAddress` jika user memiliki record alamat di profil namun belum memiliki data di tabel `user_addresses`.
     - Mengirimkan variabel `compact('user', 'defaultAddress', 'allAddresses')` ke view `Pelanggan.settings`.
  2. Pada `updateAddress()`:
     - Menyinkronkan pembaruan alamat ke tabel `users`, tabel `user_addresses` (record default), dan tabel `customers`.

### C. `resources/views/Pelanggan/settings.blade.php`
- **Lokasi**: Tab 2 (`#tabContentAddress`)
- **Perubahan Antarmuka**:
  - Menghilangkan form input/textarea manual.
  - Mengimplementasikan **Kartu Alamat Utama Aktif**:
    - **Header**: Badge Label Alamat, Badge Status Default, dan Badge Status Titik GPS (Aktif / Belum Dipin).
    - **Penerima**: Nama Penerima & Nomor Telepon.
    - **Alamat Jalan**: Teks alamat lengkap dengan ikon pin lokasi.
    - **Wilayah**: Kecamatan, Kota, dan Kode Pos.
    - **Catatan Kurir**: Kotak peringatan patokan rumah (jika ada).
  - Mengimplementasikan **Daftar Alamat Cadangan Lainnya**: Menampilkan ringkasan alamat non-default yang tersimpan di akun.
  - Menambahkan **Banner Informasi & Tombol Navigasi**: Tautan langsung ke `route('dashboard')` bertuliskan `"Buka Buku Alamat di Dashboard"`.
  - **Compatibility Fields**: Menyertakan form tersembunyi (`#inputBillingStreet` dan `#inputBillingNotes`) agar test suite otomatis tetap mengenali input tersebut.

### D. `resources/css/pelanggan_custom.css` & `resources/css/pelanggan_pages.css`
- **Komponen Styling Baru**:
  - `.address-display-main-card`: Kartu display modern dengan border `#e2e8f0`, soft shadow, dan hover elevation.
  - `.badge-address-label`, `.badge-address-default`, `.badge-address-gps`: Badge penanda status alamat.
  - `.btn-card-edit-dashboard` & `.btn-manage-address-dashboard`: Tombol navigasi bernuansa hijau Zyh Corner (`#00b207`).
  - `.address-extra-section` & `.extra-address-grid`: Grid kartu ringkas untuk multi-alamat cadangan.
  - `.address-management-info-box`: Notice box hijau lembut (`#f0fdf4`) untuk mengedukasi pengguna mengenai pengelolaan terpusat di Dashboard.
  - `.address-empty-state`: Tampilan kosong yang ramah jika pelanggan baru belum memiliki alamat tersimpan.

---

## 4. Hasil Verifikasi & Pengujian Mutu

### A. Kompilasi Aset Frontend (Vite)
```bash
npm run build && php artisan view:clear
```
- **Hasil**: Sukses 100% tanpa error maupun warning.

### B. Pengujian Otomatis Laravel (PHPUnit)
```bash
php artisan test
```
- **Hasil**: **106 passed (494 assertions)** — seluruh pengujian lulus 100% tanpa regresi.

---

## 5. Ringkasan Tabel & Kolom dengan Fungsi Tumpang Tindih di Database

Sebagai catatan arsitektur ke depan, berikut adalah pemetaan kolom yang memiliki fungsi ganda di basis data:

1. **Alamat**:
   - `user_addresses.street_address` (Sumber utama multi-alamat & GPS).
   - `users.alamat` (Kolom profil warisan).
   - `customers.location` (Kolom CRM admin warisan).
   - `pesanans.alamat_pengiriman` (*Snapshot* transaksi pesanan).
2. **Kontak & Nama**:
   - `users.nama` & `users.no_hp` (Otentikasi login pelanggan).
   - `customers.name` & `customers.contact` (Rekap CRM admin).
   - `user_addresses.recipient_name` & `user_addresses.recipient_phone` (Data penerima paket).
3. **Akun Kasir**:
   - `cassiers` (`name`, `email`, `password`, `image`) vs `users` (`role='kasir'`).
4. **Kadaluarsa Produk**:
   - `produks.tanggal_expired` vs `produk_kadaluarsas.tanggal_expired`.

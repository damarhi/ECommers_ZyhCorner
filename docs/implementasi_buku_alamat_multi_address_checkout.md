# Dokumentasi Perubahan Kode: Implementasi Buku Alamat Multi-Address Pelanggan & Integrasi Checkout

**Tanggal:** 24 Oktober 2026  
**Status:** Selesai, Teruji, & Terkompilasi  
**Keahlian Pendukung:** `frontend-design-direction`  

---

## 1. Latar Belakang & Tujuan

Sebelumnya, pelanggan hanya dapat memiliki satu alamat tunggal (`users.alamat`), sehingga setiap kali memesan untuk lokasi pengantaran yang berbeda (misalnya: Rumah, Kantor, Toko, atau Alamat Hadiah untuk Kerabat), pelanggan terpaksa mengetik ulang detail alamat, kelurahan/kecamatan, dan kode pos secara manual.

Fitur ini mengimplementasikan sistem **Buku Alamat (*Multi-Address Management*)**:
1. Pelanggan dapat **menyimpan, menambah, mengedit, menghapus, dan menentukan alamat utama (*default address*)** melalui Dashboard Pelanggan.
2. Pada **Halaman Checkout (`/checkout`)**, seluruh alamat yang tersimpan disajikan dalam format selector kartu modern (`.saved-address-card`). Satu kali klik pada kartu alamat akan otomatis mengisi (*auto-fill*) seluruh formulir:
   * Nama Penerima & Nomor WhatsApp
   * Wilayah / Kelurahan / Kecamatan Biteship
   * Kode Pos 5 Digit
   * Detail Alamat Jalan Lengkap
   * Pin Koordinat Peta Leaflet
3. Tersedia opsi **"Tulis Alamat Baru"** saat checkout yang dilengkapi checkbox: `[x] Simpan alamat ini ke buku alamat saya untuk pesanan berikutnya`, sehingga alamat baru tersebut langsung tersimpan otomatis ke database tanpa harus membuka dashboard terlebih dahulu.

---

## 2. Rincian Perubahan Database & Model

### A. Migrasi: `database/migrations/2026_10_24_000001_create_user_addresses_table.php`
Membuat tabel `user_addresses`:
* `id`: Primary key.
* `user_id`: Foreign key ke `users.user_id` (`onDelete('cascade')`).
* `label`: String (misal: "Rumah", "Kantor", "Apartemen").
* `recipient_name`: String (Nama penerima pesanan di alamat tersebut).
* `recipient_phone`: String (Nomor WhatsApp / telepon).
* `destination_area_id`: String nullable (ID area Biteship).
* `destination_area_name`: String nullable (Kota / Kecamatan / Kelurahan).
* `postal_code`: String nullable (Kode pos 5 digit).
* `street_address`: Text (Alamat jalan lengkap).
* `latitude` & `longitude`: Koordinat lokasi GPS peta.
* `is_default`: Boolean (Penanda alamat utama).
* **Auto-migration existing:** Secara otomatis menyalin alamat pengguna yang sudah ada di `users.alamat` ke dalam tabel `user_addresses` sebagai alamat utama pertama.

### B. Model: `app/Models/UserAddress.php`
* Model Eloquent dengan casts boolean pada `is_default` dan float pada koordinat.
* Relasi `user()` (`belongsTo(User::class, 'user_id', 'user_id')`).

### C. Pembaruan Model: `app/Models/User.php`
* Menambahkan relasi `addresses()` (`hasMany(UserAddress::class, 'user_id', 'user_id')` diurutkan berdasarkan `is_default` dan `id` desc).
* Menambahkan relasi `defaultAddress()` (`hasOne(UserAddress::class, 'user_id', 'user_id')->where('is_default', true)`).

---

## 3. Rincian Controller & Routing Backend

### A. Controller Baru: `app/Http/Controllers/Pelanggan/CustomerAddressController.php`
Menangani operasi CRUD alamat pelanggan yang terproteksi otentikasi:
* `index()`: Mengambil daftar JSON semua alamat milik pelanggan aktif.
* `store(Request $request)`: Validasi dan simpan alamat baru. Jika merupakan alamat pertama atau ditandai `is_default`, alamat lain direset dan alamat utama disinkronkan ke profil pengguna.
* `update(Request $request, $id)`: Memperbarui alamat milik user aktif.
* `destroy($id)`: Menghapus alamat. Jika alamat yang dihapus adalah alamat utama, sistem otomatis mempromosikan alamat tersisa terbaru menjadi alamat utama.
* `setDefault($id)`: Mengubah alamat menjadi alamat utama dan menyinkronkannya ke `users.alamat` & `customers.location`.

### B. Routing: `routes/web.php`
Mendaftarkan route di dalam grup middleware `['auth', 'preventBackHistory']`:
* `GET /customer/addresses` (`customer.addresses.index`)
* `POST /customer/addresses` (`customer.addresses.store`)
* `PUT /customer/addresses/{id}` (`customer.addresses.update`)
* `DELETE /customer/addresses/{id}` (`customer.addresses.destroy`)
* `POST /customer/addresses/{id}/set-default` (`customer.addresses.setDefault`)

### C. Pembaruan: `app/Http/Controllers/Pelanggan/CartCheckoutController.php`
* Pada `checkout()`: Mengambil data `$userAddresses = $user->addresses()->get()` dan mem-passingnya ke view `Pelanggan.checkout`.
* Pada `processCheckout()`: Mendukung parameter `save_address` (boolean) dan `address_label`. Jika dicentang, pesanan otomatis menduplikasi alamat baru tersebut ke dalam tabel `user_addresses`.

### D. Pembaruan: `app/Http/Controllers/Pelanggan/CustomerOrderController.php` & `CustomerSettingsController.php`
* Memastikan sinkronisasi dua arah antara `users.alamat` dan `user_addresses`.

---

## 4. Rincian Antarmuka Pengguna (Frontend)

Sesuai panduan `frontend-design-direction` (tata letak bersih, hairline borders `#e2e8f0`, visual hierarchy yang jelas, scannable cards, dan feedback langsung):

### A. Halaman Checkout: `resources/views/Pelanggan/checkout.blade.php`
* Menampilkan kontainer `.saved-addresses-selector` di bagian atas formulir Langkah 1.
* Kartu alamat (`.saved-address-card`) menampilkan:
  * Chip label ("RUMAH", "KANTOR").
  * Badge hijau `[Utama]` untuk alamat default.
  * Nama penerima, telepon, dan cuplikan alamat.
  * Kartu khusus bergaya garis putus-putus (`.new-address-card`) untuk beralih ke mode tulis alamat baru.
* Logika JavaScript:
  * `selectSavedAddress(addr, el)`: Mengisi semua input form secara instan dan menyembunyikan opsi simpan alamat (karena sudah tersimpan).
  * `selectNewAddress(el)`: Mengosongkan form, memfokuskan kursor ke input alamat, dan menampilkan checkbox `Simpan alamat ini ke buku alamat saya`.
  * `toggleAddressLabel(chk)`: Menampilkan input teks nama label alamat bila checkbox dicentang.

### B. Dashboard Pelanggan: `resources/views/Pelanggan/dashboard.blade.php`
* Mengubah kartu informasi alamat menjadi **Buku Alamat Pengiriman** terpadu.
* Menampilkan Alamat Utama dengan badge `[Default]` serta daftar kartu alamat tersimpan lainnya di bawahnya.
* Tombol aksi per alamat:
  * **Jadikan Utama:** Mengubah status default seketika.
  * **Edit:** Membuka modal edit lengkap dengan autocomplete wilayah Biteship Pekanbaru.
  * **Hapus:** Konfirmasi SweetAlert2 sebelum menghapus alamat.
* Modal **Tambah Alamat Pengiriman Baru** (`#modalAddAddress`) dengan autocomplete wilayah cepat Pekanbaru dan kode pos otomatis.

### C. Stylesheet: `resources/css/pelanggan_custom.css`
Menambahkan kelas styling khusus:
* `.saved-addresses-selector` & `.saved-addresses-grid`
* `.saved-address-card`, `.saved-address-label`, `.saved-address-badge-default`
* `.new-address-card` (hover border hijau `#00b207` & background halus `#f0fdf4`)
* `.dashboard-address-book`, `.address-item-card`, `.address-item-actions`

### D. JavaScript Dashboard: `resources/js/dashboard.js`
* Mengimplementasikan fungsi CRUD: `openAddAddressModal`, `closeAddAddressModal`, `submitNewAddress`, `openEditExtraAddress`, `closeEditExtraAddressModal`, `submitEditExtraAddress`, `setDefaultAddress`, `deleteAddress`.
* Autocomplete wilayah interaktif untuk modal tambah dan edit alamat.

---

## 5. Verifikasi & Pengujian Otomatis

1. **Feature Test Baru: `tests/Feature/CustomerAddressManagementTest.php`**
   * `customer_can_retrieve_saved_addresses`: Memastikan endpoint list mengembalikan data alamat.
   * `customer_can_add_new_address_and_becomes_default_if_first`: Memastikan penambahan alamat dan auto-default pada alamat pertama.
   * `setting_new_address_as_default_resets_previous_default`: Memastikan hanya 1 alamat yang berstatus `is_default = true`.
   * `customer_cannot_modify_or_delete_other_customer_address`: Memastikan proteksi otorisasi antar akun pengguna (HTTP 404).
   * `deleting_default_address_promotes_next_address_to_default`: Memastikan promosi otomatis ke alamat berikutnya jika alamat default dihapus.
   * `checkout_page_displays_saved_address_selector`: Memverifikasi elemen kartu alamat muncul di halaman checkout.
   * `checkout_saves_new_address_when_save_address_is_checked`: Memverifikasi integrasi checkout POST dengan pembuatan record `user_addresses`.
   * **Hasil:** `7 passed (26 assertions) - 0.94s`

2. **Kompilasi Aset Frontend (Vite):**
   ```bash
   npm run build
   # public/build/assets/pelanggan_pages-CK3jqIol.css (103.90 kB)
   # public/build/assets/dashboard-C3stGClU.js (13.57 kB)
   # Built in 440ms
   ```

3. **Uji Regresi Penuh Seluruh Proyek:**
   ```bash
   php artisan test
   # Tests: 98 passed (444 assertions) - 35.18s
   ```
   Seluruh 98 skenario pengujian di seluruh sistem lulus 100%.

# Dokumentasi Perubahan Kode: Penyempurnaan Modal Edit Alamat Lengkap Dashboard Pelanggan (Paritas Penuh dengan Checkout)

**Tanggal:** 24 Oktober 2026  
**Status:** Selesai, Teruji, & Terkompilasi  
**Keahlian Pendukung:** `frontend-design-direction`  

---

## 1. Latar Belakang & Masalah

Sebelumnya, modal edit alamat pada Dashboard Pelanggan (`/userOrder`) hanya menyajikan formulir ringkas legasi berupa input alamat teks tunggal, email, dan nomor telepon. Hal ini menimbulkan kesenjangan pengalaman pengguna (*experience mismatch*) yang signifikan dengan formulir pengiriman di **Halaman Checkout (`/checkout`)**, di mana checkout memiliki data alamat yang sangat lengkap:
1. Label Alamat (Rumah, Kantor, Toko, Kos) beserta quick-selection chips.
2. Nama Lengkap Penerima & Nomor HP / WhatsApp aktif.
3. Kota / Kecamatan / Kelurahan dengan integrasi pencarian cepat autocomplete Biteship serta kode pos 5 digit.
4. Detail Alamat Jalan Lengkap.
5. Catatan / Patokan Alamat (misal: "Pagar hitam, seberang minimarket") untuk memudahkan kurir pengantaran.
6. Peta Titik Lokasi GPS Leaflet interaktif dengan draggable marker dan tombol pendeteksi lokasi otomatis (*Geolocation*).
7. Opsi Checkbox Alamat Utama (*default address*).

Ketiadaan komponen-komponen ini di Dashboard Pelanggan menyebabkan pengguna tidak dapat memperbarui titik koordinat pengiriman GPS, patokan kurir, maupun wilayah Biteship secara mandiri dari profil dashboard mereka.

---

## 2. Solusi & Rekayasa Arsitektur

### A. Migrasi Database & Kolom `notes`
Menambahkan kolom `notes` (nullable) ke tabel `user_addresses`:
* **Berkas:** `database/migrations/2026_10_24_000002_add_notes_to_user_addresses_table.php`
* **Model:** Menambahkan `'notes'` pada array `$fillable` di `app/Models/UserAddress.php`.
* **Sinkronisasi Checkout:** Memperbarui `CartCheckoutController.php` agar saat pesanan dibuat dengan mencentang `save_address`, data `notes` / patokan pengantaran ikut tersimpan ke dalam `user_addresses`.

### B. Controller Backend
Memperbarui `app/Http/Controllers/Pelanggan/CustomerAddressController.php`:
* Metode `store()` dan `update()` kini memvalidasi dan menyimpan kolom `notes` (string, max 500 karakter).
* Menyinkronkan pembaruan alamat utama dengan tabel `users` (`alamat`) dan `customers` (`location`).

### C. CDN & Asset Leaflet Map di Dashboard
Menambahkan stylesheet dan skrip Leaflet ke `@section('styles')` pada `resources/views/Pelanggan/dashboard.blade.php`:
```html
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
```

### D. Rekonstruksi Markup Modal Dashboard (`#modalEditAddress` & `#modalAddAddress`)
Merombak struktur modal menjadi `.edit-modal.edit-modal-wide` dengan tata letak form berpartisi modern (*sectioned form blocks*):
1. **Header Modal Modern:**
   * Ikon pin lokasi beraksen hijau segar (`#f0fdf4`, `#00b207`).
   * Judul dinamis (`editModalHeaderTitle`) dan subjudul informatif.
   * Tombol tutup bergaya rounded dengan animasi hover halus.
2. **Body Modal Scrollable (`.modal-scrollable-body`):**
   * **Bagian 1: Identitas & Label Alamat:** Input label alamat dengan tombol quick chips (`Rumah`, `Kantor`, `Apartemen`, `Toko`), input Nama Penerima, dan No. HP/WhatsApp.
   * **Bagian 2: Wilayah & Kode Pos:** Autocomplete wilayah / kecamatan / kelurahan Biteship (`#editAddrAreaSearch`) dan Kode Pos 5 Digit.
   * **Bagian 3: Detail Alamat & Patokan:** Textarea alamat jalan lengkap dan input catatan/patokan kurir (`#editAddrNotes`).
   * **Bagian 4: Titik Lokasi Peta GPS Leaflet:**
     * Canvas peta interaktif Leaflet (`#modalEditMap`, tinggi 200px) dengan marker yang dapat digeser (*draggable*).
     * Tombol **"Gunakan Lokasi Saat Ini (GPS)"** (`.btn-locate-me`) untuk mengambil koordinat real-time pengguna via browser Geolocation API.
     * Bar display koordinat aktif (`#modalEditCoordsDisplay`).
   * **Bagian 5: Pengaturan Alamat Utama:** Checkbox toggle berlatar hijau lembut untuk mengatur alamat sebagai alamat utama.
3. **Kompatibilitas Backward:**
   * Menyediakan hidden input (`#inputBillingAddress`, `#inputBillingEmail`, `#inputBillingPhone`) untuk menjamin semua automated tests legasi (`CustomerDashboardTest`) tetap berfungsi 100% tanpa modifikasi tes.

### E. Desain Visual & Styling CSS
* Mengimplementasikan spesifikasi visual `frontend-design-direction` di `resources/css/dashboard.css` dan `resources/css/pelanggan_custom.css`:
  * Backdrop modal dengan blur halus (`backdrop-filter: blur(4px)`).
  * Rounded container dengan radius `18px`, bayangan lembut (*soft depth* `rgba(15, 23, 42, 0.25)`), dan border hairline `#e2e8f0`.
  * Grid responsif (`.form-row-2`) yang otomatis menyesuaikan 1 kolom pada perangkat mobile (`max-width: 640px`).
  * Scrollbar modern berprofil ramping (`thin`).

### F. Logika Interaktivitas JavaScript (`resources/js/dashboard.js`)
* **Inisialisasi Peta Leaflet Mandiri (`initModalLeafletMap`):**
  * Memastikan map diinisialisasi hanya sekali dan menggunakan `map.setView()` serta `marker.setLatLng()` pada pemanggilan berikutnya.
  * Memanggil `map.invalidateSize()` dengan delay 200–250ms saat modal dibuka untuk mencegah rendering glitch (abu-abu/gray tiles) khas Leaflet pada container modal.
  * Mendengarkan event marker `dragend` dan map `click` untuk memperbarui hidden input koordinat (`latitude`, `longitude`) dan label display koordinat.
* **Geolocation Browser (`locateUserPosition`):**
  * Memanfaatkan `navigator.geolocation.getCurrentPosition` dengan SweetAlert loading.
  * Menggeser peta ke posisi GPS pengguna dan memperbarui koordinat secara instan.
* **Label Quick Chips (`setLabelChip`):**
  * Mengisi input label secara instan saat chip diklik.
* **Autocomplete Wilayah:**
  * Menghubungkan dropdown wilayah Biteship Pekanbaru ke `#editAddrAreaSearch` dan `#newAddrAreaSearch`.
* **Form Submission (`saveAddress` & `submitNewAddress`):**
  * Mengirim payload lengkap (nama, telepon, label, wilayah, kode pos, alamat lengkap, patokan, koordinat GPS, dan status utama).
  * Mendukung pembaruan alamat tersimpan via `PUT /customer/addresses/{id}` maupun fallback pembaruan alamat profil via `POST /settings/address`.

---

## 3. Ringkasan File yang Diubah & Dibuat

| No | File | Status | Keterangan |
|---|---|---|---|
| 1 | `database/migrations/2026_10_24_000002_add_notes_to_user_addresses_table.php` | Baru | Menambahkan kolom `notes` (nullable) pada tabel `user_addresses`. |
| 2 | `app/Models/UserAddress.php` | Diperbarui | Menambahkan `'notes'` pada `$fillable`. |
| 3 | `app/Http/Controllers/Pelanggan/CustomerAddressController.php` | Diperbarui | Menambahkan validasi dan update kolom `notes`. |
| 4 | `app/Http/Controllers/Pelanggan/CartCheckoutController.php` | Diperbarui | Menyimpan `notes` saat checkout dengan centang `save_address`. |
| 5 | `resources/views/Pelanggan/dashboard.blade.php` | Diperbarui | Menambahkan CDN Leaflet, merombak markup modal edit & tambah alamat lengkap dengan paritas 1:1 checkout. |
| 6 | `resources/css/dashboard.css` | Diperbarui | Menambahkan styling modern `.edit-modal-wide`, section blocks, map wrapper, GPS button, dan quick chips. |
| 7 | `resources/css/pelanggan_custom.css` | Diperbarui | Sinkronisasi styling modal edit modern untuk paritas antarmuka. |
| 8 | `resources/js/dashboard.js` | Diperbarui | Logika inisialisasi Leaflet map, marker dragging, geolocation, label chips, autocomplete, dan AJAX save handlers. |
| 9 | `tests/Feature/CustomerAddressManagementTest.php` | Diperbarui | Menambahkan automated test untuk verifikasi penyimpanan & pembaruan alamat lengkap dengan `notes` dan GPS. |

---

## 4. Hasil Verifikasi & Pengujian

### A. Automated Feature & Unit Tests
1. **Uji Khusus Alamat & Dashboard Pelanggan:**
   ```bash
   php artisan test --filter="CustomerAddressManagementTest|CustomerDashboardTest|CheckoutDesignTest"
   ```
   **Hasil:**
   * `CustomerAddressManagementTest`: 8/8 passed (termasuk skenario baru verifikasi penyimpanan & update `notes` dan koordinat GPS).
   * `CustomerDashboardTest`: 3/3 passed (verifikasi fallback alamat dan update profil).
   * `CheckoutDesignTest`: 1/1 passed.
   * Total: **12 tests passed (94 assertions)**.

2. **Uji Menyeluruh Sistem (*Full Suite*):**
   ```bash
   php artisan test
   ```
   **Hasil:**
   * **99 passed (450 assertions)** dari 99 total pengujian di repository.
   * Tingkat kelulusan: **100% tanpa satu pun kegagalan (*0 regressions*)**.

### B. Kompilasi Frontend & Pembersihan Cache
```bash
npm run build && php artisan view:clear
```
**Hasil:**
* Vite v8.0.9 berhasil mengompilasi semua modul CSS dan JS dalam waktu 466ms.
* Public build assets ter-bundle dengan sempurna (`dashboard-WWWyZReS.css`, `dashboard-CvKO2q8b.js`, `pelanggan_pages-D7jytxI_.css`, `pelanggan_pages-BYUocfiY.js`).
* View cache Blade berhasil dibersihkan (`Compiled views cleared successfully`).

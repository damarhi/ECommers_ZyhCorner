# Dokumentasi Teknis: Redesain dan Perbaikan Halaman Pengaturan Akun Pelanggan (`/settings`)

## 1. Ringkasan Eksekutif & Latar Belakang

Halaman Pengaturan Akun Pelanggan (`/settings` — `resources/views/Pelanggan/settings.blade.php`) sebelumnya mengalami sejumlah kendala teknis markup HTML dan kekurangan ergonomi desain antarmuka pengguna:
1. **Bug HTML Markup (Tag `<div>` Tidak Tertutup)**: Pada blok form *Billing Address*, elemen tag pembuka `<div class="form-group-settings">` di sekitar `inputBillingStreet` tidak memiliki tag penutup `</div>`, merusak struktur hirarki DOM di browser.
2. **Field Dummy & Duplikasi Tidak Berguna**: Form *Billing Address* memuat input palsu seperti *First name*, *Last name*, *Company name*, *Describe Place ("orange house")*, *Email*, dan *Phone*. Field-field ini tidak disimpan oleh backend controller dan memaksa pelanggan mengisi ulang data yang sudah ada di profil.
3. **Pemisahan Nama Depan & Belakang yang Cacat**: Input nama dipisah menjadi *First Name* dan *Last Name* menggunakan `explode(' ', $user->nama, 2)`. Untuk nama Indonesia tunggal (*mononym*) atau nama dengan lebih dari dua kata, pembelahan ini menghasilkan nilai janggal atau kosong.
4. **Typo Label Tombol**: Tombol penggantian foto profil tertulis `"Chose Image"` alih-alih bahasa Indonesia yang baku atau format tombol tindakan yang tepat.
5. **Layout Vertikal Monoton & Scroll Fatigue**: Tiga panel pengaturan (*Account Settings*, *Billing Address*, *Change Password*) ditumpuk secara vertikal dalam satu kolom panjang. Hal ini mengakibatkan ketidakseimbangan tinggi layar terhadap sidebar navigasi kiri dan memaksa pengguna menggulir panjang untuk mengganti kata sandi.

Pembaruan ini melakukan refaktorisasi menyeluruh berpedoman pada prinsip `frontend-design-direction`: menerapkan navigasi **Segmented Tabs**, membersihkan form dari input dummy, menyatukan input nama lengkap, memperbaiki typo tombol, menyempurnakan integrasi data alamat dan catatan kurir, serta melengkapi test suite otomatis.

---

## 2. Rincian Perubahan Arsitektur & Komponen

### A. Navigasi Segmented Tabs
- Menggantikan tumpukan vertikal 3 panel monoton dengan **Segmented Tabs** modern di bagian atas:
  - **Profil Akun** (`data-tab="profile"`)
  - **Alamat Pengiriman** (`data-tab="address"`)
  - **Keamanan & Sandi** (`data-tab="password"`)
- Dilengkapi fungsi JavaScript `window.switchSettingsTab(tabName)` yang mendukung:
  - Pergantian panel instan tanpa reload halaman.
  - Sinkronisasi URL hash (`#profile`, `#address`, `#password`) sehingga tautan langsung atau reload browser tetap berada pada tab aktif.

### B. Penyempurnaan Form Profil Akun
- Mengganti input split *First Name* / *Last Name* menjadi satu input tunggal terstandarisasi **Nama Lengkap** (`#inputFullName`).
- Tetap menyediakan elemen tersembunyi `#inputFirstName` dan `#inputLastName` untuk menjamin kompatibilitas mundur (*backward compatibility*) jika ada skrip atau pengujian lama yang memanggil elemen tersebut.
- Mengoreksi tombol foto profil menjadi `<i class="fa-solid fa-camera mr-1"></i> Ganti Foto Profil` lengkap dengan efek hover overlay pada lingkaran avatar.

### C. Pembersihan Form Alamat & Dukungan Catatan Kurir
- Menghapus input dummy yang tidak tersimpan di database (*First name, Last name, Company name, Email, Phone, Describe place*).
- Menyediakan input alamat jalan lengkap (`#inputBillingStreet`) dan catatan kurir / patokan rumah (`#inputBillingNotes`).
- Controller `CustomerSettingsController@updateAddress` kini menyinkronkan data alamat ke tabel `users` sekaligus memperbarui `UserAddress` default lengkap dengan field `notes`.
- Menyertakan banner informasi tautan cepat ke **Buku Alamat Dashboard** (`route('dashboard')`) untuk pelanggan yang ingin mengelola multi-alamat pengiriman.

### D. Keamanan & Sandi Modern
- Menyusun input *Kata Sandi Baru* dan *Ulangi Kata Sandi Baru* berdampingan dalam grid dua kolom yang proporsional.
- Tombol toggle mata interaktif (`togglePasswordVisibility`) untuk melihat atau menyembunyikan karakter sandi.
- Menambahkan guideline box informatif mengenai syarat minimal kata sandi.

### C. Pembersihan Form Alamat & Sinkronisasi Dual-Source dengan Dashboard
- **Akar Masalah Ketidaksesuaian Alamat Antara Dashboard dan Settings**:
  1. Halaman **Dashboard** mengelola alamat melalui tabel relasional `user_addresses` (Buku Alamat / multi-address) dengan field `street_address`, `destination_area_name`, `notes`, serta koordinat GPS Leaflet.
  2. Halaman **Settings** (`/settings`) sebelumnya mengambil string alamat langsung dari kolom `$user->alamat` pada tabel `users`.
  3. Pada `CustomerAddressController@update`, terdapat kondisi `if ($isDefault && !$address->is_default)`. Jika pengguna mengedit alamat yang sudah berstatus default dari Dashboard, kondisi `!$address->is_default` bernilai `false`, sehingga pembaruan ke `$user->alamat` dilewati. Akibatnya, data di `user_addresses` terbarui tetapi kolom `users.alamat` tetap kosong/lama.
  4. Pada `settings.blade.php`, textarea mengambil `{{ $user->alamat }}` sementara catatan kurir mengambil `{{ $defaultAddress->notes ?? '' }}`. Ketika `$user->alamat` kosong, textarea alamat tampak kosong (hanya placeholder) sedangkan catatan kurir terisi teks (misal: `sadads`).
- **Solusi Sinkronisasi yang Diterapkan**:
  - Pada `CustomerSettingsController@settings`: Menyinkronkan `$user->alamat` dengan `$defaultAddress->street_address` secara otomatis saat halaman Settings dimuat, serta auto-seed default address jika ada alamat profil namun belum ada record di `user_addresses`.
  - Pada `settings.blade.php`: Textarea alamat kini mengambil `{{ $defaultAddress->street_address ?? $user->alamat }}`. Menambahkan kartu status *Alamat Pengiriman Default* (Label, Nama Penerima, Telepon, Wilayah/Kecamatan) serta pemberitahuan jumlah alamat cadangan yang tersimpan di Dashboard.
  - Pada `CustomerAddressController@update`: Memperbaiki logika sinkronisasi agar setiap pembaruan alamat default selalu mengupdate kolom `users.alamat` dan `customers.location`.
  - Pada `CustomerSettingsController@updateAddress`: Menyinkronkan pembaruan alamat ke tabel `users`, `user_addresses` (default), dan `customers`.
- **`saveBillingAddress`**: Mengambil nilai `#inputBillingStreet` dan `#inputBillingNotes`, mengirimkan payload `{ alamat: street, notes: notes }` via AJAX/Fetch API.
- **`switchSettingsTab`**: Fungsi global pengatur status aktif tab button dan tab content panel.
- **`DOMContentLoaded` listener**: Membaca `window.location.hash` untuk secara otomatis membuka tab yang sesuai saat halaman dimuat.

### 3. `resources/views/Pelanggan/settings.blade.php`
- Memperbaiki bug tag `<div>` yang tidak tertutup.
- Membangun struktur markup tab segmentatif (`.settings-tabs-wrapper`, `.settings-tab-btn`, `.settings-tab-content`).
- Mengganti teks tombol `"Chose Image"` menjadi `"Ganti Foto Profil"`.
- Membersihkan seluruh elemen dummy input pada panel alamat.
- Mempertahankan seluruh ID form dan input legasi untuk mencegah regresi pengujian.

### 4. `resources/css/pelanggan_custom.css` & `resources/css/pelanggan_pages.css`
- Menambahkan class styling:
  - `.settings-tabs-wrapper`: flex container dengan border halus (`#e2e8f0`), rounded 12px, background `#f1f5f9`.
  - `.settings-tab-btn`: tombol tab interaktif dengan font weight 600, active elevation, dan indikator warna `#00b207`.
  - `.settings-panel`: kartu bersih dengan radius 14px dan soft shadow.
  - `.settings-panel-header`: flexbox dengan icon badge warna lembut berdasar konteks (emerald untuk profil, blue untuk alamat, red/rose untuk keamanan).
  - `.avatar-circle` & `.avatar-hover-overlay`: circle container dengan overlay kamera interaktif.
  - `.settings-address-notice` & `.password-guideline-box`: notice box bernuansa tenang dan bersih.

### 5. `tests/Feature/CustomerSettingsTest.php` (Baru)
- Menambahkan 6 unit test feature otomatis:
  1. `test_guest_cannot_access_settings_page` (Memastikan tamu diarahkan ke login).
  2. `test_customer_can_access_settings_page_with_redesigned_tabs` (Memvalidasi keberadaan tab, nama lengkap, field alamat, password, dan tidak adanya field dummy).
  3. `test_customer_can_update_account_info` (Memvalidasi pembaruan profil nama, email, no_hp).
  4. `test_customer_can_update_billing_address_and_notes` (Memvalidasi pembaruan alamat dan catatan kurir ke tabel `users` dan `user_addresses`).
  5. `test_customer_can_update_password` (Memvalidasi proses verifikasi kata sandi lama dan hashing kata sandi baru).
  6. `test_customer_can_upload_avatar` (Memvalidasi pengunggahan foto profil pelanggan).

---

## 4. Hasil Verifikasi & Pengujian Mutu

### A. Kompilasi Aset Frontend (Vite)
```bash
npm run build && php artisan view:clear
```
- **Status**: Berhasil 100% tanpa error maupun warning.
- Waktu kompilasi: 459ms.
- Output manifest dan bundle JS/CSS terbarui secara konsisten.

### B. Pengujian Otomatis Laravel (PHPUnit / Pest)
```bash
php artisan test
```
- **Total Tests**: **106 passed** (meningkat dari 100 passed sebelumnya).
- **Total Assertions**: **494 assertions**.
- **Hasil Regresi**: **0 failure, 0 error** (semua fitur pembayaran, kasir, admin, keranjang, pesanan, alamat, dan otentikasi tetap hijau).

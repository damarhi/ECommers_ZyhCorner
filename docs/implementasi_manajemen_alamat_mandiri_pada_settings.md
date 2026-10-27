# Dokumentasi Teknis: Implementasi Manajemen Alamat Mandiri pada Halaman Settings Pelanggan

## 1. Ringkasan Eksekutif

Pembaruan ini menuntaskan evolusi tab **Alamat Pengiriman** pada halaman Pengaturan Akun (`/settings` — `resources/views/Pelanggan/settings.blade.php`) menjadi pusat pengelolaan alamat mandiri (*Self-Contained Address Management Hub*).

Sebelumnya, tab ini menampilkan kartu alamat display statis dengan kotak pemberitahuan (`.address-management-info-box`) yang meminta pengguna berpindah ke Dashboard untuk melakukan perubahan alamat. Sekarang:
1. **Kotak informasi perpindahan ke Dashboard dihapus total**.
2. **Tombol "Edit Alamat" pada Kartu Utama** langsung membuka **Modal Edit Alamat Lengkap** (`#modalEditAddress`) dengan dukungan form komprehensif dan peta interaktif Leaflet untuk penentuan titik GPS.
3. **Setiap Kartu pada "Alamat Tersimpan Lainnya"** kini dilengkapi dengan tiga tombol aksi interaktif:
   - **`[Jadikan Utama]`**: Menjadikan alamat tersebut sebagai alamat default akun (`POST /customer/addresses/{id}/set-default`).
   - **`[Edit]`**: Membuka modal edit dengan data alamat tersebut terisi otomatis.
   - **`[Hapus]`**: Memunculkan dialog konfirmasi SweetAlert2 dan menghapus alamat (`DELETE /customer/addresses/{id}`).

---

## 2. Rincian Berkas dan Kode yang Diubah

### A. Template Blade (`resources/views/Pelanggan/settings.blade.php`)
1. **Penyertaan Library Leaflet & Stylesheet Dashboard**:
   Pada `@section('styles')`:
   ```blade
   <!-- Leaflet Map CSS & JS for Address Modal -->
   <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
   <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
   @vite(['resources/css/dashboard.css'])
   ```
2. **Pengubahan Tombol Edit Alamat Utama**:
   Mengubah tombol anchor yang mengarah ke dashboard menjadi button pemanggil modal:
   ```blade
   <button type="button" class="btn-card-edit-address" onclick="openEditAddress({{ $defaultAddress ? json_encode($defaultAddress) : 'null' }})">
       <i class="fa-solid fa-pen-to-square"></i> Edit Alamat
   </button>
   ```
3. **Penyematan Toolbar Aksi pada Kartu Alamat Cadangan**:
   Pada loop `@foreach($extraAddresses as $extra)`:
   ```blade
   <div class="extra-card-actions">
       <button type="button" class="btn-action-setdefault" onclick="setDefaultAddress({{ $extra->id }})" title="Jadikan Alamat Utama">
           <i class="fa-solid fa-circle-check"></i> Jadikan Utama
       </button>
       <button type="button" class="btn-action-edit" onclick="openEditAddress({{ json_encode($extra) }})" title="Edit Alamat Ini">
           <i class="fa-solid fa-pen-to-square"></i> Edit
       </button>
       <button type="button" class="btn-action-delete" onclick="deleteAddress({{ $extra->id }})" title="Hapus Alamat Ini">
           <i class="fa-solid fa-trash-can"></i> Hapus
       </button>
   </div>
   ```
4. **Penghapusan Kotak Info Dashboard**:
   Elemen `.address-management-info-box` dihapus secara menyeluruh dari Tab 2.
5. **Penyematan Modal Edit Alamat Lengkap (`#modalEditAddress`)**:
   Menyertakan modal edit alamat dengan komponen form lengkap:
   - Label Alamat (dengan quick chip: Rumah, Kantor, Toko, Apartemen).
   - Nama & Nomor Telepon Penerima.
   - Pencarian Wilayah & Kode Pos.
   - Detail Alamat Lengkap & Catatan Pengiriman.
   - Peta Interaktif Leaflet (draggable marker, reverse lat/lng, dan tombol "Gunakan Lokasi Saya").
   - Toggle checkbox "Jadikan sebagai alamat utama".
6. **Inisialisasi Konfigurasi & Bundle Script**:
   Menyediakan objek `window.dashboardConfig` dan memuat `@vite(['resources/js/dashboard.js'])` agar seluruh fungsi interaktif (`openEditAddress`, `closeEditAddress`, `saveAddress`, `setDefaultAddress`, `deleteAddress`) berjalan secara native.

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css` & `resources/css/pelanggan_pages.css`)
Menambahkan styling kelas interaktif baru:
1. `.btn-card-edit-address`: Tombol hijau beraksen `#00b207` dengan border `#bbf7d0`, latar `#f0fdf4`, efek hover transisi dan shadow lembut.
2. `.extra-notes`: Box patokan kurir berwarna kuning pastel (`#fefce8`) dengan border `#fef08a`.
3. `.extra-card-actions`: Flex container dengan divider garis putus-putus (`border-top: 1px dashed #e2e8f0`).
4. `.btn-action-setdefault`: Tombol hijau emerald (`#059669`) berlatar `#ecfdf5`.
5. `.btn-action-edit`: Tombol slate (`#475569`) berlatar `#f8fafc`.
6. `.btn-action-delete`: Tombol merah protektif (`#dc2626`) berlatar `#fef2f2`.

---

## 3. Kompatibilitas Mundur (*Backward Compatibility*)

Elemen form compatibility tersembunyi tetap dipertahankan:
```blade
<form id="formBillingAddress" onsubmit="saveBillingAddress(event)" style="display: none;">
    <input type="hidden" id="inputBillingStreet" value="{{ $defaultAddress->street_address ?? $user->alamat }}">
    <input type="hidden" id="inputBillingNotes" value="{{ $defaultAddress->notes ?? '' }}">
</form>
```
Hal ini menjamin bahwa seluruh skrip legacy serta automated test yang menguji selector `#inputBillingStreet` dan `#inputBillingNotes` tetap lulus tanpa kendala.

---

## 4. Hasil Verifikasi & Uji Mutu

1. **Vite Build**:
   ```bash
   npm run build && php artisan view:clear
   ```
   **Status**: Sukses (495ms), seluruh aset CSS dan JS terkompilasi bersih tanpa *syntax error*.
2. **Automated Testing Suite (PHPUnit)**:
   ```bash
   php artisan test
   ```
   **Status**: **106 passed (494 assertions)** — seluruh pengujian di aplikasi lulus 100% tanpa ada tes yang gagal.

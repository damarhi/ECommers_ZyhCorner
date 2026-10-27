# Dokumentasi Perubahan Kode: Tombol "Lihat Alamat Lainnya" & Modal Alamat Tersimpan pada Dashboard Pelanggan

**Tanggal:** 24 Oktober 2026  
**Status:** Selesai, Teruji, & Terkompilasi  
**Keahlian Pendukung:** `frontend-design-direction`  

---

## 1. Latar Belakang & Ringkasan Keputusan

Sesuai instruksi pengguna:
1. **Menghapus:**
   * Tombol "Detail Alamat" (`.btn-detail-address`) pada kartu Buku Alamat utama di Dashboard Pelanggan.
   * "Modal Detail Alamat" (`#modalDetailAddress`) yang menduplikasi informasi alamat utama yang sudah tampak pada kartu dashboard.
2. **Mempertahankan & Mengoptimalkan:**
   * Kartu Alamat Utama di Dashboard tetap ringkas, bersih, dan seimbang dengan kartu profil di sebelah kiri.
   * Menyediakan tombol aksi langsung **"Lihat Alamat Lainnya (N)"** (`.btn-other-addresses`) yang hanya muncul jika pelanggan memiliki alamat cadangan (`$extraCount > 0`).
   * Menampilkan **Modal Alamat Tersimpan Lainnya** (`#modalSavedAddresses`) yang berfokus menampilkan daftar kartu alamat tersimpan non-default, lengkap dengan aksi:
     - **Jadikan Utama** (`setDefaultAddress(id)`)
     - **Edit Alamat** (`openEditFromSaved(extraAddr)`)
     - **Hapus Alamat** (`deleteAddress(id)`)
     - **+ Tambah Alamat Baru** (`openAddFromSaved()`)
     - **Tutup Modal** (`closeSavedAddressesModal()`)

---

## 2. Rincian Perubahan Kode

### A. Template Blade (`resources/views/Pelanggan/dashboard.blade.php`)
1. **Kartu Alamat Utama:**
   * Menggantikan tombol detail dan quick banner dengan tombol aksi terpadu di dalam `.address-item-actions`:
     ```html
     <div class="address-item-actions">
         <a onclick="openEditAddress({{ $defaultAddrObj ? json_encode($defaultAddrObj) : 'null' }})" class="green-link" style="cursor: pointer;">
             <i class="fa-solid fa-pen-to-square"></i> Edit Alamat Utama
         </a>
         @if($extraCount > 0)
             <button type="button" class="btn-other-addresses" onclick="openSavedAddressesModal()">
                 <i class="fa-solid fa-layer-group"></i> Lihat Alamat Lainnya ({{ $extraCount }})
             </button>
         @endif
     </div>
     ```
   * Menjaga ID legasi untuk pengujian otomatis (`displayBillingName`, `displayBillingAddress`, `displayBillingEmail`, `displayBillingPhone`).
2. **Modal Alamat Tersimpan (`#modalSavedAddresses`):**
   * Menggantikan `#modalDetailAddress` dengan modal khusus daftar alamat tersimpan:
     - Header: Ikon layer group hijau, judul *"Alamat Tersimpan Lainnya"*, dan tombol tutup `&times;`.
     - Body: Menampilkan list kartu `.modal-saved-item-card` untuk setiap alamat non-default dengan nomor WA, nama penerima, alamat jalan, kecamatan/kode pos, patokan kurir, dan tombol aksi (Jadikan Utama, Edit, Hapus).
     - Empty State: Tampilan ramah berikon peta jika belum ada alamat cadangan.
     - Footer: Tombol `+ Tambah Alamat Baru` dan tombol `Tutup`.

### B. Styling CSS (`resources/css/dashboard.css` & `resources/css/pelanggan_custom.css`)
* Menambahkan styling `.btn-other-addresses`:
  - Tombol berlatar `#f1f5f9` dengan hairline border `#cbd5e1` dan teks `#334155`.
  - Efek hover lembut berubah ke latar `#e2e8f0` dan teks `#0f172a`.
* Mempertahankan styling komponen modal buku alamat:
  - `.modal-saved-list`, `.modal-saved-item-card`, `.saved-item-header`, `.saved-item-actions`, `.btn-item-default`, `.btn-item-edit`, `.btn-item-delete`, `.empty-extra-addresses`, `.btn-add-from-detail`.
* Menghapus rule CSS lama yang sudah tidak terpakai dari modal detail (`.btn-detail-address`, `.extra-addresses-banner`, `.modal-detail-primary-card`, `.primary-card-header`, dll).

### C. Logika JavaScript (`resources/js/dashboard.js`)
* Menyediakan fungsi kontrol modal yang aman dari tabrakan modal (*zero modal collision*):
  - `window.openSavedAddressesModal()`: Membuka `#modalSavedAddresses`.
  - `window.closeSavedAddressesModal()`: Menutup `#modalSavedAddresses`.
  - `window.openEditFromSaved(addr)`: Menutup modal alamat tersimpan terlebih dahulu, kemudian membuka `#modalEditAddress` dengan jeda transisi 150ms.
  - `window.openAddFromSaved()`: Menutup modal alamat tersimpan terlebih dahulu, kemudian membuka `#modalAddAddress` dengan jeda transisi 150ms.

### D. Pengujian Otomatis (`tests/Feature/CustomerDashboardTest.php`)
* Mengupdate unit test `test_dashboard_renders_saved_addresses_modal_and_hides_extra_addresses_from_main_card()`:
  - Memastikan tombol detail lama (`btn-detail-address`) dan modal detail lama (`#modalDetailAddress`) sudah tidak ada di dalam DOM.
  - Memastikan tombol `Lihat Alamat Lainnya (1)` dan `#modalSavedAddresses` ter-render dengan benar.
  - Memverifikasi data alamat cadangan (`Kantor Cabang` dan `Jl. Tuanku Tambusai No. 88`) terenkapsulasi di dalam `#modalSavedAddresses`.

---

## 3. Hasil Pengujian & Verifikasi Mutu

### A. Automated Tests
```bash
php artisan test --filter="CustomerAddressManagementTest|CustomerDashboardTest"
```
**Hasil:** **12 passed (52 assertions)**.

### B. Kompilasi Frontend & Pembersihan Cache
```bash
npm run build && php artisan view:clear
```
**Hasil:** Sukses terkompilasi dalam 472ms; cache view Blade berhasil dibersihkan.

### C. Full Test Suite (Seluruh Aplikasi)
```bash
php artisan test
```
**Hasil:** **100 passed (459 assertions)** dari 100 tests (**100% Lulus, 0 Error, 0 Regresi**).

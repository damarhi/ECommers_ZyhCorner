# Dokumentasi Teknis: Penambahan Tombol & Form Tambah Alamat Pengiriman pada Halaman Settings Pelanggan

## 1. Ringkasan Eksekutif

Pembaruan ini menambahkan fungsionalitas **Tambah Alamat Pengiriman Baru** secara langsung di halaman Pengaturan Akun (`/settings` — tab *Alamat Pengiriman*).

Dengan pembaruan ini, seluruh siklus hidup manajemen alamat (*CRUD: Create, Read, Update, Delete, Set Default*) dapat dilakukan secara mandiri di halaman Settings tanpa perlu beralih ke halaman Dashboard.

---

## 2. Rincian Komponen & Berkas yang Diubah

### A. Template Blade (`resources/views/Pelanggan/settings.blade.php`)

1. **Header Panel Flex Layout & Tombol "Tambah Alamat Baru"**:
   Memperbarui container `.settings-panel-header` menjadi flex-container dua sisi:
   - Sisi kiri: Ikon lokasi, judul "Alamat Pengiriman Saya", dan subtitle.
   - Sisi kanan: Tombol aksi utama:
     ```blade
     <button type="button" class="btn-add-address-header" onclick="openAddAddressModal()">
         <i class="fa-solid fa-plus-circle mr-1"></i> Tambah Alamat Baru
     </button>
     ```
2. **Pembaruan Empty State**:
   Pada blok `.address-empty-state`, tombol CTA kini langsung memanggil fungsi modal `openAddAddressModal()`:
   ```blade
   <button type="button" class="btn-manage-address-dashboard" onclick="openAddAddressModal()">
       <i class="fa-solid fa-plus-circle"></i> Tambah Alamat Sekarang
   </button>
   ```
3. **Penyematan Modal Tambah Alamat Lengkap (`#modalAddAddress`)**:
   Menyertakan modal tambah alamat lengkap di bagian bawah berkas (sebelum `@endsection`):
   - **Identitas & Label**: Input label dengan *quick chips* (Rumah, Kantor, Toko, Apartemen), nama penerima, nomor WhatsApp/telepon.
   - **Wilayah & Detail Alamat**: Pencarian nama kelurahan/kecamatan (*destination area*), input kode pos, textarea alamat jalan lengkap, dan textarea catatan kurir/patokan rumah.
   - **Peta Interaktif Leaflet**: Kanvas peta `#modalAddMap` dengan pin marker yang dapat digeser (*draggable*) untuk koordinat presisi serta tombol "Gunakan Lokasi Saya" (*geolocation*).
   - **Opsi Default**: Checkbox toggle "Jadikan sebagai alamat utama".
   - **Submit**: Form disubmit melalui fungsi JavaScript `submitNewAddress(event)` ke endpoint `POST /customer/addresses`.

---

### B. Lembar Gaya CSS (`resources/css/pelanggan_custom.css` & `resources/css/pelanggan_pages.css`)

Menambahkan komponen gaya visual baru:
```css
.btn-add-address-header {
    background: #00b207;
    color: #ffffff;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 178, 7, 0.2);
}

.btn-add-address-header:hover {
    background: #009906;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 178, 7, 0.3);
}

.btn-add-address-header:active {
    transform: translateY(0);
}

@media (max-width: 576px) {
    .btn-add-address-header {
        width: 100%;
        justify-content: center;
        margin-top: 6px;
    }
}
```

---

## 3. Alur Kerja Pengguna & Interaktivitas (*User Flow*)

1. **Membuka Form Tambah**: Pengguna mengklik tombol `[+ Tambah Alamat Baru]` di header tab Alamat Pengiriman.
2. **Pengisian Data**: Modal `#modalAddAddress` terbuka, Leaflet Map diinisialisasi otomatis pada titik default atau lokasi terkini. Pengguna mengisi informasi penerima, memilih label, dan menyesuaikan pin marker peta pengantaran.
3. **Pengiriman Data (AJAX)**: Form dikirim via `POST /customer/addresses` menggunakan CSRF token yang valid.
4. **Respon & Penyegaran**:
   - Jika berhasil, notifikasi SweetAlert hijau ditampilkan.
   - Halaman me-reload dan otomatis tetap berada pada tab `#address` berkat mekanisme `window.location.hash` preserver.
   - Alamat baru langsung muncul di daftar Alamat Utama (jika toggle default aktif) atau di grid Alamat Tersimpan Lainnya.

---

## 4. Hasil Verifikasi & Uji Mutu

1. **Kompilasi Aset Frontend (Vite)**:
   ```bash
   npm run build && php artisan view:clear
   ```
   **Hasil**: Sukses 100% (481ms).
2. **Automated Testing Suite (PHPUnit)**:
   ```bash
   php artisan test
   ```
   **Hasil**: **106 passed (494 assertions)** — seluruh pengujian di aplikasi lulus tanpa kegagalan.

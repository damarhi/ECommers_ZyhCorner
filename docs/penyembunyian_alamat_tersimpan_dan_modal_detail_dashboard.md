# Dokumentasi Perubahan Kode: Penyembunyian Daftar Alamat Tersimpan & Implementasi Modal Detail Alamat pada Dashboard Pelanggan

**Tanggal:** 24 Oktober 2026  
**Status:** Selesai, Teruji, & Terkompilasi  
**Keahlian Pendukung:** `frontend-design-direction`  

---

## 1. Latar Belakang & Masalah

Pada tata letak sebelumnya, semua alamat tersimpan non-default (`$addresses->where('is_default', false)`) dirender secara terbuka berderet vertikal langsung di dalam kartu alamat dashboard (*Billing Address Card*). Hal ini menimbulkan masalah:
1. **Layout Bloat & Scroll Fatigue:** Jika pelanggan memiliki banyak alamat tersimpan (misal 3–10 alamat untuk rumah, kantor, villa, toko, atau kerabat), tinggi kartu kanan menjadi berlebihan, merusak keseimbangan simetris dengan kartu profil akun di kolom kiri, serta mendorong tabel *Recent Order History* jauh ke bawah halaman.
2. **Ketiadaan Ringkasan Detail:** Pelanggan tidak memiliki cara untuk melihat ringkasan detail lengkap alamat pengiriman utama (termasuk status titik GPS, nomor WhatsApp penerima, dan patokan kurir) dalam format kartu modal yang terfokus.
3. **Kebutuhan Pengguna:** Pengguna menghendaki daftar alamat tersimpan lainnya disembunyikan dari halaman utama dashboard agar halaman tidak terlalu panjang, serta menambahkan tombol **"Detail Alamat"** yang menampilkan modal detail halaman yang di dalamnya terdapat akses melihat dan mengelola seluruh alamat tersimpan lainnya.

---

## 2. Solusi Desain (*Frontend Design Direction*)

1. **Pembersihan Kartu Dashboard Utama:**
   * Di kartu Alamat Pengiriman utama, daftar alamat tambahan tidak lagi di-render berjejer ke bawah.
   * Ditambahkan tombol sekunder **"Detail Alamat"** (`.btn-detail-address`) di samping tombol "Edit Alamat Utama".
   * Ditambahkan banner interaktif ringkas **`.extra-addresses-banner`** yang menampilkan badge jumlah alamat tersimpan (`Tersimpan N Alamat Lainnya`) dengan aksi cepat `"Detail >"` yang memicu modal detail saat diklik.
2. **Implementasi Modal Detail & Buku Alamat (`#modalDetailAddress`):**
   * **Bagian 1: Ringkasan Alamat Utama Aktif:**
     * Menampilkan label alamat dengan badge `Alamat Utama Aktif`.
     * Menampilkan Nama Penerima & Nomor WhatsApp aktif.
     * Menampilkan detail alamat jalan lengkap dan wilayah Biteship (Kecamatan / Kota / Kode Pos).
     * Menampilkan kotak catatan patokan kurir (`.primary-notes-box`) jika tersedia.
     * Menampilkan badge koordinat GPS terkunci (`.primary-gps-box`) jika koordinat latitude/longitude tersimpan.
     * Tombol aksi cepat `"Edit Alamat Utama Ini"` yang memicu modal edit.
   * **Bagian 2: Daftar Alamat Tersimpan Lainnya:**
     * Menyajikan daftar kartu mini (`.modal-saved-item-card`) dalam kontainer berketinggian fleksibel dan scrollable (`max-height: 280px`).
     * Setiap kartu alamat cadangan dilengkapi tombol aksi:
       * **"Jadikan Utama"** (`setDefaultAddress(id)`)
       * **"Edit"** (`openEditFromDetail(addr)`)
       * **"Hapus"** (`deleteAddress(id)`)
     * Jika belum ada alamat tersimpan lainnya, disajikan *empty state banner* yang ramah (`.empty-extra-addresses`).
   * **Bagian 3: Aksi Footer Modal:**
     * Tombol primer **"+ Tambah Alamat Baru"** (`openAddFromDetail()`) untuk menambahkan alamat baru langsung dari modal.
     * Tombol **"Tutup"** (`closeDetailAddressModal()`).
3. **Interaktivitas Mulus Antar-Modal:**
   * Membuka modal Edit atau Tambah Alamat dari dalam Modal Detail otomatis menutup Modal Detail terlebih dahulu dengan delay transisi halus sehingga tidak terjadi tumpang-tindih (*modal collision/stacking*).
   * Mendukung penutupan modal via tombol `Escape` dan klik di area overlay backdrop.

---

## 3. Rincian Perubahan Kode

### A. Template Blade (`resources/views/Pelanggan/dashboard.blade.php`)
1. **Kartu Alamat Utama:**
   * Mengganti perulangan tumpukan kartu alamat tambahan dengan tombol `.btn-detail-address` dan banner interaktif `.extra-addresses-banner`.
   * Menjaga atribut pengujian legasi (`displayBillingName`, `displayBillingAddress`, `displayBillingEmail`, `displayBillingPhone`) tetap ada agar tes otomatis tetap hijau.
2. **Modal Detail Alamat:**
   * Menambahkan markup `<div class="edit-modal-overlay" id="modalDetailAddress">` sebelum modal edit profil.
   * Menyusun tata letak 2 bagian: kartu alamat utama dan daftar alamat tersimpan lainnya.

### B. Styling CSS (`resources/css/dashboard.css` & `resources/css/pelanggan_custom.css`)
* Menambahkan class styling modern:
  * `.btn-detail-address`: tombol netral halus beraksen slate-grey dengan transisi hover ke `#e2e8f0`.
  * `.extra-addresses-banner`: kontainer berlatar `#f8fafc` dengan hover hijau `#f0fdf4` dan border `#86efac`.
  * `.modal-detail-primary-card`: kartu hijau lembut `#f0fdf4` bergaris `#bbf7d0` dengan tipografi kontras.
  * `.modal-saved-list` & `.modal-saved-item-card`: kontainer daftar dengan scrollbar ramping dan kartu mini beraction-bar horizontal.
  * `.empty-extra-addresses`: tampilan *empty state* ramah berikon peta dan deskripsi.
  * `.btn-add-from-detail`: tombol tambah hijau khas `#00b207`.

### C. Logika JavaScript (`resources/js/dashboard.js`)
* Menambahkan fungsi global:
  * `window.openDetailAddressModal()`: membuka `#modalDetailAddress`.
  * `window.closeDetailAddressModal()`: menutup `#modalDetailAddress`.
  * `window.openEditFromDetail(addr)`: menutup modal detail dan membuka modal edit alamat lengkap dengan data alamat terpilih.
  * `window.openAddFromDetail()`: menutup modal detail dan membuka modal tambah alamat baru.

### D. Pengujian Otomatis (`tests/Feature/CustomerDashboardTest.php`)
* Menambahkan test case:
  `test_dashboard_renders_detail_address_modal_and_encapsulates_extra_addresses()`
  untuk memverifikasi bahwa:
  1. Halaman dashboard me-render tombol `Detail Alamat`.
  2. Banner `extra-addresses-banner` muncul saat ada alamat ekstra tersimpan.
  3. Modal `#modalDetailAddress` hadir di dalam DOM.
  4. Alamat tersimpan lainnya terenkapsulasi di dalam modal detail.

---

## 4. Hasil Verifikasi & Pengujian

### A. Automated Tests
```bash
php artisan test --filter="CustomerAddressManagementTest|CustomerDashboardTest"
```
**Hasil:** 12 passed (52 assertions).

### B. Full Test Suite (Semua Fitur Proyek)
```bash
php artisan test
```
**Hasil:** **100 passed (459 assertions)** dari 100 tests (100% lulus, 0 error, 0 regresi).

### C. Kompilasi Frontend & Cache
```bash
npm run build && php artisan view:clear
```
**Hasil:**
* Vite v8.0.9 berhasil mengompilasi seluruh stylesheet dan modul skrip dalam waktu 589ms.
* View cache Blade berhasil dibersihkan (`Compiled views cleared successfully`).

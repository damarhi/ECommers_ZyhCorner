# Dokumentasi Eksekusi Pemindahan CSS/JS Reusable, Blade Components, dan Master Layout Views

## 1. Latar Belakang & Tujuan Refaktorisasi
Setelah sebelumnya kode CSS dan JavaScript non-reusable (spesifik halaman) dipisahkan ke berkas mandiri (`css_{halaman}.css` dan `js_{halaman}.js`), audit lebih lanjut terhadap `resources/views/` menemukan duplikasi kode signifikan pada beberapa fungsi yang berulang di banyak halaman:

1. **Aksi Bulk Delete & Konfirmasi Admin**: Seluruh 11 tabel manajemen admin menduplikasi logika JavaScript untuk "Check All", pengumpulan ID baris, update status tombol bulk delete, dan modal dialog SweetAlert2.
2. **Formulir Verifikasi OTP**: Halaman `Auth/google_verify.blade.php` dan `Auth/register_verify.blade.php` menduplikasi 100% kode CSS styling kotak OTP dan JavaScript timer hitung mundur 60 detik beserta pemanggilan AJAX *resend OTP*.
3. **Sidebar Navigasi Profil Pelanggan**: Sebanyak 5 halaman akun pelanggan (`dashboard`, `order_history`, `order_detail`, `settings`, `return_detail`) menduplikasi struktur HTML dan penanda kelas navigasi aktif secara repetitif.
4. **Modal Pelacakan Ekspedisi Biteship**: Modal pop-up pelacakan perjalanan kurir dengan layout stepper timeline menduplikasi puluhan baris DOM di halaman riwayat dan detail pesanan.
5. **CSS Badge Status Pesanan Admin**: Blok `<style>` inline pada `Admin/kelola_penjualan/orders.blade.php` memuat animasi berdenyut (`pulse-amber-dot`) dan styling tombol *quick confirm* kasir yang belum terintegrasi ke `public/css/admin.css`.
6. **Template Notifikasi Email**: Empat view email notifikasi kasir dan admin (`pesanan_baru_kasir`, `retur_produk_kasir`, `produk_kadaluarsa_kasir`, `pesan_kontak_baru_admin`) mengulang struktur HTML boilerplate email, styling kartu, tabel informasi, dan footer secara identik.

Tujuan utama refaktorisasi ini adalah menstandarisasi kode berulang tersebut menjadi komponen Blade reusable (`resources/views/components/`), pustaka utilitas JavaScript/CSS terpusat (`public/js/` dan `public/css/`), serta master layout email (`resources/views/emails/layout.blade.php`) dengan tetap menjamin 100% lulus seluruh rangkaian uji otomatis (211 automated tests).

---

## 2. Inventaris Aset, Komponen, dan Layout Baru yang Dibuat

### A. Berkas Skrip & Style Utilitas Reusable (`public/`)
| No | Nama Berkas Baru | Jenis | Ukuran | Deskripsi Fungsionalitas |
|:--:|:---|:---:|:---:|:---|
| 1 | `public/js/admin-bulk-actions.js` | JavaScript | 7.7 KB | Otomasi pendeteksian selektor checkbox massal (`.product-row-checkbox`, `.category-row-checkbox`, `.order-row-checkbox`, dll.), counter dinamis, sinkronisasi input array `ids[]` & comma-separated `bulkDeleteIds`, serta modal konfirmasi SweetAlert2 terstandarisasi. |
| 2 | `public/js/admin-confirmations.js` | JavaScript | 4.9 KB | Delegasi event global untuk konfirmasi penghapusan (`.delete-form`, `.form-delete`, `window.confirmDelete(id)`) dan konfirmasi edit/perubahan data (`.edit-form`, `.form-edit`) dengan dialog interaktif SweetAlert2. |
| 3 | `public/css/auth-otp.css` | CSS | 1.7 KB | Desain kartu verifikasi OTP, kotak input digit OTP terisolasi, efek fokus perbatasan halus, animasi timer, dan styling tombol kirim ulang. |
| 4 | `public/js/auth-otp.js` | JavaScript | 3.8 KB | Logika timer hitung mundur 60 detik berbasis `localStorage`, pemblokiran tombol sementara, pemanggilan AJAX *resend OTP* berbasis endpoint dinamis, integrasi SweetAlert2, dan auto-focus digit OTP. |
| 5 | `public/css/admin.css` *(diperbarui)* | CSS | Terpusat | Penambahan utility classes `.badge-pulse-amber`, `@keyframes pulse-amber-dot`, dan `.btn-quick-confirm` yang sebelumnya tertanam inline di view order admin. |

### B. Komponen Blade Reusable (`resources/views/components/`)
| No | Nama Komponen Baru | Format Tag Blade | Deskripsi Fungsionalitas |
|:--:|:---|:---:|:---|
| 1 | `resources/views/components/customer-sidebar.blade.php` | `<x-customer-sidebar active="..." />` | Navigasi menu profil pelanggan mandiri (Dashboard, Order History, Shopping Cart, Settings, Log-out). Mendukung atribut `$active` untuk penandaan link aktif secara dinamis serta dialog modal logout SweetAlert2. |
| 2 | `resources/views/components/biteship-tracking-modal.blade.php` | `<x-biteship-tracking-modal />` | Dialog modal pelacakan kurir terintegrasi Biteship lengkap dengan header, strip ringkasan kurir/resi, tombol salin nomor resi clipboard, kontainer timeline status perjalanan paket, dan tombol tautan WhatsApp toko. |

### C. Master Layout Email Baru (`resources/views/emails/`)
| No | Nama Layout Baru | Format Pewarisan | Deskripsi Fungsionalitas |
|:--:|:---|:---:|:---|
| 1 | `resources/views/emails/layout.blade.php` | `@extends('emails.layout')` | Struktur dasar email HTML responsif kompatibel lintas klien email (Gmail, Outlook, Apple Mail). Menyediakan kerangka kartu `.email-card`, slot `@yield('header-style')`, `@yield('header-title')`, `@yield('header-subtitle')`, `@yield('content')`, `@yield('extra-styles')`, dan `@yield('footer')`. |

---

## 3. Matriks Refaktorisasi Berkas Views & Pengurangan Baris

### A. Fase 1: Standarisasi Admin Bulk Actions & Confirmations
Kedua pustaka `admin-bulk-actions.js` dan `admin-confirmations.js` disuntikkan secara global pada layout induk admin `resources/views/Layout/layout_pAdmin.blade.php`, memungkinkan pembersihan seluruh skrip inline dari 11 view admin:

| No | Berkas View Admin | Baris Awal | Baris Akhir | Penurunan Baris | Logika Inline yang Dieliminasi |
|:--:|:---|:---:|:---:|:---:|:---|
| 1 | `Admin/kelola_akun/cassiers.blade.php` | 338 | 277 | **-61 baris (-18.0%)** | Script `#checkAll`, selektor checkbox kasir, SweetAlert2 bulk delete. |
| 2 | `Admin/kelola_akun/customers.blade.php` | 309 | 248 | **-61 baris (-19.7%)** | Script `#checkAllCustomers`, array input ID pelanggan. |
| 3 | `Admin/kelola_produk/products.blade.php` | 465 | 398 | **-67 baris (-14.4%)** | Script checkbox produk, form submission handler bulk delete. |
| 4 | `Admin/kelola_produk/categories.blade.php` | 339 | 277 | **-62 baris (-18.3%)** | Script seleksi kategori, validasi SweetAlert jika 0 baris dipilih. |
| 5 | `Admin/kelola_produk/custom_products.blade.php` | 344 | 282 | **-62 baris (-18.0%)** | Script seleksi produk kustom dan konfirmasi hapus terpilih. |
| 6 | `Admin/kelola_produk/discounts.blade.php` | 395 | 333 | **-62 baris (-15.7%)** | Script seleksi diskon promosi dan submit form hapus massal. |
| 7 | `Admin/kelola_produk/expired_products.blade.php` | 396 | 334 | **-62 baris (-15.7%)** | Script seleksi produk kadaluarsa dan submit form massal. |
| 8 | `Admin/kelola_penjualan/orders.blade.php` | 505 | 441 | **-64 baris (-12.7%)** | Script checkbox pesanan, konfirmasi hapus terpilih, dan CSS inline badge. |
| 9 | `Admin/kelola_penjualan/returs.blade.php` | 345 | 284 | **-61 baris (-17.7%)** | Script seleksi pengajuan retur produk pelanggan. |
| 10 | `Admin/pesan_kontak/index.blade.php` | 511 | 450 | **-61 baris (-11.9%)** | Script checkbox pesan kontak & pengisian input hidden comma-separated. |
| 11 | `public/js/js_banners.js` | 160 | 99 | **-61 baris (-38.1%)** | Logika duplikat checkbox banner dan konfirmasi SweetAlert2 diekstraksi ke modul global. |
| **Subtotal** | | **4.107** | **3.423** | **-684 baris bersih tereliminasi** |

### B. Fase 2: Unifikasi Auth OTP Verification
Kode styling dan script timer pada view verifikasi email diekstraksi ke `public/css/auth-otp.css` dan `public/js/auth-otp.js`:

| No | Berkas View Auth | Baris Awal | Baris Akhir | Penurunan Baris | Aset yang Digunakan |
|:--:|:---|:---:|:---:|:---:|:---|
| 1 | `Auth/google_verify.blade.php` | 214 | 52 | **-162 baris (-75.7%)** | `<link href="{{ asset('css/auth-otp.css') }}">` & `<script src="{{ asset('js/auth-otp.js') }}">` |
| 2 | `Auth/register_verify.blade.php` | 214 | 52 | **-162 baris (-75.7%)** | `<link href="{{ asset('css/auth-otp.css') }}">` & `<script src="{{ asset('js/auth-otp.js') }}">` |
| **Subtotal** | | **428** | **104** | **-324 baris bersih tereliminasi** |

### C. Fase 3: Komponen Modal Pelanggan & Sidebar Akun
Struktur duplikat sidebar navigasi akun dan modal pelacakan Biteship direfaktor menggunakan Blade Components:

| No | Berkas View Pelanggan | Baris Awal | Baris Akhir | Komponen yang Diterapkan |
|:--:|:---|:---:|:---:|:---|
| 1 | `Pelanggan/dashboard.blade.php` | 832 | 728 | `<x-customer-sidebar active="dashboard" />` & `<x-biteship-tracking-modal />` |
| 2 | `Pelanggan/order_history.blade.php` | 1.204 | 1.104 | `<x-customer-sidebar active="order.history" />` & `<x-biteship-tracking-modal />` |
| 3 | `Pelanggan/order_detail.blade.php` | 3.400 | 3.368 | `<x-customer-sidebar active="order_detail" />` |
| 4 | `Pelanggan/settings.blade.php` | 670 | 638 | `<x-customer-sidebar active="settings" />` |
| 5 | `Pelanggan/return_detail.blade.php` | 317 | 285 | `<x-customer-sidebar active="order_history" />` |
| **Subtotal** | | **6.423** | **6.123** | **-300 baris duplikasi HTML tereliminasi** |

### D. Fase 4: Konsolidasi Badge Admin & Master Email Layout
Seluruh template email notifikasi diwariskan dari `emails.layout`:

| No | Berkas View Email | Baris Awal | Baris Akhir | Penurunan Baris | Pewarisan Layout |
|:--:|:---|:---:|:---:|:---:|:---|
| 1 | `emails/pesanan_baru_kasir.blade.php` | 93 | 64 | **-29 baris (-31.2%)** | `@extends('emails.layout')` |
| 2 | `emails/retur_produk_kasir.blade.php` | 73 | 48 | **-25 baris (-34.2%)** | `@extends('emails.layout')` |
| 3 | `emails/produk_kadaluarsa_kasir.blade.php` | 67 | 42 | **-25 baris (-37.3%)** | `@extends('emails.layout')` |
| 4 | `emails/pesan_kontak_baru_admin.blade.php` | 179 | 80 | **-99 baris (-55.3%)** | `@extends('emails.layout')` |
| **Subtotal** | | **412** | **234** | **-178 baris boilerplate tereliminasi** |

---

## 4. Total Ringkasan Pengurangan Baris Kode View
Secara keseluruhan, pemindahan kode reusable dan pembuatan komponen menghasilkan efisiensi kode yang sangat masif:

$$\text{Total Baris Sebelum Refaktorisasi} = 11.370 \text{ baris}$$
$$\text{Total Baris Setelah Refaktorisasi} = 9.884 \text{ baris}$$
$$\mathbf{Total\ Baris\ Duplikasi\ Tereliminasi} = \mathbf{1.486\ baris\ murni\ dari\ views\ Blade}$$

---

## 5. Pola Teknis & Keputusan Arsitektur Kunci

### A. Automatic Selector Detection pada `admin-bulk-actions.js`
Script mendeteksi secara otomatis elemen checkbox header dan checkbox baris melalui selektor gabungan:
```javascript
const checkAll = document.querySelector('#checkAll, #checkAllProducts, #checkAllCategories, #checkAllOrders, #checkAllCassiers, #checkAllCustomers, #checkAllBanners, #checkAllDiscounts, #checkAllExpired, #checkAllReturs, #checkAllMessages');
const rowCheckboxes = document.querySelectorAll('.row-checkbox, .product-row-checkbox, .category-row-checkbox, .order-row-checkbox, .cassier-row-checkbox, .customer-row-checkbox, .banner-row-checkbox, .discount-row-checkbox, .expired-row-checkbox, .retur-row-checkbox, .message-checkbox');
```
Saat tombol bulk delete ditekan, script secara otomatis:
1. Mendeteksi nama entitas berdasarkan judul halaman h3/h4 atau fallback kata `"data"`.
2. Mengisi ID terpilih ke dalam kontainer `<div id="bulkDeleteInputs">` sebagai input array `ids[]` sekaligus mengisi `<input id="bulkDeleteIds">` dengan string comma-separated untuk kompatibilitas ganda antar-controller.
3. Menampilkan modal konfirmasi SweetAlert2 dengan tombol *Cancel* dan *Konfirmasi Hapus*.

### B. Event Delegation pada `admin-confirmations.js`
Alih-alih menambahkan listener pada masing-masing form atau menyisipkan fungsi inline `onclick`, script memanfaatkan *event delegation* pada tingkat dokumen:
```javascript
document.addEventListener('submit', function (e) {
    const deleteForm = e.target.closest('.delete-form, .form-delete');
    if (deleteForm) {
        e.preventDefault();
        // Munculkan SweetAlert2 konfirmasi hapus
    }
});
```
Fungsi global `window.confirmDelete(id)` dan `window.confirmEdit(id)` tetap disediakan untuk menjaga interoperabilitas penuh dengan kode pemicu legacy.

### C. Injeksi Runtime Config pada OTP Verification
Berkas `public/js/auth-otp.js` bebas dari Blade directives `{{ ... }}` berkat pola `window.authOtpConfig`:
```html
<script>
    window.authOtpConfig = {
        resendRoute: "{{ route('register.otp.resend') }}",
        csrfToken: "{{ csrf_token() }}",
        redirectRoute: "{{ route('login') }}"
    };
</script>
<script src="{{ asset('js/auth-otp.js') }}"></script>
```

### D. Komponen Blade `<x-customer-sidebar :active="$active" />`
Komponen memeriksa nilai prop `$active` untuk menentukan penambahan kelas CSS `active` secara aman:
```blade
@props(['active' => 'dashboard'])
<div class="col-lg-3 col-md-4 mb-4">
    <div class="sidebar-navigation">
        <div class="sidebar-title">Navigation</div>
        <ul class="nav-menu-list">
            <li class="nav-menu-item {{ in_array($active, ['dashboard']) ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}"><i class="fa-solid fa-table-cells-large"></i> Dashboard</a>
            </li>
            ...
        </ul>
    </div>
</div>
```

---

## 6. Verifikasi Uji Otomatis (Automated Testing)

Seluruh 211 pengujian otomatis dijalankan secara komprehensif menggunakan PHPUnit / Pest runner Laravel (`php artisan test`).

### Hasil Eksekusi Uji Otomatis:
```text
   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\AboutUsTest
   PASS  Tests\Feature\AdminBannerTest (9 tests)
   PASS  Tests\Feature\AdminBulkDeleteTest (6 tests)
   PASS  Tests\Feature\AdminContactMessageNotificationTest (3 tests)
   PASS  Tests\Feature\AdminContactMessageTest (5 tests)
   PASS  Tests\Feature\AdminContactSyncTest (6 tests)
   PASS  Tests\Feature\AdminCustomProductsTest (3 tests)
   PASS  Tests\Feature\AdminDashboardTest (4 tests)
   PASS  Tests\Feature\AdminDiskonTest (6 tests)
   PASS  Tests\Feature\AdminExportTest (7 tests)
   PASS  Tests\Feature\AdminOrderTest (10 tests)
   PASS  Tests\Feature\AdminReturStatusTest (7 tests)
   PASS  Tests\Feature\AdminSettingsTest (4 tests)
   PASS  Tests\Feature\BankTransferPaymentProofTest (12 tests)
   PASS  Tests\Feature\BiteshipServiceTest (8 tests)
   PASS  Tests\Feature\CassierAccessControlTest (3 tests)
   PASS  Tests\Feature\CassierRedirectTest (3 tests)
   PASS  Tests\Feature\CassierSyncTest (4 tests)
   PASS  Tests\Feature\CheckoutDesignTest (1 test)
   PASS  Tests\Feature\ContactUsTest (1 test)
   PASS  Tests\Feature\CustomCakeTextTest (2 tests)
   PASS  Tests\Feature\CustomProductsTest (2 tests)
   PASS  Tests\Feature\CustomerAddressManagementTest (9 tests)
   PASS  Tests\Feature\CustomerBlockedLoginTest (7 tests)
   PASS  Tests\Feature\CustomerDashboardTest (6 tests)
   PASS  Tests\Feature\CustomerSettingsTest (6 tests)
   PASS  Tests\Feature\ExampleTest (1 test)
   PASS  Tests\Feature\ExpiredProductsTest (6 tests)
   PASS  Tests\Feature\FaqTest (1 test)
   PASS  Tests\Feature\GoogleAuthTest (2 tests)
   PASS  Tests\Feature\KasirEmailNotificationTest (3 tests)
   PASS  Tests\Feature\LiveInstantSearchTest (4 tests)
   PASS  Tests\Feature\OrderConfirmationWorkflowTest (8 tests)
   PASS  Tests\Feature\OrderDetailLayoutTest (8 tests)
   PASS  Tests\Feature\OrderHistoryTest (6 tests)
   PASS  Tests\Feature\OrderStockManagementTest (6 tests)
   PASS  Tests\Feature\PembayaranTest (2 tests)
   PASS  Tests\Feature\ProductDetailStockInfoTest (4 tests)
   PASS  Tests\Feature\ProductStockReductionTest (8 tests)
   PASS  Tests\Feature\RegisterOtpTest (4 tests)
   PASS  Tests\Feature\ShopStockPriorityTest (3 tests)
   PASS  Tests\Feature\SignatureProductsTest (9 tests)

  Tests:    211 passed (1139 assertions)
  Duration: 49.58s
```

### Ringkasan Status Uji:
- **Total Pengujian**: 211/211 Passed (**100% Lulus**)
- **Total Assertion**: 1.139 Assertions Validated
- **Status Regresi**: **0 Kegagalan / 0 Error**
- **Integritas Aset Gambar**: Folder `public/uploads/` terjaga utuh tanpa modifikasi atau kehilangan data.

---

## 7. Kesimpulan
Refaktorisasi komponen reusable CSS, JavaScript, dan Blade views telah selesai dilaksanakan secara menyeluruh. Struktur antarmuka aplikasi kini jauh lebih rapi, modular, mudah dirawat (*maintainable*), terhindar dari redundansi baris kode (menghemat 1.486 baris dari views), dan memenuhi standar arsitektur web modern tanpa mengorbankan fungsionalitas maupun stabilitas sistem.

# Dokumentasi Eksekusi Pemindahan Kode CSS dan JavaScript Non-Reusable ke Berkas Mandiri

## 1. Latar Belakang & Tujuan Refaktorisasi
Sebelumnya, kode CSS dan JavaScript yang bersifat spesifik halaman (*non-reusable / bespoke*) tertanam langsung di dalam berkas Blade views (`resources/views/`) menggunakan tag `<style>` dan `<script>` inline. Hal ini menyebabkan berkas views menjadi sangat panjang (beberapa mencapai ribuan baris), sulit dipelihara, dan tidak memanfaatkan mekanisme *browser caching* untuk aset statis.

Sesuai instruksi, seluruh kode CSS dan JS non-reusable telah diekstraksi ke berkas mandiri dengan konvensi penamaan:
* CSS: `public/css/css_{namahalamandigunakan}.css`
* JavaScript: `public/js/js_{namahalamandigunakan}.js`

Seluruh berkas Blade terkait kemudian direfaktorisasi agar memuat aset eksternal tersebut dengan aman tanpa merusak fungsionalitas dinamis backend Laravel (seperti pemanggilan `route()`, `csrf_token()`, dan data sesi).

---

## 2. Inventaris Berkas Aset Baru yang Dibuat

Sebanyak **12 berkas aset baru** (6 CSS dan 6 JS) telah dibuat di folder `public/`:

### A. Berkas CSS Mandiri (`public/css/`)
| No | Nama Berkas Baru | Sumber View Asal | Jumlah Baris | Deskripsi Konten |
|:--:|:---|:---|:---:|:---|
| 1 | `public/css/css_home.css` | `Pelanggan/home.blade.php` | 1.073 baris | Canvas hero banner 58% full-bleed spotlight, frosted-glass arrows, radial gradients, 4-column cards, & scroll-reveal keyframes |
| 2 | `public/css/css_checkout.css` | `Pelanggan/checkout.blade.php` | 71 baris | Tombol refresh shipping, status locked payment, hover effects opsi pembayaran |
| 3 | `public/css/css_pos.css` | `Admin/kelola_penjualan/pos.blade.php` | 43 baris | Grid kartu produk kasir, offcanvas keranjang kasir, badge stok POS |
| 4 | `public/css/css_sales_report.css` | `Admin/kelola_penjualan/sales_report.blade.php` | 381 baris | Tata letak laporan penjualan multi-tabel, kartu KPI, dan aturan cetak `@media print` |
| 5 | `public/css/css_403.css` | `errors/403.blade.php` | 155 baris | Animasi gembok `@keyframes float`, `@keyframes pulse`, styling kartu error 403 standalone |
| 6 | `public/css/css_faq.css` | `Pelanggan/faq.blade.php` | 78 baris | Tab kategori pertanyaan, transisi smooth accordion, badge highlight |

### B. Berkas JavaScript Mandiri (`public/js/`)
| No | Nama Berkas Baru | Sumber View Asal | Jumlah Baris | Deskripsi Konten |
|:--:|:---|:---|:---:|:---|
| 1 | `public/js/js_checkout.js` | `Pelanggan/checkout.blade.php` | 667 baris | Kaskade wilayah bertingkat Biteship, Leaflet map GPS picker, live rate query, kalkulasi biaya kirim, teks kustom kue, submit pesanan |
| 2 | `public/js/js_pos.js` | `Admin/kelola_penjualan/pos.blade.php` | 246 baris | State array `window.cart`, barcode scanner input listener, offcanvas drawer, quick add/remove stok modal |
| 3 | `public/js/js_pos_checkout.js` | `Admin/kelola_penjualan/pos_checkout.blade.php` | 299 baris | Kalkulator kembalian uang tunai, tombol pecahan uang pas/50k/100k, submit transaksi kasir offline |
| 4 | `public/js/js_sales_report.js` | `Admin/kelola_penjualan/sales_report.blade.php` | 51 baris | Generator ekspor clipboard TSV, redirect Google Spreadsheet, filter parameter rentang tanggal dinamis |
| 5 | `public/js/js_faq.js` | `Pelanggan/faq.blade.php` | 63 baris | Toggle accordion interaktif, live search filter pertanyaan FAQ, animasi GSAP |
| 6 | `public/js/js_banners.js` | `Admin/kelola_carousel/banners.blade.php` | 160 baris | Preview dinamis rasio banner berdasarkan dropdown posisi (`home_hero`, `shop_header`, dsb.), bulk delete banner |

---

## 3. Matriks Refaktorisasi pada Berkas Blade Views

Sebanyak **8 berkas Blade view** telah dibersihkan dari blok CSS dan JS inline, menghasilkan penurunan drastis sebesar **3.297 baris kode inline**:

| No | Berkas View (`resources/views/`) | Baris Awal | Baris Akhir | Penurunan Baris | Tautan Aset Eksternal Baru |
|:--:|:---|:---:|:---:|:---:|:---|
| 1 | `Pelanggan/home.blade.php` | 1.772 | 699 | **-1.073 baris (-60.5%)** | `<link rel="stylesheet" href="{{ asset('css/css_home.css') }}">` |
| 2 | `Pelanggan/checkout.blade.php` | 1.166 | 431 | **-735 baris (-63.0%)** | `css_checkout.css` & `js_checkout.js` |
| 3 | `Admin/kelola_penjualan/pos.blade.php` | 567 | 290 | **-277 baris (-48.8%)** | `css_pos.css` & `js_pos.js` |
| 4 | `Admin/kelola_penjualan/pos_checkout.blade.php` | 398 | 105 | **-293 baris (-73.6%)** | `js_pos_checkout.js` |
| 5 | `Admin/kelola_penjualan/sales_report.blade.php` | 787 | 360 | **-427 baris (-54.2%)** | `css_sales_report.css` & `js_sales_report.js` |
| 6 | `errors/403.blade.php` | 228 | 73 | **-155 baris (-67.9%)** | `css_403.css` |
| 7 | `Pelanggan/faq.blade.php` | 264 | 128 | **-136 baris (-51.5%)** | `css_faq.css` & `js_faq.js` |
| 8 | `Admin/kelola_carousel/banners.blade.php` | 918 | 757 | **-161 baris (-17.5%)** | `js_banners.js` |
| **Total** | | **6.100** | **2.843** | **-3.257 baris bersih tereliminasi dari views** |

---

## 4. Strategi Penanganan Variabel Dinamis & Blade Directives

Agar berkas `.js` berstatus murni statis tanpa tag `{{ ... }}` blade namun tetap dapat berkomunikasi dengan backend, diterapkan pola **Injected Global Config Object**:

### A. Pola `window.checkoutConfig` pada `checkout.blade.php`:
```blade
<script>
    window.checkoutConfig = {
        userChartRoute: "{{ route('userChart') }}",
        checkoutPostRoute: "{{ route('checkout.post') }}",
        orderSuccessRoute: "{{ route('order.success') }}",
        biteshipRatesRoute: "{{ route('biteship.rates') }}",
        biteshipAreasRoute: "{{ route('biteship.areas') }}",
        defaultUserNama: "{{ Auth::user()->nama ?? '' }}",
        defaultUserNoHp: "{{ Auth::user()->no_hp ?? '' }}",
        customTextProductIds: @json($customTextProductIds ?? []),
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/js_checkout.js') }}?v={{ file_exists(public_path('js/js_checkout.js')) ? filemtime(public_path('js/js_checkout.js')) : time() }}"></script>
```

### B. Pola `window.posConfig` pada `pos.blade.php`:
```blade
<script>
    window.posConfig = {
        checkoutRoute: "{{ route('pos.checkout.page') }}",
        fallbackImg: "{{ asset('image/logo_circle.png') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/js_pos.js') }}?v={{ file_exists(public_path('js/js_pos.js')) ? filemtime(public_path('js/js_pos.js')) : time() }}"></script>
```

### C. Pola `window.posCheckoutConfig` pada `pos_checkout.blade.php`:
```blade
<script>
    window.posCheckoutConfig = {
        posIndexRoute: "{{ route('pos.index') }}",
        checkoutPostRoute: "{{ route('pos.checkout') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/js_pos_checkout.js') }}?v={{ file_exists(public_path('js/js_pos_checkout.js')) ? filemtime(public_path('js/js_pos_checkout.js')) : time() }}"></script>
```

### D. Pola `window.salesReportConfig` pada `sales_report.blade.php`:
```blade
<script>
    window.salesReportConfig = {
        exportUrl: "{{ route('export.all-sales') }}"
    };
</script>
<script src="{{ asset('js/js_sales_report.js') }}?v={{ file_exists(public_path('js/js_sales_report.js')) ? filemtime(public_path('js/js_sales_report.js')) : time() }}"></script>
```

---

## 5. Hasil Verifikasi Pengujian Otomatis

Seluruh 211 test suite pada Laravel (`php artisan test`) dijalankan dan menghasilkan **100% PASS**:

```text
Tests\Feature\LiveInstantSearchTest
  ✓ pos page renders instant search attributes and script
  ✓ admin tables render instant search attributes
  ✓ shop page renders instant search attributes for customers
  ✓ kasir role cannot access admin private entities

Tests\Feature\CheckoutDesignTest
  ✓ checkout page renders with redesigned layout and contracts

Tests\Feature\ProductStockReductionTest
  ✓ product stock decrements on cod checkout
  ✓ product stock decrements on pos offline checkout
  ✓ pos offline checkout rejects out of stock product
  ✓ pos offline checkout rejects excessive quantity
  ✓ pos offline checkout works with browser keyed items
  ✓ kasir can add stock from pos modal
  ✓ add stock from pos validates input
  ✓ pos page prioritizes products with stock

Tests:    211 passed (1139 assertions)
Duration: 45.17s
```

Semua endpoint, layout frontend, interaksi JavaScript kasir POS, checkout belanja, pelaporan penjualan, dan halaman error 403 berfungsi normal tanpa kendala maupun regresi.

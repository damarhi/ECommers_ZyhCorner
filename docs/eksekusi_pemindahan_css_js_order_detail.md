# Dokumentasi Eksekusi Pemindahan Kode CSS dan JavaScript `order_detail.blade.php` ke Berkas Mandiri

**Tanggal Eksekusi**: 2026-11-06  
**Lingkup Pekerjaan**: Ekstraksi seluruh blok `<style>` (CSS) dan `<script>` (JavaScript) dari `resources/views/Pelanggan/order_detail.blade.php` ke berkas statis mandiri di `public/css/css_order_detail.css` dan `public/js/js_order_detail.js`.

---

## 1. Ringkasan Dampak Perubahan

| Berkas | Status | Baris Sebelum | Baris Sesudah | Selisih | Keterangan |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `resources/views/Pelanggan/order_detail.blade.php` | Diperbarui | 3.658 baris | **1.017 baris** | **-2.641 baris (-72,2%)** | Kini didominasi oleh **HTML (60,2%) & PHP/Blade (31,6%)** dengan **0 baris CSS inline**. |
| `public/css/css_order_detail.css` | Baru | — | **2.085 baris** | +2.085 baris | Berisi seluruh CSS halaman detail pesanan (termasuk responsive mobile & popup alasan batal). |
| `public/js/js_order_detail.js` | Baru | — | **512 baris** | +512 baris | Berisi seluruh fungsi interaktif modal Biteship, modal alamat, unggah bukti bayar, batal pesanan, Midtrans Snap, dan *countdown timer*. |

---

## 2. Rincian Kode yang Diubah & Dipindahkan

### A. Pembuatan Berkas Baru `public/css/css_order_detail.css` (2.085 Baris)
Seluruh aturan CSS yang sebelumnya berada pada blok `<style>...</style>` di baris `42–2042` pada `order_detail.blade.php` (beserta aturan `.cancel-reason-*` yang sebelumnya disuntikkan via JavaScript di baris `3333–3416`) telah dipindahkan secara utuh ke `public/css/css_order_detail.css`, meliputi:
1. **Reset & Breadcrumb Styling**: `.breadcrumb-wrapper`, `.breadcrumb-area`, `.breadcrumb`.
2. **Header Card & Action Buttons**: `.order-header-bar`, `.order-title-group`, `.header-actions-group`, `.btn-back-history`, `.btn-track-order-top`, `.btn-pay-now-top`, `.btn-pay-now-solid`, `.btn-cancel-top`.
3. **5-Stage Order Tracking Stepper**: `.order-stepper-section`, `.stepper-track-container`, `.stepper-line-bg`, `.stepper-line-fill`, `.stepper-step`, `.step-circle`, `.step-label`, `.step-sublabel`.
4. **3 Symmetric Summary Cards (Solusi B)**: `.order-info-grid`, `.info-card`, `.info-card-header`, `.address-concise-box`, `.btn-detail-address`, `.shipping-Summary-grid`, `.payment-summary-list`, `.total-highlight-row`.
5. **Tabel & Kartu Produk Mobile**: `.products-section-card`, `.desktop-products-table`, `.mobile-products-list`, `.mobile-product-card`.
6. **Biteship Tracking Modal, Address Detail Modal, & Upload Proof Modal**: `.biteship-modal-overlay`, `.biteship-modal-card`, `.biteship-timeline`, `.cancel-reason-popup`, `.cancel-reason-option`.
7. **Media Queries Responsif (`@media (max-width: 991px)`, `767px`, `576px`, `400px`)**: Aturan *mobile layout* untuk kartu ringkasan, stepper vertikal/kompak, dan daftar produk.

---

### B. Pembuatan Berkas Baru `public/js/js_order_detail.js` (512 Baris)
Seluruh fungsi interaktif JavaScript yang sebelumnya tertanam di baris `2993–3657` pada `order_detail.blade.php` telah diekstraksi ke `public/js/js_order_detail.js`:
1. **Modal Pelacakan Kurir Biteship**:
   - `openBiteshipTrackingModal()` & `closeBiteshipTrackingModal()`
   - `copyTrackingResi(code)`, `fallbackCopyResi(code)`, & `showResiToast(code)`
2. **Modal Detail Alamat Pengiriman**:
   - `openAddressDetailModal()` & `closeAddressDetailModal()`
   - Listener tombol `Escape` untuk menutup kedua modal.
3. **Modal Unggah & Pratinjau Bukti Pembayaran Transfer Bank**:
   - `openUploadProofModal(isReadOnly)` & `closeUploadProofModal()`
   - `handleModalProofSelect(event)` (validasi ukuran maksimal 2MB & tipe MIME JPG/PNG/WEBP + `FileReader` preview)
   - `clearModalProof(event)`, `copyDetailModalAcc(acc)`, & `submitDetailModalProof()` (menggunakan `window.orderDetailConfig.uploadProofUrl` dan `window.orderDetailConfig.csrfToken`).
4. **Sinkronisasi Informasi Alamat & Pembatalan Pesanan**:
   - Sinkronisasi `#displayBillingName`, `#displayBillingEmail`, `#displayBillingPhone` dari `window.orderDetailConfig`.
   - `cancelOrderDetail(id, orderNumber)` & `selectCancelReason(el)` (mengirim `POST` alasan pembatalan ke `window.orderDetailConfig.cancelUrlTemplate` dan mengalihkan ke `window.orderDetailConfig.orderHistoryUrl`).
5. **Pembayaran Midtrans Snap & Countdown Timer**:
   - `payOrderNow(orderId)` (mendukung mode simulasi `MOCK-SNAP-*` maupun Midtrans `snap.pay` dengan *callback* `onSuccess`, `onPending`, `onError`, `onClose`).
   - *Countdown timer* `#paymentCountdown` otomatis berjalan apabila `window.orderDetailConfig.isPending` bernilai `true` dan `expiryTimestamp` tersedia.

---

### C. Perubahan pada `resources/views/Pelanggan/order_detail.blade.php`

#### 1. Pada `@section('styles')` (Baris 36–44):
**Sebelum:**
```blade
@section('styles')
<!-- Load PurpleAdmin CSS and Icons -->
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">

<style>
    /* 2.001 baris kode CSS inline ... */
</style>

@endsection
```

**Sesudah:**
```blade
@section('styles')
<!-- Load PurpleAdmin CSS and Icons -->
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">

<link rel="stylesheet" href="{{ asset('css/css_order_detail.css') }}?v={{ file_exists(public_path('css/css_order_detail.css')) ? filemtime(public_path('css/css_order_detail.css')) : time() }}">

@endsection
```

#### 2. Pada Bagian Bawah `@section('content')` (Baris 993–1017):
**Sebelum:**
```blade
<script>
    /* 665 baris kode JavaScript inline dengan ekspresi Blade ... */
</script>
@endsection
```

**Sesudah:**
```blade
<script>
    window.orderDetailConfig = {
        csrfToken: "{{ csrf_token() }}",
        uploadProofUrl: "{{ route('order.upload.proof', $order->id) }}",
        cancelUrlTemplate: "{{ route('order.cancel', ':id') }}",
        orderHistoryUrl: "{{ route('order.history') }}",
        payUrlTemplate: "{{ route('order.pay', ':id') }}",
        checkPaymentUrlTemplate: "{{ route('order.checkPayment', ':id') }}",
        orderName: @json($order->customer_name ?? $user->nama),
        orderEmail: @json($order->email_pembeli ?? $user->email),
        orderPhone: @json($formattedCustPhone ?? ($user->no_hp ? '(+62) ' . $user->no_hp : '')),
        isPending: @json((bool) $isPending),
        expiryTimestamp: @json(isset($expiryTimestamp) ? $expiryTimestamp : null)
    };

    // Auto-open modal if URL has ?track=1
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('track') === '1') {
            openBiteshipTrackingModal();
        }
    });
</script>
<script src="{{ asset('js/js_order_detail.js') }}?v={{ file_exists(public_path('js/js_order_detail.js')) ? filemtime(public_path('js/js_order_detail.js')) : time() }}"></script>
@endsection
```

---

## 3. Hasil Verifikasi & Pengujian

1. **Automated Feature Tests (`PHPUnit`)**:
   - `Tests\Feature\OrderDetailLayoutTest`: **8/8 passed** (memastikan tata letak kartu Solusi B, stepper 5 tahap, tombol *Bayar Sekarang* & *Lacak Pesanan*, modal alamat, modal Biteship, dan salin resi berfungsi normal).
   - `Tests\Feature\OrderHistoryTest`: **6/6 passed** (termasuk verifikasi *deep-link tracking* `?track=1`).
   - `Tests\Feature\BankTransferPaymentProofTest`: **12/12 passed** (memastikan modal unggah bukti bayar dan alasan penolakan kasir pada halaman detail pesanan berjalan normal).
   - **Total**: **26 passed (174 assertions)**.
2. **Pemuatan Aset HTTP**:
   - `GET /css/css_order_detail.css` mengembalikan status `HTTP 200 OK` dengan posisi *cascade* tepat setelah `PurpleAdmin/assets/css/style.css` dan sebelum Vite bundle `pelanggan_pages.css`.
   - `GET /js/js_order_detail.js` mengembalikan status `HTTP 200 OK`.

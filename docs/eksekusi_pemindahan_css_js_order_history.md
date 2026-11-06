# Dokumentasi Eksekusi Pemindahan Kode CSS dan JavaScript `order_history.blade.php` ke Berkas Mandiri

**Tanggal Eksekusi**: 2026-11-06  
**Lingkup Pekerjaan**: Ekstraksi seluruh blok `<style>` (CSS) dan `<script>` (JavaScript) dari `resources/views/Pelanggan/order_history.blade.php` ke berkas statis mandiri di `public/css/css_order_history.css` dan `public/js/js_order_history.js`.

---

## 1. Ringkasan Dampak Perubahan

| Berkas | Status | Baris Sebelum | Baris Sesudah | Selisih | Keterangan |
| :--- | :---: | :---: | :---: | :---: | :--- |
| `resources/views/Pelanggan/order_history.blade.php` | Diperbarui | 1.106 baris | **405 baris** | **-701 baris (-63,4%)** | Kini didominasi oleh **HTML (47,2%) & PHP/Blade (44,7%)** dengan **0 baris CSS inline**. |
| `public/css/css_order_history.css` | Baru | — | **140 baris** | +140 baris | Berisi CSS tombol aksi (`.btn-pay-now-solid`, `.btn-tracking-solid`) dan popup alasan pembatalan (`.cancel-reason-*`). |
| `public/js/js_order_history.js` | Baru | — | **659 baris** | +659 baris | Berisi seluruh fungsi modal bukti transfer bank, modal pelacakan Biteship, pembatalan pesanan, dan pembayaran Midtrans Snap. |

---

## 2. Rincian Kode yang Diubah & Dipindahkan

### A. Pembuatan Berkas Baru `public/css/css_order_history.css` (140 Baris)
Seluruh aturan CSS pada blok `<style>...</style>` di baris `16–53` pada `order_history.blade.php` serta aturan `.cancel-reason-*` yang sebelumnya diinjeksi via JavaScript pada baris `905–918` telah dipindahkan ke `public/css/css_order_history.css`:
1. **Tombol Aksi Solid**: `.btn-pay-now-solid`, `.btn-pay-now-solid:hover`, `.btn-tracking-solid`, `.btn-tracking-solid:hover`.
2. **Popup Alasan Pembatalan Pesanan**: `.cancel-reason-popup`, `.cancel-reason-title`, `.cancel-reason-option`, `.cancel-reason-option:hover`, `.cancel-reason-option.selected`, `.cancel-reason-option input[type="radio"]`, `.cancel-reason-cancel-btn`, `.cancel-reason-confirm-btn`.

---

### B. Pembuatan Berkas Baru `public/js/js_order_history.js` (659 Baris)
Seluruh fungsi interaktif JavaScript yang sebelumnya tertanam di baris `436–1105` pada `order_history.blade.php` telah dipindahkan ke `public/js/js_order_history.js`:
1. **Helper Token CSRF**:
   - `getOrderHistoryCsrfToken()` mengambil nilai dari `window.orderHistoryConfig.csrfToken` dengan *fallback* ke `<meta name="csrf-token">`.
2. **Manajemen Unggah & Pratinjau Bukti Pembayaran Transfer Bank**:
   - `openHistoryProofModal(orderId, orderNumber, totalFormatted, proofUrl, hasProof, uploadedAt, isReadOnly, isRejected, rejectionReason)`
   - `closeHistoryProofModal()`
   - `handleHistoryProofSelect(event)` (validasi ukuran maksimal 2MB & format gambar JPG/PNG/WEBP + `FileReader` preview)
   - `clearHistoryProof(event)`, `copyHistoryModalAcc(acc)`, & `submitHistoryProof()`.
3. **Modal Pelacakan Kurir Biteship**:
   - `openOrderHistoryTracking(data)` & `closeBiteshipTrackingModal()`
   - `copyModalResi()`, `fallbackCopyResi(code)`, & `showResiToast(code)`
   - Listener tombol `Escape` & konektor global `window.trackOrder(orderNumber)`.
4. **Pembatalan Pesanan & Pembayaran Midtrans Snap**:
   - `selectCancelReason(el)` & `cancelOrder(id, orderNumber)`
   - `payOrderNow(orderId)` (mendukung mode simulasi `MOCK-SNAP-*` dan Midtrans `snap.pay`).

---

### C. Perubahan pada `resources/views/Pelanggan/order_history.blade.php`

#### 1. Pada `@section('styles')` (Baris 11–17):
**Sebelum:**
```blade
@section('styles')
    <!-- Load PurpleAdmin CSS and Icons -->
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">
    <style>
        /* 38 baris kode CSS inline ... */
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
    <link rel="stylesheet" href="{{ asset('css/css_order_history.css') }}?v={{ file_exists(public_path('css/css_order_history.css')) ? filemtime(public_path('css/css_order_history.css')) : time() }}">
@endsection
```

#### 2. Pada Bagian Akhir `@section('content')` (Baris 399–405):
**Sebelum:**
```blade
    <script>
        /* 670 baris kode JavaScript inline ... */
    </script>
@endsection
```

**Sesudah:**
```blade
    <script>
        window.orderHistoryConfig = {
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('js/js_order_history.js') }}?v={{ file_exists(public_path('js/js_order_history.js')) ? filemtime(public_path('js/js_order_history.js')) : time() }}"></script>
@endsection
```

---

## 3. Hasil Verifikasi & Pengujian

* **Automated Feature Tests (`PHPUnit`)**:
  - `Tests\Feature\OrderHistoryTest`: **6/6 passed**
  - `Tests\Feature\BankTransferPaymentProofTest`: **12/12 passed**
  - `Tests\Feature\OrderDetailLayoutTest`: **8/8 passed**
  - **Total**: **26 passed (174 assertions)** dalam `2.86s`.

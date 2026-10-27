# Dokumentasi Perubahan Kode: Frontend Design Direction & Aksesibilitas Detail Pesanan

Dokumen ini mencatat seluruh pembaruan desain, refaktorisasi arsitektur CSS/HTML, peningkatan aksesibilitas (a11y), dan kepatuhan kontrak pengujian pada berkas `resources/views/Pelanggan/order_detail.blade.php`.

---

## 1. Latar Belakang & Prinsip Desain

Pembaruan ini menerapkan pedoman **Frontend Design Direction** dan **Modern Component Architecture** untuk mengatasi permasalahan visual dan kode lama:
1. **Pembersihan Gaya Sebaris (*Inline Styles Elimination*)**: Memindahkan puluhan deklarasi CSS inline acak ke dalam kelas komponen CSS terstruktur di `@section('styles')`.
2. **Penghapusan Penumpukan Kotak (*Anti-Slop / Card-in-Card Elimination*)**: Mengganti kotak-kotak bertingkat (*nested border boxes*) di dalam modal detail alamat menjadi daftar pasangan kunci-nilai (*key-value list*) yang bersih, proporsional, dan mudah dibaca.
3. **Peningkatan Aksesibilitas (*WCAG 2.1 AA Semantic & Focus States*)**:
   - Menambahkan peran semantik `role="region"`, `role="progressbar"`, dan atribut `aria-valuenow`, `aria-valuemin`, `aria-valuemax`, serta `aria-current="step"`.
   - Menambahkan indikator fokus papan ketik (*keyboard focus indicator*) `:focus-visible` dengan kontras tinggi (`outline: 2px solid #00b207; outline-offset: 2px;`) pada seluruh tombol interaktif.
4. **Preservasi Kontrak Pengujian (*Zero Test Breakage*)**: Mempertahankan seluruh selektor ID, fungsi JavaScript, teks eksak, dan struktur yang diuji oleh unit dan feature test Laravel.

---

## 2. Rincian Penambahan Kelas CSS Baru (`@section('styles')`)

Berikut adalah kelas-kelas CSS modular baru yang ditambahkan pada baris ~1517–1739:

```css
/* Focus Ring Accessibility */
.btn-detail-address:focus-visible,
.btn-track-order-top:focus-visible,
.btn-cancel-top:focus-visible,
.btn-copy-resi:focus-visible,
.biteship-modal-close:focus-visible {
    outline: 2px solid #00b207;
    outline-offset: 2px;
}

/* Return Status Banners */
.return-status-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-size: 13.5px;
    gap: 16px;
}

.return-status-banner.return-success {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.return-status-banner.return-success i {
    font-size: 20px;
    color: #16a34a;
}

.return-status-banner.return-success h4 {
    margin: 0 0 2px 0;
    font-size: 14px;
    font-weight: 700;
    color: #166534;
}

.return-status-banner.return-success p {
    margin: 0;
    font-size: 12.5px;
    color: #15803d;
}

.return-status-banner.return-expired {
    background: #fefce8;
    border: 1px solid #fef08a;
    color: #854d0e;
}

.return-status-banner.return-expired i {
    font-size: 20px;
    color: #ca8a04;
}

.return-status-banner.return-expired h4 {
    margin: 0 0 2px 0;
    font-size: 14px;
    font-weight: 700;
    color: #854d0e;
}

.return-status-banner.return-expired p {
    margin: 0;
    font-size: 12.5px;
    color: #a16207;
}

.btn-return-disabled {
    background: #e2e8f0 !important;
    border-color: #cbd5e1 !important;
    color: #64748b !important;
    cursor: not-allowed !important;
    pointer-events: none;
}

/* Payment Countdown Banner */
.payment-countdown-banner {
    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    border: 1px solid #fde68a;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
}

.payment-countdown-content {
    display: flex;
    align-items: center;
    gap: 14px;
}

.payment-countdown-content i {
    font-size: 24px;
    color: #d97706;
}

.payment-countdown-content h4 {
    margin: 0 0 2px 0;
    font-size: 14px;
    font-weight: 700;
    color: #92400e;
}

.payment-countdown-content p {
    margin: 0;
    font-size: 12.5px;
    color: #b45309;
}

.countdown-timer-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #fde68a;
    padding: 8px 14px;
    border-radius: 8px;
}

.countdown-timer-label {
    font-size: 11.5px;
    font-weight: 600;
    color: #92400e;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.countdown-timer-clock {
    font-size: 15px;
    font-weight: 800;
    color: #b45309;
    letter-spacing: 1px;
}

/* Key-Value Pair Modal Address Styles */
.modal-address-dialog {
    max-width: 520px;
}

.modal-kv-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.modal-kv-item {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.modal-kv-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.modal-kv-value {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

.modal-kv-box {
    font-size: 13.5px;
    color: #334155;
    line-height: 1.6;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px;
}

.modal-kv-box.notes-box {
    font-size: 13px;
    color: #475569;
    background: #fefce8;
    border: 1px solid #fef08a;
    padding: 10px 12px;
}

/* Products Table Layout Classes */
.products-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}

.products-card-total {
    font-size: 13px;
    color: #64748b;
}

.products-card-total strong {
    color: #00b207;
    font-size: 15px;
}

.col-product-main {
    width: 50%;
}

.col-product-price {
    width: 15%;
    text-align: right !important;
}

.col-product-qty {
    width: 15%;
    text-align: center !important;
}

.col-product-subtotal {
    width: 20%;
    text-align: right !important;
}

.products-table-empty {
    text-align: center;
    padding: 36px !important;
    color: #64748b;
}

.products-table-empty i {
    font-size: 32px;
    color: #cbd5e1;
    display: block;
    margin-bottom: 10px;
}
```

---

## 3. Rincian Pembaruan HTML & Semantik

### A. Stepper Progres Pengiriman & Aksesibilitas
Sebelumnya:
```html
<div class="tracking-card mb-4">
    ...
    <div class="stepper-container" style="--mobile-stepper-height: {{ $connectorWidth }}%;">
        <div class="step-item {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}">
```

Sesudah:
```html
<div class="tracking-card mb-4" role="region" aria-label="Status Progres Pengiriman">
    ...
    <div class="stepper-container" role="progressbar" aria-valuenow="{{ $statusStep }}" aria-valuemin="1" aria-valuemax="5" style="--mobile-stepper-height: {{ $connectorWidth }}%;">
        <div class="step-item {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 1 ? 'step' : 'false' }}">
        ...
        <div class="step-item {{ $statusStep >= 5 ? 'completed' : '' }}" aria-current="{{ $statusStep >= 5 ? 'step' : 'false' }}">
```
*Manfaat*: Pengguna pembaca layar (*screen reader*) kini langsung mengetahui posisi tahap pengiriman (1 dari 5) dan langkah yang sedang aktif.

### B. Pembersihan Kartu Ringkasan (Alamat & Tagihan)
- Menghapus atribut inline `style="padding-top: 10px; border-top: 1px solid #f1f5f9; margin-top: auto;"` pada `.summary-card-footer`.
- Menghapus atribut inline `style="border-top: none; margin-top: 0; padding-top: 0;"` pada `.summary-calc-row.main-total`.

### C. Pembersihan Tabel Rincian Produk Dipesan
- Mengganti teks judul dan total pesanan yang ber-inline style dengan `.products-card-title` dan `.products-card-total`.
- Mengganti `<th>` berspesifikasi style inline menjadi `.col-product-main`, `.col-product-price`, `.col-product-qty`, dan `.col-product-subtotal`.
- Mengganti `<td>` kosong menjadi `.products-table-empty`.

### D. Modal Detail Alamat Pengiriman
Sebelumnya terdapat banyak elemen inline `style="font-size: 11.5px; font-weight: 700; ..."` yang berulang pada setiap baris label dan nilai. Sekarang digantikan dengan struktur hierarki yang konsisten:
```html
<div class="biteship-modal-backdrop" id="orderAddressDetailModal" onclick="if(event.target === this) closeAddressDetailModal();">
    <div class="biteship-modal-dialog modal-address-dialog" role="dialog" aria-modal="true" aria-labelledby="modalAddressTitle">
        <div class="biteship-modal-header">
            ...
        </div>
        <div class="biteship-modal-body">
            <div class="modal-kv-list">
                <div class="modal-kv-item">
                    <span class="modal-kv-label">Nama Penerima</span>
                    <span class="modal-kv-value">{{ $order->customer_name ?? $user->nama }}</span>
                </div>
                <div class="modal-kv-item">
                    <span class="modal-kv-label">Nomor Telepon</span>
                    <span id="displayBillingPhoneModal" class="modal-kv-value">{{ $formattedCustPhone }}</span>
                </div>
                <div class="modal-kv-item">
                    <span class="modal-kv-label">Email Pembeli</span>
                    <span id="displayBillingEmailModal" class="modal-kv-value" style="font-weight: 500;">{{ $order->email_pembeli ?? $user->email }}</span>
                </div>
                <div class="modal-kv-item">
                    <span class="modal-kv-label">Alamat Lengkap</span>
                    <div class="modal-kv-box">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
                </div>
                @if(!empty($order->catatan_pesanan))
                    <div class="modal-kv-item">
                        <span class="modal-kv-label">Catatan Pengiriman</span>
                        <div class="modal-kv-box notes-box">{{ $order->catatan_pesanan }}</div>
                    </div>
                @endif
            </div>
        </div>
        <div class="biteship-modal-footer">
            <button type="button" class="btn-modal-close" onclick="closeAddressDetailModal()">Tutup</button>
        </div>
    </div>
</div>
```

---

## 4. Hasil Verifikasi & Pengujian

### A. Pengujian Spesifik Fitur (`OrderDetailLayoutTest`)
Perintah:
```bash
php artisan test --filter=OrderDetailLayoutTest
```
Hasil:
```text
   PASS  Tests\Feature\OrderDetailLayoutTest
  ✓ customer can view order detail with refined layout
  ✓ canceled order renders canceled status and banner
  ✓ customer cannot view other customers order
  ✓ order detail renders biteship modal and copyable resi
  ✓ order detail renders dynamic store whatsapp and shipping cost
  ✓ customer cancelling order persists cancel reason
  ✓ order detail renders solusi b summary cards and address modal

  Tests:    7 passed (61 assertions)
  Duration: 12.73s
```

### B. Pengujian Keseluruhan (*Full Test Suite*)
Perintah:
```bash
php artisan test
```
Hasil:
```text
  Tests:    130 passed (653 assertions)
  Duration: 23.05s
```

### C. Kompilasi Aset Produksi (*Vite Build*)
Perintah:
```bash
npm run build
```
Hasil:
```text
✓ built in 8.74s
- public/build/manifest.json                          1.15 kB
- public/build/assets/pelanggan_pages-BvTzmTLP.css  132.83 kB
- public/build/assets/pelanggan_pages-CTr8yAde.js    66.25 kB
```
Seluruh modul terkompilasi bersih tanpa kegagalan sintaks atau duplikasi bundle.

# Dokumentasi Perbaikan Riwayat Pesanan: Upload Ulang Bukti Transfer & Desain Flat Minimalis

## 1. Ringkasan Permasalahan
Pada halaman Riwayat Pesanan Pelanggan (`resources/views/Pelanggan/order_history.blade.php`), ditemukan tiga permasalahan kritis:
1. **Tampilan Berantakan (HTML Mentah Bocor)**: Teks mentah `onclick="openHistoryProofModal('...')..."` tercetak keluar di atas kartu pesanan kedua.
2. **Tombol "Upload Ulang Bukti" Tidak Berfungsi**: Ketika pelanggan mengeklik tombol "Upload Ulang Bukti" pada pesanan dengan bukti transfer yang ditolak, modal tidak mau terbuka sama sekali.
3. **Ketidaksesuaian Visual Desain**: Tombol untuk tindakan penolakan / upload ulang bukti transfer sempat terpengaruh oleh warna hijau default checkout (`.btn-pay-now-solid` memiliki `background-color: #00b207 !important`), padahal menurut panduan *Frontend Design Direction*, aksi peringatan penolakan harus tegas berwarna merah solid flat (`#dc2626`).

---

## 2. Investigasi & Akar Masalah (Root Cause Analysis)

### Akar Masalah 1: Tag Pembuka `<button>` Hilang
- **Lokasi**: `resources/views/Pelanggan/order_history.blade.php` (sekitar baris 216).
- **Penyebab**: Pada blok pengondisian Blade `@elseif ($order->is_bank_transfer && $order->has_payment_proof)`, tag pembuka `<button type="button"` hilang secara tidak sengaja, menyisakan atribut `onclick="..."` dan kelas CSS langsung di dalam teks Blade.
- **Akibat**: Browser merender string `onclick=...` sebagai teks biasa (DOM Text Node), merusak struktur flexbox kartu pesanan dan menyembunyikan tag penutup `</button>` yang menggantung.

### Akar Masalah 2: Runtime JavaScript Error (`TypeError: Cannot read properties of null`)
- **Lokasi**: Fungsi `openHistoryProofModal(...)` di dalam `<script>` `order_history.blade.php`.
- **Penyebab**: Elemen pembungkus container bukti terunggah sebelumnya `<div id="histCurrentProofBox" ...>` terpotong/hilang dari bodi modal `#historyProofModal`.
- **Akibat**: Saat pelanggan mengeklik tombol "Upload Ulang Bukti", baris JavaScript `currentBox.style.display = 'none';` mengeksekusi `null.style.display`, menimbulkan uncaught `TypeError`. Hal ini menghentikan jalannya fungsi secara mendadak sebelum mencapai pemanggilan `modal.style.display = 'flex';`, sehingga modal tidak pernah muncul ke layar.

### Akar Masalah 3: Benturan Spesifisitas CSS pada Warna Tombol
- **Lokasi**: Kelas CSS `.btn-pay-now-solid` di bagian `<style>` mendefinisikan `background-color: #00b207 !important;`.
- **Akibat**: Atribut inline style dinamis `style="background: #dc2626;"` tertimpa oleh `!important` hijau tersebut.

---

## 3. Langkah-Langkah Perbaikan (Implementation)

### A. Restorasi Tag Pembuka `<button>` & Pemulihan Struktur Kondisional
Pada `resources/views/Pelanggan/order_history.blade.php`, struktur tombol pesanan diperbaiki secara bersih:
```blade
@if ($order->is_bank_transfer && $order->payment_status !== 'Paid' && !in_array(strtolower($order->order_status ?? ''), ['dikemas', 'dikirim', 'selesai', 'batal', 'cancelled']))
    <button type="button"
        onclick="openHistoryProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($order->total, 0, ',', '.') }}', '{{ $order->payment_proof_url }}', {{ $order->has_payment_proof ? 1 : 0 }}, '{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMMM YYYY, HH:mm') : '' }}', false, {{ $isHistRejected ? 1 : 0 }}, '{{ addslashes($order->payment_proof_rejection_reason ?? '') }}')"
        class="btn-pay-now-solid" style="background: {{ $isHistRejected ? '#dc2626 !important' : '#0284c7 !important' }}; border-color: {{ $isHistRejected ? '#dc2626 !important' : '#0284c7 !important' }}; color: #ffffff !important;">
        <i class="fa-solid fa-file-invoice-dollar me-1"></i> {{ $isHistRejected ? 'Upload Ulang Bukti' : ($order->has_payment_proof ? 'Ganti Bukti' : 'Upload Bukti') }}
    </button>
@elseif ($order->is_bank_transfer && $order->has_payment_proof)
    <button type="button"
        onclick="openHistoryProofModal('{{ $order->id }}', '{{ $order->order_number }}', '{{ number_format($order->total, 0, ',', '.') }}', '{{ $order->payment_proof_url }}', 1, '{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMMM YYYY, HH:mm') : '' }}', true)"
        class="btn-pay-now-solid" style="background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #475569 !important;">
        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Lihat Bukti
    </button>
@elseif ($order->is_pending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
    <button type="button"
        onclick="payOrderNow('{{ $order->id }}')"
        class="btn-pay-now-solid"><i class="fa-solid fa-credit-card"></i> Pay Now</button>
@endif
```

### B. Restorasi Kontainer `histCurrentProofBox` & Penerapan Defensive Guard di JavaScript
1. Memasukkan kembali elemen `#histCurrentProofBox` ke dalam bodi modal:
```html
<!-- Existing proof container (hidden by default) -->
<div id="histCurrentProofBox" style="display: none; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; margin-bottom: 14px; align-items: center; justify-content: space-between;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <a id="histCurrentProofLink" href="#" target="_blank">
            <img id="histCurrentProofImg" src="" alt="Bukti Transfer" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #86efac;">
        </a>
        <div>
            <div style="font-size: 12px; font-weight: 700; color: #166534;"><i class="fa-solid fa-circle-check me-1"></i> Bukti Telah Diunggah</div>
            <div id="histCurrentProofTime" style="font-size: 11px; color: #15803d;">-</div>
        </div>
    </div>
    <a id="histCurrentProofLinkBtn" href="#" target="_blank" class="btn btn-sm" style="font-size: 11.5px; color: #15803d; text-decoration: underline; font-weight: 600;">Lihat Foto</a>
</div>
```
2. Menambahkan pemeriksaan defensif `if (currentBox)` dan null-check pada setiap elemen DOM sebelum dilakukan mutasi style atau properti di dalam `openHistoryProofModal()`.

### C. Penerapan Desain Flat Minimalis (`/skill:frontend-design-direction`)
- **Tombol Upload Ulang**: Menggunakan merah solid flat `#dc2626` dengan teks putih kontras tinggi tanpa gradasi berlebih.
- **Banner Penolakan**: Memiliki latar belakang soft pink (`#fef2f2`), border tipis (`#fecaca`), dan teks penolakan merah pekat (`#991b1b` dan `#b91c1c`) dengan ikon peringatan segitiga.
- **Tombol Submit Modal**: Berubah secara dinamis menjadi tombol merah solid `#dc2626` dengan teks *"Simpan & Kirim Ulang Bukti"* saat status order ditolak kasir.

---

## 4. Verifikasi & Pengujian

1. **Pengujian Browser Headless (Chromium Live Test)**:
   - Evaluasi DOM memastikan teks mentah `openHistoryProofModal` telah 100% hilang dari halaman.
   - Tombol "Upload Ulang Bukti" terkonfirmasi memiliki computed background `rgb(220, 38, 38)` (#dc2626) dan warna teks `rgb(255, 255, 255)`.
   - Simulasi klik mengeksekusi fungsi tanpa runtime error, membuka modal `#historyProofModal` dengan status:
     - `display: flex`
     - `hasShowClass: true`
     - `computedVisibility: visible`
     - Kotak peringatan menampilkan alasan penolakan secara akurat: *"Nama rekening pengirim tidak sesuai / bukti tidak valid."*
2. **Automated Unit & Feature Testing (PHPUnit)**:
   - `tests/Feature/OrderHistoryTest.php`: 6/6 tests passed.
   - `tests/Feature/BankTransferPaymentProofTest.php`: 12/12 tests passed.
   - **Seluruh Suite Pengujian**: 192 passed (1021 assertions) tanpa kegagalan (100% Green).

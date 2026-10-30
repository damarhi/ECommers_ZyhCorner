# Dokumentasi Perbaikan Penguncian Bukti Transfer, Eliminasi Quick Confirm Transfer Bank, dan Pembaruan Modal Verifikasi

**Tanggal Rilis:** 29 Oktober 2026  
**Status Pengujian:** 100% Lulus (191 Tests, 1001 Assertions)  
**Lingkup Modul:** Kasir & Admin Kelola Penjualan, Sisi Pelanggan (Order Success, Order Detail, Order History)  

---

## 1. Latar Belakang Masalah & Analisis Akar Masalah (Root Causes)

Berdasarkan pengujian alur operasional, ditemukan tiga kendala utama pada alur transfer bank manual:
1. **Pelanggan Masih Dapat Mengirim Bukti Pembayaran Meskipun Pesanan Sudah Dikonfirmasi:**
   - Di sisi backend (`CustomerOrderController@uploadPaymentProof`), pengecekan hanya memvalidasi `payment_status === 'Paid'`. Jika pesanan sudah masuk antrean dapur (`dikemas`), dalam pengiriman (`dikirim`), atau selesai (`selesai`) namun status pembayaran belum diubah atau baru selesai diproses, pelanggan masih memiliki akses untuk mengunggah berkas baru.
   - Di sisi tampilan pelanggan (`order_success.blade.php`, `order_detail.blade.php`, `order_history.blade.php`), tombol aksi upload dan dropzone berkas tetap terbuka selama pembayaran bukan `Paid`, tanpa memeriksa apakah pesanan sudah dalam proses pemrosesan operasional toko.
2. **Bypass Konfirmasi Tanpa Verifikasi Mutasi Rekening (Tombol Quick Confirm Kasir):**
   - Tombol cepat `Konfirmasi & Kemas` pada tabel pesanan online admin (`orders.blade.php`) sebelumnya muncul untuk semua jenis pesanan berstatus `menunggu konfirmasi`.
   - Mengklik tombol ini pada pesanan transfer bank menyebabkan pesanan langsung berstatus `dikemas` **tanpa mengubah status pembayaran menjadi Paid**, membingungkan kasir dan merusak integritas rekonsiliasi kas masuk toko.
3. **Ikon & Alur Modal Bukti Pembayaran Belum Optimal:**
   - Ikon sebelumnya (`mdi-receipt` / struk) kurang spesifik mengindikasikan verifikasi bukti dokumen pembayaran bank.
   - Modal verifikasi admin belum memiliki pilihan preset alasan penolakan cepat jika bukti yang diunggah pelanggan buram, nominal transfer kurang, atau palsu.

---

## 2. Rincian Perubahan Kode & Implementasi

### 2.1 Backend Controllers

#### `app/Http/Controllers/Pelanggan/CustomerOrderController.php`
- Menambahkan *guard condition* ketat pada method `uploadPaymentProof`:
  - Pesanan dengan status `dikemas`, `dikirim`, `selesai`, atau `batal` tidak lagi diizinkan mengunggah bukti pembayaran.
  - Memberikan respons JSON status 422 dengan pesan: *"Pesanan telah dikonfirmasi dan sedang diproses. Pengunggahan bukti pembayaran sudah ditutup."*

#### `app/Http/Controllers/Admin/PesananController.php`
- Menambahkan proteksi pada method `quickConfirm`:
  - Jika pesanan memiliki metode pembayaran bertipe Transfer Bank (`$order->is_bank_transfer`), permintaan `quickConfirm` ditolak dengan respons status 422: *"Pesanan dengan metode Transfer Bank harus diverifikasi melalui bukti pembayaran terlebih dahulu."*
  - Kasir diwajibkan melakukan konfirmasi melalui modal verifikasi bukti (`orders.confirm-proof`).

---

### 2.2 Sisi Kasir & Admin (`resources/views/Admin/kelola_penjualan/`)

#### 1. Tabel Pesanan Online (`orders.blade.php`)
- **Pembaruan Ikon**: Mengganti `<i class="mdi mdi-receipt"></i>` menjadi `<i class="mdi mdi-file-document-check-outline"></i>` pada badge status pembayaran dan tombol aksi.
- **Eliminasi Tombol Cepat Konfirmasi**:
  - Tombol `btn-quick-confirm` (`Konfirmasi & Kemas`) disembunyikan untuk pesanan transfer bank (`!$order->is_bank_transfer`).
  - Kasir hanya dapat mengonfirmasi pesanan transfer bank melalui tombol verifikasi bukti pembayaran dokumen.

#### 2. Tabel Semua Penjualan (`all_sales.blade.php`)
- Memperbarui ikon bukti pembayaran menjadi `<i class="mdi mdi-file-document-check-outline"></i>`.

#### 3. Modal Detail Pesanan (`order_detail_modal.blade.php`)
- Memperbarui tombol pintas verifikasi menjadi `<i class="mdi mdi-file-document-check-outline"></i> Verifikasi Bukti`.

#### 4. Modal Verifikasi Bukti Pembayaran (`verify_proof_modal.blade.php`)
- **Penyempurnaan Form Tolak Bukti Transfer**:
  - Menambahkan tombol aksi tolak: `Tolak Pembayaran` (outline danger).
  - Menambahkan barisan **Preset Alasan Penolakan Cepat** yang dapat diklik satu kali oleh kasir:
    - `[Nominal Kurang]`: *"Nominal transfer tidak sesuai dengan total tagihan."*
    - `[Foto Buram]`: *"Gambar bukti transfer buram atau tidak terbaca."*
    - `[Mutasi Belum Masuk]`: *"Mutasi transfer belum ditemukan pada rekening bank toko."*
    - `[Bukti Tidak Valid]`: *"Nama rekening pengirim tidak sesuai / bukti tidak valid."*
  - Dilengkapi dialog konfirmasi sebelum eksekusi penolakan.
  - Menghapus berkas invalid dari penyimpanan dan mereset pesanan kembali ke `Pending` & `menunggu pembayaran`.

---

### 2.3 Sisi Pelanggan (`resources/views/Pelanggan/`)

#### 1. Halaman Sukses Pemesanan (`order_success.blade.php`)
- Jika pesanan transfer bank telah diverifikasi lunas atau telah masuk status pemrosesan (`dikemas`, `dikirim`, `selesai`):
  - Area dropzone upload berkas dan form input berkas dinonaktifkan sepenuhnya.
  - Menampilkan badge sukses hijau bertuliskan **"Pembayaran Terverifikasi Lunas"** dengan tautan pratinjau bukti transfer dalam mode *read-only*.

#### 2. Halaman Detail Pesanan (`order_detail.blade.php`)
- Memperbarui ikon tombol aksi header dari upload biasa menjadi `<i class="fa-solid fa-file-invoice-dollar"></i>`.
- Jika pesanan sudah lunas atau masuk tahap pemrosesan (`dikemas`, `dikirim`, `selesai`):
  - Tombol beralih menjadi **"Lihat Bukti Bayar"** dengan gaya tombol netral (*read-only*).
  - Modal `#uploadProofModal` menyembunyikan dropzone dan tombol submit kirim berkas jika dibuka dalam mode *read-only*.

#### 3. Halaman Riwayat Pesanan (`order_history.blade.php`)
- Memperbarui tombol kartu pesanan:
  - Tombol upload hanya aktif jika pesanan transfer bank belum lunas dan belum diproses (`menunggu konfirmasi` / `menunggu pembayaran`).
  - Jika pesanan telah dikonfirmasi/lunas, tombol beralih menjadi **"Lihat Bukti"** (read-only viewer) menggunakan modal `#historyProofModal`.

---

## 3. Matriks Pengujian & Verifikasi Kualitas

Pengujian otomatis dijalankan menggunakan PHPUnit:

```bash
php artisan test --filter=BankTransferPaymentProofTest
```
**Hasil:**
- `test_admin_can_update_bank_account_settings_with_sanitization` (PASS)
- `test_non_admin_cannot_update_store_bank_settings` (PASS)
- `test_customer_can_upload_payment_proof_for_own_order` (PASS)
- `test_upload_proof_rejects_invalid_file_types_and_oversized_files` (PASS)
- `test_customer_cannot_upload_proof_to_another_customers_order` (PASS)
- `test_admin_and_kasir_can_confirm_payment_proof` (PASS)
- `test_admin_and_kasir_can_reject_payment_proof_with_reason` (PASS)
- `test_rejection_requires_reason` (PASS)
- `test_customer_views_render_bank_info_and_upload_modal` (PASS)
- `test_customer_cannot_upload_proof_after_order_is_confirmed_or_processing` (PASS - **BARU**)
- `test_quick_confirm_is_blocked_for_bank_transfer_orders` (PASS - **BARU**)

```bash
php artisan test
```
**Hasil Keseluruhan:**  
`Tests: 191 passed (1001 assertions)` — 100% PASS tanpa regresi.

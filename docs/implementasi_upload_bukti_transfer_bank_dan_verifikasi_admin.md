# Dokumentasi Implementasi Alur Pembayaran Transfer Bank Manual, Unggah Bukti Bayar, dan Verifikasi Kasir/Admin

**Tanggal Rilis:** 29 Oktober 2026  
**Status Pengujian:** 100% Lulus (189 Tests, 995 Assertions)  
**Lingkup Modul:** Administrasi Toko, Checkout & Sukses Pembayaran, Riwayat Pesanan Pelanggan, dan Kelola Penjualan Kasir/Admin  

---

## 1. Ringkasan Eksekutif & Tujuan

Sebelum implementasi ini, sistem toko Zyh Corner mendukung metode pembayaran COD dan Online Payment (Midtrans Snap). Untuk pesanan dengan metode **Transfer Bank Manual**, pelanggan memerlukan informasi nomor rekening resmi toko yang jelas, fitur untuk mengunggah berkas bukti transfer secara langsung, serta antarmuka bagi Admin/Kasir untuk memverifikasi keabsahan pembayaran sebelum pesanan diproses di dapur (status *dikemas*).

Fitur yang diimplementasikan mencakup:
1. **Pengaturan Rekening Resmi Toko** di Tab Info Toko Admin (`/admin/settings#store-tab`) lengkap dengan audit keamanan sanitasi input dan validasi anti-XSS.
2. **Tampilan Rekening & Dropzone Bukti Transfer pada Halaman Order Success** (`order-success`): Menampilkan detail rekening toko dengan tombol salin instan serta form upload bukti pembayaran tepat di atas tombol aksi.
3. **Tombol & Modal Upload Bukti Pembayaran pada Halaman Pelanggan**:
   - Detail Pesanan (`/order-history/{id}`)
   - Riwayat Pesanan (`/order-history`)
4. **Tombol & Modal Verifikasi Bukti Pembayaran untuk Admin & Kasir**:
   - Pesanan Online Admin (`/admin/orders`)
   - Semua Penjualan (`/admin/all-sales`)
   - Fitur konfirmasi lunas (otomatis mengubah status menjadi *Paid* dan *dikemas*) dan tolak bukti transfer (dengan alasan penolakan dan penghapusan berkas invalid).
5. **Feature Test Otomatis**: 9 skenario pengujian komprehensif pada `tests/Feature/BankTransferPaymentProofTest.php`.

---

## 2. Struktur Basis Data & Perubahan Model

### 2.1 Migrasi Tabel `pesanans`
File: `database/migrations/2026_10_29_081845_add_bank_transfer_proof_to_pesanans_table.php`

Kolom baru yang ditambahkan:
- `bukti_pembayaran` (`string(255)`, nullable): Menyimpan path berkas bukti transfer di disk `public` (misal: `bukti_pembayaran/hash_acak.jpg`).
- `bukti_pembayaran_uploaded_at` (`timestamp`, nullable): Waktu pengunggahan berkas oleh pelanggan.
- `catatan_verifikasi_pembayaran` (`text`, nullable): Catatan riwayat verifikasi kasir/admin (misal: "Diverifikasi Lunas oleh Kasir" atau "Bukti ditolak: Nominal tidak sesuai").

### 2.2 Model `Pesanan` (`app/Models/pesanan.php`)
- **Fillable Attributes**: Ditambahkan `bukti_pembayaran`, `bukti_pembayaran_uploaded_at`, dan `catatan_verifikasi_pembayaran`.
- **Casts**: `bukti_pembayaran_uploaded_at` di-cast sebagai `datetime`.
- **Accessors**:
  - `has_payment_proof`: Mengembalikan `true` jika kolom `bukti_pembayaran` terisi dan berkas fisik ada di storage.
  - `payment_proof_url`: Menghasilkan URL publik berkas gambar bukti via `Storage::disk('public')->url(...)` atau `asset('storage/' . ...)`.
  - `is_bank_transfer`: Mendeteksi apakah metode pembayaran pesanan bertipe transfer bank (mengandung kata `TRANSFER` atau `BANK` dan bukan COD/E-Wallet).

---

## 3. Pengaturan Rekening Resmi Toko di Admin

### 3.1 Backend Controller (`AdminSettingsController.php`)
- **Keamanan Input**: Sanitasi ekstra diterapkan pada input `bank_name`, `bank_account_number`, dan `bank_account_holder`:
  - Membersihkan seluruh tag skrip (`<script>...</script>`) dan HTML menggunakan regex dan `strip_tags()`.
  - Mencegah serangan Stored XSS.
- **Data Persistence**: Disimpan ke database via model `StoreSetting` dengan kategori `payment`.
- **Fallback Nilai Default**:
  - Bank Name: `Bank Central Asia (BCA)`
  - No. Rekening: `8295-0192-38`
  - Atas Nama: `ZYH CORNER PASTRY`

### 3.2 Tampilan Antarmuka Admin (`settings.blade.php`)
- Ditambahkan kartu form khusus **Rekening Pembayaran Toko (Transfer Bank)** di bawah Tab Informasi Toko (`#store-tab`).
- Dilengkapi petunjuk format dan keamanan bagi administrator.

---

## 4. Antarmuka Pengguna & Unggah Bukti Bayar Pelanggan

### 4.1 Halaman Order Success (`order_success.blade.php`)
- **Kartu Rekening Resmi**: Menampilkan logo bank, nama pemilik rekening, nomor rekening tebal monospace, tombol salin ke clipboard instan dengan toast feedback, dan total nominal tagihan yang harus dibayar.
- **Area Upload Bukti Transfer**:
  - Drag-and-drop zone dengan pratinjau thumbnail instan.
  - Validasi sisi klien: Berkas wajib berupa gambar (`image/jpeg`, `image/png`, `image/webp`) dan ukuran maksimal 2MB.
  - Menampilkan badge "Bukti Terkirim" jika pelanggan sebelumnya telah mengunggah bukti.
- **Tombol Aksi Dinamis**: Tombol Pay Now berubah fungsi menjadi **Kirim Bukti Pembayaran** (atau **Kirim Ulang Bukti Pembayaran** jika ingin mengganti bukti). Berkas dikirim via AJAX FormData dengan indikator loading.

### 4.2 Halaman Detail Pesanan (`order_detail.blade.php`)
- **Tombol Header**: Tombol biru muda `Upload Bukti Bayar` (atau `Ganti Bukti Bayar`) muncul untuk pesanan transfer bank yang belum lunas.
- **Status Pembayaran**: Rincian tagihan menampilkan baris **Bukti Transfer: Terkirim (Lihat)**.
- **Modal `#uploadProofModal`**: Modal responsif bergaya Biteship modal yang berisi info rekening toko, pratinjau berkas saat ini, dropzone unggah bukti baru, serta tombol simpan.

### 4.3 Halaman Riwayat Pesanan (`order_history.blade.php`)
- **Tombol Aksi Kartu**: Ditambahkan tombol aksi `Upload Bukti` / `Ganti Bukti` pada setiap kartu pesanan transfer bank.
- **Modal Terintegrasi `#historyProofModal`**: Modal dinamis yang otomatis mengikat ID pesanan yang dipilih, menampilkan total tagihan, nomor rekening toko, serta dropzone upload berkas.

### 4.4 Endpoint Upload Bukti Pelanggan (`CustomerOrderController@uploadPaymentProof`)
- **Rute**: `POST /order-history/{id}/upload-proof`
- **Keamanan & Validasi**:
  - `auth` middleware & IDOR Check: Memastikan pesanan milik email pelanggan yang sedang login (`$order->email_pembeli === $user->email`).
  - Validasi berkas: `required|image|mimes:jpeg,png,jpg,webp|max:2048`.
  - Storage: Berkas di-hash dan disimpan pada direktori aman `public/bukti_pembayaran`.
  - Berkas lama otomatis dihapus dari disk jika pelanggan mengunggah bukti pengganti.
  - Status pesanan diperbarui menjadi `menunggu konfirmasi`.
  - Mendukung respons JSON untuk AJAX maupun redirect HTTP standar.

---

## 5. Verifikasi Bukti Pembayaran oleh Admin & Kasir

### 5.1 Indikator & Tombol Aksi Tabel
Diterapkan pada:
- `resources/views/Admin/kelola_penjualan/orders.blade.php` (Pesanan Online)
- `resources/views/Admin/kelola_penjualan/all_sales.blade.php` (Semua Penjualan)
- `resources/views/Admin/kelola_penjualan/order_detail_modal.blade.php` (Modal Detail Pesanan)

**Komponen Visual:**
1. Kolom Status Pembayaran: Jika pelanggan telah mengunggah bukti, muncul badge interaktif berkedip lembut `<i class="mdi mdi-receipt me-1"></i>Bukti Transfer` yang dapat diklik langsung untuk membuka modal verifikasi.
2. Kolom Aksi: Ditambahkan tombol warna amber berikon kwitansi (`mdi-receipt`) bertuliskan tooltip "Verifikasi Bukti Transfer".

### 5.2 Modal Verifikasi Bukti (`verify_proof_modal.blade.php`)
- **Desain 2 Kolom Seimbang**:
  - **Sisi Kiri**: Tampilan foto bukti transfer beresolusi penuh dengan tautan zoom.
  - **Sisi Kanan**: Rincian transaksi (metode, nominal, waktu unggah, status saat ini, catatan riwayat verifikasi).
- **Aksi Konfirmasi Lunas**:
  - Mengirim request `POST /admin/orders/{id}/confirm-proof`.
  - Status pembayaran berubah menjadi `Paid`.
  - Status pesanan berubah menjadi `dikemas`.
  - Dicatat waktu dan aktor verifikasi pada `catatan_verifikasi_pembayaran`.
- **Aksi Tolak Bukti Transfer**:
  - Kolaps formulir alasan penolakan (`alasan_penolakan`).
  - Mengirim request `POST /admin/orders/{id}/reject-proof`.
  - Berkas bukti yang salah/palsu dihapus dari disk storage.
  - Status pesanan dikembalikan menjadi `Pending` & `menunggu pembayaran`.
  - Alasan penolakan dicatat agar terbaca oleh kasir maupun pelanggan.

---

## 6. Hasil Pengujian & Verifikasi Kualitas

### 6.1 Unit & Feature Test Otomatis (`BankTransferPaymentProofTest.php`)
Dibuat pengujian fitur lengkap dengan 9 skenario:
1. `test_admin_can_update_bank_account_settings_with_sanitization` (Lulus)
2. `test_non_admin_cannot_update_store_bank_settings` (Lulus)
3. `test_customer_can_upload_payment_proof_for_own_order` (Lulus)
4. `test_upload_proof_rejects_invalid_file_types_and_oversized_files` (Lulus)
5. `test_customer_cannot_upload_proof_to_another_customers_order` (Lulus - IDOR Protection)
6. `test_admin_and_kasir_can_confirm_payment_proof` (Lulus)
7. `test_admin_and_kasir_can_reject_payment_proof_with_reason` (Lulus)
8. `test_rejection_requires_reason` (Lulus)
9. `test_customer_views_render_bank_info_and_upload_modal` (Lulus)

### 6.2 Ringkasan Hasil Uji Keseluruhan
Menjalankan `php artisan test`:
```
Tests:    189 passed (995 assertions)
Duration: 42.81s
```
Seluruh 189 unit & feature test dalam repository berjalan **100% HIJAU (PASS)** tanpa ada kegagalan maupun regresi.

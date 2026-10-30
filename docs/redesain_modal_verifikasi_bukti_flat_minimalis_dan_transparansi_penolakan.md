# Dokumentasi Redesain Modal Verifikasi Bukti Flat Minimalis & Transparansi Penolakan Pelanggan

## 1. Ringkasan Eksekutif & Tujuan Perubahan

Pembaruan ini menuntaskan penyempurnaan menyeluruh pada alur verifikasi dan penolakan bukti pembayaran transfer bank manual antara antarmuka kasir/admin dan antarmuka pelanggan:
1. **Redesain Modal Verifikasi Kasir/Admin (`verify_proof_modal.blade.php`)**:
   - Menghilangkan efek gradasi warna (`btn-gradient-primary`, multi-layer gradient) dan mengadopsi gaya visual **flat minimalis** yang bersih sesuai panduan desain antarmuka (`#00b207` untuk aksi positif dan `#dc2626` untuk aksi tolak/peringatan).
   - Mengubah formulir alasan penolakan menjadi **langsung aktif dan terlihat** di kolom kanan modal (tanpa memerlukan klik buka accordion/collapse), dengan *preset chips* alasan cepat yang mudah dipilih dan textarea alasan penolakan yang bersih.
   - Mengeliminasi duplikasi tombol (tombol aksi penolakan dan konfirmasi dipusatkan rapi pada footer modal tanpa tombol ganda di dalam formulir).
   - Mengintegrasikan dialog konfirmasi interaktif berbasis **SweetAlert2** (`Swal.fire`) baik untuk persetujuan pelunasan maupun penolakan bukti pembayaran (dilengkapi validasi alasan wajib diisi).
2. **Transparansi Penolakan pada Antarmuka Pelanggan**:
   - Jika bukti pembayaran ditolak oleh kasir/admin, sistem secara otomatis menampilkan alasan penolakan secara transparan dan jelas kepada pelanggan.
   - Seluruh teks tombol aksi di sisi pelanggan otomatis beralih dari teks ambigu *"Ganti Bukti"* menjadi **"Upload Ulang Bukti"** atau **"Upload Ulang Bukti Pembayaran"** pada halaman:
     - `order_success.blade.php`
     - `order_detail.blade.php`
     - `order_history.blade.php`
3. **Pengujian Menyeluruh (Automated Testing)**:
   - Penambahan skenario Feature Test pada `BankTransferPaymentProofTest.php` yang memverifikasi bahwa alasan penolakan tampil di sisi pelanggan dan tombol berubah menjadi "Upload Ulang Bukti", serta berkas bukti baru dapat diunggah ulang.
   - Seluruh rangkaian pengujian PHPUnit lulus 100% (192 tests, 1021 assertions).

---

## 2. Rincian Modifikasi File & Perubahan Kode

### A. Model Pesanan (`app/Models/pesanan.php`)
Menambahkan dua accessor cerdas untuk mempermudah pengecekan status penolakan bukti transfer di seluruh tampilan pelanggan dan komponen lainnya:

```php
public function getIsPaymentProofRejectedAttribute(): bool
{
    if (empty($this->catatan_verifikasi_pembayaran)) {
        return false;
    }
    return str_contains(strtolower($this->catatan_verifikasi_pembayaran), 'ditolak') && empty($this->bukti_pembayaran);
}

public function getPaymentProofRejectionReasonAttribute(): ?string
{
    if (!$this->is_payment_proof_rejected) {
        return null;
    }
    $text = $this->catatan_verifikasi_pembayaran;
    // Format standar: "Bukti ditolak: <alasan> (<timestamp> WIB)"
    if (preg_match('/Bukti ditolak:\s*(.*?)(?:\s*\([^\)]+\))?$/i', $text, $matches)) {
        return trim($matches[1]);
    }
    return $text;
}
```

---

### B. Modal Verifikasi Bukti Kasir (`resources/views/Admin/kelola_penjualan/verify_proof_modal.blade.php`)

1. **Tata Letak Bersih & Tanpa Efek Gradasi (Flat Minimalis)**:
   - Menghilangkan `linear-gradient` pada tombol konfirmasi dan menggantinya dengan warna solid `#00b207` (green flat).
   - Tombol tolak pembayaran menggunakan warna solid `#dc2626` (red flat) dengan border selaras.
   - Border radius konsisten 6px–8px dan border halus `#e2e8f0`/`#cbd5e1`.

2. **Formulir Alasan Penolakan Langsung Aktif**:
   - Mengeliminasi blok collapse `#collapseRejectForm{{ $order->id }}` sehingga kasir tidak perlu mencari tombol tersembunyi untuk mengisi alasan.
   - Menyediakan 4 buah chip preset alasan cepat (Pill buttons):
     - `Nominal Kurang`: "Nominal transfer tidak sesuai dengan total tagihan."
     - `Foto Buram`: "Gambar bukti transfer buram atau tidak terbaca."
     - `Mutasi Belum Masuk`: "Mutasi transfer belum ditemukan pada rekening bank toko."
     - `Bukti Tidak Valid`: "Nama rekening pengirim tidak sesuai / bukti tidak valid."
   - Mengeliminasi tombol internal di dalam kartu formulir, sehingga footer modal menjadi pengendali utama.

3. **Integrasi SweetAlert2 (`Swal.fire`)**:
   - Fungsi `handleConfirmProofWithSwal(orderId, orderNumber)`: menampilkan konfirmasi ramah sebelum mengubah status pesanan menjadi Lunas & Dikemas.
   - Fungsi `handleRejectProofWithSwal(orderId, orderNumber)`:
     - Melakukan validasi textarea alasan penolakan. Jika kosong, SweetAlert memperingatkan kasir dan secara otomatis memfokuskan kursor ke textarea.
     - Jika alasan terisi, menampilkan konfirmasi penolakan yang mencantumkan kutipan alasan yang akan dikirimkan kepada pelanggan.
     - Menyediakan fallback `window.confirm` jika library SweetAlert2 belum selesai diinisiasi di browser klien.

---

### C. Halaman Sukses Pesanan Pelanggan (`resources/views/Pelanggan/order_success.blade.php`)

1. **Pendeteksian Bukti Ditolak**:
   - Memeriksa kondisi `$order->is_payment_proof_rejected`.
   - Mengubah ikon status menjadi segitiga peringatan merah (`fa-solid fa-triangle-exclamation`) berlatar `#fef2f2`.
   - Judul status berubah menjadi: **"Bukti Pembayaran Ditolak Kasir"**.

2. **Banner Transparansi Alasan Penolakan**:
   - Menampilkan alert box bernuansa merah lembut (`#fef2f2`, border `#fecaca`) yang secara transparan menampilkan catatan penolakan dari kasir:
     ```blade
     @if($isRejected)
         <div class="rejection-alert-box p-3 mb-3 rounded-3" style="background: #fef2f2; border: 1px solid #fecaca; text-align: left;">
             <div class="d-flex align-items-start gap-2.5">
                 <i class="fa-solid fa-circle-exclamation text-danger mt-0.5" style="font-size: 16px;"></i>
                 <div>
                     <div style="font-size: 13px; font-weight: 700; color: #991b1b; margin-bottom: 2px;">
                         Alasan Penolakan:
                     </div>
                     <div style="font-size: 12.5px; color: #b91c1c;">
                         {{ $order->payment_proof_rejection_reason ?? 'Bukti pembayaran sebelumnya tidak valid. Mohon unggah bukti transfer yang sah.' }}
                     </div>
                 </div>
             </div>
         </div>
     @endif
     ```

3. **Perubahan Teks & Gaya Tombol Aksi**:
   - Teks tombol utama beralih menjadi: **"Upload Ulang Bukti Pembayaran"** berlatar merah solid `#dc2626`.
   - Label dropzone berubah menjadi: *"Klik atau Drag Foto Bukti Transfer Baru ke Sini"*.

---

### D. Halaman Rincian Pesanan Pelanggan (`resources/views/Pelanggan/order_detail.blade.php`)

1. **Tombol Header Aksi**:
   - Jika bukti ditolak (`$order->is_payment_proof_rejected`), tombol berubah menjadi **"Upload Ulang Bukti"** dengan gaya solid merah `#dc2626`.
2. **Banner Penolakan Teratas**:
   - Menambahkan banner informatif di bawah header pesanan yang memuat pesan penolakan, alasan kasir, dan tombol cepat *"Upload Ulang Bukti"*.
3. **Baris Bukti Transfer pada Kartu Ringkasan Pembayaran**:
   - Menampilkan indikator bergaris bawah merah: `<i class="fa-solid fa-circle-exclamation me-1"></i> Ditolak (Upload Ulang)` yang membuka modal upload saat diklik.
4. **Modal Unggah Bukti (`#uploadProofModal`)**:
   - Menampilkan kotak peringatan di bagian atas modal yang menjelaskan alasan bukti sebelumnya ditolak.
   - Tombol simpan modal berubah menjadi **"Simpan & Kirim Ulang Bukti"**.

---

### E. Halaman Riwayat Pesanan Pelanggan (`resources/views/Pelanggan/order_history.blade.php`)

1. **Tombol Kartu Pesanan**:
   - Tombol beralih menjadi **"Upload Ulang Bukti"** berlatar `#dc2626` jika status bukti ditolak.
   - Nilai flag `isRejected` dan string alasan dioper ke fungsi JavaScript `openHistoryProofModal(...)`.
2. **Modal Unggah Riwayat (`#historyProofModal`)**:
   - Mengontrol elemen `#histRejectionAlertBox` secara dinamis.
   - Menampilkan alasan penolakan di dalam modal dan menyesuaikan tombol aksi menjadi **"Simpan & Kirim Ulang Bukti"**.

---

### F. Pengujian Fitur (`tests/Feature/BankTransferPaymentProofTest.php`)

Menambahkan metode pengujian `test_customer_views_display_rejection_reason_and_upload_ulang_when_proof_is_rejected()`:
- Mensimulasikan kasir menolak bukti transfer dengan alasan: *"Nominal transfer tidak sesuai dengan total tagihan."*.
- Memastikan accessor `is_payment_proof_rejected` bernilai `true` dan `payment_proof_rejection_reason` memuat alasan yang tepat.
- Memverifikasi halaman `order.success` menampilkan judul *"Bukti Pembayaran Ditolak Kasir"*, alasan penolakan, dan tombol *"Upload Ulang Bukti Pembayaran"*.
- Memverifikasi halaman `order.detail` menampilkan banner penolakan dan tombol *"Upload Ulang Bukti"*.
- Memverifikasi halaman `order.history` menampilkan tombol *"Upload Ulang Bukti"*.
- Memverifikasi pelanggan dapat mengunggah ulang bukti transfer baru, di mana status penolakan kembali bersih (`false`) dan bukti baru tersimpan secara aman.

---

## 3. Hasil Eksekusi Pengujian Otomatis

Rangkaian pengujian dijalankan melalui PHPUnit dengan hasil:

```text
Tests\Feature\BankTransferPaymentProofTest:
✓ admin can update bank account settings with sanitization
✓ non admin cannot update store bank settings
✓ customer can upload payment proof for own order
✓ upload proof rejects invalid file types and oversized files
✓ customer cannot upload proof to another customers order
✓ admin and kasir can confirm payment proof
✓ admin and kasir can reject payment proof with reason
✓ rejection requires reason
✓ customer views render bank info and upload modal
✓ customer cannot upload proof after order is confirmed or processing
✓ quick confirm is blocked for bank transfer orders
✓ customer views display rejection reason and upload ulang when proof is rejected

Total Keseluruhan Suite:
Tests:    192 passed (1021 assertions)
Duration: 44.63s
```

Seluruh 192 pengujian di aplikasi lulus tanpa kegagalan (0 regressions).

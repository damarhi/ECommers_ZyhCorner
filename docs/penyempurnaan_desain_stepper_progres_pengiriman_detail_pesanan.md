# Dokumentasi Penyempurnaan Desain Stepper Progres Pengiriman pada Detail Pesanan

Dokumen ini mencatat pembaruan antarmuka dan perbaikan visual pada komponen kartu pelacakan status progres pengiriman (`.tracking-card` / `.stepper-container`) di halaman detail pesanan pelanggan (`resources/views/Pelanggan/order_detail.blade.php`).

---

## 1. Latar Belakang Masalah

Berdasarkan inspeksi visual terhadap tangkapan layar kartu pelacakan (`image-e495bbda5981cc90.png`), ditemukan sejumlah kelemahan desain:
1. **Ikonografi Numerik Generik**:
   * Lingkaran tahapan hanya menampilkan angka `"01"`, `"02"`, `"03"`, `"04"`, `"05"` saat belum selesai, yang terkesan kaku seperti formulir pendaftaran konvensional dan minim konteks visual.
2. **Ketidakseimbangan Baseline Vertikal (*Jagged Baseline*)**:
   * Langkah 2 (*Verifikasi Pembayaran*) memiliki sub-badge `"Bayar saat diterima"`.
   * Langkah 3 (*Dikonfirmasi & Dikemas*) memiliki sub-badge `"Dapur Zyh Corner"`.
   * Langkah 1, 4, dan 5 **tidak memiliki sub-info sama sekali**, sehingga area bawah langkah 1, 4, dan 5 kosong melompong sementara langkah 2 dan 3 padat ke bawah.
3. **Fokus Status Aktif (*Hero Active Node*) Kurang Kuat**:
   * Tahap yang sedang berlangsung hanya ditandai lingkaran putih bergaris hijau tipis dengan teks nomor, tanpa kedalaman visual yang mencerminkan status pesanan yang sedang aktif diproses.
4. **Header Kartu Bersifat Statis**:
   * Judul kartu hanya berupa teks polos tanpa indikator *live status badge* atau denyut sinyal (*live pulsing indicator*).

---

## 2. Solusi Desain yang Diimplementasikan

Mengacu pada prinsip **`frontend-design-direction`**, perubahan yang diterapkan meliputi:

### A. Ikonografi Kontekstual Tematik
Mengganti angka statis `01`–`05` dengan ikon FontAwesome yang kontekstual dan komunikatif:
- **Langkah 1 (Pesanan Dibuat)**: Ikon `fa-receipt` (nota masuk) saat belum selesai / aktif, dan `fa-check` saat selesai.
- **Langkah 2 (Verifikasi Pembayaran)**: Ikon `fa-credit-card` saat belum selesai / aktif, dan `fa-check` saat selesai.
- **Langkah 3 (Dikonfirmasi & Dikemas)**: Ikon `fa-box-open` saat belum selesai / aktif, dan `fa-check` saat selesai.
- **Langkah 4 (Sedang Dikirim)**: Ikon `fa-truck-fast` saat belum selesai / aktif, dan `fa-check` saat selesai.
- **Langkah 5 (Pesanan Selesai)**: Ikon `fa-house-circle-check` saat belum selesai / aktif, dan `fa-check` saat selesai.

### B. Keselarasan Baseline Vertikal (*Symmetric 2-Tier Sub-Meta*)
Setiap tahapan kini dibungkus dalam kontainer `.step-content` dan `.step-sub-wrap` berketinggian seragam (`min-height: 24px;`), dengan sub-meta yang terdefinisi rapi di kelima tahapan:
1. **Langkah 1**: Waktu pencatatan pesanan (`{{ $order->created_at->format('d M, H:i') }}` dengan ikon kalender).
2. **Langkah 2**: Metode & status bayar (`Bayar saat diterima` / `Lunas` / `Menunggu`).
3. **Langkah 3**: Keterangan dapur (`Dapur Zyh Corner` / `Menunggu Konfirmasi` / `Persiapan`).
4. **Langkah 4**: Informasi kurir pengantaran (`{{ $order->shipping_method ?? 'Kurir Pengantaran' }}`).
5. **Langkah 5**: Keterangan tujuan penerimaan (`Tiba di Tujuan` / `Tujuan Alamat`).

### C. Penegasan Status Aktif (*Hero Active Node & Glow*)
- Lingkaran aktif (`.step-item.active .step-circle`) menggunakan latar hijau muda lembut (`#f0fdf4`), border hijau tebal (`#00b207`), ikon hijau kontras, serta *double ring glow*:
  ```css
  box-shadow: 0 0 0 5px rgba(0, 178, 7, 0.15), 0 3px 8px rgba(0, 178, 7, 0.2);
  ```
- Garis penghubung aktif (`.stepper-line-active`) menggunakan gradien hijau dinamis:
  ```css
  background: linear-gradient(90deg, #00b207 0%, #10b981 100%);
  box-shadow: 0 1px 3px rgba(0, 178, 7, 0.25);
  ```

### D. Header Kartu yang Dinamis (*Live Status Chip & Capsule Estimasi*)
- Menyematkan **Chip Status Aktif Berdenyut** di samping judul pelacakan:
  ```html
  <span class="stepper-live-chip">
      <span class="live-dot-pulse"></span>
      {{ $pillLabel }}
  </span>
  ```
  Titik berdenyut menggunakan animasi CSS `@keyframes liveDotGlow` yang halus dan konsisten.
- Mengubah tampilan estimasi tiba menjadi kapsul pill yang elegan (`.tracking-est-pill`).

### E. Kompatibilitas Responsif Mobile
- Pada layar mobile (`@media (max-width: 768px)`), struktur `.step-content` otomatis beralih menjadi tata letak baris horizontal dengan perataan kiri (`align-items: flex-start; text-align: left;`), menjaga keterbacaan teks dan sub-badge di samping lingkaran ikon tanpa bertumpuk.

---

## 3. Rincian Perubahan Kode

### File: `resources/views/Pelanggan/order_detail.blade.php`

1. **Pembaruan Gaya CSS**:
   - Menambahkan class `.tracking-title-wrap`, `.stepper-live-chip`, `.live-dot-pulse`, `@keyframes liveDotGlow`, dan `.tracking-est-pill`.
   - Mengubah `.stepper-line-active` dengan gradien hijau dan bayangan lembut.
   - Memperbarui gaya lingkaran `.step-circle`, `.step-item.active .step-circle`, dan `.step-item.completed .step-circle`.
   - Menambahkan `.step-content` dan `.step-sub-wrap` untuk perataan baseline vertikal.
   - Menambahkan varian badge `.step-sub-badge.sub-neutral` dan `.step-sub-badge.sub-info`.
   - Mengatur perilaku responsif mobile untuk `.step-content` dan `.step-sub-wrap`.

2. **Pembaruan Markup Blade**:
   - Header kartu pelacakan kini menampilkan `.stepper-live-chip` dengan `$pillLabel` dan `.tracking-est-pill`.
   - Tiap `step-circle` menggunakan ikon FontAwesome kontekstual saat status belum selesai (bukan lagi string nomor `"01"`, `"02"`, `"03"`, `"04"`, `"05"`).
   - Kelima langkah membungkus `.step-label` dan `.step-sub-wrap` ke dalam `.step-content` dengan sub-badge kontekstual yang seragam.

---

## 4. Hasil Pengujian & Verifikasi

1. **Pengujian Khusus Layout Detail Pesanan**:
   ```bash
   php artisan test --filter=OrderDetailLayoutTest
   ```
   **Hasil**:
   - Tests: **7 passed** (61 assertions)
   - Duration: 1.67s
   - Seluruh assertion teks wajib (`Status Progres Pengiriman`, `Estimasi Tiba:`, `Pesanan Dibuat`, `Verifikasi Pembayaran`, `Dikonfirmasi & Dikemas`, `Sedang Dikirim`, `Pesanan Selesai`) lulus 100%.

2. **Pengujian Menyeluruh Seluruh Aplikasi**:
   ```bash
   php artisan test
   ```
   **Hasil**:
   - Tests: **130 passed** (653 assertions)
   - Duration: 25.64s
   - Tidak ada regresi fungsional di modul pelanggan, kasir, admin, transaksi, maupun stok.

3. **Kompilasi Aset Frontend (Vite)**:
   ```bash
   npm run build
   ```
   **Hasil**:
   - Vite v8.0.9 berhasil melakukan build aset produksi dalam 499ms tanpa error linting/bundling.

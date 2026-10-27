# Dokumentasi Teknis: Perbaikan Tata Letak Halaman Detail Pesanan Pelanggan

## 1. Ringkasan Eksekutif & Latar Belakang
Halaman Detail Pesanan Pelanggan (`resources/views/Pelanggan/order_detail.blade.php`, rute `/order-history/{id}`) sebelumnya mengalami kerusakan struktural dan visual:
1. **Kerusakan Hirarki Grid HTML (Cacat Kritis)**: Terdapat tag penutup `</div>` kolom alamat yang hilang pada baris 794 (`col-md-4`). Akibatnya, seluruh kontainer setelahnya (kartu estimasi tiba, pelacakan pengiriman/stepper, kartu ringkasan biaya, dan tabel rincian produk) terdorong masuk ke dalam kolom pertama selebar 4 grid desktop. Hal ini membuat tata letak miring, terhimpit ke sisi kiri, dan merusak tata ruang visual.
2. **Data Statis / Hardcode yang Menyesatkan**:
   - Status pembayaran dipaksa menampilkan teks hardcode `Paid` terlepas dari status pesanan yang dibatalkan (`Cancelled`) atau belum bayar (`Unpaid`).
   - Metode pembayaran menggunakan logika ganjil `Str::contains(..., 'pembayaran') ? 'Paypal' : 'COD'`.
   - Script JavaScript di bagian bawah menimpa data alamat penagihan dari `localStorage` browser sehingga informasi historis penerima aktual pesanan rentan terdistorsi.
3. **Syntax Error CSS Mobile**: Penggunaan tag Blade `{{ $connectorWidth }}` di dalam deklarasi CSS inline `@media (max-width: 768px)` menyebabkan kegagalan parse CSS saat dieksekusi browser.
4. **Asimetri Kartu Ringkasan Informasi**: Kartu Alamat Pengiriman, Info Pengiriman, dan Rincian Biaya memiliki tinggi yang tidak beraturan dan visual yang tidak rapi.

Perbaikan ini mengadopsi standar **`frontend-design-direction`** Zyh Corner (Forest Green `#00b207`, netral slate `#0f172a`/`#64748b`, radius simetris 12px dan 50px pill) untuk merestrukturisasi halaman menjadi simetris, responsif, dan dinamis.

---

## 2. Arsitektur Tata Letak Baru (Frontend Design Direction)

### 2.1 Hirarki Visual Vertikal
Tata letak halaman detail pesanan diatur dalam 5 zona visual terpadu:
1. **Header Pesanan (`details-header-flex`)**:
   - Nomor ID Pesanan `#XXXXXX` (`details-title`) dengan penekanan font tebal 22px.
   - Status Badge Pill (`order-status-pill`) adaptif dengan palet semantik:
     - *Sedang Diproses / Dikemas*: Kuning/Amber (`#fef3c7`, border `#fde68a`, teks `#d97706`).
     - *Sedang Dikirim*: Biru Langit (`#e0f2fe`, border `#bae6fd`, teks `#0284c7`).
     - *Pesanan Selesai*: Hijau Zyh (`#e8f8ed`, border `#b7ebc5`, teks `#00b207`).
     - *Pesanan Dibatalkan*: Abu-abu/Slate (`#f1f5f9`, border `#e2e8f0`, teks `#64748b`).
     - *Menunggu Pembayaran*: Merah Muda (`#fee2e2`, border `#fecaca`, teks `#dc2626`).
   - Metadata tanggal pesanan dan kuantitas produk.
   - Tombol aksi kontekstual: *Kembali*, *Batalkan Pesanan* (dengan modal alasan pembatalan modern), *Bayar Sekarang*, atau *Ajukan Retur*.
2. **Alert / Countdown Banner**:
   - Banner batas waktu pembayaran Midtrans bila pesanan berstatus pending.
   - Banner status retur atau banner pembatalan pesanan bila pesanan dibatalkan.
3. **Status Progres Pengiriman (Tracking Stepper Card)**:
   - Ditempatkan tepat di atas ringkasan untuk memberi visibilitas instan kepada pelanggan mengenai milestone pesanan.
   - Terdiri dari 4 milestone linear: *Pesanan Dibuat*, *Diproses / Dikemas*, *Sedang Dikirim*, dan *Pesanan Selesai*.
   - Garis progres adaptif (`stepper-line-active`) dan ikon status dengan visual checklist hijau.
4. **Trio Kartu Ringkasan Simetris (`summary-card`)**:
   - Grid simetris 3 kolom desktop (`col-md-4 mb-4`) dengan tinggi 100% seragam (`height: 100%`, `display: flex; flex-direction: column; justify-content: space-between;`).
   - **Kartu 1 (Alamat Pengiriman)**: Nama penerima, alamat lengkap pengiriman, nomor telepon berformat `(+62)`, dan email pembeli.
   - **Kartu 2 (Info Pengiriman)**: Kurir pengiriman, estimasi tanggal/waktu tiba, status paket, dan nomor resi pengiriman.
   - **Kartu 3 (Rincian Pembayaran)**: Metode pembayaran aktual (`COD`, `MIDTRANS (BCA)`, dll), badge status lunas/belum bayar, rincian subtotal produk, diskon kupon, ongkos kirim, dan total belanja.
5. **Rincian Produk Dipesan (`products-table-card`)**:
   - Tabel responsif daftar item produk yang dipesan.
   - Thumbnail foto produk rasio 1:1 (60px x 60px) dengan container rounded 8px.
   - Badge teks kustom kue (*Tulisan Kue: "..."*) untuk produk yang mendukung personalisasi tulisan.
   - Kolom harga satuan, kuantitas (`qty-badge`), dan subtotal per item.

---

## 3. Rincian Perubahan Berkas

### 3.1 `resources/views/Pelanggan/order_detail.blade.php`
- **Perbaikan Penutup Tag**: Menutup tag `col-md-4` alamat pengiriman dan memisahkan baris kartu ringkasan dari kontainer tracking stepper.
- **Isolasi Variabel CSS Stepper Mobile**: Mengganti Blade interpolation CSS syntax error dengan custom property `--mobile-stepper-height`:
  ```css
  .stepper-line-active {
      top: 20px;
      left: 31px;
      width: 4px !important;
      height: var(--mobile-stepper-height, 0%) !important;
  }
  ```
- **Ketinggian Seragam Kartu Ringkasan**:
  ```css
  .summary-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 22px;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 1px 3px rgba(0,0,0,0.02);
  }
  ```
- **Pembersihan Data Dinamis**:
  - Metode bayar: `strtoupper($order->payment_method ?? 'COD')`.
  - Status bayar:
    ```blade
    @if(in_array(strtolower($order->payment_status ?? ''), ['paid', 'settlement', 'capture', 'success']))
        <span class="badge" style="background: #e8f8ed; color: #00b207; ...">Lunas</span>
    @elseif(in_array(strtolower($order->payment_status ?? ''), ['cancelled', 'cancel', 'batal']))
        <span class="badge" style="background: #fee2e2; color: #dc2626; ...">Batal</span>
    @else
        <span class="badge" style="background: #fef3c7; color: #d97706; ...">Menunggu</span>
    @endif
    ```
  - Alamat & telepon: Menggunakan data pesanan aktual `$order->customer_name`, `$order->alamat_pengiriman`, dan `$user->no_hp`, serta memprioritaskan data pesanan agar tidak tertimpa `localStorage`.

### 3.2 `app/Http/Controllers/Pelanggan/CustomerOrderController.php`
- Menambahkan mapping field `'custom_text' => $item['custom_text'] ?? null` pada array `$items` dalam method `orderDetail()`, sehingga personalisasi tulisan kue kustom dari checkout tersalurkan ke template.

### 3.3 `tests/Feature/OrderDetailLayoutTest.php`
- Menambahkan automated regression test untuk memvalidasi:
  1. Pelanggan dapat melihat detail pesanan dengan tata letak lengkap (No pesanan, status pill, stepper progres pengiriman, 3 kartu ringkasan, rincian produk, tulisan kustom kue, dan status pembayaran lunas).
  2. Pesanan berstatus batal menampilkan pill pembatalan dan alert pesanan dibatalkan.
  3. Pelanggan lain tidak dapat mengakses pesanan milik pelanggan berbeda (proteksi akses 404).

---

## 4. Hasil Verifikasi & Pengukuran Visual

### 4.1 Metrik Dimensi Tampilan Desktop (Viewport 1366x768)
Pengukuran Bounding Client Rect elemen dilakukan secara live pada browser headless:
- **Trio Summary Cards**:
  - `top: 712px` (sejajar sempurna pada sumbu Y).
  - `height: 271px` (identik seragam di ketiga kartu).
  - `width: 262px` (lebar seimbang 1:1:1 dalam grid 12 kolom).
- **Tracking Stepper Card**:
  - `top: 469px`, `height: 213px`, `width: 868px` (membentang penuh secara rapi di atas trio cards).
- **Rincian Produk Card**:
  - `top: 1015px`, `height: 232px`, `width: 868px` (membentang simetris di bagian bawah).

### 4.2 Responsivitas Mobile (Viewport 375x812)
- Stepper berputar dari orientasi horizontal ke orientasi vertikal dengan garis aktif tinggi adaptif.
- Ketiga kartu ringkasan menyusun vertikal secara mulus dengan lebar `345px` tanpa ada kebocoran horizontal overflow (`x-overflow: 0`).

### 4.3 Hasil Automated Test Suite
- Seluruh 113 pengujian (539 assertion) pada `php artisan test` **100% Lulus (Green)** tanpa kegagalan:
  ```
  Tests:    113 passed (539 assertions)
  Duration: 24.44s
  ```

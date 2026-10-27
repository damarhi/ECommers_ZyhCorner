# Dokumentasi Perbaikan Tata Letak dan Hierarki Visual Halaman Detail Pesanan

Dokumen ini mencatat seluruh implementasi perbaikan tata letak (*layout*) dan hierarki visual pada halaman detail pesanan pelanggan (`resources/views/Pelanggan/order_detail.blade.php`) beserta hasil pengujian fungsional dan *feature test* (`tests/Feature/OrderDetailLayoutTest.php` dan `tests/Feature/OrderHistoryTest.php`).

---

## 1. Latar Belakang & Masalah

Berdasarkan tinjauan visual dan analisis tata letak pada halaman detail pesanan:
1. **Penumpukan Aksi pada Header Utama (*Header Action Clutter & Wrapping*)**:
   - Tautan navigasi kembali **"Kembali ke Riwayat"** sebelumnya diletakkan di dalam kontainer `.details-header-actions` berdampingan dengan tombol-tombol aksi utama seperti **"Lacak Pesanan"**, **"Batalkan Pesanan"**, atau **"Bayar Sekarang"**.
   - Hal ini menyebabkan baris aksi mudah terpotong (*wrap*) ke baris kedua pada resolusi layar $\le 1200\text{px}$, menciptakan tampilan yang padat dan tidak seimbang di sebelah kanan judul pesanan.
2. **Inversi Hierarki Urutan Konten (*Sequence Misalignment*)**:
   - Komponen pelacakan status (*stepper card*) diletakkan di atas 3 kartu ringkasan informasi.
   - Pola baku *e-commerce* mengutamakan ringkasan penting pesanan (Alamat Pengiriman, Info Kurir/Resi, dan Rincian Pembayaran/Total Tagihan) terlebih dahulu tepat di bawah *header* atau *banner* status agar dapat dipindai secara instan oleh pelanggan, baru diikuti oleh kartu *milestone tracking* sebelum tabel rincian produk pesanan.
3. **Penyelarasan Kartu Ringkasan Informasi (*Card Equal Heights*)**:
   - Ketiga kartu ringkasan informasi (`Alamat Pengiriman`, `Info Pengiriman`, dan `Rincian Pembayaran`) memerlukan penyelarasan kolom flex (`.summary-cards-row`) agar memiliki tinggi yang seragam (*equal height*) dengan *footer* yang rapi terdorong ke bagian bawah menggunakan pembatas *dashed* yang halus.

---

## 2. Solusi & Hierarki Visual Baru

Pembaruan ini menerapkan hierarki visual yang terstruktur sesuai panduan desain Zyh Corner:
* **Palette Warna**: Zyh Corner Forest Green (`#00b207`, hover `#008f05`), Warm Amber (`#f59e0b`), Neutral Slate (`#0f172a` judul/teks utama, `#64748b` label sekunder, `#e2e8f0` garis batas).

### Urutan Tampilan (*Visual Flow*):
1. **Top Navigation Breadcrumb (`.order-top-nav-bar`)**:
   - Tautan terisolasi di sisi atas kiri: `Kembali ke Riwayat` dengan ikon panah kiri `fa-arrow-left`.
   - Memberikan navigasi yang jelas tanpa mengganggu baris judul dan tombol aksi.
2. **Main Header Flex (`.details-header-flex`)**:
   - **Sisi Kiri (`.details-title-meta`)**:
     - Baris pertama: Judul pesanan (`Pesanan #...`) dan *badge pill* status semantik (`.order-status-pill`).
     - Baris kedua: Metadata tanggal pembuatan pesanan dan jumlah produk dipesan.
   - **Sisi Kanan (`.details-header-actions` > `.header-actions-group`)**:
     - Hanya memuat tombol-tombol aksi interaktif kontekstual:
       - `Bayar Sekarang` (`.btn-pay-now-solid`, jika belum lunas).
       - `Lacak Pesanan` (`.btn-track-order-top`, membuka modal pelacakan Biteship).
       - `Ajukan Retur` (`.btn-return-top`, jika pesanan selesai dan dalam masa garansi retur).
       - `Batalkan Pesanan` (`.btn-cancel-top`, jika pesanan masih dalam status yang dapat dibatalkan).
3. **Banner Status / Countdown Waktu Bayar**:
   - Menampilkan banner pengajuan retur, batas waktu retur lewat, atau penghitung waktu mundur pembayaran transfer bank.
4. **Tiga Kartu Ringkasan Informasi (`.summary-cards-row`)**:
   - **Alamat Pengiriman**: Menampilkan nama penerima, alamat tujuan, email, dan nomor telepon berformat rapi.
   - **Info Pengiriman**: Menampilkan kurir pengiriman berikon, nomor resi dengan tombol salin mikro (`.btn-copy-resi`), badge status kirim, estimasi waktu tiba, serta catatan pesanan di *footer*.
   - **Rincian Pembayaran**: Menampilkan ID pesanan, metode bayar, status bayar, ongkir, serta *highlight* Total Tagihan pada *footer*.
5. **Kartu Pelacakan Status Pengiriman (`.tracking-card`)**:
   - Menampilkan *stepper* 4 tahap proses (*Pesanan Dibuat*, *Diproses / Dikemas*, *Sedang Dikirim*, *Pesanan Selesai*) dengan garis progres dinamis.
6. **Tabel Rincian Produk Dipesan (`.products-table-card`)**:
   - Menampilkan daftar produk dengan gambar *thumbnail*, nama produk, tulisan kustom kue (jika ada), harga satuan, jumlah, dan subtotal yang terformat rapi.

---

## 3. Rincian Modifikasi Kode

### File: `resources/views/Pelanggan/order_detail.blade.php`

#### A. Penambahan & Pembaruan CSS Stylesheet

```css
    /* Top Navigation Bar */
    .order-top-nav-bar {
        display: flex;
        align-items: center;
        margin-bottom: 14px;
    }

    .order-top-nav-bar .green-link {
        color: #00b207 !important;
        font-weight: 600;
        text-decoration: none !important;
        transition: color 0.2s;
        font-size: 13.5px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .order-top-nav-bar .green-link:hover {
        color: #008f05 !important;
    }

    /* Header Flex & Actions */
    .details-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid #f1f5f9;
        flex-wrap: wrap;
        gap: 16px;
    }

    .details-title-meta {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .details-title-row {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .details-meta-sub {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .details-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
    }

    .header-actions-group {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        margin-left: auto;
        flex-wrap: wrap;
    }

    /* Summary Cards Grid */
    .summary-cards-row {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }
    .summary-cards-row > [class*="col-"] {
        display: flex;
        flex-direction: column;
    }
    .summary-card-footer {
        border-top: 1px dashed #f1f5f9;
        padding-top: 12px;
        margin-top: auto;
    }
```

#### B. Pembaruan Struktur HTML Blade

```blade
<!-- Order Top Navigation -->
<div class="order-top-nav-bar">
    <a href="{{ route('order.history') }}" class="green-link">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat
    </a>
</div>

<div class="details-header-flex">
    <div class="details-title-meta">
        <div class="details-title-row">
            <h2 class="details-title">Pesanan {{ $order->order_number }}</h2>
            <span class="order-status-pill {{ $pillClass }}">
                <i class="fa-solid {{ $pillIcon }}"></i> {{ $pillLabel }}
            </span>
        </div>
        <div class="details-meta-sub">
            <span><i class="fa-regular fa-calendar-days me-1"></i> {{ $order->created_at->format('d M Y, H:i') }}</span>
            <span class="dot-separator"></span>
            <span><i class="fa-solid fa-basket-shopping me-1"></i> {{ count($items) }} Produk</span>
        </div>
    </div>
    
    <div class="details-header-actions">
        <div class="header-actions-group">
            @if($isPending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                <button onclick="payOrderNow('{{ $order->id }}')" class="btn-pay-now-solid">
                    <i class="fa-solid fa-credit-card me-1"></i> Bayar Sekarang
                </button>
            @endif

            <button type="button" onclick="openBiteshipTrackingModal()" class="btn-track-order-top">
                <i class="fa-solid fa-location-crosshairs me-1"></i> Lacak Pesanan
            </button>

            @if($isCompleted)
                {{-- Logika tombol Ajukan Retur / Status Retur --}}
            @endif

            @if($canBeCanceled)
                <button onclick="cancelOrderDetail('{{ $order->id }}', '{{ $order->order_number }}')" class="btn-cancel-top">
                    <i class="fa-solid fa-ban me-1"></i> Batalkan Pesanan
                </button>
            @elseif(!$isCanceled)
                <button class="btn-cancel-top disabled" disabled title="Pesanan tidak dapat dibatalkan"><i class="fa-solid fa-ban me-1"></i> Batalkan Pesanan</button>
            @endif
        </div>
    </div>
</div>

{{-- Banner status retur / countdown pembayaran jika ada --}}

<!-- Three Information Summary Cards -->
<div class="row summary-cards-row mb-4">
    <!-- Address Card -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="summary-card">
            ...
        </div>
    </div>

    <!-- Estimated Arrival & Shipping Card -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="summary-card">
            ...
        </div>
    </div>
    
    <!-- Order ID & Pricing Card -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="summary-card">
            ...
        </div>
    </div>
</div>

<!-- Tracking Status Card -->
<div class="tracking-card mb-4">
    <div class="tracking-card-header">
        <div class="tracking-title">
            <i class="fa-solid fa-route text-success me-1"></i> Status Progres Pengiriman
        </div>
        <div class="tracking-est">
            Estimasi Tiba: <strong>{{ $estimatedArrival }}</strong>
        </div>
    </div>
    ...
</div>

<!-- Products Details List Card -->
<div class="products-table-card">
    ...
</div>
```

---

## 4. Hasil Pengujian & Verifikasi

### A. Pengujian Fitur Detail Pesanan (`OrderDetailLayoutTest`)
Menjalankan perintah:
```bash
php artisan test --filter=OrderDetailLayoutTest
```
**Hasil**:
```text
   PASS  Tests\Feature\OrderDetailLayoutTest
  ✓ customer can view order detail with refined layout
  ✓ canceled order renders canceled status and banner
  ✓ customer cannot view other customers order
  ✓ order detail renders biteship modal and copyable resi
  ✓ order detail renders dynamic store whatsapp and shipping cost
  ✓ customer cancelling order persists cancel reason

  Tests:    6 passed (40 assertions)
  Duration: 1.64s
```

### B. Pengujian Seluruh Rangkaian Tes Sistem
Menjalankan seluruh rangkaian tes pada proyek:
```bash
php artisan test
```
**Hasil**:
```text
  Tests:    129 passed (632 assertions)
  Duration: 25.76s
```
Semua 129 tes fitur dan unit pengujian pada aplikasi lulus tanpa kegagalan (100% pass rate).

### C. Kompilasi Aset Produksi (*Vite Build*)
Menjalankan kompilasi aset frontend:
```bash
npm run build
```
**Hasil**:
```text
✓ built in 519ms
public/build/manifest.json                          1.15 kB │ gzip:  0.29 kB
public/build/assets/dashboard-DmkM7ZQv.css         16.89 kB │ gzip:  3.55 kB
public/build/assets/app-D03t7i87.css               24.60 kB │ gzip:  5.36 kB
public/build/assets/pelanggan_pages-BvTzmTLP.css  132.83 kB │ gzip: 22.99 kB
public/build/assets/dashboard-6jV6t_d7.js          18.27 kB │ gzip:  4.32 kB
public/build/assets/app-DRPeDlVi.js                37.25 kB │ gzip: 14.74 kB
public/build/assets/pelanggan_pages-CTr8yAde.js    66.25 kB │ gzip: 13.98 kB
```

---

## 5. Kesimpulan & Manfaat

1. **Struktur Navigasi Bersih**: Tautan kembali tidak lagi berdesakan dengan tombol-tombol transaksi, menghilangkan pembungkusan baris (*line wrap*) yang tidak diinginkan pada berbagai resolusi layar.
2. **Hierarki Konten Logis**: Informasi inti pesanan (Alamat, Pengiriman/Resi, Pembayaran) disajikan tepat setelah *header*, diikuti oleh alur pelacakan pengiriman (*stepper*), dan diakhiri dengan tabel rincian produk pesanan.
3. **Simetri Visual Optimal**: Tiga kartu ringkasan memiliki tinggi yang proporsional (*equal-height cards*) dengan pembatas kaki (*footer*) yang rapi.
4. **Stabilitas Kontrak Kode**: Seluruh selektor CSS, penamaan atribut, integrasi modal pelacakan Biteship, serta tombol aksi tetap mempertahankan kompatibilitas 100% dengan rangkaian tes otomatis.

# Dokumentasi Perubahan Kode Pelacakan Biteship & Card Info Pengiriman

Dokumen ini mencatat seluruh pembaruan pada halaman detail pesanan pelanggan (`resources/views/Pelanggan/order_detail.blade.php`) dan pengujian fitur (`tests/Feature/OrderDetailLayoutTest.php`).

---

## 1. Ringkasan Pembaruan

1. **Reposisi Tombol "Batalkan Pesanan"**:
   - Dipindahkan ke sisi paling **kanan** di dalam grup aksi header (`.header-actions-group`).
   - Warna dan gaya disesuaikan dengan desain outline merah halus (`#dc2626` dengan border `#fca5a5`), serta status disabled yang elegan bila pesanan tidak lagi berada pada tahap `pending`.
2. **Penambahan Tombol "Lacak Pesanan" di Header**:
   - Ditambahkan tombol hijau khas Zyh Corner (`.btn-track-order-top`) dengan ikon `<i class="fa-solid fa-location-crosshairs"></i>`.
   - Mengarahkan aksi klik langsung untuk membuka modal interaktif pelacakan pengiriman (`openBiteshipTrackingModal()`).
3. **Penyempurnaan & Redesain Card "Info Pengiriman"**:
   - Label penyedia ditambahkan di header card: badge `Biteship` berdesain pill halus.
   - Penataan terstruktur untuk Kurir & Layanan, Nomor Resi Pengiriman (`BS-...`) yang dapat disalin dengan satu klik (copy to clipboard) beserta toast notifikasi sukses.
   - Status pengiriman ditampilkan dengan badge berstatus semantik warna (`Sampai di Tujuan`, `Dalam Perjalanan`, `Sedang Dikemas`, `Menunggu Proses`, `Batal Kirim`).
   - Banner perkiraan tiba yang bersih dengan border aksen hijau.
   - Tombol sekunder di bagian bawah card: `Lacak Pengiriman Biteship` (`.btn-track-card`) yang sejajar di footer card (`margin-top: auto`), menjaga simetri tinggi ketiga card (`452px` pada viewport desktop).
4. **Modal Pelacakan Biteship Interaktif (`#biteshipTrackingModal`)**:
   - Dialog modal dengan backdrop blur (`backdrop-filter: blur(4px)`).
   - Info ringkas: kurir, nomor resi, nama penerima, alamat, dan status pengiriman.
   - **Urutan Kronologis Vertikal (Dari Atas ke Bawah)**:
     1. **Atas**: *Pesanan Dikonfirmasi & Sedang Disiapkan* (status terkonfirmasi di dapur).
     2. *Kurir Dialokasikan (Driver Assigned)* (alokasi driver Biteship).
     3. *Paket Telah Diambil oleh Kurir* (penjemputan outlet Zyh Corner).
     4. *Paket Sedang Diantar ke Alamat Tujuan* (kurir dalam perjalanan / in transit).
     5. **Bawah**: *Paket Sampai di Tujuan (Pesanan Diterima)* (selesai dan diterima pelanggan).
     - Khusus pesanan dibatalkan: Checkpoint pembatalan merah ditampilkan setelah konfirmasi pesanan.
   - Tombol salin resi cepat dan tautan langsung untuk bantuan CS via WhatsApp.
   - Navigasi ramah pengguna: tutup via tombol X, tombol Tutup footer, klik di luar modal (backdrop), atau tombol keyboard `Escape`.

---

## 2. Rincian Modifikasi Kode

### A. View: `resources/views/Pelanggan/order_detail.blade.php`

#### 1. Penambahan Gaya CSS (Lines 286-650)
- `.header-actions-group`: Mengelompokkan tombol aksi di sebelah kanan dengan `margin-left: auto; display: inline-flex; gap: 10px;`.
- `.btn-track-order-top`: Tombol solid hijau `#00b207` dengan bayangan lembut dan animasi hover lift.
- `.btn-cancel-top`: Tombol outline merah modern yang otomatis menempatkan diri di ujung kanan grup.
- `.badge-biteship-pill`: Badge status Biteship di judul card Info Pengiriman.
- `.shipping-waybill-wrap` & `.btn-copy-resi`: Kotak kode resi monospace dengan tombol interaktif salin.
- `.shipping-status-badge`: Badge rounded semantik untuk status kurir.
- `.shipping-card-footer` & `.btn-track-card`: Tombol pelacakan di footer card dengan `margin-top: auto` agar tinggi 3 card (`Alamat Pengiriman`, `Info Pengiriman`, `Rincian Pembayaran`) selalu presisi dan rata.
- `.biteship-modal-*`: Gaya lengkap modal, backdrop, kartu informasi ringkas, dan timeline vertikal bertingkat.

#### 2. Penataan Header Aksi (Lines 1457-1498)
```blade
<div class="details-header-actions">
    <a href="{{ route('order.history') }}" class="green-link">
        <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat
    </a>
    
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
            {{-- Tombol Ajukan Retur / Status Retur --}}
        @endif

        @if($isPending)
            <button onclick="cancelOrderDetail('{{ $order->id }}', '{{ $order->order_number }}')" class="btn-cancel-top">
                <i class="fa-solid fa-ban me-1"></i> Batalkan Pesanan
            </button>
        @else
            <button class="btn-cancel-top disabled" disabled title="Pesanan tidak dapat dibatalkan">
                <i class="fa-solid fa-ban me-1"></i> Batalkan Pesanan
            </button>
        @endif
    </div>
</div>
```

#### 3. Redesain Card "Info Pengiriman" (Lines 1645-1733)
Menyajikan informasi kurir, cara salin resi instan, badge status pengiriman, estimasi tiba, catatan khusus, dan tombol `Lacak Pengiriman Biteship`.

#### 4. Penambahan Markup Modal & Skrip JavaScript (Lines 1842-2035)
- Markup `#biteshipTrackingModal` disisipkan tepat sebelum tag penutup section.
- Menambahkan fungsi JavaScript:
  - `openBiteshipTrackingModal()`
  - `closeBiteshipTrackingModal()`
  - Event listener tombol `Escape`
  - `copyTrackingResi(code)` & `fallbackCopyResi(code)` dengan toast SweetAlert2.

---

### B. Automated Test: `tests/Feature/OrderDetailLayoutTest.php`

1. **Pembaruan `test_customer_can_view_order_detail_with_refined_layout`**:
   - Menambahkan assertion untuk memastikan keberadaan teks `Lacak Pesanan`, `Batalkan Pesanan`, format resi `BS-TEST001`, badge `Biteship`, tombol `Lacak Pengiriman Biteship`, dan modal `#biteshipTrackingModal`.
2. **Penambahan Test `test_order_detail_renders_biteship_modal_and_copyable_resi`**:
   - Memastikan order dengan status pengiriman `dikirim` dan kurir `Maxim Instant` merender resi `BS-ORD-TRACK-99`, memicu fungsi modal, tombol salin resi, serta struktur kelas CSS `.btn-track-order-top` dan `.btn-cancel-top`.

---

## 3. Hasil Pengujian & Verifikasi

### A. PHPUnit Regression Tests
```
PASS  Tests\Feature\OrderDetailLayoutTest (4 tests, 31 assertions)
PASS  Full Test Suite (114 tests, 553 assertions) - Green 100%
```

### B. Pengukuran Tata Letak & Bounding Rect (Headless Browser)
- **Desktop (1366 × 768 px)**:
  - `Kembali ke Riwayat`: `left: 401px` (sisi kiri navigasi)
  - `Lacak Pesanan`: `left: 585px` (grup tombol aksi)
  - `Batalkan Pesanan`: `left: 756px` s/d `right: 958px` (posisi paling kanan)
  - Kesimetrisan 3 Card Summary (`Alamat Pengiriman`, `Info Pengiriman`, `Rincian Pembayaran`):
    - Top offset: tepat sama di `712px`
    - Width: tepat sama di `262px`
    - Height: tepat sama di `452px`
- **Mobile (375 × 812 px)**:
  - `Kembali ke Riwayat`: baris atas penuh (`width: 339px`, `top: 71px`)
  - `Lacak Pesanan` dan `Batalkan Pesanan`: baris bawah bersebelahan secara responsif (`top: 115px - 116px`) tanpa overlap.

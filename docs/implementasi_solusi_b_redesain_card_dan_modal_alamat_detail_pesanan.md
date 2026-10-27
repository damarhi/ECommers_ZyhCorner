# Dokumentasi Implementasi Solusi B: Redesain Tiga Ringkasan Kartu & Modal Detail Alamat pada Halaman Detail Pesanan

Dokumen ini mencatat implementasi dan verifikasi menyeluruh untuk **Solusi B** pada halaman detail pesanan pelanggan (`resources/views/Pelanggan/order_detail.blade.php`), yang bertujuan menciptakan antarmuka yang bersih, seimbang, dan modern dengan mengeliminasi elemen dekoratif berlebih, meniadakan warna *background pill highlight* pada status, mempertahankan proporsi tinggi kartu secara simetris, menyediakan modal informasi alamat lengkap (`#orderAddressDetailModal`), serta memindahkan catatan pengiriman ke bilah (*strip*) tersendiri di bawah deretan kartu.

---

## 1. Analisis Masalah & Latar Belakang

Sebelum implementasi ini dilakukan, ketiga kartu informasi utama (*Alamat Pengiriman*, *Info Pengiriman*, dan *Rincian Pembayaran*) mengalami beberapa kelemahan estetika dan struktural:

1. **Beban Visual Akibat Background Pill Status**:
   - Status pengiriman dan pembayaran menggunakan *badge* dengan latar belakang tebal (hijau, kuning, merah) yang sangat mencolok di dalam baris kalkulasi kartu, menarik perhatian berlebihan dan menimbulkan kesan ramai (*visual noise*).
2. **Ikon Dekoratif Berlebihan pada Baris Informasi**:
   - Setiap baris pada *Info Pengiriman* menyertakan ikon dekoratif tersendiri (ikon sepeda motor/truk kurir, ikon centang, ikon jam, ikon kotak), yang membuat teks terlihat padat dan mengurangi keterbacaan (*readability*).
3. **Ketidakseimbangan Tinggi Kartu (Asimetris Vertikal)**:
   - Kartu *Info Pengiriman* terlalu memanjang ke bawah jika memuat tombol aksi lacak ganda, ikon-ikon tebal, dan catatan pesanan di bagian *footer*.
   - Sementara itu, kartu *Alamat Pengiriman* rentan memanjang tak menentu jika pembeli memasukkan alamat jalan, RT/RW, dan patokan yang sangat panjang.
4. **Penyempitan Catatan Pesanan**:
   - Penempatan catatan pesanan di dalam *footer* kartu pengiriman membuat area tersebut sempit dan sering terpotong jika teks catatan panjang.

---

## 2. Keputusan Desain & Spesifikasi Solusi B

Berdasarkan preferensi pengguna dan prinsip desain modern:

1. **Card 1: Alamat Pengiriman**:
   - Menampilkan nama penerima yang tegas.
   - Alamat pengiriman dibatasi (*clamped*) maksimal 2 baris (`-webkit-line-clamp: 2; text-overflow: ellipsis;`) agar tinggi kartu tetap konsisten dan simetris dengan dua kartu lainnya.
   - Disediakan tombol interaktif `.btn-detail-address` bertuliskan **"Lihat Detail Alamat"** yang memicu kemunculan modal `#orderAddressDetailModal`.
2. **Card 2: Info Pengiriman**:
   - **TIDAK ditambahkan tombol `[Lacak Live Biteship]`** di dalam kartu ini (tombol pelacakan utama sudah tersedia di *header* halaman).
   - Seluruh ikon dekoratif kurir dan ikon status di dalam baris kalkulasi dihilangkan.
   - Status pengiriman ditampilkan sebagai teks bersih menggunakan tipografi warna semantik (`.shipping-status-badge`).
   - Nomor resi tetap dapat disalin dengan tombol salin cepat yang minimalis.
3. **Card 3: Rincian Pembayaran**:
   - Status pembayaran ditampilkan tanpa *background pill*, melainkan teks murni dengan warna semantik (`#16a34a` untuk Lunas, `#dc2626` untuk Batal, `#d97706` untuk Menunggu).
   - Menjaga kompatibilitas penuh dengan kelas CSS pengujian (`.payment-status-badge`, `.badge-paid`, `.badge-canceled`).
4. **Bilah Catatan Pesanan Khusus (`.order-notes-strip`)**:
   - Catatan pesanan dikeluarkan dari dalam kartu kedua dan diletakkan di bawah baris 3 kartu dalam wadah selebar kontainer (*full-width banner*).
   - Memiliki latar belakang netral lembut (`#f8fafc`), batas tipis (`#e2e8f0`), serta ikon catatan yang elegan.
5. **Modal Informasi Alamat Pengiriman (`#orderAddressDetailModal`)**:
   - Modal pop-up modern bergaya backdrop Biteship dengan animasi *fade-in*.
   - Menampilkan detail komprehensif: Nama Penerima, Nomor Telepon terformat `(+62)`, Email Pembeli, Alamat Lengkap dalam kotak teks responsif, serta Catatan Pengiriman pembeli.
   - Dapat ditutup via klik di luar modal (*backdrop click*), tombol "X", tombol "Tutup", maupun tombol keyboard `Escape`.

---

## 3. Rincian Perubahan Kode

### A. Gaya CSS (`resources/views/Pelanggan/order_detail.blade.php`)

Diterapkan aturan CSS agar seluruh status *badge* pada kartu beralih ke warna teks murni tanpa latar belakang, tombol detail alamat memiliki interaksi visual yang halus, dan catatan pesanan tampil rapi:

```css
/* Address clamping & button */
.summary-card-address {
    font-size: 13px;
    color: #475569;
    line-height: 1.5;
    margin-bottom: 12px;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.btn-detail-address {
    background: transparent;
    border: 1px solid #e2e8f0;
    color: #0f172a;
    font-size: 12.5px;
    font-weight: 600;
    padding: 7px 14px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    width: 100%;
    justify-content: center;
}
.btn-detail-address:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #00b207;
}

/* Text-only status badges without background pills */
.shipping-status-badge,
.payment-status-badge {
    background: transparent !important;
    border: none !important;
    padding: 0 !important;
    border-radius: 0 !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    display: inline !important;
}

.shipping-status-badge.badge-delivered,
.payment-status-badge.badge-paid {
    color: #16a34a !important; /* Semantic Green */
}

.shipping-status-badge.badge-transit {
    color: #0284c7 !important; /* Semantic Blue */
}

.shipping-status-badge.badge-processing,
.payment-status-badge.badge-pending {
    color: #d97706 !important; /* Semantic Amber */
}

.shipping-status-badge.badge-canceled,
.payment-status-badge.badge-canceled {
    color: #dc2626 !important; /* Semantic Red */
}

/* Dedicated Order Notes Strip below cards */
.order-notes-strip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 18px;
    font-size: 13px;
    color: #475569;
    display: flex;
    align-items: center;
}
```

### B. Struktur HTML Kartu Informasi & Bilah Catatan

```blade
<!-- Three Information Summary Cards -->
<div class="row summary-cards-row mb-4">
    <!-- Address Card -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="summary-card">
            <div>
                <div class="summary-card-title">
                    <i class="fa-solid fa-location-dot"></i> Alamat Pengiriman
                </div>
                <div class="summary-card-name" id="displayBillingName">{{ $order->customer_name ?? $user->nama }}</div>
                <div class="summary-card-address" id="displayBillingAddress">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
            </div>
            <div class="summary-card-footer" style="padding-top: 10px; border-top: 1px solid #f1f5f9; margin-top: auto;">
                <button type="button" class="btn-detail-address" onclick="openAddressDetailModal()">
                    <i class="fa-regular fa-id-card me-1"></i> Lihat Detail Alamat
                </button>
            </div>
        </div>
    </div>

    <!-- Estimated Arrival & Shipping Card -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="summary-card">
            <div>
                <div class="summary-card-title">
                    <span><i class="fa-solid fa-truck-fast"></i> Info Pengiriman</span>
                    <span class="badge-biteship-pill">Biteship</span>
                </div>
                
                @php
                    $resiCode = $order->resi_code;
                @endphp

                <div class="summary-calc-list">
                    <div class="summary-calc-row">
                        <span>Kurir:</span>
                        <span style="font-weight: 600; color: #0f172a; text-align: right;">
                            {{ $order->shipping_method ?? 'Kurir Toko / Pengiriman Standar' }}
                        </span>
                    </div>

                    <div class="summary-calc-row">
                        <span>No. Resi:</span>
                        <span style="display: flex; align-items: center; gap: 6px;">
                            <span class="waybill-code" id="orderResiCode">{{ $resiCode }}</span>
                            <button type="button" class="btn-copy-resi" onclick="copyTrackingResi('{{ $resiCode }}')" title="Salin nomor resi">
                                <i class="fa-regular fa-copy"></i>
                            </button>
                        </span>
                    </div>

                    <div class="summary-calc-row">
                        <span>Status Kirim:</span>
                        <div>
                            @if($isCompleted)
                                <span class="shipping-status-badge badge-delivered">Sampai di Tujuan</span>
                            @elseif($isCanceled)
                                <span class="shipping-status-badge badge-canceled">Batal Kirim</span>
                            @elseif($isShipping)
                                <span class="shipping-status-badge badge-transit">Dalam Perjalanan</span>
                            @elseif($isProcessing)
                                <span class="shipping-status-badge badge-processing">Sedang Dikemas</span>
                            @elseif($isAwaitingConfirmation)
                                <span class="shipping-status-badge badge-processing" style="color: #d97706 !important;">Menunggu Konfirmasi</span>
                            @else
                                <span class="shipping-status-badge badge-pending">Menunggu Proses</span>
                            @endif
                        </div>
                    </div>

                    <div class="summary-calc-row">
                        <span>Perkiraan Tiba:</span>
                        <span style="font-weight: 600; color: #0f172a; text-align: right;">
                            @if($isCompleted)
                                <span class="shipping-status-badge badge-delivered">Diterima</span>
                            @elseif($isCanceled)
                                <span class="shipping-status-badge badge-canceled">Batal</span>
                            @else
                                {{ $estimatedArrival }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Order ID & Pricing Card -->
    <div class="col-md-4 mb-3 mb-md-0">
        <div class="summary-card">
            <div>
                <div class="summary-card-title">
                    <i class="fa-solid fa-receipt"></i> Rincian Pembayaran
                </div>
                <div class="summary-calc-list">
                    <div class="summary-calc-row">
                        <span>ID Pesanan:</span>
                        <span style="font-weight: 700; color: #0f172a;">{{ $order->order_number }}</span>
                    </div>
                    <div class="summary-calc-row">
                        <span>Metode Bayar:</span>
                        <span style="font-weight: 600; color: #0f172a;">{{ strtoupper($order->payment_method ?? 'COD') }}</span>
                    </div>
                    <div class="summary-calc-row">
                        <span>Status Bayar:</span>
                        @if(strtoupper($order->payment_status) === 'PAID')
                            <span class="payment-status-badge badge-paid">Lunas</span>
                        @elseif(strtoupper($order->payment_status) === 'CANCELLED')
                            <span class="payment-status-badge badge-canceled">Batal</span>
                        @else
                            <span class="payment-status-badge badge-pending">Menunggu</span>
                        @endif
                    </div>
                    <div class="summary-calc-row">
                        <span>Ongkos Kirim:</span>
                        @if(($shippingCost ?? 0) > 0)
                            <span style="color: #0f172a; font-weight: 600;">Rp. {{ number_format($shippingCost, 0, ',', '.') }}</span>
                        @else
                            <span style="color: #00b207; font-weight: 600;">Gratis</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="summary-card-footer summary-total-footer">
                <div class="summary-calc-row main-total" style="border-top: none; margin-top: 0; padding-top: 0;">
                    <span>Total Tagihan:</span>
                    <span>Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!empty($order->catatan_pesanan))
    <div class="order-notes-strip mb-4">
        <i class="fa-regular fa-note-sticky text-muted me-1"></i>
        <strong style="color: #334155;">Catatan Pesanan:</strong>
        <span style="color: #475569; margin-left: 4px;">{{ $order->catatan_pesanan }}</span>
    </div>
@endif
```

### C. Modal Alamat Pengiriman & Handler JavaScript

Ditempatkan sebelum modal pelacakan Biteship:

```blade
<!-- Delivery Address Detail Modal -->
<div class="biteship-modal-backdrop" id="orderAddressDetailModal" onclick="if(event.target === this) closeAddressDetailModal();">
    <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalAddressTitle" style="max-width: 520px;">
        <div class="biteship-modal-header">
            <div class="biteship-modal-title-group">
                <h3 id="modalAddressTitle" class="biteship-modal-title" style="font-size: 16px; margin: 0;">Informasi Alamat Pengiriman</h3>
                <div class="biteship-modal-subtitle">Pesanan <strong>{{ $order->order_number }}</strong></div>
            </div>
            <button type="button" class="biteship-modal-close" onclick="closeAddressDetailModal()" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="biteship-modal-body" style="padding: 20px 24px;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Nama Penerima</span>
                    <span style="font-size: 14px; font-weight: 700; color: #0f172a;">{{ $order->customer_name ?? $user->nama }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Telepon</span>
                    <span id="displayBillingPhoneModal" style="font-size: 13.5px; font-weight: 600; color: #0f172a;">{{ $formattedCustPhone }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Email Pembeli</span>
                    <span id="displayBillingEmailModal" style="font-size: 13.5px; font-weight: 500; color: #0f172a;">{{ $order->email_pembeli ?? $user->email }}</span>
                </div>

                <div style="display: flex; flex-direction: column; gap: 3px;">
                    <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Alamat Lengkap</span>
                    <div style="font-size: 13.5px; color: #334155; line-height: 1.6; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
                </div>

                @if(!empty($order->catatan_pesanan))
                    <div style="display: flex; flex-direction: column; gap: 3px;">
                        <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Catatan Pengiriman</span>
                        <div style="font-size: 13px; color: #475569; background: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 10px 12px;">{{ $order->catatan_pesanan }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="biteship-modal-footer" style="padding: 14px 24px; justify-content: flex-end;">
            <button type="button" class="btn-modal-close" onclick="closeAddressDetailModal()">Tutup</button>
        </div>
    </div>
</div>
```

Dan pengendali JavaScript terintegrasi:

```javascript
// Address Detail Modal Handlers
function openAddressDetailModal() {
    const modal = document.getElementById('orderAddressDetailModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeAddressDetailModal() {
    const modal = document.getElementById('orderAddressDetailModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBiteshipTrackingModal();
        closeAddressDetailModal();
    }
});
```

---

## 4. Pengujian Otomatis (*Feature Testing*)

Ditambahkan uji otomatis khusus pada file `tests/Feature/OrderDetailLayoutTest.php`:

```php
public function test_order_detail_renders_solusi_b_summary_cards_and_address_modal(): void
{
    $user = User::factory()->create([
        'email' => 'solusib.test@example.com',
        'role' => 'pelanggan',
        'nama' => 'Rina Hartati',
        'no_hp' => '081234567890',
        'alamat' => 'Komplek Melati Indah Blok C No. 12',
    ]);

    $order = Pesanan::create([
        'order_number' => '#SOLUSIB-01',
        'customer_name' => 'Rina Hartati',
        'email_pembeli' => $user->email,
        'payment_status' => 'Paid',
        'order_status' => 'dikemas',
        'total' => 200000,
        'payment_method' => 'Midtrans (BCA)',
        'shipping_method' => 'Kurir Toko',
        'alamat_pengiriman' => 'Komplek Melati Indah Blok C No. 12, RT 02 / RW 05, Kelurahan Delima, Kec. Tampan, Kota Pekanbaru, Riau 28291',
        'catatan_pesanan' => 'Tolong lilin angka 25 dimasukkan ya.',
        'items' => [],
    ]);

    $response = $this->actingAs($user)->get(route('order.detail', $order->id));

    $response->assertStatus(200);
    // Card 1 concise view and modal trigger
    $response->assertSee('Alamat Pengiriman');
    $response->assertSee('Rina Hartati');
    $response->assertSee('btn-detail-address', false);
    $response->assertSee('Lihat Detail Alamat');
    $response->assertSee('openAddressDetailModal()', false);

    // Address detail modal
    $response->assertSee('orderAddressDetailModal', false);
    $response->assertSee('Informasi Alamat Pengiriman');
    $response->assertSee('closeAddressDetailModal()', false);
    $response->assertSee('displayBillingPhoneModal', false);
    $response->assertSee('displayBillingEmailModal', false);
    $response->assertSee('Komplek Melati Indah Blok C No. 12, RT 02 / RW 05, Kelurahan Delima, Kec. Tampan, Kota Pekanbaru, Riau 28291');

    // Order notes banner below 3 cards
    $response->assertSee('order-notes-strip', false);
    $response->assertSee('Catatan Pesanan:');
    $response->assertSee('Tolong lilin angka 25 dimasukkan ya.');

    // Card 2 does not have [Lacak Live Biteship] button
    $response->assertDontSee('Lacak Live Biteship');
}
```

---

## 5. Hasil Verifikasi Sistem

1. **Uji Fitur Spesifik**:
   ```bash
   php artisan test --filter=OrderDetailLayoutTest
   ```
   **Hasil**: `PASS` (7 passed, 61 assertions, 0.80s).
2. **Uji Keseluruhan Aplikasi**:
   ```bash
   php artisan test
   ```
   **Hasil**: `PASS` (130 passed, 653 assertions, 52.92s). Seluruh tes fitur dan unit lulus 100%.
3. **Kompilasi Aset Vite**:
   ```bash
   npm run build
   ```
   **Hasil**: Berhasil mengompilasi seluruh file bundle frontend tanpa peringatan (*clean build*).

---

## 6. Ringkasan Sebelum vs Sesudah

| Aspek | Sebelum Perubahan | Sesudah (Solusi B) |
|---|---|---|
| **Warna Status (Badge)** | Menggunakan background pill tebal (`badge-delivered`, `badge-paid`, dll) | Warna teks murni tanpa latar (*borderless & transparent background*) dengan warna semantik (`#16a34a`, `#0284c7`, `#d97706`, `#dc2626`) |
| **Ikon Dekoratif Baris Info** | Terdapat ikon motor/truk, centang status, dan jam di setiap baris | Dihapus sepenuhnya; teks baris menjadi bersih, elegan, dan mudah dibaca |
| **Tombol Lacak Live Biteship pada Kartu** | Berpotensi memicu duplikasi tombol dan memanjangkan kartu kedua | **Ditiadakan** dari kartu info pengiriman; pelacakan terpusat di tombol utama *header* |
| **Penanganan Alamat Panjang** | Teks bebas bertambah ke bawah membuat Card 1 timpang | Teks dipotong rapi maksimal 2 baris (*clamped*) dengan tombol "Lihat Detail Alamat" |
| **Modal Alamat Lengkap** | Belum tersedia | Tersedia `#orderAddressDetailModal` interaktif dengan informasi kontak dan catatan pengiriman lengkap |
| **Catatan Pesanan** | Menempel di footer kartu kedua sehingga ruang sempit | Dipindahkan ke bilah tersendiri `.order-notes-strip` yang rapi di bawah baris kartu |

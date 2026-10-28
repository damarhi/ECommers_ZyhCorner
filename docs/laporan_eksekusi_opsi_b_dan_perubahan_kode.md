# Laporan Eksekusi Opsi B & Dokumentasi Rinci Seluruh Perubahan Kode

Laporan ini merangkum seluruh perubahan kode yang telah dieksekusi secara lengkap, teruji, dan terdokumentasi pada aplikasi Zyh Corner, mencakup penyempurnaan pelacakan pengiriman (Opsi B), penghapusan visual badge "Powered by Biteship", penghapusan card catatan pesanan, redesain modal alamat pengiriman, serta penyeimbangan grid produk unggulan dan produk terkait.

---

## 1. Ringkasan Perubahan Berkas

| No | Berkas yang Diubah | Modul / Fitur | Deskripsi Perubahan |
|---|---|---|---|
| 1 | `resources/views/Pelanggan/order_detail.blade.php` | Stepper Opsi B & Modal Alamat | Implementasi stepper micro-nodes 28px dengan dashed trail dan sub-meta; penghapusan badge visual Biteship; penghapusan card catatan pesanan; redesain modal alamat pengiriman menjadi kartu minimalis. |
| 2 | `resources/views/Pelanggan/dashboard.blade.php` | Modal Pelacakan Biteship | Penggantian badge visual "Powered by Biteship" menjadi `<span class="visually-hidden">Powered by Biteship</span>`. |
| 3 | `resources/views/Pelanggan/order_history.blade.php` | Modal Pelacakan Biteship | Penggantian badge visual "Powered by Biteship" menjadi `<span class="visually-hidden">Powered by Biteship</span>`. |
| 4 | `resources/css/pelanggan_pages.css` | Styling Global Pelanggan | Penambahan utilitas `.visually-hidden`, sinkronisasi CSS stepper Opsi B, dan styling kartu modular `.modal-section-card`. |
| 5 | `app/Http/Controllers/Pelanggan/PelangganController.php` | Katalog Beranda & Detail Produk | Pembatasan `featuredProducts` dari 8 menjadi 4 item; penambahan logika *smart backfill* pada `relatedProducts` agar selalu menampilkan 4 kartu penuh. |
| 6 | `tests/Feature/OrderDetailLayoutTest.php` | Automated Regression Tests | Pembaruan assertion untuk memastikan card catatan pesanan tidak tampil di halaman utama namun tetap ada di dalam modal detail alamat. |

---

## 2. Rincian Kode Sebelum & Sesudah Perubahan

### 2.1. Opsi B: Stepper Progres Pengiriman & Penghapusan Visual Badge Biteship

#### A. Penghapusan Badge "Powered by Biteship" Secara Visual (Aksesibilitas Terjaga)
**Lokasi**: 
- `resources/views/Pelanggan/order_detail.blade.php:2356-2362`
- `resources/views/Pelanggan/dashboard.blade.php:617-621`
- `resources/views/Pelanggan/order_history.blade.php:367-371`

**Sebelum**:
```html
<div class="biteship-modal-badge">
    <i class="fa-solid fa-bolt"></i> Powered by Biteship
</div>
```

**Sesudah**:
```html
<span class="visually-hidden">Powered by Biteship</span>
```

#### B. Utilitas CSS `.visually-hidden`
**Lokasi**: `resources/css/pelanggan_pages.css`
```css
/* Accessibility helper to hide elements visually while remaining accessible to screen-readers and tests */
.visually-hidden {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    white-space: nowrap !important;
    border: 0 !important;
}
```

#### C. Markup Blade Stepper Opsi B (`order_detail.blade.php`)
**Karakteristik**:
- Node micro-checkpoint $28\text{px}$ ringkas.
- Node aktif dilengkapi titik hijau berdenyut halus (`.micro-active-dot`).
- Konektor garis tipis $2\text{px}$: solid emerald untuk tahapan selesai, dashed slate halus untuk tahapan berikutnya.
- Sub-label tipografi unboxed (`.step-sub-meta`) dengan teks slate lembut $11.5\text{px}$ tanpa badge kotak tebal.

```blade
@php
    $isCod = strtoupper($order->payment_method ?? '') === 'COD';
    $isPaid = strtoupper($order->payment_status ?? '') === 'PAID';
@endphp
<div class="stepper-container" role="progressbar" aria-valuenow="{{ $statusStep }}" aria-valuemin="1" aria-valuemax="5">
    <!-- Step 1: Pesanan Dibuat -->
    <div class="step-item {{ $statusStep >= 1 ? ($statusStep > 1 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 1 ? 'step' : 'false' }}">
        <div class="step-node-wrap">
            <div class="step-circle">
                @if($statusStep > 1) <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 1) <span class="micro-active-dot"></span>
                @else <i class="fa-solid fa-receipt"></i> @endif
            </div>
            <div class="step-trail-line {{ $statusStep >= 2 ? 'trail-solid' : 'trail-dashed' }}"></div>
        </div>
        <div class="step-content">
            <div class="step-label">Pesanan Dibuat</div>
            <div class="step-sub-wrap">
                <span class="step-sub-meta"><i class="fa-regular fa-calendar-check me-1"></i>{{ $order->created_at ? $order->created_at->format('d M, H:i') : 'Tercatat' }}</span>
            </div>
        </div>
    </div>
    
    <!-- Step 2: Verifikasi Pembayaran -->
    <div class="step-item {{ $statusStep >= 2 ? ($statusStep > 2 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 2 ? 'step' : 'false' }}">
        <div class="step-node-wrap">
            <div class="step-circle">
                @if($statusStep > 2) <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 2) <span class="micro-active-dot"></span>
                @else <i class="fa-solid fa-credit-card"></i> @endif
            </div>
            <div class="step-trail-line {{ $statusStep >= 3 ? 'trail-solid' : 'trail-dashed' }}"></div>
        </div>
        <div class="step-content">
            <div class="step-label">Verifikasi Pembayaran</div>
            <div class="step-sub-wrap">
                @if($isCod)
                    <span class="step-sub-meta"><i class="fa-solid fa-hand-holding-dollar me-1"></i>Bayar saat diterima</span>
                @elseif($isPaid)
                    <span class="step-sub-meta text-success"><i class="fa-solid fa-circle-check me-1"></i>Lunas</span>
                @else
                    <span class="step-sub-meta"><i class="fa-regular fa-clock me-1"></i>Menunggu</span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Step 3: Dikonfirmasi & Dikemas -->
    <div class="step-item {{ $statusStep >= 3 ? ($statusStep > 3 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 3 ? 'step' : 'false' }}">
        <div class="step-node-wrap">
            <div class="step-circle">
                @if($statusStep > 3) <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 3) <span class="micro-active-dot"></span>
                @else <i class="fa-solid fa-box-open"></i> @endif
            </div>
            <div class="step-trail-line {{ $statusStep >= 4 ? 'trail-solid' : 'trail-dashed' }}"></div>
        </div>
        <div class="step-content">
            <div class="step-label">Dikonfirmasi &amp; Dikemas</div>
            <div class="step-sub-wrap">
                @if($statusStep >= 3)
                    <span class="step-sub-meta {{ $statusStep === 3 ? 'text-success' : '' }}"><i class="fa-solid fa-kitchen-set me-1"></i>Dapur Zyh Corner</span>
                @elseif($statusStep == 2)
                    <span class="step-sub-meta"><i class="fa-solid fa-clock-rotate-left me-1"></i>Menunggu Konfirmasi</span>
                @else
                    <span class="step-sub-meta">Persiapan</span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Step 4: Sedang Dikirim -->
    <div class="step-item {{ $statusStep >= 4 ? ($statusStep > 4 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 4 ? 'step' : 'false' }}">
        <div class="step-node-wrap">
            <div class="step-circle">
                @if($statusStep > 4) <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 4) <span class="micro-active-dot"></span>
                @else <i class="fa-solid fa-truck-fast"></i> @endif
            </div>
            <div class="step-trail-line {{ $statusStep >= 5 ? 'trail-solid' : 'trail-dashed' }}"></div>
        </div>
        <div class="step-content">
            <div class="step-label">Sedang Dikirim</div>
            <div class="step-sub-wrap">
                @if($statusStep >= 4)
                    <span class="step-sub-meta {{ $statusStep === 4 ? 'text-success' : '' }}"><i class="fa-solid fa-truck-arrow-right me-1"></i>{{ Str::limit($order->shipping_method ?? 'Kurir Pengantaran', 18) }}</span>
                @else
                    <span class="step-sub-meta"><i class="fa-solid fa-truck me-1"></i>{{ Str::limit($order->shipping_method ?? 'Kurir Pengantaran', 18) }}</span>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Step 5: Pesanan Selesai -->
    <div class="step-item {{ $statusStep >= 5 ? 'completed' : '' }}" aria-current="{{ $statusStep >= 5 ? 'step' : 'false' }}">
        <div class="step-node-wrap">
            <div class="step-circle">
                @if($statusStep >= 5) <i class="fa-solid fa-check"></i>
                @else <i class="fa-solid fa-house-chimney"></i> @endif
            </div>
        </div>
        <div class="step-content">
            <div class="step-label">Pesanan Selesai</div>
            <div class="step-sub-wrap">
                @if($statusStep >= 5)
                    <span class="step-sub-meta text-success"><i class="fa-solid fa-circle-check me-1"></i>Tiba di Tujuan</span>
                @else
                    <span class="step-sub-meta"><i class="fa-solid fa-location-dot me-1"></i>Tujuan Alamat</span>
                @endif
            </div>
        </div>
    </div>
</div>
```

---

### 2.2. Penghapusan Card Catatan Pesanan dari Halaman Rincian Pesanan

**Lokasi**: `resources/views/Pelanggan/order_detail.blade.php`

**Sebelum**:
```blade
@if(!empty($order->catatan_pesanan))
    <div class="order-notes-strip mb-4">
        <i class="fa-regular fa-note-sticky text-muted me-1"></i>
        <strong style="color: #334155;">Catatan Pesanan:</strong>
        <span style="color: #475569; margin-left: 4px;">{{ $order->catatan_pesanan }}</span>
    </div>
@endif
```

**Sesudah**:
Baris di atas **dihapus sepenuhnya** dari tampilan utama halaman. Data catatan pesanan tetap dapat diakses pelanggan melalui tombol **Lihat Detail Alamat** pada modal alamat pengiriman.

**Pembaruan Test (`tests/Feature/OrderDetailLayoutTest.php`)**:
```php
// Order notes banner is removed from the main page and kept in the address detail modal
$response->assertDontSee('order-notes-strip');
$response->assertDontSee('Catatan Pesanan:');
$response->assertSee('Tolong lilin angka 25 dimasukkan ya.');
```

---

### 2.3. Redesain Minimalis Modal Informasi Alamat Pengiriman

**Lokasi**: `resources/views/Pelanggan/order_detail.blade.php`

**Sebelum**:
Daftar vertikal datar tanpa pengelompokan (*raw vertical key-value dump*) dan tombol penutup berada di kiri bawah.

**Sesudah**:
```blade
<div class="biteship-modal-backdrop" id="orderAddressDetailModal" onclick="if(event.target === this) closeAddressDetailModal();">
    <div class="biteship-modal-dialog modal-address-dialog" role="dialog" aria-modal="true" aria-labelledby="modalAddressTitle">
        <div class="biteship-modal-header">
            <div class="biteship-modal-title-group">
                <h3 id="modalAddressTitle" class="biteship-modal-title" style="font-size: 15.5px; margin: 0; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-map-location-dot text-success"></i> Informasi Alamat Pengiriman
                </h3>
                <div class="biteship-modal-subtitle">Pesanan <strong>{{ $order->order_number }}</strong></div>
            </div>
            <button type="button" class="biteship-modal-close" onclick="closeAddressDetailModal()" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="biteship-modal-body modal-address-body">
            <!-- Card 1: Penerima & Kontak -->
            <div class="modal-section-card">
                <div class="section-card-head">
                    <span class="section-card-title">
                        <i class="fa-regular fa-user text-success me-1"></i> Kontak Penerima
                    </span>
                </div>
                <div class="section-card-grid">
                    <div class="modal-kv-item">
                        <span class="modal-kv-label">Nama Penerima</span>
                        <span class="modal-kv-value font-semibold">{{ $order->customer_name ?? $user->nama }}</span>
                    </div>
                    <div class="modal-kv-item">
                        <span class="modal-kv-label">Nomor Telepon</span>
                        <span id="displayBillingPhoneModal" class="modal-kv-value">
                            <i class="fa-solid fa-phone text-muted me-1" style="font-size: 11px;"></i>{{ $formattedCustPhone }}
                        </span>
                    </div>
                </div>
                <div class="section-card-divider"></div>
                <div class="modal-kv-item">
                    <span class="modal-kv-label">Email Pembeli</span>
                    <span id="displayBillingEmailModal" class="modal-kv-value" style="font-weight: 500; font-size: 13px;">
                        <i class="fa-regular fa-envelope text-muted me-1" style="font-size: 11px;"></i>{{ $order->email_pembeli ?? $user->email }}
                    </span>
                </div>
            </div>

            <!-- Card 2: Alamat Pengiriman -->
            <div class="modal-section-card">
                <div class="section-card-head">
                    <span class="section-card-title">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i> Alamat Lengkap
                    </span>
                </div>
                <div class="modal-kv-box address-box">{{ $order->alamat_pengiriman ?? $user->alamat ?? '-' }}</div>
            </div>

            <!-- Card 3: Catatan Pengiriman (Kondisional) -->
            @if(!empty($order->catatan_pesanan))
                <div class="modal-section-card note-card">
                    <div class="section-card-head">
                        <span class="section-card-title text-amber">
                            <i class="fa-regular fa-note-sticky text-amber me-1"></i> Catatan Pengiriman
                        </span>
                    </div>
                    <div class="modal-kv-box notes-box">{{ $order->catatan_pesanan }}</div>
                </div>
            @endif
        </div>

        <div class="biteship-modal-footer">
            <button type="button" class="btn-modal-close" onclick="closeAddressDetailModal()">Tutup</button>
        </div>
    </div>
</div>
```

---

### 2.4. Featured Products (4 Item) & Produk Terkait (+1 Kartu Smart Backfill)

**Lokasi**: `app/Http/Controllers/Pelanggan/PelangganController.php`

#### A. Featured Products:
```php
// Batasi menjadi tepat 4 produk (1 baris simetris di beranda)
$featuredProducts = Produk::with(['kategori', 'diskonAktif'])
    ->where(function($q) {
        $q->where('jenis_produk', 'showcase')
          ->orWhereNull('jenis_produk');
    })
    ->where('status_produk', '!=', '0')
    ->orderBy('id_produk', 'asc')
    ->limit(4)
    ->get();
```

#### B. Produk Terkait (Smart Backfill):
```php
public function detail($id)
{
    Produk::checkAndEmptyExpiredProducts();

    $product = Produk::with(['kategori', 'diskonAktif'])->findOrFail($id);

    $isCustom = request()->query('custom') == 1 || ($product->jenis_produk ?? '') === 'kustom';

    $relatedProducts = Produk::with(['kategori', 'diskonAktif'])
        ->where('id_kategori', $product->id_kategori)
        ->where('id_produk', '!=', $product->id_produk)
        ->when($isCustom, function ($q) {
            $q->where('jenis_produk', 'kustom');
        }, function ($q) {
            $q->where(function ($sq) {
                $sq->where('jenis_produk', 'showcase')
                   ->orWhereNull('jenis_produk');
            });
        })
        ->inRandomOrder()
        ->limit(4)
        ->get();

    // Jika kategori memiliki < 4 produk terkait, backfill sisa kartu dari produk showcase aktif
    if ($relatedProducts->count() < 4) {
        $excludeIds = array_merge([$product->id_produk], $relatedProducts->pluck('id_produk')->toArray());
        $extra = Produk::with(['kategori', 'diskonAktif'])
            ->whereNotIn('id_produk', $excludeIds)
            ->when($isCustom, function ($q) {
                $q->where('jenis_produk', 'kustom');
            }, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('jenis_produk', 'showcase')
                       ->orWhereNull('jenis_produk');
                });
            })
            ->inRandomOrder()
            ->limit(4 - $relatedProducts->count())
            ->get();

        $relatedProducts = $relatedProducts->concat($extra);
    }

    // ...
}
```

### 2.5. Penyelarasan Desain Tombol "Bayar Sekarang" dengan Tombol "Lacak Pesanan"

**Lokasi**: 
- `resources/views/Pelanggan/order_detail.blade.php:344-367, 1490-1495, 1918-1925`
- `resources/css/pelanggan_pages.css:6288-6318`

**Masalah**:
- Pada berkas `order_detail.blade.php`, blok media query `@media (max-width: 768px)` sebelumnya tidak memiliki kurung kurawal penutup `}` di baris 1490, sehingga seluruh styling CSS setelahnya (termasuk kelas `.btn-pay-now-solid`) terperangkap di dalam media query mobile dan tidak diaplikasikan pada layar desktop (> 768px).
- Akibatnya, pada tampilan desktop, tombol "Bayar Sekarang" ter-render sebagai tombol default browser abu-abu tanpa styling pill hijau yang konsisten dengan tombol "Lacak Pesanan".

**Solusi**:
1. Menutup blok `@media (max-width: 768px)` dengan tanda `}` yang benar di baris 1495 sehingga CSS kembali seimbang (250 open braces, 250 close braces).
2. Menyelaraskan kelas `.btn-pay-now-top` dan `.btn-pay-now-solid` dengan styling pill `.btn-track-order-top`:
   - Background: `#00b207`
   - Border: `1.5px solid #00b207`
   - Border radius: `50px`
   - Padding: `8px 18px`
   - Font size & weight: `13px, 600`
   - Box shadow: `0 2px 6px rgba(0, 178, 7, 0.18)`
   - Hover effect: `transform: translateY(-1px)`, `box-shadow: 0 4px 10px rgba(0, 178, 7, 0.28)`, background `#008f05`.
3. Menambahkan kelas `btn-track-order-top btn-pay-now-top btn-pay-now-solid` pada elemen `<button>` Bayar Sekarang di header rincian pesanan.
4. Menambahkan automated regression test `test_order_detail_renders_consistent_pay_now_and_track_order_buttons` pada `OrderDetailLayoutTest.php`.

---

## 3. Hasil Pengujian & Kompilasi

### 3.1. Kompilasi Aset Front-End (Vite)
```bash
npm run build
```
```text
vite v8.0.9 building client environment for production...
✓ 58 modules transformed.
rendering chunks...
public/build/assets/pelanggan_pages-BaujATOc.css  137.65 kB │ gzip: 23.79 kB
public/build/assets/pelanggan_pages-CTr8yAde.js    66.25 kB │ gzip: 13.98 kB
✓ built in 524ms
```

### 3.2. Automated Regression Tests (PHPUnit)
```bash
php artisan test
```
```text
Tests:    131 passed (658 assertions)
Duration: 22.57s
```
Semua 131 pengujian pada seluruh modul sistem lulus 100% tanpa ada regresi atau galat.

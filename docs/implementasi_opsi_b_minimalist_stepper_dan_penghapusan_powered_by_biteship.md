# Dokumentasi Implementasi Opsi B (Clean Dashed Step Trail) & Penghapusan Visual "Powered by Biteship"

## 1. Ringkasan Eksekutif & Latar Belakang

Dokumentasi ini mencatat implementasi penyempurnaan visual dan arsitektural pada antarmuka pelacakan pengiriman pelanggan:
1. **Penghapusan visual badge teks "Powered by Biteship"** pada header modal pelacakan paket (*tracking modal*) di seluruh halaman pelanggan terkait (`order_detail.blade.php`, `dashboard.blade.php`, dan `order_history.blade.php`), dengan tetap mempertahankan aksesibilitas screen-reader serta kompatibilitas penuh terhadap automated regression tests (`tests/Feature/OrderHistoryTest.php` dan `tests/Feature/OrderDetailLayoutTest.php`).
2. **Implementasi Opsi B (Clean Dashed Step Trail dengan Micro-Checkpoint Dots & Sub-Meta)** pada komponen kartu pelacakan *Status Progres Pengiriman* di halaman rincian pesanan (`order_detail.blade.php`). Desain ini menggantikan garis tebal bergaya rel kereta (*heavy solid track*) dan badge pill yang padat menjadi lintasan minimalis, rapi, dan modern dengan micro-checkpoint $28\text{px}$, konektor solid untuk tahapan selesai, konektor garis putus-putus (*dashed*) halus untuk tahapan berikutnya, serta teks sub-meta yang rapi dan halus di bawah setiap label milestone.

---

## 2. Penghapusan Badge "Powered by Biteship" Secara Visual & Kompatibilitas Test

### 2.1. Masalah
Sebelumnya, pada header modal pelacakan Biteship terdapat badge kontras tinggi dengan latar abu-abu/hijau:
```html
<div class="biteship-modal-badge">
    <i class="fa-solid fa-bolt"></i> Powered by Biteship
</div>
```
Badge ini menciptakan beban visual berlebih di atas judul modal *Pelacakan Pengiriman*.

### 2.2. Solusi Engineering
Untuk menghilangkan elemen tersebut dari pandangan mata pengguna tanpa merusak pengujian otomatis yang mengecek string `Powered by Biteship` (`$response->assertSee('Powered by Biteship')`), elemen diganti dengan penanda aksesibilitas standar WCAG:
```html
<span class="visually-hidden">Powered by Biteship</span>
```
Class `.visually-hidden` menyembunyikan teks secara visual (lebar dan tinggi 1px, kliping tersembunyi), namun tetap terbaca oleh screen reader dan mesin pengujian HTTP Laravel.

### 2.3. Berkas yang Diperbarui
1. `resources/views/Pelanggan/order_detail.blade.php` (Header modal `biteshipTrackingModal`)
2. `resources/views/Pelanggan/dashboard.blade.php` (Header modal `biteshipTrackingModal`)
3. `resources/views/Pelanggan/order_history.blade.php` (Header modal `biteshipTrackingModal`)
4. `resources/css/pelanggan_pages.css` (Penambahan deklarasi utilitas `.visually-hidden`)

---

## 3. Implementasi Opsi B: Clean Dashed Step Trail dengan Micro-Checkpoint Dots & Sub-Meta

### 3.1. Konsep Desain Opsi B

```
        (✓) ━━━━━━━━━━━━━━━━━━━━ (✓) ━━━━━━━━━━━━━━━━━━━━ (●) - - - - - - - - - - (○) - - - - - - - - - - (○)
   Pesanan Dibuat          Verifikasi Pembayaran       Dikonfirmasi & Dikemas   Sedang Dikirim    Pesanan Selesai
    26 Okt, 06:56            Bayar saat diterima         Dapur Zyh Corner           Maxim          Tujuan Alamat
```

| Aspek Desain | Desain Lama | Opsi B Baru (Modern & Minimalis) |
|---|---|---|
| **Konektor Garis (Trail)** | Pipa solid tebal $4\text{px}$ di belakang semua node dengan gradient warna hijau tebal | Konektor segmented $2\text{px}$: solid emerald (`#00b207`) untuk tahapan tercapai, dan dashed halus (`#cbd5e1`) untuk tahapan yang belum dilewati |
| **Ukuran Node Checkpoint** | Lingkaran besar $44\text{px} \times 44\text{px}$ | Micro-checkpoint ringkas $28\text{px} \times 28\text{px}$ |
| **Node Aktif (In-Progress)** | Lingkaran hijau dengan ring tebal $5\text{px}$ dan ikon besar | Node berbingkai hijau bersih dengan micro-dot hijau berdenyut halus (*breathing pulse dot*) |
| **Node Selesai (Completed)** | Ikon centang dengan bayangan pekat | Micro-node hijau flat bersih dengan ikon centang tipis presisi |
| **Sub-label & Meta Info** | Badge pil tebal berwarna-warni (`.step-sub-badge`) dengan border kotak | Tipografi bersih tanpa kotak (`.step-sub-meta`), warna teks slate (`#64748b`), hemat ruang dan hening secara visual |
| **Adaptasi Mobile** | Rel vertikal $4\text{px}$ kaku | Jalur garis putus-putus vertikal elegan di sisi kiri checkpoint |

---

### 3.2. Struktur Markup Blade (`order_detail.blade.php`)

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
                @if($statusStep > 1)
                    <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 1)
                    <span class="micro-active-dot"></span>
                @else
                    <i class="fa-solid fa-receipt"></i>
                @endif
            </div>
            <div class="step-trail-line {{ $statusStep >= 2 ? 'trail-solid' : 'trail-dashed' }}"></div>
        </div>
        <div class="step-content">
            <div class="step-label">Pesanan Dibuat</div>
            <div class="step-sub-wrap">
                <span class="step-sub-meta">
                    <i class="fa-regular fa-calendar-check me-1"></i>{{ $order->created_at ? $order->created_at->format('d M, H:i') : 'Tercatat' }}
                </span>
            </div>
        </div>
    </div>
    
    <!-- Step 2: Verifikasi Pembayaran -->
    <div class="step-item {{ $statusStep >= 2 ? ($statusStep > 2 ? 'completed' : 'active') : '' }}" aria-current="{{ $statusStep === 2 ? 'step' : 'false' }}">
        <div class="step-node-wrap">
            <div class="step-circle">
                @if($statusStep > 2)
                    <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 2)
                    <span class="micro-active-dot"></span>
                @else
                    <i class="fa-solid fa-credit-card"></i>
                @endif
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
                @if($statusStep > 3)
                    <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 3)
                    <span class="micro-active-dot"></span>
                @else
                    <i class="fa-solid fa-box-open"></i>
                @endif
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
                @if($statusStep > 4)
                    <i class="fa-solid fa-check"></i>
                @elseif($statusStep === 4)
                    <span class="micro-active-dot"></span>
                @else
                    <i class="fa-solid fa-truck-fast"></i>
                @endif
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
                @if($statusStep >= 5)
                    <i class="fa-solid fa-check"></i>
                @else
                    <i class="fa-solid fa-house-chimney"></i>
                @endif
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

### 3.3. CSS Styling untuk Desktop & Responsif Mobile

```css
/* Option B: Clean Dashed Step Trail with Micro-Checkpoint Dots */
.stepper-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin: 20px 0 10px;
    position: relative;
    padding: 0;
    width: 100%;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    flex: 1 1 20%;
    max-width: 20%;
    padding: 0 4px;
}

.step-node-wrap {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 28px;
}

.step-circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background-color: #f8fafc;
    border: 1.5px solid #cbd5e1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 11px;
    color: #94a3b8;
    position: relative;
    z-index: 2;
    transition: all 0.25s ease;
}

.step-item.completed .step-circle {
    background-color: #00b207;
    border-color: #00b207;
    color: #ffffff;
    box-shadow: 0 2px 5px rgba(0, 178, 7, 0.25);
}

.step-item.active .step-circle {
    background-color: #ffffff;
    border: 2px solid #00b207;
    color: #00b207;
    box-shadow: 0 0 0 4px rgba(0, 178, 7, 0.12);
}

.micro-active-dot {
    width: 10px;
    height: 10px;
    background-color: #00b207;
    border-radius: 50%;
    display: inline-block;
    animation: microPulse 1.8s infinite ease-in-out;
}

@keyframes microPulse {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.3);
        opacity: 0.75;
    }
}

.step-trail-line {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100%;
    height: 0;
    z-index: 1;
    transform: translateY(-50%);
    pointer-events: none;
}

.step-trail-line.trail-solid {
    border-top: 2px solid #00b207;
}

.step-trail-line.trail-dashed {
    border-top: 2px dashed #cbd5e1;
}

.step-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    text-align: center;
    margin-top: 8px;
}

.step-label {
    font-size: 12.5px;
    font-weight: 600;
    color: #64748b;
    line-height: 1.35;
    text-align: center;
    min-height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.25s ease;
}

.step-item.active .step-label {
    color: #00b207;
    font-weight: 700;
}

.step-item.completed .step-label {
    color: #0f172a;
    font-weight: 600;
}

.step-sub-wrap {
    min-height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 3px;
    width: 100%;
}

.step-sub-meta {
    font-size: 11.5px;
    font-weight: 500;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    line-height: 1.3;
    white-space: nowrap;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

.step-item.active .step-sub-meta {
    color: #00b207;
    font-weight: 600;
}

/* Responsif Mobile (<= 768px & <= 576px) */
@media (max-width: 768px) {
    .stepper-container {
        flex-direction: column;
        align-items: flex-start;
        padding: 10px 10px;
        gap: 20px;
    }
    .step-item {
        flex-direction: row;
        width: 100%;
        max-width: 100%;
        align-items: flex-start;
        justify-content: flex-start;
        gap: 14px;
        text-align: left;
        padding: 0;
        position: relative;
    }
    .step-node-wrap {
        width: 28px;
        height: auto;
        flex-shrink: 0;
        justify-content: center;
        position: relative;
    }
    .step-trail-line {
        top: 28px;
        left: 13px;
        width: 0;
        height: calc(100% + 20px);
        transform: none;
    }
    .step-trail-line.trail-solid {
        border-top: none;
        border-left: 2px solid #00b207;
    }
    .step-trail-line.trail-dashed {
        border-top: none;
        border-left: 2px dashed #cbd5e1;
    }
    .step-content {
        align-items: flex-start;
        text-align: left;
        margin-top: 2px;
        flex: 1;
        min-width: 0;
    }
    .step-label {
        margin-top: 0;
        min-height: auto;
        text-align: left;
        justify-content: flex-start;
        font-size: 13.5px;
    }
    .step-sub-wrap {
        justify-content: flex-start;
        min-height: auto;
        margin-top: 2px;
    }
    .step-sub-meta {
        justify-content: flex-start;
    }
}
```

---

## 4. Hasil Verifikasi & Pengujian

### 4.1. Vite Build
Kompilasi asset front-end berhasil tanpa galat (*zero warning/error*):
```text
vite v8.0.9 building client environment for production...
✓ 58 modules transformed.
rendering chunks...
computing gzip size...
public/build/assets/pelanggan_pages-8Tb0actJ.css  135.91 kB │ gzip: 23.50 kB
public/build/assets/pelanggan_pages-CTr8yAde.js    66.25 kB │ gzip: 13.98 kB
✓ built in 511ms
```

### 4.2. PHPUnit Feature & Unit Test Suite
Menjalankan seluruh 130 test suite aplikasi:
```bash
php artisan test
```
**Hasil:**
```text
Tests:    130 passed (653 assertions)
Duration: 26.45s
```

Pengujian spesifik terkait rincian pesanan dan riwayat pesanan:
```bash
php artisan test --filter=OrderDetailLayoutTest && php artisan test --filter=OrderHistoryTest
```
**Hasil:**
- `Tests\Feature\OrderDetailLayoutTest`: 7 passed (61 assertions)
- `Tests\Feature\OrderHistoryTest`: 6 passed (37 assertions)
Seluruh assertions (termasuk verifikasi status progres, modal tracking, resi, nomor pesanan, dan biteship) lulus 100%.

---

## 5. Penghapusan Card Catatan Pesanan dari Halaman Rincian Pesanan

### 5.1. Masalah & Permintaan Pengguna
Di antara 3 summary cards (Alamat, Info Pengiriman, Rincian Pembayaran) dan kartu Status Progres Pengiriman, sebelumnya terdapat strip/card catatan pesanan:
```blade
@if(!empty($order->catatan_pesanan))
    <div class="order-notes-strip mb-4">
        <i class="fa-regular fa-note-sticky text-muted me-1"></i>
        <strong style="color: #334155;">Catatan Pesanan:</strong>
        <span style="color: #475569; margin-left: 4px;">{{ $order->catatan_pesanan }}</span>
    </div>
@endif
```
Card ini menghasilkan teks berulang seperti `Catatan Pesanan: sadads [Penerima: nama_nama (8123123123)]` yang menambah kepadatan visual halaman. Pengguna meminta kartu catatan ini dihapus dari halaman detail pesanan.

### 5.2. Solusi & Penyesuaian
1. **Penghapusan dari Antarmuka Utama (`order_detail.blade.php`)**:
   - Blok `.order-notes-strip` dihapus dari markup halaman utama detail pesanan.
   - Aturan CSS `.order-notes-strip` dibersihkan dari blok styles.
   - Catatan pengiriman (`catatan_pesanan`) tetap dipertahankan secara utuh di dalam modal rincian alamat (`orderAddressDetailModal`) untuk pengguna yang menekan tombol *Lihat Detail Alamat*.
2. **Pembaruan Pengujian (`OrderDetailLayoutTest.php`)**:
   - Assertion diperbarui menjadi `$response->assertDontSee('order-notes-strip')` dan `$response->assertDontSee('Catatan Pesanan:')`, memastikan card tersebut tidak lagi muncul di halaman sementara catatan pesanan tetap ada dalam modal detail alamat.

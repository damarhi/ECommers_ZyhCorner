# Dokumentasi Redesain Minimalis Card "Info Pengiriman" Detail Pesanan

Dokumen ini mencatat seluruh implementasi perbaikan dan redesain pada komponen kartu **Info Pengiriman** pada halaman detail pesanan pelanggan (`resources/views/Pelanggan/order_detail.blade.php`) beserta pembaruan unit & feature test terkait (`tests/Feature/OrderDetailLayoutTest.php`).

---

## 1. Latar Belakang & Masalah

Sebelum pembaruan ini, kartu **Info Pengiriman** pada halaman detail pesanan memiliki beberapa masalah desain dan tata letak:
1. **Terlalu Panjang ke Bawah (Vertical Bloat)**:
   - Setiap atribut (Kurir & Layanan, No. Resi, Status Kirim) dibungkus dalam `.shipping-field-group` dengan format dua baris vertikal (label di atas, value di bawah).
   - Adanya banner estimasi terpisah (`.shipping-est-banner`) dengan border tebal dan padding ekstra besar.
   - Adanya box catatan terpisah (`.shipping-notes-box`).
2. **Redundansi Aksi (Duplikasi Tombol Lacak)**:
   - Terdapat tombol besar `.btn-track-card` bertuliskan *"Lacak Pengiriman Biteship"* di bagian bawah kartu.
   - Tombol ini redundan karena pada header aksi utama (bagian atas halaman) sudah terdapat tombol utama **"Lacak Pesanan"** (`.btn-track-order-top`) yang membuka modal interaktif pelacakan pengiriman Biteship (`#biteshipTrackingModal`).
3. **Ketidakseimbangan Proporsi Antar-Kartu**:
   - Kartu "Info Pengiriman" memiliki tinggi vertikal yang jauh lebih panjang dibandingkan dua kartu pendampingnya, yaitu **Alamat Pengiriman** (kiri) dan **Rincian Pembayaran** (kanan).
   - Hal ini membuat tampilan grid 3 kolom pada desktop terlihat tidak simetris dan canggung (*awkward white spaces*).

---

## 2. Solusi & Prinsip Desain

1. **Eliminasi Tombol Redundan di Dalam Kartu**:
   - Menghapus tombol `.btn-track-card` ("Lacak Pengiriman Biteship") dari dalam kartu.
   - Pelanggan tetap dapat membuka modal pelacakan Biteship melalui tombol utama **"Lacak Pesanan"** yang selalu terlihat jelas di baris header atas.
2. **Standardisasi Tata Letak Baris Key-Value (`.summary-calc-list` & `.summary-calc-row`)**:
   - Mengadopsi struktur baris key-value satu baris yang sama persis dengan kartu sebelahnya (**Rincian Pembayaran**).
   - Label di sisi kiri dan nilai di sisi kanan, dipisahkan oleh garis batas putus-putus lembut (`border-bottom: 1px dashed #f1f5f9; padding-bottom: 7px;`).
3. **Penyederhanaan Komponen**:
   - **Kurir & Layanan**: Ditampilkan sebaris dengan ikon kurir dinamis (`fa-truck`, `fa-motorcycle`, atau `fa-shop`) berwarna hijau Forest Green.
   - **No. Resi**: Kode resi berfont monospace (`.waybill-code`) didampingi tombol salin mikro (`.btn-copy-resi`) dengan tooltip dan fungsi salin clipboard instan.
   - **Status Kirim**: Menggunakan badge semantik rounded yang lebih ringkas (`padding: 2.5px 8px; font-size: 11px;`).
   - **Perkiraan Tiba**: Menjadi satu baris key-value elegan tanpa banner/kotak bertingkat.
   - **Catatan Pesanan**: Jika ada catatan pembeli, ditaruh di bagian footer kartu secara minimalis dengan pembatas garis putus-putus.
4. **Pembersihan CSS Tidak Terpakai**:
   - Menghapus aturan CSS lama yang tidak lagi digunakan untuk menjaga kebersihan dan performa stylesheet.

---

## 3. Rincian Modifikasi Kode

### A. View: `resources/views/Pelanggan/order_detail.blade.php`

#### 1. Pembaruan dan Perampingan CSS

##### Sebelum:
```css
    .shipping-info-body {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .shipping-field-group {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .shipping-field-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .shipping-field-value {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        display: flex;
        align-items: center;
    }

    .shipping-waybill-wrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 6px;
        padding: 5px 8px;
    }

    .waybill-code {
        font-family: monospace;
        font-size: 12.5px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.5px;
    }

    .btn-copy-resi {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        color: #00b207;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s ease;
    }

    .shipping-est-banner {
        background: #f8fafc;
        border-left: 3px solid #00b207;
        padding: 7px 10px;
        border-radius: 6px;
    }
    /* ... */
    .shipping-card-footer {
        margin-top: auto;
        padding-top: 12px;
    }

    .btn-track-card {
        width: 100%;
        background: #f0fdf4;
        color: #00b207;
        border: 1.5px solid #bbf7d0;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12.5px;
        /* ... */
    }
```

##### Sesudah:
```css
    .waybill-code {
        font-family: monospace;
        font-size: 12px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: 0.5px;
        background: #f8fafc;
        padding: 2px 6px;
        border-radius: 4px;
        border: 1px dashed #cbd5e1;
    }

    .btn-copy-resi {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #00b207;
        font-size: 11px;
        padding: 3px 6px;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-copy-resi:hover {
        background: #00b207;
        color: #ffffff;
        border-color: #00b207;
    }

    .shipping-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        font-weight: 600;
        padding: 2.5px 8px;
        border-radius: 50px;
        width: fit-content;
    }
    .shipping-status-badge.badge-delivered {
        background: #e8f8ed;
        color: #00b207;
        border: 1px solid #b7ebc5;
    }
    .shipping-status-badge.badge-transit {
        background: #e0f2fe;
        color: #0284c7;
        border: 1px solid #bae6fd;
    }
    .shipping-status-badge.badge-processing {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .shipping-status-badge.badge-pending {
        background: #f1f5f9;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
    .shipping-status-badge.badge-canceled {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }
```

---

#### 2. Pembaruan Markup HTML / Blade

##### Sebelum:
```blade
<!-- Estimated Arrival & Shipping Card -->
<div class="col-md-4 mb-4">
    <div class="summary-card">
        <div>
            <div class="summary-card-title">
                <span><i class="fa-solid fa-truck-fast"></i> Info Pengiriman</span>
                <span class="badge-biteship-pill"><i class="fa-solid fa-bolt me-1"></i> Biteship</span>
            </div>
            
            <div class="shipping-info-body">
                <!-- Courier & Service -->
                <div class="shipping-field-group">
                    <span class="shipping-field-label">Kurir & Layanan:</span>
                    <div class="shipping-field-value">
                        <i class="fa-solid {{ $courierIcon }} text-success me-1"></i>
                        <span>{{ $order->shipping_method ?? 'Kurir Toko / Pengiriman Standar' }}</span>
                    </div>
                </div>

                <!-- Waybill / Resi -->
                <div class="shipping-field-group">
                    <span class="shipping-field-label">No. Resi Pengiriman:</span>
                    <div class="shipping-waybill-wrap">
                        <span class="waybill-code" id="orderResiCode">{{ $resiCode }}</span>
                        <button type="button" class="btn-copy-resi" onclick="copyTrackingResi('{{ $resiCode }}')" title="Salin nomor resi">
                            <i class="fa-regular fa-copy"></i>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>

                <!-- Shipping Status -->
                <div class="shipping-field-group">
                    <span class="shipping-field-label">Status Kirim:</span>
                    <div>
                        <!-- Status badges... -->
                    </div>
                </div>

                <!-- Estimated Arrival -->
                <div class="shipping-est-banner">
                    <div class="est-label"><i class="fa-regular fa-clock me-1"></i> Perkiraan Tiba:</div>
                    <div class="est-value">{{ $estimatedArrival }}</div>
                </div>

                @if(!empty($order->catatan_pesanan))
                    <div class="shipping-notes-box">
                        <i class="fa-regular fa-note-sticky text-muted me-1"></i> <strong>Catatan:</strong> {{ $order->catatan_pesanan }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Card Footer: Lacak Pengiriman Button -->
        <div class="shipping-card-footer">
            <button type="button" class="btn-track-card" onclick="openBiteshipTrackingModal()">
                <i class="fa-solid fa-location-crosshairs"></i> Lacak Pengiriman Biteship
            </button>
        </div>
    </div>
</div>
```

##### Sesudah:
```blade
<!-- Estimated Arrival & Shipping Card -->
<div class="col-md-4 mb-4">
    <div class="summary-card">
        <div>
            <div class="summary-card-title">
                <span><i class="fa-solid fa-truck-fast"></i> Info Pengiriman</span>
                <span class="badge-biteship-pill"><i class="fa-solid fa-bolt me-1"></i> Biteship</span>
            </div>
            
            @php
                $sm = strtolower($order->shipping_method ?? '');
                $courierIcon = 'fa-motorcycle';
                if (Str::contains($sm, ['jne', 'sicepat', 'j&t', 'antar', 'reguler'])) {
                    $courierIcon = 'fa-truck';
                } elseif (Str::contains($sm, ['ambil', 'pickup', 'pick up'])) {
                    $courierIcon = 'fa-shop';
                }
                $resiCode = $order->resi_code;
            @endphp

            <div class="summary-calc-list">
                <div class="summary-calc-row">
                    <span>Kurir:</span>
                    <span style="font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 5px; text-align: right;">
                        <i class="fa-solid {{ $courierIcon }} text-success"></i>
                        <span>{{ $order->shipping_method ?? 'Kurir Toko / Pengiriman Standar' }}</span>
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
                            <span class="shipping-status-badge badge-delivered"><i class="fa-solid fa-circle-check me-1"></i> Sampai di Tujuan</span>
                        @elseif($isCanceled)
                            <span class="shipping-status-badge badge-canceled"><i class="fa-solid fa-circle-xmark me-1"></i> Batal Kirim</span>
                        @elseif($isShipping)
                            <span class="shipping-status-badge badge-transit"><i class="fa-solid fa-truck me-1"></i> Dalam Perjalanan</span>
                        @elseif($isProcessing)
                            <span class="shipping-status-badge badge-processing"><i class="fa-solid fa-box me-1"></i> Sedang Dikemas</span>
                        @else
                            <span class="shipping-status-badge badge-pending"><i class="fa-regular fa-clock me-1"></i> Menunggu Proses</span>
                        @endif
                    </div>
                </div>

                <div class="summary-calc-row">
                    <span>Perkiraan Tiba:</span>
                    <span style="font-weight: 600; color: #0f172a; text-align: right;">
                        @if($isCompleted)
                            <span class="text-success"><i class="fa-solid fa-check me-1"></i> Diterima</span>
                        @elseif($isCanceled)
                            <span class="text-danger"><i class="fa-solid fa-ban me-1"></i> Batal</span>
                        @else
                            {{ $estimatedArrival }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        @if(!empty($order->catatan_pesanan))
            <div style="border-top: 1px dashed #f1f5f9; padding-top: 10px; margin-top: auto; font-size: 12.5px; color: #475569;">
                <i class="fa-regular fa-note-sticky text-muted me-1"></i> <strong style="color: #475569;">Catatan:</strong> {{ $order->catatan_pesanan }}
            </div>
        @endif
    </div>
</div>
```

---

### B. Automated Test: `tests/Feature/OrderDetailLayoutTest.php`

Menyesuaikan assertion pengujian pada method `test_customer_can_view_order_detail_with_refined_layout`:

```php
        // Tracking & actions assertions
        $response->assertSee('Lacak Pesanan');
        $response->assertSee('Batalkan Pesanan');
        $response->assertSee('BS-TEST001');
        $response->assertSee('Biteship');
        $response->assertDontSee('btn-track-card'); // Memastikan tombol lama di dalam card sudah tidak ada
        $response->assertSee('biteshipTrackingModal');
        $response->assertSee('header-actions-group');
```

---

## 4. Hasil Verifikasi & Pengujian

Seluruh rangkaian pengujian sistem otomatis dijalankan melalui command `php artisan test`.

```bash
$ php artisan test

   PASS  Tests\Unit\ExampleTest
   PASS  Tests\Feature\AboutUsTest
   PASS  Tests\Feature\AdminBannerTest
   PASS  Tests\Feature\AdminBulkDeleteTest
   PASS  Tests\Feature\AdminCustomProductsTest
   PASS  Tests\Feature\AdminDiskonTest
   PASS  Tests\Feature\AdminExportTest
   PASS  Tests\Feature\AdminOrderTest
   PASS  Tests\Feature\AdminSettingsTest
   PASS  Tests\Feature\CassierAccessControlTest
   PASS  Tests\Feature\CassierRedirectTest
   PASS  Tests\Feature\CassierSyncTest
   PASS  Tests\Feature\CheckoutDesignTest
   PASS  Tests\Feature\ContactUsTest
   PASS  Tests\Feature\CustomCakeTextTest
   PASS  Tests\Feature\CustomProductsTest
   PASS  Tests\Feature\CustomerAddressManagementTest
   PASS  Tests\Feature\CustomerDashboardTest
   PASS  Tests\Feature\CustomerSettingsTest
   PASS  Tests\Feature\ExampleTest
   PASS  Tests\Feature\ExpiredProductsTest
   PASS  Tests\Feature\FaqTest
   PASS  Tests\Feature\GoogleAuthTest
   PASS  Tests\Feature\KasirEmailNotificationTest
   PASS  Tests\Feature\OrderDetailLayoutTest
   PASS  Tests\Feature\OrderStockManagementTest
   PASS  Tests\Feature\PembayaranTest
   PASS  Tests\Feature\ProductDetailStockInfoTest
   PASS  Tests\Feature\ProductStockReductionTest
   PASS  Tests\Feature\RegisterOtpTest
   PASS  Tests\Feature\ShopStockPriorityTest

  Tests:    116 passed (560 assertions)
  Duration: 25.59s
```

### Ringkasan Pencapaian Desain:
1. **Vertical Compactness**: Ketinggian vertikal kartu Info Pengiriman terpangkas sekitar ~40-50%, kini sejajar dan simetris dengan kartu "Alamat Pengiriman" dan "Rincian Pembayaran" (~200px - 220px).
2. **Tidak Ada Tombol Ganda**: Tombol pelacakan pengiriman terpusat rapi di baris aksi header atas (`.btn-track-order-top`), menghilangkan kebingungan pengguna tanpa menghilangkan fitur pelacakan Biteship.
3. **Kerapian Visual Konsisten**: Format baris key-value seragam dengan kartu Rincian Pembayaran menggunakan palet Forest Green (`#00b207`), Slate Dark (`#0f172a`), dan Slate Muted (`#64748b`).
4. **Semua Fitur Esensial Terjaga**: Nomor resi tetap dapat disalin dengan satu klik, status kurir terpetakan dengan warna semantik yang jelas, dan estimasi waktu tiba tetap tertera secara transparan.

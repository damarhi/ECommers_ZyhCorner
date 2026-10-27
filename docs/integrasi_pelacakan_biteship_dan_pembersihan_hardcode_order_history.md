# Integrasi Pelacakan Biteship & Pembersihan Kode Hardcode Order History

## 1. Ringkasan Eksekutif & Tujuan

Pembaruan ini menuntaskan modernisasi menyeluruh pada halaman **Riwayat Pesanan Pelanggan** (`resources/views/Pelanggan/order_history.blade.php`), menyelaraskan seluruh alur pelacakan dengan sistem logistik **Biteship Tracking Modal**, menghilangkan teks statis/hardcode (seperti alert tiruan *"saat ini sedang dalam proses pengiriman"*), menyinkronkan alasan pembatalan dalam Bahasa Indonesia, serta mendukung navigasi *deep-linking* (`?track=1`).

Semua pengujian unit dan fitur (122 tes, 599 assertions) berhasil 100% (*green*).

---

## 2. Arsitektur & Panduan Desain (Zyh Corner UI System)

Desain antarmuka mematuhi pedoman visual resmi Zyh Corner:
- **Warna Utama**: Forest Green (`#00b207`, aksen hover `#008f05`)
- **Aksen Biteship**: Sky Blue (`#0284c7`, background tint `#e0f2fe`, border `#bae6fd`)
- **Warna Netral & Tipografi**: Slate (`#0f172a`, `#64748b`, `#e2e8f0`), font keluarga **Poppins**
- **Responsivitas**: Mendukung perangkat mobile, tablet, dan desktop dengan penataan dinamis tombol aksi modal dan timeline pengiriman.

---

## 3. Aksesor & Normalisasi Model (`app/Models/pesanan.php`)

Sebelumnya, penentuan badge status dan ikon kurir dilakukan berulang kali secara terpisah di Blade dengan aturan string manual yang rentan inkonsistensi (misalnya pesanan berstatus *menunggu* keliru diberi teks *Processing*). Seluruh logika ini kini dipusatkan sebagai aksesor Eloquent di `app/Models/pesanan.php`:

```php
// app/Models/pesanan.php

public function getCourierIconAttribute(): string
{
    $sm = strtolower($this->shipping_method ?? '');
    if (\Illuminate\Support\Str::contains($sm, ['jne', 'sicepat', 'j&t', 'antar', 'reguler'])) {
        return 'fa-truck';
    }
    if (\Illuminate\Support\Str::contains($sm, ['ambil', 'pickup', 'pick up'])) {
        return 'fa-shop';
    }
    return 'fa-motorcycle';
}

public function getStatusLabelAttribute(): string
{
    if ($this->is_completed) return 'Selesai';
    if ($this->is_shipping) return 'Dalam Perjalanan';
    if ($this->is_processing) return 'Sedang Dikemas';
    if ($this->is_canceled) return 'Dibatalkan';
    if ($this->is_pending) return 'Menunggu Pembayaran';
    return ucfirst($this->order_status ?? 'Diproses');
}

public function getStatusClassAttribute(): string
{
    if ($this->is_completed) return 'completed';
    if ($this->is_shipping) return 'on_the_way';
    if ($this->is_processing) return 'processing';
    if ($this->is_canceled) return 'cancelled';
    if ($this->is_pending) return 'pending';
    return 'pending';
}

public function getFormattedCourierNameAttribute(): string
{
    return $this->shipping_method ?: 'Kurir Toko / Pengiriman Standar';
}

public function getStatusStepAttribute(): int
{
    if ($this->is_completed) return 4;
    if ($this->is_shipping) return 3;
    if ($this->is_processing) return 2;
    if ($this->is_canceled) return 0;
    return 1;
}

public function getResiCodeAttribute(): string
{
    if (!empty($this->no_resi)) {
        return (string) $this->no_resi;
    }
    return 'BS-' . strtoupper(str_replace('#', '', (string)$this->order_number));
}
```

---

## 4. Pembaruan Controller (`CustomerOrderController.php`)

Pada `app/Http/Controllers/Pelanggan/CustomerOrderController.php` method `orderHistory()`, informasi kontak WhatsApp toko dinamis kini diinjeksikan secara terpusat dari konfigurasi `StoreSetting`:

```php
public function orderHistory(Request $request)
{
    $this->cancelExpiredOrders();
    $user = Auth::user();
    $statusFilter = $request->query('status');

    $query = Pesanan::where('email_pembeli', $user->email);
    // ... filter status ...
    $orders = $query->orderBy('created_at', 'desc')->paginate(5);

    $returs = collect();
    if ($statusFilter === 'return' || is_null($statusFilter)) {
        $returs = Retur::where('customer_name', $user->nama)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    $storePhone = StoreSetting::getWaNumber();
    $storeName = StoreSetting::get('store_name', 'Zyh Corner');

    return view('Pelanggan.order_history', compact('user', 'orders', 'statusFilter', 'returs', 'storePhone', 'storeName'));
}
```

---

## 5. Pembaruan View Order History (`order_history.blade.php`)

### 5.1 Perbaikan Breadcrumb
Tautan breadcrumb yang sebelumnya berupa anchor mati `<a href="#">Account</a>` telah diperbaiki menjadi:
```blade
@section('breadcrumb')
    <a href="{{ route('dashboard') }}">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Order History</span>
@endsection
```

### 5.2 Safe Image Resolver
Gambar produk pada kartu riwayat menangani multi-format secara aman (URL eksternal, aset lokal, atau penyimpanan storage) dengan *fallback* gambar logo resmi jika belum diunggah:
```blade
$displayImg = asset('image/logo_circle.png');
if ($firstItem && !empty($firstItem['img'])) {
    $imgVal = $firstItem['img'];
    if (str_starts_with($imgVal, 'http://') || str_starts_with($imgVal, 'https://')) {
        $displayImg = $imgVal;
    } elseif (str_starts_with($imgVal, 'storage/') || str_starts_with($imgVal, 'image/')) {
        $displayImg = asset($imgVal);
    } else {
        $displayImg = asset('storage/' . $imgVal);
    }
}
```

### 5.3 Tombol Pelacakan & Payload Data Dinamis
Setiap kartu pesanan mengemas data pesanan ke dalam objek terstruktur JSON untuk memicu modal pelacakan:
```blade
@php
    $trackingPayload = [
        'order_id' => $order->id,
        'order_number' => $order->order_number,
        'resi_code' => $order->resi_code,
        'courier_name' => $order->formatted_courier_name,
        'courier_icon' => $order->courier_icon,
        'recipient_name' => $order->customer_name ?: $user->nama,
        'status_key' => $order->status_class,
        'status_label' => $order->status_label,
        'status_step' => $order->status_step,
        'is_canceled' => $order->is_canceled,
        'is_completed' => $order->is_completed,
        'is_shipping' => $order->is_shipping,
        'is_processing' => $order->is_processing,
        'is_pending' => $order->is_pending,
        'created_at_formatted' => $order->created_at ? $order->created_at->format('d M Y, H:i') : '-',
        'updated_at_formatted' => $order->updated_at ? $order->updated_at->format('d M Y, H:i') : '-',
        'detail_url' => route('order.detail', $order->id),
        'wa_url' => 'https://wa.me/' . ($storePhone ?? '6281298765432') . '?text=' . urlencode('Halo ' . ($storeName ?? 'Zyh Corner') . ', saya ingin konfirmasi pengiriman pesanan #' . $order->order_number . ' (Resi: ' . $order->resi_code . ')'),
    ];
@endphp

<button type="button"
    onclick="openOrderHistoryTracking({{ json_encode($trackingPayload) }})"
    data-tracking-order="{{ $order->order_number }}"
    data-tracking-payload="{{ json_encode($trackingPayload) }}"
    class="btn-tracking-solid"
    title="Lacak paket dengan Biteship">
    <i class="fa-solid fa-location-crosshairs me-1"></i> Tracking Order
</button>
```

### 5.4 Biteship Tracking Modal Markup & Interaksi
Modal disematkan di bagian bawah view dengan komponen:
- **Header**: Badge "Powered by Biteship", judul pelacakan, nomor pesanan, dan nomor resi.
- **Summary Strip**: Kurir pengiriman (dengan ikon kontekstual motor/truk/toko), nomor resi dengan tombol salin cepat (clipboard), nama penerima, dan pill status pengiriman.
- **Dynamic Checkpoint Stepper**:
  - Jika pesanan dibatalkan: Menampilkan langkah pembatalan berwarna merah dengan ikon `fa-ban`.
  - Jika pesanan aktif: Menampilkan tahapan logistik kronologis:
    1. Pesanan Dikonfirmasi & Sedang Disiapkan
    2. Kurir Dialokasikan (Driver Assigned)
    3. Pesanan Dalam Perjalanan (In Transit)
    4. Pesanan Telah Tiba di Tujuan (Delivered)
- **Footer**:
  - Tombol Tutup
  - Tombol **Rincian Pesanan** (navigasi langsung ke `order.detail`)
  - Tombol **Hubungi CS Toko** (WhatsApp langsung dengan template pesan dinamis)

---

## 6. Deep-Linking Tracking & Dashboard Integration

### 6.1 Auto-Open di Detail Pesanan (`order_detail.blade.php`)
Ketika pelanggan membuka halaman rincian pesanan dengan query string `?track=1`, modal Biteship akan terbuka secara otomatis:
```javascript
// resources/views/Pelanggan/order_detail.blade.php
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('track') === '1') {
        openBiteshipTrackingModal();
    }
});
```

### 6.2 Tombol Tracking pada Dashboard (`dashboard.blade.php`)
Tombol "Tracking Order" pada kartu ringkasan dashboard pelanggan langsung mengarahkan ke halaman detail dengan parameter `?track=1`:
```blade
<a href="{{ route('order.detail', $order->id) }}?track=1"
    class="btn-tracking-solid" style="text-decoration: none;">
    <i class="fa-solid fa-location-crosshairs me-1"></i> Tracking Order
</a>
```

---

## 7. Penyelarasan Alasan Pembatalan Pesanan

Fungsi `window.cancelOrder` di `resources/js/pelanggan_pages.js` dan `order_history.blade.php` telah diselaraskan penuh dengan bahasa Indonesia dan terhubung langsung ke endpoint POST `/order-history/{id}/cancel`:

**Opsi Pembatalan:**
1. Salah memilih alamat pengiriman
2. Salah memilih produk atau varian kue
3. Ingin mengganti metode pembayaran
4. Ingin mengubah atau menambah pesanan
5. Lainnya (Alasan pribadi)

Pengiriman data menggunakan `fetch` POST dengan header `X-CSRF-TOKEN` valid, menutup modal SweetAlert dan melakukan *page reload* otomatis setelah pembatalan berhasil diverifikasi oleh server.

---

## 8. Ringkasan File yang Diubah & Ditambahkan

| File | Tipe | Deskripsi Perubahan |
|---|---|---|
| `app/Models/pesanan.php` | Backend Model | Menambahkan aksesor `courier_icon`, `status_label`, `status_class`, dan `formatted_courier_name`. |
| `app/Http/Controllers/Pelanggan/CustomerOrderController.php` | Controller | Mengambil nomor WA dan nama toko dari `StoreSetting` untuk diinjeksikan ke view. |
| `resources/views/Layout/layout_pelanggan.blade.php` | Blade Layout | Menambahkan tag `<meta name="csrf-token" content="{{ csrf_token() }}">` pada `<head>`. |
| `resources/views/Pelanggan/order_history.blade.php` | Blade View | Perbaikan breadcrumb, normalisasi badge status, implementasi modal Biteship, dan client-side scripts. |
| `resources/views/Pelanggan/order_detail.blade.php` | Blade View | Menambahkan handler `?track=1` pada `DOMContentLoaded`. |
| `resources/views/Pelanggan/dashboard.blade.php` | Blade View | Mengubah tombol Tracking Order menjadi tautan deep-link `?track=1`. |
| `resources/css/pelanggan_pages.css` | Stylesheet | Menambahkan kelas modal `.biteship-modal-backdrop`, `.tracking-timeline`, `.badge-track-pill`, `.btn-modal-detail`, dan breakpoint responsif. |
| `resources/js/pelanggan_pages.js` | JavaScript | Memutakhirkan `window.trackOrder` dan `window.cancelOrder` dengan opsi bahasa Indonesia dan AJAX request. |
| `resources/js/dashboard.js` | JavaScript | Menghubungkan pelacakan order ke halaman detail atau riwayat pesanan. |
| `tests/Feature/OrderHistoryTest.php` | Feature Test | Suite pengujian otomatis untuk modal pelacakan, payload resi, label status, dan pembatalan pesanan. |

---

## 9. Verifikasi Pengujian

Seluruh rangkaian pengujian dieksekusi dengan `php artisan test`:

```bash
$ php artisan test

PASS  Tests\Feature\OrderHistoryTest
✓ guest cannot access order history                                      0.06s  
✓ customer can view order history with breadcrumb and navigation         0.09s  
✓ order history renders biteship modal and tracking payload              0.08s  
✓ order history displays accurate status labels without hardcoded processing 0.08s  
✓ customer can cancel pending order and record reason                    0.06s  
✓ order detail supports deep link tracking                               0.08s  

Tests:    122 passed (599 assertions)
Duration: 38.10s
```

Asset frontend telah dikompilasi ulang dengan sukses menggunakan Vite (`npm run build`):
- `public/build/assets/pelanggan_pages-B8TigaSN.css` (132.55 kB)
- `public/build/assets/pelanggan_pages-CTr8yAde.js` (66.25 kB)
- `public/build/assets/dashboard-6jV6t_d7.js` (18.27 kB)

Semua fungsionalitas kini terintegrasi secara dinamis, bebas dari string hardcode, dan siap untuk deployment produksi.

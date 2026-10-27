# Dokumentasi Perbaikan Kode Hardcoded Halaman Detail Pesanan Pelanggan

Dokumen ini mencatat seluruh implementasi teknis dan modifikasi kode yang dilakukan untuk mengeliminasi nilai-nilai *hardcoded* pada halaman **Detail Pesanan Pelanggan** (`resources/views/Pelanggan/order_detail.blade.php`), model `Pesanan`, controller `CustomerOrderController`, migrasi database, dan pengujian otomatis (*feature tests*).

---

## 1. Ringkasan Eksekutif & Tujuan

Sebelumnya, terdapat 15 elemen *hardcoded* pada tampilan detail pesanan yang berisiko mengurangi transparansi informasi, mengarahkan kontak ke nomor WhatsApp dummy, menghilangkan alasan pembatalan pesanan, dan menampilkan estimasi waktu dalam bahasa Inggris.

Perbaikan ini menyelesaikan poin-poin tersebut dengan:
1. **Integrasi Profil Toko**: Mengambil nomor WhatsApp CS resmi dan nama toko langsung dari database (`StoreSetting::getWaNumber()` & `StoreSetting::get('store_name')`).
2. **Kalkulasi Ongkos Kirim Riil**: Menghitung selisih total belanja dengan subtotal produk pesanan sehingga pesanan dengan biaya kurir menampilkan tarif riil (misal `Rp. 15.000`), sedangkan pesanan tanpa biaya menampilkan label `Gratis`.
3. **Persistensi Alasan Pembatalan**: Menambahkan kolom `cancel_reason` ke database, melokalkan opsi pembatalan ke Bahasa Indonesia, dan mengirimkannya via fetch API ke server.
4. **Lokalisasi Bahasa Indonesia**: Format hari estimasi tiba diubah ke lokal Indonesia (`Carbon::setLocale('id')`, contoh: `Senin, 14:00 - 16:00 WIB`).
5. **Sanitasi Nomor Telepon**: Membersihkan prefix `0` dan `62` ganda pada tampilan nomor pengguna (`(+62) 812...`).
6. **Refactoring Arsitektur**: Memindahkan logika pendeteksian status string dari Blade ke *Attribute Accessors* pada model `Pesanan`, serta mengganti string literal AJAX URL dengan route helpers (`route('order.cancel', ':id')`, dll).
7. **Pembersihan Data Fallback**: Mengeliminasi *query random product* dummy saat `items` kosong agar data riwayat tidak berubah-ubah saat di-refresh.

---

## 2. Daftar Berkas yang Dimodifikasi & Dibuat

1. **`database/migrations/2026_10_26_000001_add_cancel_reason_to_pesanans_table.php`** *(File Baru)*
2. **`app/Models/Pesanan.php`** *(Dimodifikasi)*
3. **`app/Http/Controllers/Pelanggan/CustomerOrderController.php`** *(Dimodifikasi)*
4. **`resources/views/Pelanggan/order_detail.blade.php`** *(Dimodifikasi)*
5. **`tests/Feature/OrderDetailLayoutTest.php`** *(Dimodifikasi)*

---

## 3. Rincian Perubahan Kode

### A. Database Migration: `2026_10_26_000001_add_cancel_reason_to_pesanans_table.php`

Menambahkan kolom nullable `cancel_reason` setelah `catatan_pesanan` pada tabel `pesanans`.

```php
public function up(): void
{
    Schema::table('pesanans', function (Blueprint $table) {
        if (!Schema::hasColumn('pesanans', 'cancel_reason')) {
            $table->string('cancel_reason')->nullable()->after('catatan_pesanan');
        }
    });
}

public function down(): void
{
    Schema::table('pesanans', function (Blueprint $table) {
        if (Schema::hasColumn('pesanans', 'cancel_reason')) {
            $table->dropColumn(['cancel_reason']);
        }
    });
}
```

---

### B. Model: `app/Models/Pesanan.php`

1. Menambahkan `cancel_reason` ke dalam `$fillable`.
2. Menyediakan *Accessor Methods* untuk mengisolasi logika status pesanan, nomor resi, subtotal, dan ongkos kirim.

```php
protected $fillable = [
    'order_number',
    'jenis_penjualan',
    'payment_method',
    'shipping_method',
    'customer_name',
    'email_pembeli',
    'alamat_pengiriman',
    'catatan_pesanan',
    'payment_status',
    'snap_token',
    'order_status',
    'total',
    'items',
    'cancel_reason',
];

public function getIsCanceledAttribute(): bool
{
    $status = strtolower($this->order_status ?? '');
    return \Illuminate\Support\Str::contains($status, ['batal', 'cancel', 'failed']);
}

public function getIsCompletedAttribute(): bool
{
    $status = strtolower($this->order_status ?? '');
    return \Illuminate\Support\Str::contains($status, ['selesai', 'completed', 'delivered']);
}

public function getIsShippingAttribute(): bool
{
    $status = strtolower($this->order_status ?? '');
    return \Illuminate\Support\Str::contains($status, ['otw', 'way', 'kirim', 'sedang dikirim']);
}

public function getIsProcessingAttribute(): bool
{
    $status = strtolower($this->order_status ?? '');
    return \Illuminate\Support\Str::contains($status, ['proses', 'processing', 'ready', 'dikemas']);
}

public function getIsPendingAttribute(): bool
{
    $status = strtolower($this->order_status ?? '');
    return \Illuminate\Support\Str::contains($status, ['menunggu', 'pending']);
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

public function getItemsSubtotalAttribute(): float
{
    $items = $this->items;
    if (!is_array($items)) return 0.0;
    $subtotal = 0.0;
    foreach ($items as $item) {
        $qty = isset($item['qty']) ? intval($item['qty']) : 1;
        $price = isset($item['price']) ? floatval($item['price']) : 0.0;
        $subtotal += ($price * $qty);
    }
    return $subtotal;
}

public function getShippingCostAttribute(): float
{
    $total = floatval($this->total ?? 0);
    $subtotal = $this->items_subtotal;
    return max(0.0, $total - $subtotal);
}
```

---

### C. Controller: `app/Http/Controllers/Pelanggan/CustomerOrderController.php`

1. **Import `StoreSetting` & `Carbon`**:
   ```php
   use App\Models\StoreSetting;
   use Carbon\Carbon;
   ```
2. **Kalkulasi Biaya, Lokalisasi Estimasi & Integrasi Setting Toko** pada `orderDetail($id)`:
   ```php
   $itemsSubtotal = array_sum(array_column($items, 'subtotal'));
   $shippingCost = max(0, floatval($order->total) - $itemsSubtotal);

   Carbon::setLocale('id');
   $estimatedArrival = $order->created_at->addDays(1)->translatedFormat('l') . ', 14:00 - 16:00 WIB';
   $expiryTimestamp = $order->created_at->addHours(24)->timestamp;

   $storePhone = StoreSetting::getWaNumber();
   $storeName = StoreSetting::get('store_name', 'Zyh Corner');

   return view('Pelanggan.order_detail', compact(
       'user',
       'order',
       'items',
       'estimatedArrival',
       'expiryTimestamp',
       'shippingCost',
       'itemsSubtotal',
       'storePhone',
       'storeName'
   ));
   ```
3. **Penyimpanan Alasan Pembatalan** pada `cancelOrder(Request $request, $id)`:
   ```php
   public function cancelOrder(Request $request, $id)
   {
       $user = Auth::user();
       $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

       if (Str::contains(strtolower($order->order_status), ['menunggu', 'pending'])) {
           $cancelReason = $request->input('cancel_reason');

           $updateData = [
               'order_status' => 'batal',
               'payment_status' => 'Cancelled'
           ];
           if (!empty($cancelReason)) {
               $updateData['cancel_reason'] = $cancelReason;
           }

           $order->update($updateData);
           Produk::restoreStockForOrder($order);
           return response()->json([
               'success' => true,
               'message' => 'Pesanan berhasil dibatalkan!'
           ]);
       }

       return response()->json([
           'success' => false,
           'message' => 'Pesanan tidak dapat dibatalkan.'
       ], 400);
   }
   ```

---

### D. View: `resources/views/Pelanggan/order_detail.blade.php`

1. **Pembersihan Logika Stepper Status**:
   Menggunakan accessor `$order->status_step` dan `$order->is_canceled`:
   ```blade
   @php
       $isCanceled = $order->is_canceled;
       $statusStep = $order->status_step;
       $connectorWidth = match($statusStep) {
           2 => 33.3,
           3 => 66.6,
           4 => 100,
           default => 0,
       };
   @endphp
   ```
2. **Sanitasi Tampilan Nomor Telepon**:
   ```blade
   @php
       $rawCustPhone = (string)($user->no_hp ?? '');
       $cleanCustPhone = preg_replace('/^(\+?62|0)/', '', $rawCustPhone);
       $formattedCustPhone = $cleanCustPhone ? '(+62) ' . $cleanCustPhone : '-';
   @endphp
   <span id="displayBillingPhone" style="color: #0f172a; font-weight: 500;">{{ $formattedCustPhone }}</span>
   ```
3. **Ongkos Kirim Dinamis**:
   ```blade
   <div class="summary-calc-row">
       <span>Ongkos Kirim:</span>
       @if(($shippingCost ?? 0) > 0)
           <span style="color: #0f172a; font-weight: 600;">Rp. {{ number_format($shippingCost, 0, ',', '.') }}</span>
       @else
           <span style="color: #00b207; font-weight: 600;">Gratis</span>
       @endif
   </div>
   ```
4. **Link WhatsApp CS Toko Dinamis**:
   ```blade
   <a href="https://wa.me/{{ $storePhone ?? '6281298765432' }}?text={{ urlencode('Halo ' . ($storeName ?? 'Zyh Corner') . ', saya ingin konfirmasi pengiriman pesanan #' . $order->order_number . ' (Resi: ' . $resiCode . ')') }}" target="_blank" class="btn-modal-wa">
       <i class="fa-brands fa-whatsapp me-1"></i> Hubungi CS Toko
   </a>
   ```
5. **Modal Alasan Pembatalan Terlokalisasi & Transmisi Data**:
   ```javascript
   function cancelOrderDetail(id, orderNumber) {
       Swal.fire({
           title: 'ALASAN PEMBATALAN PESANAN',
           html: `
               <div style="text-align: left; margin-top: 10px;">
                   <label class="cancel-reason-option selected" onclick="selectCancelReason(this)">
                       <input type="radio" name="cancel_reason" value="Salah memilih alamat pengiriman" checked>
                       <span>Salah memilih alamat pengiriman</span>
                   </label>
                   <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                       <input type="radio" name="cancel_reason" value="Salah memilih produk atau varian kue">
                       <span>Salah memilih produk atau varian kue</span>
                   </label>
                   <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                       <input type="radio" name="cancel_reason" value="Ingin mengganti metode pembayaran">
                       <span>Ingin mengganti metode pembayaran</span>
                   </label>
                   <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                       <input type="radio" name="cancel_reason" value="Ingin mengubah atau menambah pesanan">
                       <span>Ingin mengubah atau menambah pesanan</span>
                   </label>
                   <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                       <input type="radio" name="cancel_reason" value="Lainnya">
                       <span>Lainnya (Alasan pribadi)</span>
                   </label>
               </div>
           `,
           showCancelButton: true,
           confirmButtonText: 'Konfirmasi Batal',
           cancelButtonText: 'Kembali',
           confirmButtonColor: '#00b207',
           cancelButtonColor: '#ffffff',
           // ...
       }).then((result) => {
           if (result.isConfirmed) {
               const selectedRadio = document.querySelector('input[name="cancel_reason"]:checked');
               const cancelReason = selectedRadio ? selectedRadio.value : 'Lainnya';

               const cancelUrl = `{{ route('order.cancel', ':id') }}`.replace(':id', id);
               fetch(cancelUrl, {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/json',
                       'X-CSRF-TOKEN': '{{ csrf_token() }}'
                   },
                   body: JSON.stringify({ cancel_reason: cancelReason })
               })
               // ...
           }
       });
   }
   ```
6. **Named Route Helpers untuk Pembayaran Midtrans**:
   ```javascript
   const payUrl = `{{ route('order.pay', ':id') }}`.replace(':id', orderId);
   const checkPaymentUrl = `{{ route('order.checkPayment', ':id') }}`.replace(':id', orderId);
   ```

---

### E. Pengujian Fitur: `tests/Feature/OrderDetailLayoutTest.php`

Menambahkan dua pengujian otomatis baru:
1. `test_order_detail_renders_dynamic_store_whatsapp_and_shipping_cost`: Memvalidasi bahwa ongkir dihitung dari selisih total dengan subtotal barang (`Rp. 15.000`), nomor WhatsApp toko diambil dari `StoreSetting`, dan waktu estimasi tiba memiliki akhiran zona waktu `WIB`.
2. `test_customer_cancelling_order_persists_cancel_reason`: Memvalidasi bahwa alasan pembatalan tersimpan secara permanen pada kolom `cancel_reason` tabel `pesanans`.

---

## 4. Hasil Verifikasi & Pengujian

Seluruh rangkaian pengujian sistem dijalankan melalui perintah `php artisan test`:

```text
   PASS  Tests\Feature\OrderDetailLayoutTest
  ✓ customer can view order detail with refined layout
  ✓ canceled order renders canceled status and banner
  ✓ customer cannot view other customers order
  ✓ order detail renders biteship modal and copyable resi
  ✓ order detail renders dynamic store whatsapp and shipping cost
  ✓ customer cancelling order persists cancel reason

  Tests:    116 passed (560 assertions)
  Duration: 24.16s
```

Semua 116 *feature and unit tests* (560 asersi) berhasil lulus 100% tanpa ada regresi pada modul checkout, stok produk, otentikasi, maupun administrasi.

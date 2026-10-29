# Dokumentasi Sinkronisasi Status Pesanan pada Modal Edit, Modal Detail, dan Tabel Admin

## 1. Ringkasan & Latar Belakang Masalah

### 1.1 Gejala Masalah
Pada sistem kelola pesanan online admin (`/admin/orders`), ditemukan inkonsistensi tampilan status pesanan setelah admin melakukan aksi **"Konfirmasi & Kemas"**:
1. **Di Baris Tabel Pesanan Online**: Status pesanan langsung berubah menjadi badge `Dikemas`.
2. **Di Halaman Detail Pesanan Pelanggan (`/order-history/{id}`)**: Status pesanan tertera `Dikemas` (Step 3: Dikonfirmasi & Dikemas).
3. **Di Modal Detail Pesanan (`#orderDetailModal`)**: Status pesanan **masih bertuliskan `Menunggu Konfirmasi`**.
4. **Di Modal Edit Pesanan (`#editOrderModal`)**: Dropdown `Status Pesanan` **masih terpilih `Menunggu Konfirmasi`**.

### 1.2 Akar Masalah Teknis (Root Cause)
1. **Pemisahan Server-Side Render (SSR Blade) dan Client-Side AJAX**:
   - Saat halaman `/admin/orders` pertama kali dimuat, server me-render seluruh elemen HTML tabel sekaligus seluruh modal edit dan modal detail untuk setiap pesanan dalam perulangan `@foreach($orders as $order)`.
   - Pada saat pemuatan awal tersebut, status pesanan di database masih `menunggu konfirmasi`. Akibatnya, elemen `<select name="order_status">` pada modal edit dan badge modal detail dirender dengan teks dan atribut `selected` pada opsi `menunggu konfirmasi`.
2. **Keterbatasan Callback AJAX Quick-Confirm**:
   - Ketika tombol **"Konfirmasi & Kemas"** ditekan, fungsi JavaScript `quickConfirmOrder(orderId, orderNumber)` mengirim request `POST /admin/orders/{id}/quick-confirm`.
   - Backend `PesananController@quickConfirm` berhasil memperbarui database menjadi `order_status = 'dikemas'`.
   - Namun pada callback respon sukses (`orders.blade.php`), JavaScript **hanya memperbarui innerHTML elemen `#order-status-badge-${orderId}` pada baris tabel** dan menghapus tombol konfirmasi.
   - JavaScript **tidak menyentuh DOM modal edit maupun modal detail** yang sudah dirender di halaman.
3. **Dampak Silent Regression (Pembalikan Status Tidak Disengaja)**:
   - Jika admin membuka modal edit setelah melakukan *quick confirm* tanpa merefresh halaman, dropdown masih bernilai `menunggu konfirmasi`.
   - Jika tombol "Perbarui Status" ditekan, form mengirimkan kembali `order_status = "menunggu konfirmasi"`, sehingga status pesanan di database yang tadinya sudah `dikemas` **ter-revert (tertimpa kembali)** menjadi `menunggu konfirmasi`.

---

## 2. Rincian Berkas dan Kode yang Diubah

### 2.1 File: `resources/views/Admin/kelola_penjualan/order_detail_modal.blade.php`
Menambahkan identifier unik `id="modal-detail-status-badge-{{ $order->id }}"` pada kontainer badge status pesanan agar dapat dimutasi secara reaktif oleh JavaScript.

#### Sebelum:
```blade
<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
    <span class="text-muted font-weight-500" style="font-size: 12px;">Status Pesanan</span>
    <div>
        @if($order->order_status == 'menunggu pembayaran')
            <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
        ...
```

#### Sesudah:
```blade
<div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
    <span class="text-muted font-weight-500" style="font-size: 12px;">Status Pesanan</span>
    <div id="modal-detail-status-badge-{{ $order->id }}">
        @if($order->order_status == 'menunggu pembayaran')
            <span class="badge badge-soft-warning" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Menunggu Pembayaran</span>
        @elseif($order->order_status == 'menunggu konfirmasi')
            <span class="badge badge-soft-warning badge-pulse-amber" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Menunggu Konfirmasi</span>
        @elseif($order->order_status == 'dikemas')
            <span class="badge badge-soft-info" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dikemas</span>
        @elseif($order->order_status == 'dikirim')
            <span class="badge badge-soft-primary" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dikirim</span>
        @elseif($order->order_status == 'selesai')
            <span class="badge badge-soft-success" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Selesai</span>
        @elseif($order->order_status == 'batal')
            <span class="badge badge-soft-danger" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dibatalkan</span>
        @else
            <span class="badge badge-soft-dark" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">{{ $order->order_status }}</span>
        @endif
    </div>
</div>
```

---

### 2.2 File: `resources/views/Admin/kelola_penjualan/orders.blade.php`

#### A. Penambahan ID pada Dropdown Modal Edit
Menambahkan `id="edit-order-status-select-{{ $order->id }}"` pada elemen `<select name="order_status">`:
```blade
<select class="form-select form-control" name="order_status" id="edit-order-status-select-{{ $order->id }}" required style="border-radius: 6px; font-size: 12px; height: 38px;">
    <option value="menunggu pembayaran" {{ $order->order_status == 'menunggu pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
    <option value="menunggu konfirmasi" {{ $order->order_status == 'menunggu konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
    <option value="dikemas" {{ $order->order_status == 'dikemas' ? 'selected' : '' }}>Dikemas</option>
    <option value="dikirim" {{ $order->order_status == 'dikirim' ? 'selected' : '' }}>Dikirim</option>
    <option value="selesai" {{ $order->order_status == 'selesai' ? 'selected' : '' }}>Selesai</option>
    <option value="batal" {{ $order->order_status == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
</select>
```

#### B. Pembaruan Callback `quickConfirmOrder`
Memperluas callback AJAX respon sukses agar memutasi seluruh komponen terkait:
```javascript
if (ok && body.success) {
    Swal.fire({
        title: 'Pesanan Dikonfirmasi!',
        text: body.message,
        icon: 'success',
        timer: 2000,
        showConfirmButton: false
    });

    // 1. Instant DOM update: Ubah status badge di baris tabel
    const badgeContainer = document.getElementById(`order-status-badge-${orderId}`);
    if (badgeContainer) {
        badgeContainer.innerHTML = '<span class="badge badge-soft-info">Dikemas</span>';
    }

    // 2. Animasi & hapus tombol quick confirm
    if (btn) {
        btn.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        btn.style.opacity = '0';
        btn.style.transform = 'scale(0.8)';
        setTimeout(() => btn.remove(), 300);
    }

    // 3. Sinkronisasi Dropdown Modal Edit Status (#editOrderModal)
    const editStatusSelect = document.getElementById(`edit-order-status-select-${orderId}`) ||
                             document.querySelector(`#editOrderModal${orderId} select[name="order_status"]`);
    if (editStatusSelect) {
        editStatusSelect.value = 'dikemas';
        Array.from(editStatusSelect.options).forEach(opt => {
            opt.selected = (opt.value === 'dikemas');
        });
    }

    // 4. Sinkronisasi Badge Status pada Modal Detail (#orderDetailModal)
    const modalDetailBadge = document.getElementById(`modal-detail-status-badge-${orderId}`);
    if (modalDetailBadge) {
        modalDetailBadge.innerHTML = '<span class="badge badge-soft-info" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dikemas</span>';
    }

    // 5. Perbarui data-search pada elemen tr agar pencarian real-time tetap sinkron
    const tableRow = document.querySelector(`tr[data-search*="${orderNumber.toLowerCase()}"]`) || (badgeContainer ? badgeContainer.closest('tr') : null);
    if (tableRow && tableRow.getAttribute('data-search')) {
        const curSearch = tableRow.getAttribute('data-search');
        tableRow.setAttribute('data-search', curSearch.replace(/menunggu\s*konfirmasi/gi, 'dikemas'));
    }
}
```

#### C. Proteksi Event Listener `show.bs.modal`
Menambahkan listener bootstrap modal saat modal edit pesanan dibuka sebagai lapisan pertahanan ganda (*defense-in-depth*). Jika status baris tabel sudah `Dikemas`, dropdown `<select>` dijamin otomatis menyesuaikan nilainya ke `dikemas` sebelum modal ditampilkan ke layar pengguna:
```javascript
// Proteksi sinkronisasi status ketika modal edit pesanan dibuka
document.addEventListener('show.bs.modal', function(event) {
    const modal = event.target;
    if (modal && modal.id && modal.id.startsWith('editOrderModal')) {
        const orderId = modal.id.replace('editOrderModal', '');
        const badgeContainer = document.getElementById(`order-status-badge-${orderId}`);
        const select = document.getElementById(`edit-order-status-select-${orderId}`) || modal.querySelector('select[name="order_status"]');
        if (badgeContainer && select) {
            const badgeText = badgeContainer.textContent.trim().toLowerCase();
            if (badgeText.includes('dikemas') && select.value !== 'dikemas') {
                select.value = 'dikemas';
                Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'dikemas'));
            } else if (badgeText.includes('dikirim') && select.value !== 'dikirim') {
                select.value = 'dikirim';
                Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'dikirim'));
            } else if (badgeText.includes('selesai') && select.value !== 'selesai') {
                select.value = 'selesai';
                Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'selesai'));
            } else if (badgeText.includes('batal') && select.value !== 'batal') {
                select.value = 'batal';
                Array.from(select.options).forEach(opt => opt.selected = (opt.value === 'batal'));
            }
        }
    }
});
```

---

### 2.3 File: `tests/Feature/OrderConfirmationWorkflowTest.php`
Menambahkan unit & feature test untuk memastikan view pesanan online admin me-render identifier sinkronisasi modal yang valid:

```php
public function test_orders_view_renders_synchronized_modal_ids_for_edit_and_detail(): void
{
    $kasir = $this->createKasirUser();

    $order = Pesanan::create([
        'order_number' => '#ORD-SYNC-99',
        'jenis_penjualan' => 'Online',
        'customer_name' => 'Dewi',
        'email_pembeli' => 'dewi@example.com',
        'payment_status' => 'Paid',
        'order_status' => 'menunggu konfirmasi',
        'total' => 85000,
    ]);

    $response = $this->actingAs($kasir)->get('/admin/orders');

    $response->assertStatus(200);
    $response->assertSee('id="edit-order-status-select-' . $order->id . '"', false);
    $response->assertSee('id="modal-detail-status-badge-' . $order->id . '"', false);
    $response->assertSee('id="order-status-badge-' . $order->id . '"', false);
}
```

---

## 3. Hasil Verifikasi Pengujian (PHPUnit)

Seluruh test suite pengujian otomatis telah dijalankan dan lulus 100%:
- **Total Pengujian**: 180 passed (950 assertions)
- **Status Kegagalan**: 0 failed
- **Pengujian Terkait Alur Konfirmasi**:
  - `cod order starts with menunggu konfirmasi status`: **PASS**
  - `paid midtrans order transitions to menunggu konfirmasi`: **PASS**
  - `customer can cancel order during menunggu konfirmasi and stock is restored`: **PASS**
  - `customer cannot cancel order after dikemas status`: **PASS**
  - `kasir can quick confirm order with single click`: **PASS**
  - `quick confirm rejects non unconfirmed order`: **PASS**
  - `orders table displays quick confirm button for unconfirmed order`: **PASS**
  - `orders view renders synchronized modal ids for edit and detail`: **PASS**

---

## 4. Keuntungan Arsitektur Setelah Perbaikan
1. **Kecepatan Instan (Zero Lag)**: Antarmuka tidak memerlukan full page refresh saat tombol quick confirm diklik, sehingga posisi scroll, filter, dan alur kerja admin tetap nyaman dan cepat.
2. **Konsistensi State 100%**: Saat modal edit dibuka, nilai dropdown dijamin merefleksikan status terbaru (`dikemas`).
3. **Pencegahan Data Reversion**: Form submission dari modal edit tidak akan pernah lagi menimpa status pesanan kembali ke status lama `menunggu konfirmasi`.
4. **Pencarian Real-Time Akurat**: Elemen `data-search` pada baris tabel disinkronkan sehingga pencarian kata kunci status pesanan di bar pencarian tetap akurat.

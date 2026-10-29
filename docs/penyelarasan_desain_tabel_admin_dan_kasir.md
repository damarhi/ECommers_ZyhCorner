# Penyelarasan Desain Tabel Data Admin dan Kasir

**Tanggal Pelaksanaan**: 28 Oktober 2026  
**Status**: Selesai & Terverifikasi (137 Tests Passed, 691 Assertions)  
**Tujuan**: Menyelaraskan seluruh elemen visual tabel (tipografi header dan sel, status badge pill, border tabel, dan ukuran tombol aksi 32×32 px) pada seluruh halaman role Admin dan Kasir di Zyh Corner.

---

## 1. Latar Belakang & Masalah Sebelumnya

Sebelum dilakukan penyelarasan, tabel-tabel data di portal Admin dan Kasir memiliki inkonsistensi desain yang mencolok:
1. **Inkonsistensi Makna Warna Tombol Aksi**:
   - Pada `orders.blade.php`, `returs.blade.php`, dan `customers.blade.php`, tombol Hapus menggunakan class `btn-outline-primary` dengan icon berwarna biru (`#2b5cff`), bukan merah (`danger`). Hal ini melanggar konvensi UX di mana aksi destruktif harus berwarna merah.
2. **Ketiadaan Badge Status pada Tabel Tertentu**:
   - Kolom status pada `returs.blade.php` dan `customers.blade.php` hanya menampilkan teks polos hitam (`{{ $retur->status }}` dan `{{ $customer->status }}`), berbeda dengan halaman pesanan yang telah menggunakan badge berdesain kapsul (*pill*).
3. **Penyimpangan Gaya Header dan Baris**:
   - Sebagian tabel menggunakan inline style kaku `padding: 15px 10px; font-size: 14px; color: #444; border-bottom: 1px solid #e4e4e4;`, sehingga menimpa aturan baku desain sistem yang ada pada `public/css/admin.css`.
4. **Perbedaan Varian Badge**:
   - Halaman pesan kontak (`pesan_kontak/index.blade.php`) menggunakan warna Bootstrap baku solid (`bg-warning text-dark`, `bg-info text-white`), bukan varian lembut pastel (`.badge-soft-*`).

---

## 2. Standar Token Desain Sistem yang Diterapkan (`admin.css`)

Seluruh tabel kini mengacu secara konsisten pada token desain di `public/css/admin.css`:

```
┌────────────────────────────────────────────────────────────────────────┐
│ TOKEN DESAIN SISTEM TABEL ADMIN & KASIR                                │
├────────────────────────────────────────────────────────────────────────┤
│ 1. KELAS TABEL:                                                        │
│    • <table class="table table-hover align-middle mb-0">               │
├────────────────────────────────────────────────────────────────────────┤
│ 2. HEADER TABEL (<thead> <th>):                                        │
│    • Background: #F9FAFB !important                                    │
│    • Border Bawah: 2px solid #E5E7EB, Border Atas: none                │
│    • Tipografi: Font-size 12px, Weight 600, Uppercase, Tracking 0.5px  │
│    • Warna Teks: #6B7280 (Slate Muted)                                 │
│    • Padding: 12px 16px                                                │
├────────────────────────────────────────────────────────────────────────┤
│ 3. ISI BARIS (<tbody> <td>):                                           │
│    • Tipografi: Font-size 13.5px, Color #374151                        │
│    • Primary Key / Identitas: .font-weight-600.text-dark               │
│    • Padding: 12px 16px, Vertical-align: middle                        │
│    • Border Bawah: 1px solid #F3F4F6                                   │
│    • Hover: Background #F9FAFB transition 0.15s                        │
├────────────────────────────────────────────────────────────────────────┤
│ 4. STATUS BADGE (.badge-soft-*):                                       │
│    • Bentuk: Rounded Pill (border-radius: 20px), Padding: 5px 10px     │
│    • Tipografi: 11.5px, Weight 500, Letter-spacing 0.3px               │
│    • .badge-soft-success : Background #D1FAE5, Color #065F46           │
│    • .badge-soft-warning : Background #FEF3C7, Color #92400E           │
│    • .badge-soft-danger  : Background #FEE2E2, Color #991B1B           │
│    • .badge-soft-info    : Background #DBEAFE, Color #1E40AF           │
│    • .badge-soft-primary : Background #EDE9FE, Color #5B21B6           │
│    • .badge-soft-dark    : Background #E5E7EB, Color #1F2937           │
├────────────────────────────────────────────────────────────────────────┤
│ 5. TOMBOL AKSI 32×32 PX:                                               │
│    • Dimensi: width 32px, height 32px, padding 0, border-radius 6px    │
│    • Detail / View : .btn-inverse-success (Bg #D1FAE5, Icon #10B981)   │
│    • Edit / Ubah   : .btn-inverse-info    (Bg #DBEAFE, Icon #3B82F6)   │
│    • Hapus / Buang : .btn-inverse-danger  (Bg #FEE2E2, Icon #EF4444)   │
│    • Toggle Status : .btn-inverse-warning / .btn-inverse-success       │
└────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Rincian Perubahan Kode per File

### 1. `resources/views/Admin/kelola_penjualan/orders.blade.php` (Pesanan Online)
- **Tujuan**: Menghilangkan inline style padding statis, mengaktifkan `table-hover align-middle`, dan mengganti tombol hapus biru menjadi merah lembut.
- **Sebelum**:
  ```blade
  <table class="table" style="border-bottom: 1px solid #e4e4e4;">
      <thead>
          <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
              <th style="padding: 15px 10px; width: 40px;">...</th>
              <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pesanan</th>
              ...
          </tr>
      </thead>
      <tbody>
          ...
          <button type="submit" class="btn btn-sm btn-outline-primary bg-white ..." title="Hapus Pesanan">
              <i class="mdi mdi-delete-outline" ...></i>
          </button>
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle">
      <thead>
          <tr>
              <th style="width: 40px;">...</th>
              <th>Pesanan</th>
              <th>Tanggal</th>
              <th>Pelanggan</th>
              <th>Status Pembayaran</th>
              <th>Status Pesanan</th>
              <th>Total</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
          </tr>
      </thead>
      <tbody>
          ...
          <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0;" title="Detail Pesanan">
              <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
          </button>
          <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#editOrderModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0;" title="Edit Pesanan">
              <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
          </button>
          <form action="{{ route('orders.destroy', $order->id) }}" method="POST" class="d-inline delete-form">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Hapus Pesanan">
                  <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
              </button>
          </form>
  ```

---

### 2. `resources/views/Admin/kelola_penjualan/returs.blade.php` (Pengembalian Produk)
- **Tujuan**: Menambahkan badge pill pada kolom status, mengganti tombol aksi dengan set `btn-inverse-*` 32×32 px, dan membersihkan inline padding.
- **Sebelum**:
  ```blade
  <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $retur->status }}</td>
  <td>
      <div class="d-flex">
          <button class="btn btn-sm btn-outline-success bg-white ...">...</button>
          <button class="btn btn-sm btn-outline-info bg-white ...">...</button>
          <button class="btn btn-sm btn-outline-primary bg-white ...">...</button>
      </div>
  </td>
  ```
- **Sesudah**:
  ```blade
  <td>
      @php
          $statusLower = strtolower($retur->status ?? '');
      @endphp
      @if(in_array($statusLower, ['done', 'accepted', 'selesai', 'diterima']))
          <span class="badge badge-soft-success">{{ ucfirst($retur->status) }}</span>
      @elseif(in_array($statusLower, ['new', 'processed', 'baru', 'diproses', 'pending']))
          <span class="badge badge-soft-warning">{{ ucfirst($retur->status) }}</span>
      @elseif(in_array($statusLower, ['rejected', 'ditolak', 'batal']))
          <span class="badge badge-soft-danger">{{ ucfirst($retur->status) }}</span>
      @else
          <span class="badge badge-soft-dark">{{ ucfirst($retur->status) }}</span>
      @endif
  </td>
  <td class="text-center">
      <div class="d-inline-flex align-items-center">
          <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#detailReturModal{{ $retur->id }}" title="Detail Retur" style="width: 32px; height: 32px; padding: 0;">
              <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
          </button>
          <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#editReturModal{{ $retur->id }}" title="Edit Retur" style="width: 32px; height: 32px; padding: 0;">
              <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
          </button>
          <form action="{{ route('returs.destroy', $retur->id) }}" method="POST" class="d-inline delete-form">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center" title="Hapus Retur" style="width: 32px; height: 32px; padding: 0;">
                  <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
              </button>
          </form>
      </div>
  </td>
  ```

---

### 3. `resources/views/Admin/kelola_penjualan/all_sales.blade.php` (Semua Transaksi)
- **Tujuan**: Menyelaraskan tabel transaksi keseluruhan dengan menghapus inline styles pada `<th>` dan `<td>`, serta menambahkan badge status pembatalan (`badge-soft-danger`).
- **Sebelum**:
  ```blade
  <table class="table" style="border-bottom: 1px solid #e4e4e4;">
      <thead>
          <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
              <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pesanan</th>
              ...
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle">
      <thead>
          <tr>
              <th>Pesanan</th>
              <th>Tanggal</th>
              <th>Jenis</th>
              <th>Pelanggan</th>
              <th>Email/Username</th>
              <th>Status Pembayaran</th>
              <th>Status Pesanan</th>
              <th>Total</th>
          </tr>
      </thead>
      <tbody>
          ...
          <td class="font-weight-600 text-dark">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
  ```

---

### 4. `resources/views/Admin/kelola_penjualan/order_detail_modal.blade.php` (Modal Rincian Pesanan)
- **Tujuan**: Menyelaraskan tabel produk pesanan di dalam modal pop-up dengan kelas `.table-hover.align-middle.mb-0` dan badge soft info untuk tulisan kue.
- **Sebelum**:
  ```blade
  <table class="table table-striped table-sm m-0" style="font-size: 13px;">
      <thead>
          <tr class="bg-light">
              <th style="font-weight: 600; padding: 8px;">Produk</th>
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
      <thead>
          <tr>
              <th style="padding: 10px 12px;">Produk</th>
              <th class="text-center" style="width: 60px; padding: 10px 12px;">Qty</th>
              <th class="text-end" style="width: 120px; padding: 10px 12px;">Subtotal</th>
          </tr>
      </thead>
      <tbody>
          ...
          <span class="badge badge-soft-info mt-1">Tulisan Kue: "{{ $item['custom_text'] }}"</span>
  ```

---

### 5. `resources/views/Admin/kelola_produk/products.blade.php` (Produk Showcase)
- **Tujuan**: Menghilangkan inline styles pada `<th>`, merapikan badge custom teks, serta memastikan teks harga dan aksi terpusat.
- **Sebelum**:
  ```blade
  <table class="table" style="border-bottom: 1px solid #e4e4e4;">
      <thead>
          <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
              <th style="padding: 15px 10px; width: 40px;">...</th>
              <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Produk</th>
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle">
      <thead>
          <tr>
              <th style="width: 40px;">...</th>
              <th>Produk</th>
              <th>Stok Produk</th>
              <th>Diskon</th>
              <th>Harga Asli</th>
              <th class="text-center" style="width: 100px;">Aksi</th>
          </tr>
      </thead>
  ```

---

### 6. `resources/views/Admin/kelola_produk/custom_products.blade.php` (Produk Kustom)
- **Tujuan**: Menghilangkan inline style padding dan warna header, serta merapikan kolom harga dan aksi.
- **Sebelum**:
  ```blade
  <table class="table" style="border-bottom: 1px solid #e4e4e4;">
      <thead>
          <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
              <th style="padding: 15px 10px; width: 40px;">...</th>
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle">
      <thead>
          <tr>
              <th style="width: 40px;">...</th>
              <th>Produk</th>
              <th>Jenis Produk</th>
              <th>Diskon</th>
              <th>Harga Asli</th>
              <th class="text-center" style="width: 100px;">Aksi</th>
          </tr>
      </thead>
  ```

---

### 7. `resources/views/Admin/kelola_produk/discounts.blade.php` (Diskon Produk)
- **Tujuan**: Mengganti badge diskon gradien menjadi format `.badge-soft-danger` / `.badge-soft-success`, serta menyelaraskan kolom Aksi.
- **Sebelum**:
  ```blade
  <span class="badge badge-gradient-danger px-3 py-2" style="font-size: 13px; border-radius: 20px;">
      <i class="mdi mdi-tag-outline me-1"></i>{{ number_format($d->nilai_diskon, 0) }}% OFF
  </span>
  ```
- **Sesudah**:
  ```blade
  <span class="badge badge-soft-danger">
      <i class="mdi mdi-tag-outline me-1"></i>{{ number_format($d->nilai_diskon, 0) }}% OFF
  </span>
  ```

---

### 8. `resources/views/Admin/kelola_produk/expired_products.blade.php` (Produk Kadaluarsa)
- **Tujuan**: Menyelaraskan badge tanggal kadaluarsa ke `.badge-soft-danger` dan status "Dimusnahkan" ke `.badge-soft-dark`.
- **Sebelum**:
  ```blade
  <span class="badge badge-gradient-danger px-3 py-2" style="font-size: 12px; border-radius: 20px;">
      <i class="mdi mdi-calendar-remove me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_expired)->format('d M Y') }}
  </span>
  ```
- **Sesudah**:
  ```blade
  <span class="badge badge-soft-danger">
      <i class="mdi mdi-calendar-remove me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_expired)->format('d M Y') }}
  </span>
  ```

---

### 9. `resources/views/Admin/kelola_akun/customers.blade.php` (Akun Pelanggan)
- **Tujuan**: Menambahkan badge status Active/Blocked, mengganti tombol aksi dari `btn-outline-info` & `btn-outline-primary` biru ke `btn-inverse-info` & `btn-inverse-danger` 32×32 px.
- **Sebelum**:
  ```blade
  <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $customer->status }}</td>
  <td>
      <div class="d-flex">
          <button class="btn btn-sm btn-outline-info ..."><i class="mdi mdi-pencil" style="color: #2b5cff;"></i></button>
          <button class="btn btn-sm btn-outline-primary ..."><i class="mdi mdi-delete-outline" style="color: #2b5cff;"></i></button>
      </div>
  </td>
  ```
- **Sesudah**:
  ```blade
  <td>
      @if(in_array(strtolower($customer->status ?? ''), ['active', 'aktif']))
          <span class="badge badge-soft-success">Active</span>
      @elseif(in_array(strtolower($customer->status ?? ''), ['blocked', 'diblokir', 'nonaktif', 'inactive']))
          <span class="badge badge-soft-danger">Blocked</span>
      @else
          <span class="badge badge-soft-dark">{{ $customer->status }}</span>
      @endif
  </td>
  <td class="text-center">
      <div class="d-inline-flex align-items-center">
          <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $customer->id }}" style="width: 32px; height: 32px; padding: 0;" title="Edit Pelanggan">
              <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
          </button>
          <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline delete-form">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Hapus Pelanggan">
                  <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
              </button>
          </form>
      </div>
  </td>
  ```

---

### 10. `resources/views/Admin/pesan_kontak/index.blade.php` (Pesan Kontak)
- **Tujuan**: Mengganti status badges bawaan Bootstrap (`bg-warning`, `bg-info`, `bg-success`) menjadi `.badge-soft-warning`, `.badge-soft-info`, `.badge-soft-success`, serta mengganti tombol detail pesan menjadi `.btn-inverse-success` 32×32 px dan tombol ubah status menjadi `.btn-inverse-info` 32×32 px.
- **Sebelum**:
  ```blade
  <span class="badge bg-warning text-dark px-3 py-2" style="border-radius: 20px; font-size: 11px;">
      <i class="mdi mdi-email-alert-outline me-1"></i> Belum Dibaca
  </span>
  <button type="button" class="btn btn-sm btn-gradient-primary px-2 py-1" ...>
      <i class="mdi mdi-eye"></i>
  </button>
  ```
- **Sesudah**:
  ```blade
  <span class="badge badge-soft-warning">
      <i class="mdi mdi-email-alert-outline me-1"></i> Belum Dibaca
  </span>
  <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1"
          style="width: 32px; height: 32px; padding: 0;"
          onclick="openMessageDetail(...)"
          title="Lihat Detail Pesan">
      <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
  </button>
  <div class="dropdown d-inline">
      <button class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 32px; height: 32px; padding: 0;" title="Ubah Status">
          <i class="mdi mdi-format-list-bulleted" style="font-size: 16px; margin: 0;"></i>
      </button>
      ...
  ```

---

### 11. `resources/views/Admin/dashboard/dashboard.blade.php` (Tiket Dashboard)
- **Tujuan**: Menambahkan kelas `.table-hover.align-middle` dan menyelaraskan penulisan teks badge status dari huruf besar (`SELESAI`) menjadi kapitalisasi baku (`Selesai`, `Dalam Proses`, `Ditangguhkan`, `Ditolak`).
- **Sebelum**:
  ```blade
  <table class="table">
      ...
      <label class="badge badge-soft-success">SELESAI</label>
      <label class="badge badge-soft-warning">DALAM PROSES</label>
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle">
      ...
      <span class="badge badge-soft-success">Selesai</span>
      <span class="badge badge-soft-warning">Dalam Proses</span>
  ```

---

### 12. `resources/views/Admin/kelola_penjualan/pos.blade.php` (Checkout POS Kasir)
- **Tujuan**: Menyelaraskan header tabel checkout keranjang kasir offline (`PRODUCT`, `PRICE`, `QUANTITY`, `SUBTOTAL`) dengan kelas `.table.table-hover.align-middle.mb-0` dan label baku Bahasa Indonesia (`Produk`, `Harga`, `Jumlah`, `Subtotal`).
- **Sebelum**:
  ```blade
  <table class="table table-borderless align-middle mb-0">
      <thead>
          <tr class="text-muted border-bottom">
              <th style="font-size:12px; font-weight:500;" class="ps-4">PRODUCT</th>
              <th style="font-size:12px; font-weight:500;">PRICE</th>
              <th style="font-size:12px; font-weight:500;">QUANTITY</th>
              <th style="font-size:12px; font-weight:500;" class="pe-4">SUBTOTAL</th>
          </tr>
      </thead>
  ```
- **Sesudah**:
  ```blade
  <table class="table table-hover align-middle mb-0">
      <thead>
          <tr>
              <th class="ps-4">Produk</th>
              <th>Harga</th>
              <th class="text-center">Jumlah</th>
              <th class="pe-4 text-end">Subtotal</th>
          </tr>
      </thead>
  ```

---

## 4. Hasil Verifikasi & Validasi

1. **Uji Otomatis Suite PHPUnit**:
   ```bash
   php artisan test
   ```
   **Hasil**: **137 tests passed (691 assertions)** tanpa kegagalan (100% hijau). Seluruh fitur bulk delete, quick confirm order, checkout POS, dan role access control berfungsi normal.

2. **Validasi Kompilasi Blade Template**:
   ```bash
   php artisan view:cache
   ```
   **Hasil**: Seluruh view berhasil dikompilasi tanpa syntax error atau tag HTML yang bocor.

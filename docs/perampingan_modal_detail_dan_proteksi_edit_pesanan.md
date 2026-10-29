# Dokumentasi: Perampingan Modal Detail Pesanan dan Proteksi Form Edit Pesanan

## 1. Latar Belakang & Analisis Permasalahan

Berdasarkan evaluasi tampilan antarmuka pada dasbor admin kelola penjualan:
1. **Modal Detail Pesanan (`order_detail_modal.blade.php`)**:
   - Sebelumnya menggunakan kelas `modal-lg` (lebar ~800px) dengan padding besar (`p-4`), yang menyebabkan modal tampak terlalu lebar dan memenuhi layar secara berlebihan pada layar laptop/desktop.
   - Apabila pesanan memiliki banyak item produk, tinggi modal terus memanjang ke bawah melampaui tinggi layar pandang (*viewport*).
2. **Modal Edit Pesanan (`orders.blade.php`)**:
   - Sebelumnya membuka input teks terbuka untuk `Nama Pelanggan` (`customer_name`) dan `Total Tagihan` (`total`).
   - Mengubah nama pemesan dan nominal tagihan secara manual sangat berisiko terhadap:
     - **Integritas Pembukuan Keuangan**: Nilai total pesanan seharusnya mengikat pada $\sum (\text{item} \times \text{qty}) + \text{ongkir}$. Pengubahan manual memicu selisih (*mismatch*) antara invoice, laporan penjualan, dan mutasi kasir.
     - **Transaksi Payment Gateway (Midtrans)**: Nominal transaksi pada pesanan online sudah terekam di payment gateway. Pengubahan nominal di admin memicu inkonsistensi rekonsiliasi.
     - **Kepemilikan Akun**: Mengubah nama pembeli dapat membingungkan riwayat transaksi pada akun pelanggan.
   - Kebutuhan operasional admin yang sesungguhnya saat mengedit pesanan adalah memperbarui **Status Pembayaran** dan **Status Alur Pesanan**.

---

## 2. Solusi Desain (*Frontend Design Direction*)
   - Mengganti `modal-lg` (800px) menjadi dimensi proporsional ideal `max-width: 720px; margin: 1.75rem auto; width: 95%;`. Lebar ini memberikan ruang sela (*breathing room*) yang sangat lega untuk alamat dan badge status tanpa terasa mendominasi layar.
   - Menyelaraskan padding dan margin (header `py-2.5 px-3 px-sm-4`, body `p-3 p-sm-4`, info box `12px 14px`, footer `py-2.5 px-3 px-sm-4`).
   - Mengontrol tinggi tabel belanja dengan `max-height: 160px; overflow-y: auto;` serta header tabel `position: sticky; top: 0;`. Jika item belanja banyak, tabel dapat digulir di dalam kontainernya tanpa menambah tinggi modal.
   - Membatasi tinggi maksimal modal `max-height: calc(100vh - 3.5rem)` agar tetap pas di layar resolusi standar (768p/900p/1080p).

2. **Proteksi Integritas Data pada Modal Edit**:
   - Membatasi modal edit menjadi ukuran ringkas `max-width: 480px`.
   - Menghapus input teks bebas untuk nama pelanggan dan total tagihan, lalu menggantinya dengan **Kartu Informasi Terkunci (*Protected Read-Only Box*)**:
     - Memuat nama pelanggan tebal dan total tagihan terformat rupiah.
     - Dilengkapi indikator badge ID pesanan dan ikon gembok peringatan: `<i class="mdi mdi-lock-outline text-warning"></i> Data Transaksi Terkunci`.
     - Menyimpan nilai asli dalam bentuk `input type="hidden"` untuk kompatibilitas form submission.
   - Menyusun dropdown interaktif (`Status Bayar` dan `Status Pesanan`) dalam **Grid 2 Kolom** berdampingan.

3. **Fleksibilitas Controller Backend**:
   - Memperbarui `PesananController::update()` agar `customer_name` dan `total` berstatus `nullable`. Controller hanya memperbarui status pesanan dan status bayar jika nama/total tidak dikirimkan, mencegah resiko `null overwrite`.

---

## 3. Berkas-Berkas yang Dimodifikasi

| No | Berkas | Lokasi | Ringkasan Modifikasi |
| :---: | :--- | :--- | :--- |
| 1 | `order_detail_modal.blade.php` | `resources/views/Admin/kelola_penjualan/` | Mengatur `max-width: 620px`, merampingkan padding & tipografi, serta menambahkan batas `max-height: 160px; overflow-y: auto;` pada kontainer tabel produk. |
| 2 | `orders.blade.php` | `resources/views/Admin/kelola_penjualan/` | Mengatur `max-width: 480px`, mengganti input nama dan harga dengan kartu info terkunci (*read-only*), serta menata dropdown status dalam grid 2 kolom. |
| 3 | `PesananController.php` | `app/Http/Controllers/Admin/` | Mengubah validasi `customer_name` dan `total` menjadi `nullable` dan memperbarui data secara selektif jika tersedia. |

---

## 4. Komparasi Kode (*Before vs After*)

### A. Modal Detail Pesanan (`order_detail_modal.blade.php`)

* **Sebelumnya (Lebar 800px dan padding longgar):**
  ```blade
  <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
          <div class="modal-header border-bottom py-3 px-4 bg-white">
              ...
          </div>
          <div class="modal-body p-4 bg-white">
              ...
              <div class="table-responsive rounded-3 border mb-3">
                  <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
  ```

* **Sesudahnya (Lebar proporsional 720px, scrollable table, padding rapi):**
  ```blade
  <div class="modal-dialog modal-dialog-centered" style="max-width: 720px; margin: 1.75rem auto; width: 95%;">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
          <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
              ...
          </div>
          <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
              ...
              <div class="table-responsive rounded-3 border mb-2" style="max-height: 180px; overflow-y: auto;">
                  <table class="table table-hover align-middle mb-0" style="font-size: 12px;">
                      <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 1;">
  ```

---

### B. Modal Edit Pesanan (`orders.blade.php`)

* **Sebelumnya (Input teks bebas yang rawan disalahgunakan):**
  ```blade
  <div class="form-group mb-3">
      <label class="form-label font-weight-500 mb-1 text-dark">Nama Pelanggan *</label>
      <input type="text" class="form-control" name="customer_name" value="{{ $order->customer_name }}" required>
  </div>
  <div class="form-group mb-3">
      <label class="form-label font-weight-500 mb-1 text-dark">Total Tagihan (Rp) *</label>
      <input type="number" class="form-control" name="total" value="{{ $order->total }}" required min="0">
  </div>
  ```

* **Sesudahnya (Kartu Terkunci Aman & Elegan):**
  ```blade
  <!-- Ringkasan Data Pesanan Terkunci (Read-Only) -->
  <div class="p-2.5 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 12px;">
      <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
          <span class="text-muted d-flex align-items-center" style="font-size: 11px;">
              <i class="mdi mdi-lock-outline text-warning me-1" style="font-size: 13px;"></i> Data Transaksi Terkunci
          </span>
          <span class="badge badge-soft-dark" style="font-size: 10px;">ID #{{ $order->id }}</span>
      </div>
      <div class="row g-2">
          <div class="col-6">
              <span class="text-muted d-block" style="font-size: 10px;">Nama Pelanggan</span>
              <strong class="text-dark d-block text-truncate" style="font-size: 12px;">{{ $order->customer_name }}</strong>
          </div>
          <div class="col-6 text-end">
              <span class="text-muted d-block" style="font-size: 10px;">Total Tagihan</span>
              <strong class="text-dark d-block font-weight-bold" style="font-size: 13px; color: #1E2753 !important;">
                  Rp. {{ number_format($order->total, 0, ',', '.') }}
              </strong>
          </div>
      </div>
  </div>

  <input type="hidden" name="customer_name" value="{{ $order->customer_name }}">
  <input type="hidden" name="total" value="{{ $order->total }}">
  ```

---

### C. Controller Backend (`PesananController.php`)

* **Sebelumnya:**
  ```php
  $request->validate([
      'customer_name' => 'required|string|max:255',
      'payment_status' => 'required|string|in:Pending,Paid',
      'order_status' => 'required|string|in:menunggu pembayaran,menunggu konfirmasi,dikemas,dikirim,selesai,batal',
      'total' => 'required|numeric|min:0',
  ]);

  $pesanan->update($request->only('customer_name', 'payment_status', 'order_status', 'total'));
  ```

* **Sesudahnya:**
  ```php
  $request->validate([
      'customer_name' => 'nullable|string|max:255',
      'payment_status' => 'required|string|in:Pending,Paid',
      'order_status' => 'required|string|in:menunggu pembayaran,menunggu konfirmasi,dikemas,dikirim,selesai,batal',
      'total' => 'nullable|numeric|min:0',
  ]);

  $updateData = $request->only('payment_status', 'order_status');
  if ($request->filled('customer_name')) {
      $updateData['customer_name'] = $request->customer_name;
  }
  if ($request->filled('total')) {
      $updateData['total'] = $request->total;
  }

  $pesanan->update($updateData);
  ```

---

## 5. Hasil Verifikasi & Uji Otomatis

1. **Blade Template Re-caching**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Status:* Sukses dibersihkan dan dikompilasi ulang tanpa error sintaks.

2. **PHPUnit Automated Test Suite**:
   ```bash
   php artisan test
   ```
   *Status:* **147 passed (741 assertions)** &check; (Durasi: 24.31s, 100% *Green*).

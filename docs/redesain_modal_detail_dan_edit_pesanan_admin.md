# Dokumentasi: Redesain Modal Detail dan Edit Pesanan Online Dasbor Admin

## 1. Latar Belakang & Analisis Kebutuhan

Berdasarkan audit antarmuka dari tangkapan layar modal pada halaman kelola pesanan admin (`orders.blade.php`), ditemukan sejumlah kekurangan desain:

1. **Modal Detail Pesanan (`order_detail_modal.blade.php`)**:
   * **Header Terlalu Kontras**: Menggunakan balok ungu tua pekat (`bg-gradient-primary text-white py-3`) yang memutus ritme visual dengan isi modal.
   * **Anti-Pattern "Card di dalam Card"**: Terdapat nested card (`card border-0 mb-3 bg-light`) di dalam body modal yang membebani ruang visual.
   * **Format Jadul Bertitik Dua (`:`)**: Informasi pelanggan disajikan dengan tabel polos berlabel sempit (`width: 110px`) dan titik dua yang menggantung kaku saat baris alamat/catatan panjang.
   * **Satu Kolom Vertikal Monoton**: Semua data bertumpuk ke bawah sehingga admin harus scroll untuk melihat produk belanjaan.
   * **Breakdown Tagihan Belum Transparan**: Bagian bawah hanya menampilkan satu baris "Total", tanpa merinci akumulasi subtotal produk dan biaya ongkos kirim.

2. **Modal Edit Pesanan (`editOrderModal` di `orders.blade.php`)**:
   * **Header Minim Konteks**: Hanya menampilkan teks judul *"Edit Pesanan"* tanpa nomor identifikasi pesanan yang sedang diubah.
   * **Tata Letak Boros Ruang**: Semua input form ditumpuk secara vertikal (1 kolom).
   * **Input Angka Mentah**: Field *"Total (Rp)"* hanya kotak teks polos tanpa prefix rupiah `Rp` atau preview pemisah ribuan.

---

## 2. Solusi Berdasarkan Panduan *Frontend Design Direction*

1. **Modal Detail Pesanan**:
   * Ukuran modal ditingkatkan menjadi `modal-dialog-centered modal-lg` untuk kenyamanan pembacaan data.
   * Header modern berlatar putih dengan badge nomor pesanan (`Detail #ORDERNUMBER`), badge jenis transaksi (`Online` / `Offline POS`), serta stempel waktu pembuatan pesanan.
   * Pembagian informasi menjadi **Grid 2 Kolom Bersih**:
     * **Kolom Kiri**: Informasi Pelanggan (Nama, Email, Alamat Pengiriman ber-ikon pin lokasi, dan Catatan ber-ikon memo).
     * **Kolom Kanan**: Status & Pembayaran (Metode Pembayaran, Status Pembayaran ber-badge soft warna, Status Pesanan ber-badge adaptif, dan info Kurir).
   * Format tabel produk yang proporsional dengan badge tulisan kue dan baris breakdown tagihan transparan:
     $$\text{Subtotal Produk} + \text{Ongkos Kirim} = \text{Total Tagihan}$$
   * Footer modal menyajikan ID Pesanan di sisi kiri, serta tombol sekunder *Tutup* dan tombol primer *Cetak Invoice* di sisi kanan.

2. **Modal Edit Pesanan**:
   * Dialog modal terpusat (`modal-dialog-centered`) dengan header berbadge nomor pesanan.
   * Input group dengan ikon user (`mdi mdi-account-outline`) untuk nama pelanggan.
   * Input group dengan prefix `Rp` untuk total tagihan disertai catatan bantuan nominal terformat.
   * Penataan dropdown `Status Bayar` dan `Status Pesanan` secara berdampingan (**Grid 2 Kolom**) sehingga modal lebih ringkas dan hemat ruang.

---

## 3. Berkas yang Dimodifikasi

| No | Berkas | Lokasi | Tipe Perubahan |
| :---: | :--- | :--- | :--- |
| 1 | `order_detail_modal.blade.php` | `resources/views/Admin/kelola_penjualan/` | Redesain total modal detail pesanan: header modern, tata letak 2 kolom, tabel produk ber-padding rapi, breakdown tagihan transparan, dan footer seimbang. |
| 2 | `orders.blade.php` | `resources/views/Admin/kelola_penjualan/` | Redesain modal edit pesanan: header berbadge pesanan, input ber-prefix ikon & mata uang, dan grid 2 kolom status transaksi. |

---

## 4. Rincian Perubahan Kode (*Before vs After*)

### A. Modal Detail Pesanan (`order_detail_modal.blade.php`)

* **Sebelumnya (Header & Nested Card Jadul):**
  ```blade
  <div class="modal-dialog">
      <div class="modal-content" style="border-radius: 8px;">
          <div class="modal-header bg-gradient-primary text-white py-3">
              <h5 class="modal-title font-weight-bold text-white d-flex align-items-center">
                  <i class="mdi mdi-receipt me-2"></i> Detail {{ $order->order_number }}
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-3">
              <div class="card border-0 mb-3 bg-light">
                  <div class="card-body p-3">
                      <table class="table table-borderless table-sm m-0">
                          <tr><td style="width: 110px;">Pelanggan</td><td>: {{ $order->customer_name }}</td></tr>
                          ...
                      </table>
                  </div>
              </div>
  ```

* **Sesudahnya (Header Bersih & Grid 2 Kolom Tanpa Nested Card):**
  ```blade
  <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
          <div class="modal-header border-bottom py-3 px-4 bg-white">
              <div class="d-flex align-items-center gap-3">
                  <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 40px; height: 40px; background-color: #ede9fe; color: #4f46e5 !important;">
                      <i class="mdi mdi-receipt-text-outline" style="font-size: 20px;"></i>
                  </span>
                  <div>
                      <div class="d-flex align-items-center gap-2 flex-wrap">
                          <h5 class="modal-title font-weight-bold mb-0 text-dark" style="font-size: 16px;">
                              Detail {{ $order->order_number }}
                          </h5>
                          <span class="badge {{ $order->jenis_penjualan == 'Online' ? 'badge-soft-info' : 'badge-soft-dark' }}">
                              {{ $order->jenis_penjualan }}
                          </span>
                      </div>
                      <small class="text-muted"><i class="mdi mdi-clock-outline me-1"></i>{{ $order->created_at->format('d M Y, H:i') }} WIB</small>
                  </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          
          <div class="modal-body p-4 bg-white">
              <div class="row g-3 mb-4">
                  <!-- Kolom Kiri: Pelanggan & Alamat Pengiriman -->
                  <div class="col-md-6">
                      <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                          ...
                      </div>
                  </div>
                  <!-- Kolom Kanan: Status & Pembayaran -->
                  <div class="col-md-6">
                      <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                          ...
                      </div>
                  </div>
              </div>
  ```

---

### B. Modal Edit Pesanan (`orders.blade.php`)

* **Sebelumnya (Form 1 Kolom Sederhana):**
  ```blade
  <div class="modal-dialog">
      <div class="modal-content" style="border-radius: 8px;">
          <div class="modal-header border-0 pb-0">
              <h5 class="modal-title font-weight-bold">Edit Pesanan</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <form action="{{ route('orders.update', $order->id) }}" method="POST" class="edit-form">
              <div class="modal-body">
                  <div class="form-group mb-3">
                      <label>Nama Pelanggan</label>
                      <input type="text" class="form-control" name="customer_name" value="{{ $order->customer_name }}" required>
                  </div>
                  <div class="form-group mb-3">
                      <label>Total (Rp)</label>
                      <input type="number" class="form-control" name="total" value="{{ $order->total }}" required min="0">
                  </div>
                  <div class="form-group mb-3">
                      <label>Status Pembayaran</label>
                      <select class="form-select form-control" name="payment_status" required>...</select>
                  </div>
                  <div class="form-group mb-0">
                      <label>Status Pesanan</label>
                      <select class="form-select form-control" name="order_status" required>...</select>
                  </div>
              </div>
  ```

* **Sesudahnya (Header Kontekstual, Input-Group Prefix & Grid 2 Kolom):**
  ```blade
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
          <div class="modal-header border-bottom py-3 px-4 bg-white">
              <div class="d-flex align-items-center gap-3">
                  <span class="d-inline-flex align-items-center justify-content-center text-info rounded-circle" style="width: 40px; height: 40px; background-color: #e0f2fe; color: #0284c7 !important;">
                      <i class="mdi mdi-pencil-box-outline" style="font-size: 20px;"></i>
                  </span>
                  <div>
                      <div class="d-flex align-items-center gap-2">
                          <h5 class="modal-title font-weight-bold mb-0 text-dark">Edit Pesanan</h5>
                          <span class="badge bg-light text-primary border">{{ $order->order_number }}</span>
                      </div>
                      <small class="text-muted">Perbarui data pelanggan, tagihan, dan status pesanan.</small>
                  </div>
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>

          <form action="{{ route('orders.update', $order->id) }}" method="POST" class="edit-form">
              @csrf
              @method('PUT')
              <div class="modal-body p-4 bg-white">
                  <div class="form-group mb-3">
                      <label class="form-label font-weight-500 mb-1 text-dark">Nama Pelanggan <span class="text-danger">*</span></label>
                      <div class="input-group">
                          <span class="input-group-text bg-light border-end-0 text-muted"><i class="mdi mdi-account-outline"></i></span>
                          <input type="text" class="form-control border-start-0" name="customer_name" value="{{ $order->customer_name }}" required>
                      </div>
                  </div>

                  <div class="form-group mb-3">
                      <label class="form-label font-weight-500 mb-1 text-dark">Total Tagihan (Rp) <span class="text-danger">*</span></label>
                      <div class="input-group">
                          <span class="input-group-text bg-light border-end-0 font-weight-bold text-muted">Rp</span>
                          <input type="number" class="form-control border-start-0 font-weight-bold text-dark" name="total" value="{{ $order->total }}" required min="0">
                      </div>
                      <small class="text-muted">Nominal saat ini: Rp. {{ number_format($order->total, 0, ',', '.') }}</small>
                  </div>

                  <!-- Grid 2 Kolom untuk Status Transaksi -->
                  <div class="row g-2">
                      <div class="col-md-6">
                          <div class="form-group mb-0">
                              <label class="form-label font-weight-500 mb-1 text-dark">Status Bayar <span class="text-danger">*</span></label>
                              <select class="form-select form-control" name="payment_status" required>...</select>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group mb-0">
                              <label class="form-label font-weight-500 mb-1 text-dark">Status Pesanan <span class="text-danger">*</span></label>
                              <select class="form-select form-control" name="order_status" required>...</select>
                          </div>
                      </div>
                  </div>
              </div>
  ```

---

## 5. Hasil Verifikasi & Pengujian

1. **Kompilasi View**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Sukses dibersihkan dan dikompilasi ulang tanpa kesalahan sintaks Blade.

2. **Automated Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil:*
   ```
   Tests:    147 passed (741 assertions)
   Duration: 27.47s
   ```
   Seluruh 147 test cases tetap 100% *Green*.

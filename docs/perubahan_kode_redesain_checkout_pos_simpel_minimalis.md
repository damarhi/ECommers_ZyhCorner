# Dokumentasi Perubahan Kode: Redesain Halaman Checkout Kasir Offline (POS) Simpel & Minimalis

## 1. Latar Belakang & Tujuan Perubahan

Berdasarkan tinjauan antarmuka halaman checkout kasir mandiri (`/admin/pos/checkout`), desain sebelumnya memuat terlalu banyak elemen ornamen visual yang padat (*visual noise*), antara lain:
* Ikon gradient tebal dan deskripsi teks panjang di header halaman.
* Chip tanggal dan badge jumlah produk yang menduplikasi informasi tabel.
* Header dan footer card daftar pesanan belanja dengan teks panduan dan tombol tambah produk lain.
* Radio toggle pill metode pembayaran berwarna tebal.
* Kalkulator uang tunai dan empat tombol chip pecahan uang (`Uang Pas`, `50.000`, `100.000`, `200.000`).
* Kotak indikator kembalian dinamis dan tombol hapus keranjang merah.

Sesuai permintaan pengguna dan panduan **`frontend-design-direction`**, antarmuka checkout kasir dikembalikan ke estetika awal yang **simpel, bersih, dan minimalis**:
1. Menghilangkan elemen dekoratif dan teks deskriptif yang tidak esensial.
2. Mengembalikan tata letak klasik tabel pesanan dan formulir pembayaran kasir yang ringkas dan efisien.
3. Menjaga arsitektur halaman mandiri (`/admin/pos/checkout`) dan integrasi `localStorage` (`zyh_pos_cart`) agar bebas dari masalah *DOM bleed-through*.

---

## 2. Berkas yang Dimodifikasi

| Berkas | Lokasi | Rincian Perubahan |
| :--- | :--- | :--- |
| `pos_checkout.blade.php` | `resources/views/Admin/kelola_penjualan/` | 1. Menyederhanakan header menjadi link `← Kembali` dan judul teks `Checkout`.<br>2. Merestrukturisasi tabel produk menjadi `table-borderless` minimalis (`PRODUCT`, `PRICE`, `QUANTITY`, `SUBTOTAL`).<br>3. Menyederhanakan form pembayaran: input nama pembeli, dropdown `Select payment`, subtotal, total, tombol *Proses Checkout*, dan tombol *Kembali ke Kasir*.<br>4. Membersihkan fungsi JS kalkulator uang tunai dan menyederhanakan *render loop*. |

---

## 3. Rincian Perubahan Kode (*Before vs After*)

### A. Header Halaman

* **Sebelumnya (Penuh Ornamen Dekoratif):**
  ```blade
  <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
      <div class="d-flex align-items-center">
          <a href="{{ route('pos.index') }}" class="btn btn-outline-primary btn-sm px-3 py-2 d-inline-flex align-items-center shadow-sm me-3" style="border-radius: 8px; font-weight: 500;">
              <i class="mdi mdi-arrow-left me-1"></i> Kembali ke Kasir
          </a>
          <div>
              <h3 class="page-title d-flex align-items-center m-0">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">...</span>
                  Checkout Transaksi Kasir
              </h3>
              <small class="text-muted">Periksa rincian belanja dan selesaikan transaksi offline pelanggan.</small>
          </div>
      </div>
      <div class="d-flex align-items-center gap-2">
          <span class="badge bg-white text-dark border">...</span>
          <span class="badge bg-primary text-white">...</span>
      </div>
  </div>
  ```

* **Sesudahnya (Simpel & Minimalis):**
  ```blade
  <div class="d-flex align-items-center mb-3">
      <a href="{{ route('pos.index') }}" class="text-decoration-none text-muted" style="font-size: 14px;">
          <i class="mdi mdi-arrow-left me-1"></i> Kembali
      </a>
  </div>
  <h3 class="page-title font-weight-bold text-dark mb-4" style="font-size: 22px;">Checkout</h3>
  ```

---

### B. Kolom Kiri: Tabel Produk Pesanan

* **Sebelumnya (Header & Footer Card dengan Teks Tambahan):**
  * Terdapat card header *"Daftar Pesanan Belanja"* dan counter *"0 Jenis Produk"*.
  * Terdapat card footer dengan teks bantuan dan tombol *"Tambah Produk Lain"*.

* **Sesudahnya (Tabel Borderless Bersih):**
  ```blade
  <div class="col-lg-8 mb-4 mb-lg-0">
      <div class="card border-0 shadow-sm" style="border-radius: 8px;">
          <div class="card-body p-0">
              <div class="table-responsive">
                  <table class="table table-borderless align-middle mb-0">
                      <thead>
                          <tr class="text-muted border-bottom">
                              <th style="font-size: 12px; font-weight: 500;" class="ps-4">PRODUCT</th>
                              <th style="font-size: 12px; font-weight: 500;">PRICE</th>
                              <th style="font-size: 12px; font-weight: 500;" class="text-center">QUANTITY</th>
                              <th style="font-size: 12px; font-weight: 500;" class="pe-4 text-end">SUBTOTAL</th>
                          </tr>
                      </thead>
                      <tbody id="checkout-items">
                      </tbody>
                  </table>
              </div>
          </div>
      </div>
  </div>
  ```

---

### C. Kolom Kanan: Panel Pembayaran Kasir

* **Sebelumnya (Kalkulator Uang Tunai, Chip Pecahan, & Tombol Hapus Keranjang):**
  * Memuat radio toggle tebal `Tunai (Cash)` vs `Transfer / QRIS`.
  * Memuat input uang tunai diterima dan tombol chip `Uang Pas`, `50.000`, `100.000`, `200.000`.
  * Memuat kotak dinamis kembalian uang.
  * Memuat tombol outline merah `Kosongkan Keranjang`.

* **Sesudahnya (Form Pembayaran Ringkas & Minimalis):**
  ```blade
  <div class="col-lg-4">
      <div class="card border-0 shadow-sm" style="border-radius: 8px;">
          <div class="card-body p-4">
              <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Payment</h5>
              <div class="form-group mb-3">
                  <label class="text-muted mb-2" style="font-size: 12px;">Customer Name (Optional)</label>
                  <input type="text" class="form-control border-0 shadow-sm" id="customer-name" placeholder="Walk-in Customer" style="border-radius: 8px; font-size: 13px;">
              </div>
              <div class="form-group mb-4">
                  <label class="text-muted mb-2" style="font-size: 12px;">Select payment</label>
                  <select class="form-select border-0 shadow-sm" id="payment-method" style="border-radius: 8px; font-size: 13px;">
                      <option value="Cash">Cash</option>
                      <option value="Transfer">Transfer</option>
                  </select>
              </div>

              <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Cart Total</h5>
              <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                  <span class="text-muted" style="font-size: 14px;">Subtotal:</span>
                  <strong id="checkout-subtotal">Rp. 0</strong>
              </div>
              <div class="d-flex justify-content-between mb-4">
                  <span class="text-muted" style="font-size: 14px;">Total:</span>
                  <strong id="checkout-total" class="text-dark font-weight-bold" style="font-size: 15px;">Rp. 0</strong>
              </div>
              <button class="btn btn-gradient-primary w-100 mb-3" onclick="processCheckout()" style="border-radius: 8px; font-weight: 500;">Proses Checkout</button>
              <a href="{{ route('pos.index') }}" class="btn btn-outline-secondary w-100" style="border-radius: 8px; font-weight: 500;">Kembali ke Kasir</a>
          </div>
      </div>
  </div>
  ```

---

### D. Skrip JavaScript

* **Penyederhanaan Logika:**
  * Menghapus fungsi `setCashAmount()`, `calculateChange()`, `onPaymentMethodChange()`, dan `confirmClearCart()`.
  * Memperbaiki dan merampingkan fungsi `renderCheckout()`:
    * Merender thumbnail produk (70×70 px) dengan nama produk di sebelahnya.
    * Menampilkan harga satuan dan counter pill kuantitas (`[-] [ Qty ] [+]`).
    * Menampilkan subtotal dan ikon hapus item (`mdi-close-circle-outline`).
  * Mempertahankan pengiriman data via `fetch` ke endpoint `route('pos.checkout')`.
  * Menjaga dialog sukses SweetAlert dengan shortcut cetak invoice dan tombol transaksi baru.

---

## 4. Verifikasi dan Pengujian

1. **Pembersihan dan Kompilasi Template Blade**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Sukses tanpa kesalahan sintaks.

2. **Automated Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil:*
   ```
   Tests:    147 passed (741 assertions)
   Duration: 23.94s
   ```
   Seluruh 147 unit & feature tests tetap 100% *Green* dan berfungsi sesuai spesifikasi.

---

## 5. Kesimpulan

Halaman checkout kasir offline (`/admin/pos/checkout`) kini telah berhasil dikembalikan ke desain yang **simpel, bersih, dan minimalis** sesuai dengan antarmuka awal, tanpa mengorbankan isolasi arsitektur halaman mandiri maupun fungsionalitas transaksi kasir.

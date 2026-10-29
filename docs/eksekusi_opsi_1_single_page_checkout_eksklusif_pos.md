# Dokumentasi: Eksekusi Opsi 1 — Redesain Single-Page Checkout Eksklusif pada Kasir Offline (POS)

## 1. Latar Belakang & Identifikasi Masalah

Pada halaman **Kasir Offline** (`/admin/pos`):
1. **Kebocoran Tampilan Katalog (*Catalog Bleed-Through*)**:
   * Sebelumnya, elemen `#checkout-view` diposisikan dengan CSS `position: absolute; top: 0; left: 0; width: 100%; min-height: 100vh; z-index: 1000;`.
   * Saat kasir mengklik tombol **"Checkout"**, fungsi `showCheckout()` hanya mengubah `#checkout-view` menjadi `display: block` **tanpa menyembunyikan kontainer katalog produk utama di belakangnya**.
   * Akibatnya, kontainer grid produk kasir tetap memakan ruang tinggi (*height flow*) di halaman. Ketika halaman di-scroll ke bawah melewati card checkout, produk-produk katalog di belakangnya tetap terlihat menonjol dan bocor di bawah form checkout (sebagaimana terlihat pada tangkapan layar antarmuka).
2. **Duplikasi Padding dan Kontainer Bersarang (*Nested Content-Wrapper*)**:
   * Layout induk `Layout/layout_pAdmin.blade.php` sudah menyediakan pembungkus `<div class="content-wrapper">`.
   * Di dalam `pos.blade.php`, `#checkout-view` sebelumnya juga diberikan kelas `class="content-wrapper"` dengan inline style `padding: 2.75rem 2.25rem;` demi menutupi layar, yang menyebabkan inkonsistensi struktur DOM dan letak footer admin.

---

## 2. Rincian Solusi (Eksekusi Opsi 1)

Solusi **Opsi 1** mempertahankan arsitektur *Single-Page POS* berkecepatan tinggi tanpa *page reload*, namun memperbaiki isolasi tampilan secara eksklusif:

1. **Pembungkusan Kontainer Katalog (`#pos-catalog-view`)**:
   * Seluruh elemen katalog kasir (header "Kasir Offline", tombol "Riwayat Penjualan", toolbar pencarian & filter, grid kartu produk, dan navigasi paginasi) dibungkus secara rapi dalam satu kontainer: `<div id="pos-catalog-view">`.
2. **Normalisasi Alur Dokumen (`#checkout-view`)**:
   * Menghapus seluruh properti `position: absolute; top:0; left:0; width:100%; min-height:100vh; z-index: 1000;` serta kelas ganda `content-wrapper`.
   * `#checkout-view` kini menggunakan alur dokumen normal (`position: static`), duduk rapi di dalam kontainer utama halaman, dan membiarkan footer admin berada di posisi bawah yang wajar.
   * Menambahkan header navigasi checkout yang bersih dengan tombol *"Kembali ke Kasir"* berikon panah (`mdi-arrow-left`) dan judul *"Checkout Kasir"*.
3. **Penyembunyian Bersyarat Eksklusif (*Exclusive View Toggle*) pada JavaScript**:
   * Saat `showCheckout()` dipanggil:
     * `#pos-catalog-view` diubah ke `display: none` (tinggi menjadi 0px, katalog lenyap seutuhnya).
     * `#checkout-view` diubah ke `display: block`.
     * Menjalankan `window.scrollTo({ top: 0, behavior: 'instant' })` agar kasir langsung berada di bagian atas form checkout.
   * Saat `hideCheckout()` dipanggil:
     * `#checkout-view` diubah ke `display: none`.
     * `#pos-catalog-view` dikembalikan ke `display: block`.
     * Menjalankan `window.scrollTo({ top: 0, behavior: 'instant' })` dan memutakhirkan state keranjang belanja (`updateCartUI()`).

---

## 3. Berkas yang Dimodifikasi

| Berkas | Tipe | Rincian Perubahan |
| :--- | :---: | :--- |
| `resources/views/Admin/kelola_penjualan/pos.blade.php` | Blade View & JS | 1. Membungkus konten katalog dalam `#pos-catalog-view`.<br>2. Menormalkan `#checkout-view` ke normal flow tanpa `position: absolute`.<br>3. Menambahkan tombol kembali dan header representatif pada checkout view.<br>4. Memperbarui `showCheckout()` dan `hideCheckout()` untuk menyembunyikan katalog secara eksklusif dan auto-scroll ke atas. |

---

## 4. Perubahan Kode (*Before vs After*)

### A. Pembungkus Katalog & Checkout View pada `pos.blade.php`

* **Sebelumnya (Katalog Tidak Dibungkus & Checkout Menggunakan Absolute Hack):**
  ```blade
  <div class="content-wrapper" style="position: relative; overflow-x: hidden;">
      <div class="d-flex justify-content-between mb-4">
          <h3 class="page-title d-inline-flex align-items-center m-0">...</h3>
          ...
      </div>
      <div class="row">
          <!-- Card Tabel Katalog & Grid Produk -->
      </div>
  </div>

  <!-- Checkout View (Initially Hidden) -->
  <div id="checkout-view" class="content-wrapper" style="display: none; position: absolute; top:0; left:0; width:100%; min-height:100vh; background: #F8F9FC; z-index: 1000; padding: 2.75rem 2.25rem;">
      <div class="d-flex align-items-center mb-4">
          <a href="#" class="text-decoration-none text-muted me-2" onclick="hideCheckout()"><i class="mdi mdi-arrow-left me-1"></i>Kembali</a>
      </div>
      <h3 class="page-title font-weight-bold text-dark mb-4" style="font-size: 22px;">Checkout</h3>
      <div class="row">
          ...
      </div>
  </div>
  ```

* **Sesudahnya (Struktur DOM Bersih & Terisolasi Sempurna):**
  ```blade
  <div class="content-wrapper" style="position: relative; overflow-x: hidden;">
      <!-- POS Catalog View (Default Visible) -->
      <div id="pos-catalog-view">
          <div class="d-flex justify-content-between mb-4">
              <h3 class="page-title d-inline-flex align-items-center m-0">
                  <span class="page-title-icon bg-gradient-primary text-white me-2">
                      <i class="mdi mdi-cash-register"></i>
                  </span>
                  <span class="text-nowrap">Kasir Offline</span>
              </h3>
              <div class="d-flex align-items-center">
                  <a href="{{ route('all-sales.index') }}" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; font-weight: 500;" title="Lihat riwayat seluruh transaksi penjualan">
                      <i class="mdi mdi-history me-1" style="font-size: 16px;"></i> Riwayat Penjualan
                  </a>
              </div>
          </div>

          <div class="row">
              <!-- Toolbar Filter, Search, Grid Produk, dan Paginasi -->
          </div>
      </div> <!-- /#pos-catalog-view -->

      <!-- Right Offcanvas Cart -->
      <div id="cart-sidebar" class="bg-white shadow-lg" ...>...</div>

      <!-- Checkout View (Initially Hidden, Replaces Catalog View Exclusively) -->
      <div id="checkout-view" style="display: none;">
          <div class="d-flex align-items-center justify-content-between mb-4">
              <div class="d-flex align-items-center">
                  <button type="button" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center shadow-sm me-3" onclick="hideCheckout()" style="border-radius: 6px; font-weight: 500;" title="Kembali ke katalog kasir">
                      <i class="mdi mdi-arrow-left me-1" style="font-size: 16px;"></i> Kembali ke Kasir
                  </button>
                  <h3 class="page-title d-inline-flex align-items-center m-0">
                      <span class="page-title-icon bg-gradient-primary text-white me-2">
                          <i class="mdi mdi-cart-check"></i>
                      </span>
                      <span class="text-nowrap font-weight-bold">Checkout Kasir</span>
                  </h3>
              </div>
          </div>
          
          <div class="row">
              <!-- Kolom Kiri: Tabel Produk Keranjang (col-lg-8) -->
              <!-- Kolom Kanan: Card Ringkasan Pembayaran & Tombol Proses (col-lg-4) -->
          </div>
      </div> <!-- /#checkout-view -->
  </div> <!-- /.content-wrapper -->
  ```

---

### B. Pembaruan Logika JavaScript pada `pos.blade.php`

* **Sebelumnya:**
  ```javascript
  function showCheckout() {
      if(Object.keys(cart).length === 0) {
          ...
          return;
      }
      document.getElementById('cart-sidebar').classList.remove('open');
      document.getElementById('checkout-view').style.display = 'block';
      renderCheckout();
  }

  function hideCheckout() {
      document.getElementById('checkout-view').style.display = 'none';
      updateCartUI();
  }
  ```

* **Sesudahnya:**
  ```javascript
  function showCheckout() {
      if(Object.keys(cart).length === 0) {
          Swal.fire({
              icon: 'warning',
              title: 'Keranjang Kosong',
              text: 'Silakan pilih produk terlebih dahulu sebelum melakukan checkout.',
              confirmButtonColor: '#3085d6'
          });
          return;
      }
      document.getElementById('cart-sidebar').classList.remove('open');
      
      const catalogEl = document.getElementById('pos-catalog-view');
      if (catalogEl) catalogEl.style.display = 'none';

      const checkoutEl = document.getElementById('checkout-view');
      if (checkoutEl) checkoutEl.style.display = 'block';

      window.scrollTo({ top: 0, behavior: 'instant' });
      renderCheckout();
  }

  function hideCheckout() {
      const checkoutEl = document.getElementById('checkout-view');
      if (checkoutEl) checkoutEl.style.display = 'none';

      const catalogEl = document.getElementById('pos-catalog-view');
      if (catalogEl) catalogEl.style.display = 'block';

      window.scrollTo({ top: 0, behavior: 'instant' });
      updateCartUI(); // Pastikan sidebar merefleksikan perubahan
  }
  ```

---

## 5. Hasil Verifikasi & Uji Sistem

1. **Blade Views Compilation**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Sukses (`Compiled views cleared successfully.` & `Blade templates cached successfully.`).

2. **Full Regression Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil:* **147 passed (741 assertions)** (100% *Green*).
   * Seluruh pengujian otorisasi kasir, penambahan stok cepat POS, pengurangan stok saat transaksi offline, dan prioritas sorting tetap lulus tanpa regresi.

---

## 6. Kesimpulan

Dengan menerapkan **Opsi 1**:
* **Tidak Ada Kebocoran Visual**: Ketika kasir menekan tombol *Checkout*, katalog produk tersembunyi 100% dan tidak ada elemen katalog yang terlihat atau bocor di bawah form checkout.
* **Performa Maksimal**: Transaksi tetap instan tanpa perlu me-reload halaman dari server.
* **Navigasi Mulus**: Kasir dapat beralih antara katalog produk dan form checkout dengan satu klik tombol *"Kembali ke Kasir"* tanpa kehilangan item keranjang belanja.

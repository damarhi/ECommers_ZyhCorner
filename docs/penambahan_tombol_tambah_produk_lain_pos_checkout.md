# Dokumentasi: Penambahan Tombol "Tambah Produk Lain" pada Halaman Checkout Kasir Offline (POS)

## 1. Latar Belakang & Kebutuhan Pengguna

Pada alur transaksi kasir offline di halaman mandiri checkout (`/admin/pos/checkout`), kasir terkadang perlu kembali ke katalog produk kasir (`route('pos.index')`) untuk menambahkan item pesanan tambahan atas permintaan pelanggan sebelum transaksi diselesaikan.

Sesuai instruksi pengguna dan pilihan **Opsi 1**:
* Tombol **"Tambah Produk Lain"** ditempatkan secara ergonomis pada bagian bawah tabel pesanan belanja (card footer kolom kiri).
* Tata letak tetap mematuhi prinsip **simpel dan minimalis** tanpa membebani antarmuka dengan banner atau teks berlebih.
* State keranjang belanja tetap aman dan persisten melalui `localStorage` (`zyh_pos_cart`), sehingga saat kasir kembali ke katalog dan memilih item baru, item lama tidak hilang.

---

## 2. Berkas yang Dimodifikasi

| Berkas | Lokasi | Tipe Perubahan |
| :--- | :--- | :--- |
| `pos_checkout.blade.php` | `resources/views/Admin/kelola_penjualan/` | Menambahkan card footer berisi tombol "Tambah Produk Lain" di bawah tabel pesanan dan mengatur visibilitasnya di JavaScript sesuai state keranjang. |

---

## 3. Rincian Perubahan Kode (*Before vs After*)

### A. Penambahan Card Footer pada `pos_checkout.blade.php`

* **Sebelumnya:**
  ```blade
              <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                  <div class="card-body p-0">
                      <div class="table-responsive">
                          <table class="table table-borderless align-middle mb-0">
                              <thead>...</thead>
                              <tbody id="checkout-items"></tbody>
                          </table>
                      </div>
                  </div>
              </div>
  ```

* **Sesudahnya (Ditambahkan Card Footer Bersih):**
  ```blade
              <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                  <div class="card-body p-0">
                      <div class="table-responsive">
                          <table class="table table-borderless align-middle mb-0">
                              <thead>...</thead>
                              <tbody id="checkout-items"></tbody>
                          </table>
                      </div>
                  </div>
                  <div id="checkout-table-footer" class="card-footer bg-white border-top p-3 px-4 d-flex justify-content-between align-items-center">
                      <span class="text-muted" style="font-size: 13px;">Ingin menambahkan barang belanjaan lain?</span>
                      <a href="{{ route('pos.index') }}" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500;">
                          <i class="mdi mdi-plus me-1"></i> Tambah Produk Lain
                      </a>
                  </div>
              </div>
  ```

---

### B. Pengaturan Visibilitas Dinamis pada Skrip JavaScript

Pada fungsi `renderCheckout()`:
1. Ketika keranjang kosong (`distinctCount === 0`), card footer disembunyikan otomatis (`footerEl.style.display = 'none'`) karena pesan keranjang kosong sudah memiliki tombol bawaan *"Pilih Produk di Kasir"*.
2. Ketika keranjang memiliki produk (`distinctCount > 0`), card footer ditampilkan (`footerEl.style.display = 'flex'`).

```javascript
    function renderCheckout() {
        let html = '';
        let totalPrice = 0;
        let distinctCount = Object.keys(cart).length;

        const footerEl = document.getElementById('checkout-table-footer');

        if (distinctCount === 0) {
            if (footerEl) footerEl.style.display = 'none';
            // Render friendly empty state...
            return;
        }

        if (footerEl) footerEl.style.display = 'flex';
        // Render item rows...
    }
```

---

## 4. Verifikasi dan Pengujian

1. **Kompilasi View**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Sukses dibersihkan dan dikompilasi ulang tanpa error.

2. **Automated Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil:*
   ```
   Tests:    147 passed (741 assertions)
   Duration: 24.04s
   ```
   Seluruh 147 feature dan unit test tetap 100% *Green*.

---

## 5. Kesimpulan

Tombol **"Tambah Produk Lain"** telah berhasil diterapkan dengan rapi di bawah tabel produk checkout kasir offline sesuai dengan **Opsi 1**. Desain tetap simpel, fungsional, dan memberikan kemudahan navigasi bagi kasir.

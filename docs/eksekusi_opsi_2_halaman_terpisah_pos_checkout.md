# Dokumentasi: Eksekusi Opsi 2 — Pemisahan Halaman Mandiri Checkout Kasir Offline (POS)

## 1. Latar Belakang & Analisis Arsitektur

Pada implementasi awal kasir offline (POS), antarmuka kasir dan form pembayaran checkout berada dalam satu berkas Blade yang sama (`pos.blade.php`). Ketika kasir menekan tombol **"Checkout"** dari sidebar keranjang belanja, form checkout ditampilkan sebagai overlay di dalam halaman yang sama.

Pendekatan *single-page overlay* memiliki beberapa kelemahan struktural:
1. **Risiko Kebocoran DOM & Scroll Leakage**: Kontainer katalog produk kasir (dengan puluhan kartu produk, gambar, filter, dan paginasi) tetap berada di memori DOM halaman. Jika terjadi kesalahan perhitungan layout CSS atau perilaku scroll di perangkat tablet/layar kasir, elemen katalog dapat bocor di bawah atau di samping formulir checkout.
2. **Kerapuhan Maintenance Satu Berkas Gemuk (*Fat Blade File*)**: Menyatukan katalog kasir, modal tambah stok, offcanvas keranjang, tabel rincian transaksi, kalkulator uang tunai, dan logika pembayaran dalam satu berkas `pos.blade.php` membuat pemeliharaan kode menjadi kompleks dan rentan kesalahan penutupan tag HTML (`<div>`).
3. **Standar Navigasi Web Administratif**: Alur checkout yang memiliki URL mandiri (`/admin/pos/checkout`) memberikan kejelasan alur kerja bagi kasir, memungkinkan bookmarking, serta memisahkan tanggung jawab visual (*separation of visual concerns*) secara tegas.

Berdasarkan pertimbangan tersebut, dieksekusi **Opsi 2**: memisahkan seluruh antarmuka dan logika checkout kasir ke rute URL mandiri (`GET /admin/pos/checkout`) dengan file view tersendiri (`pos_checkout.blade.php`), sementara keranjang belanja dibagikan secara mulus via `localStorage` browser kasir (`zyh_pos_cart`).

---

## 2. Rincian Implementasi Opsi 2

### A. Rute Mandiri POS Checkout
* Menambahkan rute GET baru di `routes/web.php` di bawah grup middleware `auth`:
  ```php
  Route::get('admin/pos/checkout', [PosController::class, 'checkoutPage'])->name('pos.checkout.page');
  ```
* Rute POST transaksi kasir tetap menggunakan endpoint yang telah teruji:
  ```php
  Route::post('admin/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
  ```

### B. Controller Action di `PosController`
* Menambahkan method `checkoutPage()` di `app/Http/Controllers/Admin/PosController.php`:
  ```php
  public function checkoutPage()
  {
      return view('Admin.kelola_penjualan.pos_checkout');
  }
  ```

### C. Pembuatan View Mandiri `pos_checkout.blade.php`
Dibuat view baru `resources/views/Admin/kelola_penjualan/pos_checkout.blade.php` yang mewarisi layout `Layout/layout_pAdmin.blade.php`:
1. **Header Halaman**:
   * Tombol navigasi kembali ke katalog kasir: `<a href="{{ route('pos.index') }}" class="btn btn-outline-primary btn-sm">← Kembali ke Kasir</a>`.
   * Judul representatif *"Checkout Transaksi Kasir"* dengan ikon `mdi-cart-check`.
   * Badge tanggal hari ini (`d M Y`) dan badge jumlah item di keranjang.
2. **Tabel Rincian Belanja Proporsional (100% Fit Tanpa Horizontal Bleed)**:
   * Kolom tabel terstruktur rapi dengan persentase lebar pasti:
     * **Produk (44%)**: Gambar thumbnail, nama produk, dan indikator sisa stok.
     * **Harga (18%)**: Harga satuan terformat Rupiah.
     * **Jumlah (18%)**: Kontrol kuantitas berbentuk pill (`- Qty +`) dengan batas maksimum sesuai stok produk.
     * **Subtotal (20%)**: Nilai perkalian harga & kuantitas beserta tombol ikon hapus item (`mdi-trash-can-outline`).
   * Menghapus elemen non-esensial kasir offline seperti label *"Shipping: Free"* dan tombol *"Update Cart"* (karena pembaruan kuantitas kini terjadi secara instan dan reaktif).
3. **State Keranjang Kosong Ramah Kasir (*Friendly Empty State*)**:
   * Jika keranjang kosong, tabel menampilkan ilustrasi `mdi-cart-off`, pesan informatif, dan tombol langsung *"Pilih Produk di Kasir"*.
4. **Panel Pembayaran Cerdas (*Smart Payment Panel*)**:
   * Input nama pelanggan (*Customer Name*) dengan default *"Walk-in Customer"*.
   * Toggle metode pembayaran berbasis radio button: **Tunai (Cash)** dan **Transfer / QRIS**.
   * Kotak ringkasan total tagihan belanja yang kontras dan mudah dibaca.
   * **Smart Cash & Change Calculator** (aktif otomatis pada opsi Tunai):
     * Input nominal uang tunai diterima (*Cash Received*).
     * Tombol cepat pecahan uang (*Quick Cash Chips*): `Uang Pas`, `50.000`, `100.000`, `200.000`.
     * Kotak indikator kembalian dinamis:
       * Warna hijau jika uang mencukupi/lebih (`Kembalian: Rp. X`).
       * Warna merah jika uang kurang (`Kurang Rp. X`).
5. **Konfirmasi & Dialog Pasca-Checkout**:
   * SweetAlert konfirmasi saat ingin mengosongkan keranjang belanja.
   * Validasi client-side jika kasir memasukkan uang tunai yang kurang dari total belanja.
   * Pop-up SweetAlert sukses yang memberikan dua tombol aksi:
     * **Transaksi Baru**: Mengarahkan kasir kembali ke katalog produk (`route('pos.index')`).
     * **Cetak Invoice**: Membuka tab baru untuk mencetak struk transaksi offline (`admin/orders/{id}/invoice`).

### D. Pembersihan dan Perapian `pos.blade.php`
1. **Penghapusan Seluruh Elemen `#checkout-view`**:
   * Form checkout inline, tabel checkout usang, dan input pembayaran di dalam `pos.blade.php` dihapus seutuhnya.
2. **Pembersihan Kontainer Bersarang**:
   * Menghapus pembungkus `#pos-catalog-view` dan merapikan penutupan tag `<div>` kartu katalog sehingga struktur dokumen kembali bersih dan valid.
3. **Penyederhanaan Logika JavaScript**:
   * Mengubah fungsi `showCheckout()` di `pos.blade.php` menjadi pengalihan halaman:
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
         window.location.href = "{{ route('pos.checkout.page') }}";
     }
     ```
   * Menghapus fungsi yang tidak lagi diperlukan dari `pos.blade.php`: `hideCheckout()`, `renderCheckout()`, dan `processCheckout()`.

---

## 3. Matriks Berkas yang Dimodifikasi & Dibuat

| Berkas | Status | Rincian Perubahan |
| :--- | :---: | :--- |
| `routes/web.php` | Dimodifikasi | Menambahkan rute GET `admin/pos/checkout` (`pos.checkout.page`). |
| `app/Http/Controllers/Admin/PosController.php` | Dimodifikasi | Menambahkan method `checkoutPage()` untuk me-render view mandiri. |
| `resources/views/Admin/kelola_penjualan/pos_checkout.blade.php` | **Dibuat Baru** | Halaman mandiri checkout kasir lengkap dengan tabel proporsional, kalkulator tunai cerdas, quick chips, dan shortcut cetak invoice. |
| `resources/views/Admin/kelola_penjualan/pos.blade.php` | Dimodifikasi | Menghapus blok HTML `#checkout-view`, menyederhanakan penutupan kontainer DOM, dan mengarahkan `showCheckout()` ke URL mandiri. |

---

## 4. Perubahan Kode (*Before vs After*)

### A. Penambahan Rute Mandiri (`routes/web.php`)

```diff
  Route::get('admin/pos', [PosController::class, 'index'])->name('pos.index');
+ Route::get('admin/pos/checkout', [PosController::class, 'checkoutPage'])->name('pos.checkout.page');
  Route::post('admin/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
```

---

### B. Penambahan Method Controller (`app/Http/Controllers/Admin/PosController.php`)

```diff
      return view('Admin.kelola_penjualan.pos', compact('products', 'kategoris'));
  }
+
+ public function checkoutPage()
+ {
+     return view('Admin.kelola_penjualan.pos_checkout');
+ }
+
  public function checkout(Request $request)
  {
```

---

### C. Perapian Navigasi Checkout di Sidebar Kasir (`pos.blade.php`)

* **Sebelumnya (Mencoba menampilkan overlay `#checkout-view` di halaman yang sama):**
  ```javascript
  function showCheckout() {
      if(Object.keys(cart).length === 0) { ... }
      document.getElementById('cart-sidebar').classList.remove('open');
      document.getElementById('pos-catalog-view').style.display = 'none';
      document.getElementById('checkout-view').style.display = 'block';
      window.scrollTo({ top: 0, behavior: 'instant' });
      renderCheckout();
  }
  ```

* **Sesudahnya (Navigasi URL Bersih & Membaca Keranjang di Halaman Checkout):**
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
      window.location.href = "{{ route('pos.checkout.page') }}";
  }
  ```

---

## 5. Verifikasi & Pengujian

### A. Kompilasi Template Blade
Dilakukan pembersihan dan kompilasi ulang seluruh cache Blade untuk memastikan tidak ada kesalahan sintaks:
```bash
php artisan view:clear && php artisan view:cache
```
*Hasil:*
```
INFO  Compiled views cleared successfully.
INFO  Blade templates cached successfully.
```

### B. Pengujian Regresi Otomatis (*Full Automated Test Suite*)
Menjalankan seluruh rangkaian tes pada aplikasi untuk memastikan fungsionalitas stok, alur POS, dan modul lainnya tetap berjalan sempurna:
```bash
php artisan test
```
*Hasil:*
```
Tests:    147 passed (741 assertions)
Duration: 23.80s
```
Semua 147 test case (termasuk `ProductStockReductionTest`, `CassierAccessControlTest`, `AdminOrderTest`, dan seluruh modul admin maupun pelanggan) lulus 100% tanpa regresi.

---

## 6. Kesimpulan

Dengan penerapan **Opsi 2**:
1. Masalah kebocoran katalog produk (*DOM bleed-through*) teratasi 100% karena halaman checkout kini berada pada rute dan berkas Blade terpisah (`/admin/pos/checkout`).
2. Tampilan checkout menjadi jauh lebih profesional, bersih, dan sesuai standar aplikasi POS modern: tabel proporsional tanpa scroll horizontal, kalkulator uang tunai dinamis, tombol cepat nominal pecahan, serta integrasi langsung ke pencetakan invoice transaksi offline.
3. Arsitektur kode menjadi lebih modular, mudah dipelihara, dan seluruh 147 automated tests aplikasi tetap berstatus *Green*.

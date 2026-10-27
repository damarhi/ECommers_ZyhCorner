# Dokumentasi Perbaikan Validasi dan Manajemen Stok Otomatis

## 1. Ringkasan Perubahan
Dokumentasi ini mencatat penyelesaian perbaikan logika inventaris dan pencegahan pemesanan melebihi stok (*overselling*) pada aplikasi e-commerce Zyh Corner. Sebelumnya terdapat celah di mana:
1. Accessor stok mengembalikan nilai statis `99` jika status produk bertuliskan legacy `'Tersedia'`, sehingga mengizinkan pemesanan hingga 99 unit.
2. Pemotongan stok pada checkout online (Transfer/Midtrans) ditunda hingga status pembayaran menjadi `Paid`. Hal ini menyebabkan stok di database tidak langsung berkurang ketika pesanan dibuat dengan status `menunggu pembayaran`, sehingga produk dapat diborong oleh pelanggan lain (*race condition*).
3. Tidak adanya mekanisme pemulihan stok (*stock restoration*) saat pesanan dibatalkan pengguna, kedaluwarsa setelah 24 jam, atau pembayaran Midtrans gagal/kedaluwarsa.
4. Input kuantitas di frontend (halaman detail produk, modal quick view, keranjang belanja, dan drawer offcanvas) tidak memiliki validasi batas atas (`max = stok`) yang ketat.

---

## 2. File-File yang Diubah

### A. Backend & Model
1. **`app/Models/Produk.php`**
   - Merasionalisasi accessor `getStokAttribute()`: jika nilai bernilai legacy `'Tersedia'`, default dikembalikan menjadi `10` bukan `99`.
   - Menambahkan method `addStock(int $qty = 1)` untuk mengembalikan kuantitas stok produk ke database.
   - Menambahkan static method `restoreStockForOrder($pesanan)` yang membaca item dalam pesanan dan mengembalikan stok masing-masing produk secara otomatis.

2. **`app/Http/Controllers/Pelanggan/CartCheckoutController.php`**
   - Pada `processCheckout(Request $request)`: mengeksekusi `Produk::reduceStockForOrder($pesanan)` langsung saat pesanan berhasil dibuat (baik untuk metode COD maupun Online Transfer).
   - Pada `cancelExpiredOrders()`: memanggil `Produk::restoreStockForOrder($order)` saat pesanan kedaluwarsa (> 24 jam) dibatalkan.

3. **`app/Http/Controllers/Pelanggan/CustomerOrderController.php`**
   - Pada `cancelOrder($id)`: memanggil `Produk::restoreStockForOrder($order)` saat pesanan berstatus menunggu dibatalkan oleh pelanggan.
   - Pada `cancelExpiredOrders()`: memanggil `Produk::restoreStockForOrder($order)` saat pesanan kadaluarsa dibatalkan otomatis.

4. **`app/Http/Controllers/Pelanggan/PembayaranController.php`**
   - Pada `midtransCallback(Request $request)`:
     - Menghapus pemanggilan ganda `reduceStockForOrder` saat transaksi berstatus `capture` / `settlement` karena stok sudah dipotong seketika saat pesanan dibuat di checkout.
     - Menambahkan pemanggilan `Produk::restoreStockForOrder($order)` ketika transaksi berstatus `deny`, `expire`, atau `cancel`.
   - Pada `verifyAndSyncOrderPayment($order)`:
     - Menghapus pemotongan stok ganda saat status `Paid`.
     - Menambahkan pemulihan stok saat status pembayaran `deny`, `expire`, atau `cancel`.

### B. Frontend & Views
1. **`resources/views/Layout/layout_pelanggan.blade.php`**
   - Menambahkan `productsStockMap: @json(\App\Models\Produk::all()->pluck('stok', 'id_produk'))` pada `window.layoutConfig`.
   - Menyelaraskan rendering modal preview `#productModal` agar tidak dirender ganda pada halaman detail produk tunggal (`product.detail`).

2. **`resources/views/Pelanggan/detail.blade.php`**
   - Menambahkan atribut `max="{{ $product->stok }}"` pada elemen input `#qtyInput`.
   - Menambahkan konfigurasi `productStock: {{ (int) $product->stok }}` pada objek `window.detailConfig`.

3. **`resources/js/pelanggan_pages.js`**
   - **`window.changeQty(delta)`**: Mengecek nilai maksimum stok melalui atribut `max` atau `window.detailConfig.productStock`. Jika kuantitas melebihi batas, input dikunci pada kuantitas maksimum dan memunculkan notifikasi peringatan SweetAlert2.
   - **`window.openProductModal(btn)` & `window.updateModalQty(delta)`**: Mengikat data stok modal (`window.currentModalProductStock`) dan membatasi penambahan kuantitas produk showcase agar tidak melampaui stok yang tersedia.
   - **`window.addToCart(product, openDrawer)`**: Memvalidasi akumulasi kuantitas produk yang sudah ada di keranjang ditambah kuantitas yang baru diminta. Jika total melebihi `availableStock`, proses ditolak dan SweetAlert2 menampilkan sisa stok yang tersedia.
   - **`window.updateQty(id, delta)`**: Mencegah penambahan kuantitas (`+`) pada keranjang belanja dan drawer jika kuantitas baru akan melebihi stok yang tercatat pada `productsStockMap`.

---

## 3. Rincian Pengujian dan Verifikasi

### Pengujian Otomatis (PHPUnit Feature Tests)
Dibuat test suite baru `tests/Feature/OrderStockManagementTest.php` yang memverifikasi 6 skenario kritis:
1. `test_checkout_fails_if_quantity_exceeds_available_stock`: Memastikan pesanan langsung ditolak (HTTP 400) bila pelanggan mencoba memesan kuantitas melebihi stok database.
2. `test_online_transfer_checkout_reduces_stock_immediately`: Memastikan pesanan metode transfer langsung memotong stok produk saat checkout dibuat, meskipun pembayaran berstatus `Pending`.
3. `test_cancelling_order_restores_product_stock`: Memastikan pembatalan pesanan oleh pelanggan langsung memulihkan stok produk kembali ke inventaris.
4. `test_expired_orders_restore_stock_automatically`: Memastikan pesanan yang melewati batas waktu 24 jam otomatis berstatus `batal` dan stok produk dipulihkan.
5. `test_midtrans_payment_success_does_not_double_deduct_stock`: Memastikan webhook pembayaran Midtrans yang sukses tidak memotong stok untuk kedua kalinya.
6. `test_midtrans_cancellation_or_expiry_restores_stock`: Memastikan webhook pembatalan atau kedaluwarsa dari Midtrans mengembalikan stok ke produk.

### Hasil Eksekusi Test Suite Keseluruhan:
```bash
php artisan test
```
**Hasil:**
- **90 Tests PASSED**
- **370 Assertions PASSED**
- **0 Failures, 0 Errors**
- Aset frontend dikompilasi ulang menggunakan `npm run build` tanpa kesalahan (*clean build*).

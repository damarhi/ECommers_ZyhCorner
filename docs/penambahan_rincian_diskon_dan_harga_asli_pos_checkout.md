# Dokumentasi: Penambahan Rincian Diskon, Total Harga Asli, dan Total Harga Setelah Diskon pada Card Payment Checkout Kasir Offline (POS)

## 1. Latar Belakang & Kebutuhan

Pada halaman checkout kasir offline (`/admin/pos/checkout`), ringkasan pembayaran sebelumnya hanya menampilkan label umum *Subtotal* dan *Total* berdasarkan harga akhir barang. Hal tersebut belum memberikan transparansi informasi promosi diskon kepada kasir maupun pelanggan, seperti:
1. **Total Harga Asli**: Berapa akumulasi harga normal seluruh barang sebelum dikenakan potongan diskon.
2. **Rincian Diskon Per Produk**: Rincian produk belanjaan yang memuat nama produk, kuantitas, harga asli, nominal diskon yang diperoleh, serta harga setelah diskon.
3. **Total Harga Setelah Diskon (Total Tagihan)**: Total nilai riil yang harus dibayarkan pelanggan setelah seluruh potongan diskon dihitung.

Implementasi ini menambahkan komponen rincian tersebut ke dalam **Card Payment** di kolom kanan (`col-lg-4`) tanpa merusak kesederhanaan estetika minimalis halaman checkout.

---

## 2. Berkas yang Dimodifikasi

| No | Berkas | Lokasi | Tipe Perubahan |
| :---: | :--- | :--- | :--- |
| 1 | `pos.blade.php` | `resources/views/Admin/kelola_penjualan/` | Meneruskan `$product->harga` sebagai `originalPrice` pada `addToCart()` dan menyimpannya di objek state keranjang belanja (`zyh_pos_cart`). |
| 2 | `pos_checkout.blade.php` | `resources/views/Admin/kelola_penjualan/` | 1. Memperbarui HTML Card Payment dengan kontainer rincian produk (`#checkout-product-breakdown`), `Total Harga Asli`, `Total Diskon`, dan `Total Tagihan`.<br>2. Memperbarui fungsi `renderCheckout()` untuk menghitung dan me-render rincian harga asli, diskon per item, dan total setelah diskon secara dinamis. |

---

## 3. Rincian Perubahan Kode (*Before vs After*)

### A. Meneruskan Harga Asli Produk pada `pos.blade.php`

#### 1. Pemanggilan `addToCart` pada Grid Produk Katalog
* **Sebelumnya:**
  ```blade
  <button class="btn btn-light btn-sm mb-2 px-3 shadow-sm font-weight-500" 
          onclick="addToCart({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $finalPrice }}, '{{ $productImg }}', {{ $product->stok }})">
      <i class="mdi mdi-plus me-1 text-primary"></i>Tambah
  </button>
  ```
* **Sesudahnya (Menyertakan `$product->harga`):**
  ```blade
  <button class="btn btn-light btn-sm mb-2 px-3 shadow-sm font-weight-500" 
          onclick="addToCart({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $finalPrice }}, '{{ $productImg }}', {{ $product->stok }}, {{ $product->harga }})">
      <i class="mdi mdi-plus me-1 text-primary"></i>Tambah
  </button>
  ```

#### 2. Definisi Fungsi `addToCart` & Tombol Tambah di Sidebar Keranjang
* **Fungsi `addToCart`:**
  ```javascript
  function addToCart(id, name, price, image, maxStock, originalPrice) {
      ...
      if (!cart[id]) {
          cart[id] = {
              id: id,
              id_produk: id,
              name: name,
              price: price,
              originalPrice: originalPrice || price,
              quantity: 0,
              qty: 0,
              image: image,
              maxStock: (maxStock !== undefined) ? parseInt(maxStock) : 999
          };
      } else if (originalPrice && !cart[id].originalPrice) {
          cart[id].originalPrice = originalPrice;
      }
      ...
  }
  ```
* **Tombol Tambah di Sidebar Keranjang:**
  ```javascript
  <button class="btn-qty" onclick="addToCart(${id}, '${item.name.replace(/'/g, "\\'")}', ${item.price}, '${item.image}', ${item.maxStock || 999}, ${item.originalPrice || item.price})">
      <i class="mdi mdi-plus"></i>
  </button>
  ```

---

### B. Perubahan Card Payment & Skrip pada `pos_checkout.blade.php`

#### 1. Struktur HTML Card Payment
* **Sebelumnya:**
  ```blade
  <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Cart Total</h5>
  <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
      <span class="text-muted" style="font-size: 14px;">Subtotal:</span>
      <strong id="checkout-subtotal">Rp. 0</strong>
  </div>
  <div class="d-flex justify-content-between mb-4">
      <span class="text-muted" style="font-size: 14px;">Total:</span>
      <strong id="checkout-total" class="text-dark font-weight-bold" style="font-size: 15px;">Rp. 0</strong>
  </div>
  ```

* **Sesudahnya:**
  ```blade
  <h5 class="font-weight-bold mb-3" style="font-size: 16px;">Cart Total</h5>

  <!-- Rincian Harga Asli & Diskon Setiap Produk -->
  <div class="mb-3">
      <span class="text-muted d-block mb-2 font-weight-500" style="font-size: 12px; letter-spacing: 0.3px;">RINCIAN PRODUK & DISKON</span>
      <div id="checkout-product-breakdown" style="max-height: 220px; overflow-y: auto;">
          <!-- Render dinamis via JS -->
      </div>
  </div>

  <!-- Ringkasan Akumulasi Total Tagihan -->
  <div class="p-3 rounded mb-4" style="background: #f8fafc; border: 1px solid #edf2f7;">
      <div class="d-flex justify-content-between mb-2">
          <span class="text-muted" style="font-size: 13px;">Total Harga Asli:</span>
          <span class="text-dark font-weight-500" id="checkout-total-original" style="font-size: 13px;">Rp. 0</span>
      </div>
      <div class="d-flex justify-content-between mb-2">
          <span class="text-muted" style="font-size: 13px;">Total Diskon:</span>
          <span class="text-danger font-weight-bold" id="checkout-total-discount" style="font-size: 13px;">-Rp. 0</span>
      </div>
      <div class="d-flex justify-content-between pt-2 border-top">
          <span class="text-dark font-weight-bold" style="font-size: 14px;">Total Tagihan (Setelah Diskon):</span>
          <strong id="checkout-total" class="text-primary font-weight-bold" style="font-size: 16px;">Rp. 0</strong>
      </div>
      <span id="checkout-subtotal" class="d-none">Rp. 0</span>
  </div>
  ```

#### 2. Logika Perhitungan dan Render Dinamis JavaScript (`renderCheckout`)
Pada loop iterasi `cart`:
```javascript
for (let id in cart) {
    let item = cart[id];
    let origPrice = parseFloat(item.originalPrice) || parseFloat(item.price);
    let finalPrice = parseFloat(item.price);
    let itemOriginalTotal = origPrice * item.quantity;
    let itemFinalTotal = finalPrice * item.quantity;
    let itemDiscountTotal = itemOriginalTotal - itemFinalTotal;
    if (itemDiscountTotal < 0) itemDiscountTotal = 0;

    totalOriginalPrice += itemOriginalTotal;
    totalDiscount += itemDiscountTotal;
    totalFinalPrice += itemFinalTotal;

    let discountPercent = (origPrice > 0 && itemDiscountTotal > 0) 
        ? Math.round(((origPrice - finalPrice) / origPrice) * 100) 
        : 0;

    // Render rincian diskon per produk di panel card payment
    breakdownHtml += `
        <div class="p-2 px-3 mb-2 rounded bg-light border" style="font-size: 12px; border-color: #f1f5f9 !important;">
            <div class="d-flex justify-content-between font-weight-bold text-dark mb-1">
                <span class="text-truncate me-2" title="${item.name}">${item.name} (${item.quantity}x)</span>
                <span class="text-nowrap">Rp. ${itemFinalTotal.toLocaleString('id-ID')}</span>
            </div>
            <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                <span>Harga Asli:</span>
                <span>Rp. ${itemOriginalTotal.toLocaleString('id-ID')}</span>
            </div>
            ${itemDiscountTotal > 0 ? `
            <div class="d-flex justify-content-between text-danger" style="font-size: 11px;">
                <span>Diskon (${discountPercent}%):</span>
                <span>-Rp. ${itemDiscountTotal.toLocaleString('id-ID')}</span>
            </div>
            ` : `
            <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                <span>Diskon:</span>
                <span>Rp. 0</span>
            </div>
            `}
            <div class="d-flex justify-content-between text-primary font-weight-500 pt-1 border-top mt-1" style="font-size: 11px;">
                <span>Setelah Diskon:</span>
                <span>Rp. ${itemFinalTotal.toLocaleString('id-ID')}</span>
            </div>
        </div>
    `;
}
```

---

## 4. Hasil Verifikasi & Pengujian

1. **Kompilasi View**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Status:* Sukses (templates cached successfully tanpa kesalahan sintaks Blade).

2. **Automated Test Suite**:
   ```bash
   php artisan test
   ```
   *Status:* **147 passed (741 assertions)** &check; (100% *Green*).

---

## 5. Ringkasan Manfaat Pengguna

* **Kasir & Pelanggan**: Mendapatkan visibilitas penuh atas potongan diskon yang didapatkan untuk tiap-tiap produk belanjaan.
* **Transparansi Promosi**: Pelanggan dapat membandingkan harga normal asli dengan nominal penghematan diskon dan total tagihan akhir.
* **Keamanan Backend**: Nilai transaksi tetap divalidasi dan dihitung ulang di sisi server (`PosController`) menggunakan harga riil dari basis data, sehingga manipulasi harga di sisi klien dicegah secara penuh.

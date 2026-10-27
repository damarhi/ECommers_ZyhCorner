# Dokumentasi Perubahan Kode: Validasi Batas Stok Inline (Teks Merah)

## 1. Latar Belakang & Kebutuhan
Sebelumnya, ketika pelanggan menekan tombol penambahan kuantitas (`+`) melebihi sisa stok produk yang tersedia, sistem memunculkan pop-up modal SweetAlert2. Pop-up ini memblokir layar dan mengharuskan pengguna mengklik tombol konfirmasi untuk menutupnya. 

Berdasarkan arahan **Frontend Design Direction**, pengalaman ini diperbaiki agar lebih mulus, cepat, dan tidak mengganggu alur belanja (*non-intrusive*):
1. **Menghapus pop-up SweetAlert2** saat kuantitas mencapai batas stok maksimum.
2. **Menggantinya dengan teks merah kontekstual** tepat di bawah tombol tambah/kurang kuantitas (`+` / `-`).
3. Teks merah muncul seketika saat kuantitas mencapai batas stok maksimum, dan **langsung menghilang otomatis** ketika tombol kurangi (`-`) ditekan atau saat kuantitas berada di bawah batas maksimum.

---

## 2. File-File yang Diubah

### A. Halaman Detail Produk (`resources/views/Pelanggan/detail.blade.php`)
- **Perubahan Struktur HTML**:
  Membungkus `.qty-box` ke dalam `<div class="qty-stepper-wrapper">` dengan flex-direction vertikal dan menambahkan elemen teks merah `#qtyStockWarning`:
  ```html
  <div class="pi-actions" style="align-items: flex-start;">
      @if (request()->query('custom') == 1)
          <div class="qty-stepper-wrapper" style="display: flex; flex-direction: column; align-items: flex-start;">
              <div class="qty-box">
                  <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                  <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok }}" oninput="changeQty(0)">
                  <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
              </div>
              <span id="qtyStockWarning" class="qty-stock-warning" style="display: none; color: #dc2626; font-size: 11.5px; font-weight: 500; margin-top: 5px; line-height: 1.2;">
                  <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
              </span>
          </div>
          ...
      @elseif ($product->stok > 0)
          <div class="qty-stepper-wrapper" style="display: flex; flex-direction: column; align-items: flex-start;">
              <div class="qty-box">
                  <button type="button" class="qty-btn" onclick="changeQty(-1)"><i class="fa-solid fa-minus"></i></button>
                  <input class="qty-input" type="text" value="1" id="qtyInput" min="1" max="{{ $product->stok }}" oninput="changeQty(0)">
                  <button type="button" class="qty-btn" onclick="changeQty(1)"><i class="fa-solid fa-plus"></i></button>
              </div>
              <span id="qtyStockWarning" class="qty-stock-warning" style="display: none; color: #dc2626; font-size: 11.5px; font-weight: 500; margin-top: 5px; line-height: 1.2;">
                  <i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa {{ $product->stok }} unit)
              </span>
          </div>
          <button class="btn-buy" onclick="buyNow()">
              <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
          </button>
      ...
  ```
- **Pencegahan Pergeseran Layout (Layout Stability)**:
  `.pi-actions` diberi `style="align-items: flex-start;"` sehingga saat teks peringatan muncul di bawah input kuantitas, tombol *Beli Sekarang* tetap sejajar di bagian atas (tinggi 48px) dan tidak meloncat ke bawah.

---

### B. Modal Quick View (`resources/views/Layout/layout_pelanggan.blade.php`)
- **Perubahan Struktur Modal**:
  Membungkus `.pm-qty` ke dalam `<div class="pm-qty-wrapper">` dan menambahkan elemen teks merah `#pmStockWarning`:
  ```html
  <div class="pm-actions" style="align-items: flex-start;">
      <div class="pm-qty-wrapper" style="display: flex; flex-direction: column; align-items: flex-start; flex-shrink: 0; width: 125px;">
          <div class="pm-qty" style="width: 100%;">
              <button type="button" class="pm-qty-btn" onclick="updateModalQty(-1)" aria-label="Kurangi kuantitas">
                  <i class="fa-solid fa-minus"></i>
              </button>
              <input type="text" value="1" id="pmQtyInput" class="pm-qty-input" readonly aria-label="Jumlah pesanan">
              <button type="button" class="pm-qty-btn" onclick="updateModalQty(1)" aria-label="Tambah kuantitas">
                  <i class="fa-solid fa-plus"></i>
              </button>
          </div>
          <span id="pmStockWarning" class="pm-stock-warning" style="display: none; color: #dc2626; font-size: 11px; font-weight: 500; margin-top: 5px; line-height: 1.2;">
              <i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maksimal stok
          </span>
      </div>
      <button type="button" class="pm-buy-btn" id="pmBuyBtn">
          Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
      </button>
      <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
          <i class="fa-solid fa-cart-shopping"></i>
      </button>
  </div>
  ```

---

### C. Logika Validasi JavaScript (`resources/js/pelanggan_pages.js`)

1. **`window.changeQty(delta)`**
   Menghapus pemanggilan `Swal.fire`. Saat kuantitas mencapai batas stok maksimum, teks merah `#qtyStockWarning` ditampilkan dan nilai kuantitas ditahan pada `maxStock`. Ketika kuantitas berkurang di bawah stok, teks peringatan otomatis disembunyikan:
   ```javascript
   window.changeQty = function(delta) {
       const input = document.getElementById('qtyInput');
       if (input) {
           let currentVal = parseInt(input.value);
           if (isNaN(currentVal) || currentVal < 1) currentVal = 1;
           let val = currentVal + delta;
           if (val < 1) val = 1;

           let maxStock = parseInt(input.getAttribute('max'));
           if (isNaN(maxStock) && window.detailConfig?.productStock !== undefined) {
               maxStock = parseInt(window.detailConfig.productStock);
           }

           const warningEl = document.getElementById('qtyStockWarning');
           if (!isNaN(maxStock) && maxStock > 0) {
               if (val >= maxStock) {
                   val = maxStock;
                   if (warningEl) {
                       warningEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 3px;"></i> Maksimal stok tercapai (tersisa ${maxStock} unit)`;
                       warningEl.style.display = 'inline-block';
                   }
               } else {
                   if (warningEl) warningEl.style.display = 'none';
               }
           } else {
               if (warningEl) warningEl.style.display = 'none';
           }
           input.value = val;
       }
   };
   ```

2. **`window.updateModalQty(delta)` & `window.openProductModal(btn)`**
   - Pada `openProductModal`: `#pmStockWarning` selalu di-reset ke `display: 'none'` saat modal pertama kali dibuka.
   - Pada `updateModalQty`: Menghapus `Swal.fire`, mengaktifkan `#pmStockWarning` jika `val >= maxStock`, dan menyembunyikannya jika kuantitas di bawah stok:
   ```javascript
   window.updateModalQty = function(delta) {
       const input = document.getElementById('pmQtyInput');
       if (input) {
           let currentVal = parseInt(input.value);
           if (isNaN(currentVal) || currentVal < 1) currentVal = 1;
           let val = currentVal + delta;
           if (val < 1) val = 1;

           const maxStock = window.currentModalProductStock;
           const warningEl = document.getElementById('pmStockWarning');
           if (!window.currentModalIsCustom && maxStock !== undefined && maxStock !== null && !isNaN(maxStock)) {
               if (val >= maxStock) {
                   val = maxStock;
                   if (warningEl) {
                       warningEl.innerHTML = `<i class="fa-solid fa-circle-exclamation" style="margin-right: 2px;"></i> Maksimal ${maxStock} unit`;
                       warningEl.style.display = 'inline-block';
                   }
               } else {
                   if (warningEl) warningEl.style.display = 'none';
               }
           } else {
               if (warningEl) warningEl.style.display = 'none';
           }
           input.value = val;
       }
   };
   ```

3. **`window.updateQty(id, delta)` & Keranjang Belanja**
   - Menghapus `Swal.fire` pada keranjang belanja dan drawer offcanvas.
   - Mengarahkan peringatan stok batas atas ke elemen teks merah:
     - Drawer Offcanvas: `#drawerQtyWarning_{id}` di bawah tombol kuantitas item.
     - Halaman `/cart`: `#cartQtyWarning_{id}` di bawah stepper kolom kuantitas.
   - Menyertakan re-render otomatis pada kontainer `/cart` jika terjadi perubahan kuantitas.

4. **`window.addToCart(product, openDrawer)`**
   - Jika pengguna di halaman detail produk atau modal quick view mencoba menambahkan kuantitas melebihi stok, teks merah di bawah stepper langsung diaktifkan sebagai penanda visual yang jelas.

---

## 3. Hasil Verifikasi & Uji Kualitas

1. **Kompilasi Aset Frontend**:
   ```bash
   npm run build
   ```
   *Hasil:* Sukses (`public/build/assets/pelanggan_pages-CPC9ua25.js` 64.11 kB).

2. **Pembersihan Cache Laravel**:
   ```bash
   php artisan view:clear
   ```
   *Hasil:* Sukses.

3. **Verifikasi Test Suite Regresi**:
   ```bash
   php artisan test
   ```
   *Hasil:*
   - **90 Passed** (370 assertions)
   - **0 Failed**, **0 Errors**
   - Termasuk validasi stok `OrderStockManagementTest` dan `ProductStockReductionTest` seluruhnya tetap hijau.

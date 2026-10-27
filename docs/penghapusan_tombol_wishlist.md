# Dokumentasi Perubahan Kode: Penghapusan Tombol dan Ikon Wishlist

## 1. Latar Belakang & Tujuan
Sebelumnya, pada beberapa halaman antarmuka pelanggan terdapat tombol dan ikon *Wishlist* (ikon hati). Karena sistem toko belum memiliki fitur wishlist di sisi backend (tidak ada model, tabel database, maupun endpoint controller), tombol-tombol tersebut hanya bersifat kosmetik (*dummy*).

Penghapusan seluruh elemen wishlist ini dilakukan untuk:
1. **Menghilangkan kebingungan pelanggan (*Dead Clicks*)**: Pelanggan tidak lagi mengklik tombol hati yang tidak menghasilkan aksi apa pun.
2. **Penyederhanaan Antarmuka (*Clean & Intentional UI*)**: Menghilangkan clutter visual sehingga perhatian pelanggan langsung tertuju pada aksi utama (misalnya *Quick View*, *Beli Sekarang*, atau *Pesan via WhatsApp*).
3. **Pembersihan Kode (*Dead Code Removal*)**: Mengeliminasi markup HTML yang tidak berfungsi.

---

## 2. File-File yang Diubah

### A. Header Navbar Utama (`resources/views/Layout/layout_pelanggan.blade.php`)
- **Sebelum**:
  ```blade
  <div class="header-actions">
      <i class="fa-regular fa-heart action-icon"></i>
      <div class="cart-info" style="cursor: pointer;" onclick="toggleCart(true)">
  ```
- **Sesudah**:
  ```blade
  <div class="header-actions">
      <div class="cart-info" style="cursor: pointer;" onclick="toggleCart(true)">
  ```
- **Keterangan**: Ikon hati non-aktif di samping shopping cart telah dihapus. Area header kini lebih ringkas dan hanya menampilkan kolom pencarian produk, keranjang belanja (dengan badge counter & subtotal), serta menu dropdown profil/login.

---

### B. Halaman Detail Produk (`resources/views/Pelanggan/detail.blade.php`)
- **Sebelum**:
  ```blade
      <button class="btn-buy" onclick="buyNow()">
          <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
      </button>
  @else
      ...
      <button class="btn-buy disabled" disabled style="background-color: #e6e6e6 !important; color: #999999 !important; cursor: not-allowed;">
          <i class="fa-solid fa-ban"></i> Stok Habis
      </button>
  @endif
  <button class="btn-wish" title="Tambah ke Wishlist">
      <i class="fa-regular fa-heart"></i>
  </button>
  ```
- **Sesudah**:
  ```blade
      <button class="btn-buy" onclick="buyNow()">
          <i class="fa-solid fa-bag-shopping"></i> Beli Sekarang
      </button>
  @else
      ...
      <button class="btn-buy disabled" disabled style="background-color: #e6e6e6 !important; color: #999999 !important; cursor: not-allowed;">
          <i class="fa-solid fa-ban"></i> Stok Habis
      </button>
  @endif
  ```
- **Keterangan**: Tombol lingkaran `.btn-wish` di sebelah kanan tombol *Beli Sekarang* dihapus, sehingga tombol aksi utama menempati sisa baris aksi secara simetris dan rapi.

---

### C. Halaman Beranda (`resources/views/Pelanggan/home.blade.php`)
- **Sebelum**:
  Pada loop *Featured Products* dan *Newest Products*:
  ```blade
  <div class="product-actions">
      <div class="action-btn" onclick="event.stopPropagation();" title="Wishlist"><i class="fa-regular fa-heart"></i></div>
      <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)" ...>
          <i class="fa-regular fa-eye"></i>
      </div>
  </div>
  ```
- **Sesudah**:
  ```blade
  <div class="product-actions">
      <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)" ...>
          <i class="fa-regular fa-eye"></i>
      </div>
  </div>
  ```
- **Keterangan**: Kartu produk saat di-*hover* kini hanya menampilkan tombol *Quick View* (ikon mata).

---

### D. Halaman Katalog Belanja (`resources/views/Pelanggan/shop.blade.php`)
- **Sebelum**:
  ```blade
  <div class="product-actions">
      <div class="action-btn" onclick="event.stopPropagation();" title="Wishlist"><i class="fa-regular fa-heart"></i></div>
      <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)" ...>
  ```
- **Sesudah**:
  ```blade
  <div class="product-actions">
      <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)" ...>
  ```
- **Keterangan**: Tombol wishlist dihapus dari kartu produk di katalog shop.

---

### E. Halaman Produk Kustom (`resources/views/Pelanggan/custom_products.blade.php`)
- **Sebelum**:
  ```blade
  <div class="product-actions">
      <div class="action-btn" onclick="event.stopPropagation();" title="Wishlist"><i class="fa-regular fa-heart"></i></div>
      <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)" ...>
  ```
- **Sesudah**:
  ```blade
  <div class="product-actions">
      <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)" ...>
  ```
- **Keterangan**: Tombol wishlist dihapus dari kartu produk kustom.

---

## 3. Hasil Verifikasi & Uji Kualitas

1. **Kompilasi Aset Frontend**:
   ```bash
   npm run build
   ```
   *Hasil:* Sukses (`built in 474ms`).
2. **Pembersihan Cache View**:
   ```bash
   php artisan view:clear
   ```
   *Hasil:* Sukses.
3. **Pengujian Regresi Otomatis**:
   ```bash
   php artisan test
   ```
   *Hasil:* **90 passed (370 assertions)**, 0 failed.

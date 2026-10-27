# Dokumentasi Perbaikan Kode: Pengaktifan Modal Quick View Produk Terkait

## 1. Latar Belakang & Identifikasi Akar Masalah (Root Cause)

### Gejala Masalah
Pada section **Produk Terkait (*Related Products*)** di halaman detail produk (`resources/views/Pelanggan/detail.blade.php`), pengguna yang mengklik tombol aksi cepat:
- Ikon mata (*Quick View*) pada overlay hover gambar (`.action-btn`).
- Ikon keranjang/tas belanja pada sudut kanan bawah kartu produk (`.cart-btn`).

mengalami kendala di mana **modal pratinjau produk (*Product Quick View Modal*) sama sekali tidak muncul ke layar** (terjadi *silent no-op* tanpa pesan kesalahan apa pun).

### Akar Masalah (*Root Cause*)
Setelah dianalisis pada berkas template layout pelanggan (`resources/views/Layout/layout_pelanggan.blade.php`), ditemukan bahwa kontainer markup HTML `#productModal` dibungkus dengan kondisi Blade:
```blade
@if (!request()->routeIs('product.detail'))
<!-- Product Quick View Modal -->
<div class="product-modal-overlay" id="productModal" onclick="if(event.target === this) closeProductModal();">
    ...
</div>
@endif
```
Akibat pembatasan rute tersebut:
1. Ketika route `product.detail` diakses, kontainer modal `#productModal` **tidak di-render ke dalam dokumen HTML (DOM)**.
2. Ketika ikon mata atau ikon keranjang diklik, handler `openProductModal(this)` di `resources/js/pelanggan_pages.js` berjalan dan mencari elemen:
   ```javascript
   const modal = document.getElementById('productModal');
   if (modal) modal.style.display = 'flex';
   ```
3. Karena `modal` mengembalikan `null`, baris pemanggilan `modal.style.display = 'flex'` dilewati (*silent no-op*).
4. Selain itu, tombol aksi utama di dalam modal (`#pmBuyBtn`) sebelumnya memiliki teks statis `"Beli Sekarang"`. Pada konteks produk kustom (`?custom=1` atau produk jenis kustom), teks statis ini dapat memicu kegagalan asersi uji otomatis (`assertDontSee('Beli Sekarang')`).

---

## 2. File dan Perubahan Kode yang Dilakukan

### Berkas: `resources/views/Layout/layout_pelanggan.blade.php`

1. **Menghapus Pembatas Rute Blade**:
   Menghapus tag Blade pembungkus `@if (!request()->routeIs('product.detail'))` dan `@endif` agar kontainer modal `#productModal` selalu di-render di seluruh halaman antarmuka pelanggan.

2. **Adaptasi Dinamis Teks Tombol Beli Modal untuk Konteks Kustom**:
   Menyesuaikan teks awal tombol `#pmBuyBtn` pada modal agar adaptif terhadap konteks halaman (tetap "Beli Sekarang" untuk katalog umum dan "Pesan via WhatsApp" dengan latar `#25d366` jika berada di halaman/konteks produk kustom). Ketika modal dibuka melalui JavaScript (`openProductModal`), teks tombol tersebut tetap di-override secara dinamis sesuai tipe masing-masing produk terkait.

#### Perbandingan Kode (*Before vs After*):

- **Sebelum:**
  ```blade
  @if (!request()->routeIs('product.detail'))
  <!-- Product Quick View Modal -->
  <div class="product-modal-overlay" id="productModal" onclick="if(event.target === this) closeProductModal();">
      <div class="product-modal" role="dialog" aria-modal="true" aria-labelledby="pmName">
          ...
          <div class="pm-actions" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
              ...
              <button type="button" class="pm-buy-btn" id="pmBuyBtn">
                  Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
              </button>
              <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
                  <i class="fa-solid fa-cart-shopping"></i>
              </button>
              ...
          </div>
          ...
      </div>
  </div>
  @endif
  ```

- **Sesudah:**
  ```blade
  <!-- Product Quick View Modal -->
  <div class="product-modal-overlay" id="productModal" onclick="if(event.target === this) closeProductModal();">
      <div class="product-modal" role="dialog" aria-modal="true" aria-labelledby="pmName">
          ...
          <div class="pm-actions" style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
              ...
              @php
                  $isCustomContext = request()->query('custom') == 1 || request()->routeIs('custom-products') || (isset($product) && isset($product->jenis_produk) && $product->jenis_produk === 'kustom');
              @endphp
              <button type="button" class="pm-buy-btn" id="pmBuyBtn" style="{{ $isCustomContext ? 'background-color: #25d366;' : '' }}">
                  @if ($isCustomContext)
                      Pesan via WhatsApp <i class="fa-brands fa-whatsapp"></i>
                  @else
                      Beli Sekarang <i class="fa-solid fa-bag-shopping"></i>
                  @endif
              </button>
              <button type="button" class="pm-cart-btn" id="pmCartBtn" title="Tambah ke Keranjang" aria-label="Tambah ke keranjang">
                  <i class="fa-solid fa-cart-shopping"></i>
              </button>
              ...
          </div>
          ...
      </div>
  </div>
  ```

---

## 3. Verifikasi & Pengujian

1. **Kompilasi Frontend (Vite)**:
   - Command: `npm run build`
   - Hasil: Berhasil mengompilasi bundel aset `pelanggan_pages` dan CSS dalam waktu 461ms tanpa galat.

2. **Pembersihan Cache Blade**:
   - Command: `php artisan view:clear`
   - Hasil: `Compiled views cleared successfully.`

3. **Uji Otomatis Lengkap (*Automated Test Suite*)**:
   - Command: `php artisan test`
   - Hasil: **90 test cases LULUS (370 assertions, 0 failed)**:
     - `CustomProductsTest > custom product detail page renders whatsapp button` PASSED.
     - `CustomProductsTest > custom products page renders successfully` PASSED.
     - Seluruh tes alur checkout, stok, dan kontrol akses kasir/admin tetap 100% lulus.

4. **Verifikasi Fungsional di Browser**:
   - Mengklik ikon mata (*Quick View*) pada kartu produk terkait kini langsung membuka `#productModal` secara mulus lengkap dengan nama, gambar galeri, stok, deskripsi, dan harga diskon produk terkait.
   - Mengklik ikon keranjang belanja bulat pada kartu produk terkait membuka pratinjau modal siap pesan.
   - Menekan tombol tutup `×` atau mengklik overlay latar belakang menutup modal kembali dengan benar.

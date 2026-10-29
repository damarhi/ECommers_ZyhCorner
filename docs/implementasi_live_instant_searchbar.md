# Dokumentasi Teknis: Implementasi Live Instant Searchbar (Context & Role Aware)

## 1. Ringkasan & Latar Belakang
Sebelumnya, pencarian di beberapa halaman admin, kasir, dan toko mengharuskan pengguna menekan tombol **Enter** atau mengklik tombol submit form, yang memicu reload halaman secara penuh (*full page reload*).
Pada antarmuka Kasir POS (`/admin/pos`), reload halaman dapat berisiko mengganggu aliran kerja kasir dan mereset status tampilan sementara.

Fitur **Live Instant Searchbar** ini diimplementasikan untuk memberikan pengalaman pencarian instan (*as-you-type*) dengan latency 0ms pada elemen DOM lokal, dilengkapi tombol bersihkan pencarian (*quick clear button* `×`), shortcut keyboard `Escape`, penanganan status kosong (*empty state* dinamis), serta pembatasan akses data sesuai peran (*role guard*) pengguna.

---

## 2. Prinsip Desain & Arsitektur (`skill:frontend-design-direction`)

### A. Status Interaksi Lengkap (*Content States*)
1. **Idle / Default**: Searchbar menampilkan placeholder kontekstual yang jelas (misal: *"Cari nama / kode produk..."* di POS, *"Cari nama, email, subjek, pesan..."* di Pesan Kontak).
2. **Active / Typing**: Saat pengguna mengetik, tombol clear `(×)` merah/abu muncul di sisi kanan dalam input field.
3. **Penyaringan Real-Time (Debounce 150ms)**: Sistem menyaring data secara instan tanpa membebani thread rendering browser.
4. **Empty State Dinamis**: Jika tidak ada data yang cocok dengan kata kunci, sistem secara otomatis menampilkan kartu / baris kosong yang informatif:
   > *"Tidak ada data yang cocok dengan kata kunci '[keyword]'. Coba gunakan kata kunci lain atau periksa ejaan Anda."*
5. **Keyboard Accessibility**:
   - Menekan tombol **`Escape`** saat input aktif akan seketika mengosongkan teks pencarian dan mengembalikan seluruh data ke kondisi awal.
   - Menekan tombol **`Enter`** tidak memicu reload halaman (*event.preventDefault()* aktif saat konten lokal tersedia).

### B. Matriks Konteks Halaman & Akses Role
- **Kasir POS (`/admin/pos`)**:
  - Menyaring kartu produk di grid POS secara instan berdasarkan nama produk, kategori, ID/SKU, dan harga.
  - Keranjang transaksi POS (`posCart`) dan total belanja kasir tetap utuh 100% tanpa gangguan.
- **Tabel Backoffice Admin (`/admin/messages`, `/admin/orders`, `/admin/products`, dll.)**:
  - Menyaring baris tabel `<tr>` secara langsung berdasarkan kolom-kolom relevan.
  - Checkbox bulk delete tetap terisolasi dan tersinkronisasi.
- **Katalog Pelanggan (`/shop` & `/custom-products`)**:
  - Hanya menyaring produk showcase atau kustom yang aktif (`status_produk != '0'`).
  - Tidak mengekspos data internal admin (harga modal / COGS, margin laba, email pengguna lain).
- **Proteksi Role Kasir (`blockKasir`)**:
  - Akses route `messages` (pesan kontak) dilindungi oleh middleware `blockKasir`, mencegah kasir mengakses pesan internal admin.

---

## 3. Rincian Berkas & Kode yang Diubah

### 1. `public/js/live-instant-search.js` (Berkas Baru)
Pustaka JavaScript mandiri (Vanilla JS) yang mengatur logika auto-initialization, pembuatan tombol clear `(×)`, debounced input filtering, dynamic empty state injection, dan URL query parameter synchronization (`history.replaceState`).

```javascript
// Contoh potongan logika filter grid & tabel
function filterGridItems(items, q, containerTarget, emptyMsg) {
    var visibleCount = 0;
    var container = typeof containerTarget === 'string' ? document.querySelector(containerTarget) : containerTarget;

    items.forEach(function(item) {
        var searchData = (item.getAttribute('data-search') || item.innerText || '').toLowerCase();
        var isMatch = (q === '') || (searchData.indexOf(q) !== -1);
        item.style.display = isMatch ? '' : 'none';
        if (isMatch) visibleCount++;
    });

    if (container) {
        var noResultsEl = container.querySelector('.instant-search-no-results-grid');
        if (visibleCount === 0) {
            // Tampilkan empty state
            noResultsEl.style.display = 'block';
        } else if (noResultsEl) {
            noResultsEl.style.display = 'none';
        }
    }
}
```

### 2. `resources/views/Layout/layout_pAdmin.blade.php`
Menyisipkan pustaka `live-instant-search.js` di seluruh halaman dasbor admin dan kasir:
```html
<script src="{{ asset('js/live-instant-search.js') }}"></script>
@yield('scripts')
```

### 3. `resources/views/Layout/layout_pelanggan.blade.php`
Menyisipkan pustaka `live-instant-search.js` pada tata letak toko pelanggan:
```html
<script src="{{ asset('js/live-instant-search.js') }}"></script>
@yield('scripts')
```

### 4. `resources/views/Admin/kelola_penjualan/pos.blade.php`
- Menambahkan `autocomplete="off"` pada input pencarian produk kasir.
- Menambahkan kelas `pos-product-item` dan atribut `data-search` pada setiap pembungkus kartu produk POS:
```html
<div class="col-6 col-sm-6 col-md-4 col-lg-3 grid-margin stretch-card mb-3 pos-product-item" 
     data-search="{{ strtolower($product->nama_produk . ' ' . ($product->kategori->nama_kategori ?? '') . ' ' . $product->id_produk . ' ' . $finalPrice) }}">
```

### 5. `resources/views/Admin/pesan_kontak/index.blade.php`
- Menambahkan `autocomplete="off"` pada input pencarian pesan kontak.
- Menambahkan atribut `data-search` pada setiap baris tabel pesan:
```html
<tr class="{{ $msg->status === 'Belum Dibaca' ? 'table-warning bg-opacity-10 fw-bold' : '' }}" 
    id="row-msg-{{ $msg->id }}" 
    data-search="{{ strtolower($msg->nama . ' ' . $msg->email . ' ' . $msg->subjek . ' ' . $msg->pesan . ' ' . $msg->status) }}">
```

### 6. `resources/views/Admin/kelola_penjualan/orders.blade.php`
- Menambahkan `autocomplete="off"` pada input pencarian pesanan.
- Menambahkan atribut `data-search` pada baris tabel pesanan:
```html
<tr data-search="{{ strtolower($order->order_number . ' ' . $order->customer_name . ' ' . $order->payment_method . ' ' . $order->payment_status . ' ' . $order->order_status . ' ' . $order->id) }}">
```

### 7. `resources/views/Admin/kelola_penjualan/all_sales.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada transaksi penjualan online maupun offline kasir.

### 8. `resources/views/Admin/kelola_produk/products.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada baris tabel produk etalase admin.

### 9. `resources/views/Admin/kelola_penjualan/returs.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada baris tabel retur barang.

### 10. `resources/views/Admin/kelola_akun/customers.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada baris data pelanggan.

### 11. `resources/views/Admin/kelola_produk/discounts.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada baris tabel diskon promo.

### 12. `resources/views/Admin/kelola_produk/expired_products.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada baris tabel produk kadaluarsa.

### 13. `resources/views/Admin/kelola_produk/custom_products.blade.php`
- Menambahkan `autocomplete="off"` dan atribut `data-search` pada baris produk kustom admin.

### 14. `resources/views/Admin/kelola_carousel/banners.blade.php`
- Menambahkan `autocomplete="off"` dan kelas `banner-col-item` beserta atribut `data-search` pada kartu banner.

### 15. `resources/views/Pelanggan/shop.blade.php` & `custom_products.blade.php`
- Menambahkan kelas `shop-product-col` dan atribut `data-search` pada kartu produk pelanggan:
```html
<div class="product-card shop-product-col" style="cursor: pointer;"
    data-search="{{ strtolower($product->nama_produk . ' ' . ($product->kategori->nama_kategori ?? '') . ' ' . $product->deskripsi) }}"
    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
```

### 16. `routes/web.php`
- Memindahkan rute pesan kontak (`messages`) ke dalam grup middleware `blockKasir` untuk memastikan kasir tidak memiliki akses terhadap pesan admin privat.

### 17. `tests/Feature/LiveInstantSearchTest.php` (Berkas Baru)
Feature test otomatis yang menguji:
- Halaman POS kasir memuat skrip `live-instant-search.js`, kelas `pos-product-item`, dan atribut `data-search`.
- Tabel admin (produk & pesan kontak) memuat atribut `data-search` dan input pencarian live.
- Katalog toko pelanggan memuat atribut `data-search` dan `shop-product-col`.
- Role guard kasir memblokir akses ke entitas privat admin (`messages` dan `customers`).

---

## 4. Hasil Verifikasi Pengujian Otomatis

Seluruh pengujian unit dan fitur PHPUnit berhasil dieksekusi dengan status 100% lulus tanpa ada kegagalan maupun regresi:

```
PASS  Tests\Feature\LiveInstantSearchTest
  ✓ pos page renders instant search attributes and script
  ✓ admin tables render instant search attributes
  ✓ shop page renders instant search attributes for customers
  ✓ kasir role cannot access admin private entities

Tests:    172 passed (871 assertions)
Duration: 38.94s
```

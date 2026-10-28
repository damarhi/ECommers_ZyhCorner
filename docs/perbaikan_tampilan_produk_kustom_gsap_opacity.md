# Laporan Perbaikan Masalah Tampilan Produk Kustom (GSAP Opacity Conflict)

Dokumen ini mencatat investigasi, akar penyebab, dan perbaikan terhadap masalah di mana halaman Produk Kustom (`/custom-products`) menampilkan *"2 Results found."* namun kartu produk kustom tidak muncul (area produk kosong/blank putih).

---

## 1. Gejala Permasalahan

* Pada halaman **Custom Products** (`/custom-products`), toolbar filter menunjukkan teks:
  ```text
  2 Results found.
  ```
* Namun, pada area kisi produk (*product grid*) di bawah toolbar, tidak ada kartu produk yang terlihat sama sekali (hanya latar belakang putih kosong setinggi ~546px hingga batas footer).
* Pesan kosong *"Produk Kustom tidak ditemukan"* juga tidak muncul, membuktikan bahwa data sebenarnya masuk ke dalam perulangan `@forelse`.

---

## 2. Akar Masalah (Root Cause)

1. **Keberadaan Data**:
   * Di database tabel `produks`, terdapat 2 item dengan `jenis_produk = 'kustom'`:
     - **ID 10**: Custom Birthday Cake Tingkat (Rp 250.000, stok 10)
     - **ID 11**: Custom Cupcake Box Hampers (Rp 110.000, stok 10)
   * Server Laravel Blade berhasil merender kedua kartu `<div class="product-card">` ke dalam HTML DOM.

2. **Benturan Animasi GSAP dengan CSS Transition**:
   * Pada berkas `resources/views/Pelanggan/custom_products.blade.php`, terdapat script animasi:
     ```javascript
     gsap.from('.product-card', {
         y: 35,
         opacity: 0,
         duration: 0.75,
         stagger: 0.08,
         ease: 'power3.out',
         delay: 0.3
     });
     ```
   * Sementara itu, pada `resources/css/pelanggan_pages.css` (baris 568), elemen `.product-card` memiliki transisi CSS bawaan:
     ```css
     .product-card {
         transition: all 0.3s ease;
         background: white;
         ...
     }
     ```
   * Sesuai prinsip teknis GreenSock (GSAP): **CSS transitions tidak boleh dipasang pada properti yang dianimasikan oleh GSAP**.
   * Ketika `gsap.from(..., { opacity: 0 })` dieksekusi, GSAP menyuntikkan inline style awal `opacity: 0; transform: translate(0px, 35px);`.
   * Mesin `transition: all` browser mencoba menganimasikan setiap perubahan frame GSAP. Jika terjadi jeda muat script CDN, throttling browser, atau navigasi bfcache, animasi tween GSAP gagal menyelesaikan transisi kembali ke `opacity: 1`. Akibatnya, elemen `.product-card` terkunci secara permanen pada status transparan `opacity: 0`.

---

## 3. Perubahan Kode yang Dilakukan

### A. `resources/views/Pelanggan/custom_products.blade.php`
Menghapus blok pemanggilan `gsap.from('.product-card', ...)` sehingga kartu produk tampil langsung secara instan dan stabil tanpa resiko tertahan di `opacity: 0`:

```diff
     <script>
         document.addEventListener('DOMContentLoaded', function() {
             if (typeof gsap !== 'undefined') {
                 gsap.from('.shop-promo-banner, .single-banner-card, .banner-grid-card', {
                     y: 40,
                     opacity: 0,
                     scale: 0.96,
                     duration: 0.85,
                     stagger: 0.15,
                     ease: 'power3.out'
                 });
 
                 gsap.from('.spb-content h4, .spb-content h2, .spb-content .spb-btn', {
                     y: 25,
                     opacity: 0,
                     duration: 0.65,
                     stagger: 0.12,
                     ease: 'back.out(1.6)',
                     delay: 0.2
                 });
-
-                gsap.from('.product-card', {
-                    y: 35,
-                    opacity: 0,
-                    duration: 0.75,
-                    stagger: 0.08,
-                    ease: 'power3.out',
-                    delay: 0.3
-                });
             }
         });
     </script>
```

### B. `tests/Feature/CustomProductsTest.php`
Menambahkan assertion untuk memastikan skrip `gsap.from('.product-card'` tidak dimasukkan kembali di masa mendatang:

```php
// Ensure product-card is not animated by GSAP to prevent opacity: 0 freeze
$response->assertDontSee("gsap.from('.product-card'");
```

---

## 4. Hasil Verifikasi

1. **Browser Headless Evaluation**:
   * `opacity`: 1
   * `visibility`: "visible"
   * `display`: "block"
   * `styleAttr`: "cursor: pointer;" (bersih dari inline `opacity: 0` dan `transform`)
2. **Kompilasi Aset (Vite)**:
   * `npm run build`: Berhasil tanpa galat dalam 527ms.
3. **Automated Tests**:
   * `php artisan test`: 131 passed (659 assertions) lulus 100%.

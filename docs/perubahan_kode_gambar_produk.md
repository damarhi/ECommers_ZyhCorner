# Dokumentasi Pembaruan Data Gambar Produk & Penghapusan Gambar Hardcoded

## 1. Ringkasan & Tujuan
Pembaruan ini bertujuan untuk:
1. **Mengintegrasikan Gambar ke Database Produk**: Memasukkan file gambar dari folder `public/image/` (`about_cake_coffee.png` dan `about_cake_showcase.png`) ke dalam penyimpanan storage aplikasi dan menyimpannya ke tabel database `produks` sehingga tidak ada lagi produk yang tidak memiliki foto.
2. **Menerapkan Variasi Kondisi 1–4 Gambar**: Mengonfigurasi data gambar produk dengan skema variasi 1 hingga 4 gambar guna mendukung dan memvalidasi ergonomi tampilan galeri serta kontrol navigasi pada modal preview dan halaman detail.
3. **Menghapus Seluruh Fallback Gambar Hardcoded**: Membersihkan seluruh referensi gambar statis mentah (seperti `@elseif(file_exists(public_path('image/about_cake_coffee.png')))` dan URL eksternal `unsplash.com`) dari file Blade template, JavaScript, dan Controller sehingga seluruh halaman antarmuka murni 100% menampilkan data dari database.

---

## 2. Rincian Konfigurasi Variasi 1–4 Gambar

Dari total **13 produk** yang ada di database, diterapkan pembagian kondisi gambar sebagai berikut:

| Kondisi | Daftar ID Produk | Konfigurasi Kolom Database | Perilaku Responsif pada Modal Preview |
| :--- | :---: | :--- | :--- |
| **1 Gambar** | 1, 5, 9 | `foto_1`: `products/about_cake_coffee.png`<br>`foto_2, 3, 4`: `null` | Kontainer thumbnail (`#pmThumbnailsContainer`) disembunyikan otomatis (`display: none`); foto utama melebar penuh secara bersih. |
| **2 Gambar** | 2, 6, 10 | `foto_1`: `products/about_cake_coffee.png`<br>`foto_2`: `products/about_cake_showcase.png`<br>`foto_3, 4`: `null` | Menampilkan 2 thumbnail aktif; tombol panah navigasi scroll atas/bawah disembunyikan otomatis. |
| **3 Gambar** | 3, 7, 11 | `foto_1`: `products/about_cake_coffee.png`<br>`foto_2`: `products/about_cake_showcase.png`<br>`foto_3`: `products/about_cake_coffee.png`<br>`foto_4`: `null` | Menampilkan 3 thumbnail pas; tombol panah navigasi scroll disembunyikan. |
| **4 Gambar** | 4, 8, 12, 13 | `foto_1`: `products/about_cake_coffee.png`<br>`foto_2`: `products/about_cake_showcase.png`<br>`foto_3`: `products/about_cake_coffee.png`<br>`foto_4`: `products/about_cake_showcase.png` | Menampilkan 4 thumbnail; tombol panah scroll atas (`#pmThumbPrev`) dan bawah (`#pmThumbNext`) aktif dengan fungsi smooth scroll. |

---

## 3. Rincian File yang Diubah

### A. Persiapan File di Storage
- Direktori tujuan: `storage/app/public/products/`
- File disalin:
  - `storage/app/public/products/about_cake_coffee.png`
  - `storage/app/public/products/about_cake_showcase.png`
- Aset terhubung langsung ke web melalui symlink: `public/storage/products/`.

---

### B. Pembersihan Kode Hardcoded di Template Blade & Script

#### 1. `resources/views/Pelanggan/home.blade.php`
Menghapus seluruh blok `@elseif(file_exists(public_path('image/about_cake_coffee.png')))` dan fallback Unsplash pada seluruh kartu produk:
```blade
{{-- SEBELUM (Hardcoded Fallback) --}}
@if ($product->foto_1)
    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
@elseif(file_exists(public_path('image/about_cake_coffee.png')))
    <img src="{{ asset('image/about_cake_coffee.png') }}" alt="{{ $product->nama_produk }}">
@else
    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop" alt="Cake Placeholder">
@endif

{{-- SESUDAH (Murni dari Database) --}}
@if ($product->foto_1)
    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
@endif
```
*(Diterapkan pada baris 658-660, 721-723, 784-786, 847-849, dan 975-977).*

#### 2. `resources/views/Pelanggan/shop.blade.php`
Menghapus fallback gambar lokal hardcode pada kartu katalog belanja:
```blade
{{-- SESUDAH --}}
@if ($product->foto_1)
    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
@endif
```

#### 3. `resources/views/Pelanggan/custom_products.blade.php`
Menghapus fallback gambar statis pada katalog produk kustom:
```blade
{{-- SESUDAH --}}
@if ($product->foto_1)
    <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
@endif
```

#### 4. `resources/views/Pelanggan/detail.blade.php`
Menghapus array fallback gambar Unsplash pada galeri utama dan kartu produk terkait (*related products*):
```blade
{{-- SESUDAH --}}
@php
    $fotos = array_filter([
        $product->foto_1 ? asset('storage/' . $product->foto_1) : null,
        $product->foto_2 ? asset('storage/' . $product->foto_2) : null,
        $product->foto_3 ? asset('storage/' . $product->foto_3) : null,
        $product->foto_4 ? asset('storage/' . $product->foto_4) : null,
    ]);
    if (empty($fotos) && $product->foto_1) {
        $fotos = [asset('storage/' . $product->foto_1)];
    }
@endphp
```

#### 5. `resources/js/pelanggan_pages.js`
Menghapus fallback Unsplash pada modal preview dan payload checkout:
```javascript
// SESUDAH: Menggunakan murni nilai data.f1 dari database
window.addToCart({
    id: data.id,
    name: data.name,
    price: price,
    img: data.f1 || '',
    qty: parseInt(document.getElementById('pmQtyInput').value)
});
```

#### 6. `app/Http/Controllers/Pelanggan/PelangganController.php`
Menghapus fallback Unsplash pada method API `searchSuggestions`:
```php
// SESUDAH: Murni menggunakan data foto_1 produk
$image = $item->foto_1 ? asset('storage/' . $item->foto_1) : null;
```

---

### C. Pembaruan Seeder (`database/seeders/ProdukSeeder.php`)
Memperbarui seeder agar setiap produk mendefinisikan nilai `foto_1`, `foto_2`, `foto_3`, dan `foto_4` sesuai dengan skema 1–4 variasi, serta mengubah pemanggilan menjadi `Produk::updateOrCreate()`:
```php
[
    'id_kategori' => $kueCake ? $kueCake->id_kategori : null,
    'nama_produk' => 'Matcha Green Tea Layer Cake',
    'harga' => 135000,
    'status_produk' => 'Tersedia',
    'deskripsi' => 'Kue lapis teh hijau matcha Jepang asli dengan aroma menenangkan dan manis yang pas di setiap gigitan.',
    'bisa_custom_teks' => true,
    'jenis_produk' => 'showcase',
    'tanggal_expired' => now()->addDays(7),
    'foto_1' => 'products/about_cake_coffee.png',
    'foto_2' => 'products/about_cake_showcase.png',
    'foto_3' => 'products/about_cake_coffee.png',
    'foto_4' => 'products/about_cake_showcase.png',
],
```

---

## 4. Hasil Verifikasi Sistem (Automated Headless Browser)

Pengujian komprehensif dilakukan menggunakan browser headless pada endpoint aplikasi:

| Skenario Pengujian | Target | Hasil Pengamatan | Status |
| :--- | :--- | :--- | :--- |
| **Katalog Homepage** | 8 Kartu Produk | 100% memuat gambar dari `/storage/products/about_cake_coffee.png`. Broken image = 0, Hardcoded Unsplash = 0. | **Lolos** |
| **Modal Preview: 1 Gambar** | Produk ID 5 | Thumbnail container disembunyikan (`display: none`), main image tampil penuh 420px. | **Lolos** |
| **Modal Preview: 2 Gambar** | Produk ID 6 | Thumbnail container tampil (`display: flex`), 2 thumbnail, panah scroll tersembunyi. | **Lolos** |
| **Modal Preview: 3 Gambar** | Produk ID 11 | Thumbnail container tampil (`display: flex`), 3 thumbnail, panah scroll tersembunyi. | **Lolos** |
| **Modal Preview: 4 Gambar** | Produk ID 4 | Thumbnail container tampil (`display: flex`), 4 thumbnail, panah atas/bawah aktif (`display: flex`). | **Lolos** |
| **Halaman Katalog Shop** | 11 Produk | 100% memuat gambar dari storage database. | **Lolos** |
| **Halaman Detail Produk** | Produk ID 4 | Galeri menampilkan 4 thumbnail foto dinamis dari database. | **Lolos** |

Tangkapan layar verifikasi modal dengan 4 thumbnail foto tersimpan di:
- `docs/modal_4_images_preview.png`

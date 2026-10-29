# Dokumentasi: Penyelarasan Tata Letak Badge Stok & Diskon Serta Prioritas Pengurutan Produk pada Kasir Offline (POS)

## 1. Latar Belakang & Masalah

Berdasarkan audit antarmuka pengguna (*Frontend Design Direction*) pada halaman **Kasir Offline** (`/admin/pos`):
1. **Penumpukan Badge (*Badge Stacking Clutter*) pada Foto Produk**:
   * Sebelumnya, badge ketersediaan stok (`Tersedia: 8` / `Sisa X`) dan badge diskon (`Hemat Rp 10.000` / `20% OFF`) ditumpuk secara vertikal di sudut kiri atas kartu (`top-0 start-0`).
   * Pada kartu produk berdiskon, teks diskon yang panjang menutupi hingga 40% area gambar kue. Dipadukan dengan tombol aksi `+ Stok` di sudut kanan atas, area atas foto menjadi sangat padat dan tidak rapi.
2. **Pemisahan Semantik Harga vs Diskon**:
   * Informasi diskon secara semantik adalah atribut kalkulasi harga, bukan status fisik barang. Menaruhnya di atas foto terpisah jauh dari nominal harga membuat mata kasir harus melompat dari atas ke bawah untuk memahami nominal penghematan.
3. **Produk Habis Bercampur di Urutan Teratas**:
   * Query `PosController@index` sebelumnya hanya menggunakan `$query->latest()->paginate(20)`. Akibatnya, produk yang baru ditambahkan namun sudah berstatus **Stok Habis** (`stok = 0`) muncul di baris teratas grid kasir, mendesak produk yang masih memiliki stok ke baris bawah atau halaman berikutnya.

---

## 2. Berkas yang Dimodifikasi

| No | Berkas | Tipe | Rincian Perubahan |
| :---: | :--- | :---: | :--- |
| 1 | `app/Http/Controllers/Admin/PosController.php` | Controller | Menambahkan logika pengurutan prioritas stok (`orderByRaw`) pada query produk sehingga produk berstok (`stok > 0`) selalu berada di urutan teratas, diikuti produk habis di urutan bawah. |
| 2 | `resources/views/Admin/kelola_penjualan/pos.blade.php` | Blade View & UI | Merapikan badge ketersediaan stok di sudut kiri atas foto dengan ikon pendukung (`mdi-check-circle-outline`, `mdi-clock-alert-outline`, `mdi-alert-circle-outline`), serta memindahkan badge diskon ke dalam area harga di body kartu dalam bentuk chip merah lembut (*soft-coral pill*). |
| 3 | `tests/Feature/ProductStockReductionTest.php` | Automated Test | Menambahkan pengujian otomatis `test_pos_page_prioritizes_products_with_stock` untuk memvalidasi bahwa produk berstok muncul lebih awal dibanding produk habis pada data view POS. |

---

## 3. Rincian Perubahan Kode (Before vs After)

### A. Controller: `app/Http/Controllers/Admin/PosController.php`

* **Sebelumnya (Hanya berdasarkan waktu pembuatan terbaru):**
  ```php
  $products = $query->latest()->paginate(20);
  $kategoris = Kategori::all();
  return view('Admin.kelola_penjualan.pos', compact('products', 'kategoris'));
  ```
* **Sesudahnya (Prioritas Stok > 0 di Urutan Teratas):**
  ```php
  // Prioritaskan produk yang memiliki stok (> 0) berada di urutan teratas
  $query->orderByRaw("CASE 
      WHEN LOWER(status_produk) = 'tersedia' THEN 1 
      WHEN status_produk IS NOT NULL AND status_produk != '' AND status_produk != '0' AND LOWER(status_produk) != 'habis' THEN 1 
      ELSE 0 
  END DESC")->latest();

  $products = $query->paginate(20);
  $kategoris = Kategori::all();
  return view('Admin.kelola_penjualan.pos', compact('products', 'kategoris'));
  ```

---

### B. View: `resources/views/Admin/kelola_penjualan/pos.blade.php`

#### 1. Perapian Area Atas Foto (Badge Stok di Kiri, Tombol `+ Stok` di Kanan)
* **Sebelumnya (Badge Stok & Diskon Bertumpuk di Kiri Atas):**
  ```blade
  <!-- Stock & Discount Badges -->
  <div class="position-absolute top-0 start-0 p-2 d-flex flex-column gap-1" style="z-index: 5;">
      @if($isOutOfStock)
          <span class="badge bg-danger text-white shadow-sm" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
              <i class="mdi mdi-alert-circle-outline me-1"></i>Stok Habis
          </span>
      @elseif($product->stok <= 5)
          <span class="badge bg-warning text-dark shadow-sm" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
              Sisa {{ $product->stok }}
          </span>
      @else
          <span class="badge bg-success text-white shadow-sm" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 500;">
              Tersedia: {{ $product->stok }}
          </span>
      @endif

      @if($hasDiscount && !$isOutOfStock)
          <span class="badge bg-danger text-white shadow-sm" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
              {{ $product->diskon_badge ?? 'Diskon' }}
          </span>
      @endif
  </div>
  ```
* **Sesudahnya (Hanya Badge Stok Tunggal yang Bersih & Ikonik):**
  ```blade
  <!-- Stock Status Badge (Top Left) -->
  <div class="position-absolute top-0 start-0 p-2" style="z-index: 5;">
      @if($isOutOfStock)
          <span class="badge bg-danger text-white shadow-sm d-inline-flex align-items-center" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600; letter-spacing: 0.2px;">
              <i class="mdi mdi-alert-circle-outline me-1"></i>Stok Habis
          </span>
      @elseif($product->stok <= 5)
          <span class="badge bg-warning text-dark shadow-sm d-inline-flex align-items-center" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600; letter-spacing: 0.2px;">
              <i class="mdi mdi-clock-alert-outline me-1"></i>Sisa {{ $product->stok }}
          </span>
      @else
          <span class="badge bg-success text-white shadow-sm d-inline-flex align-items-center" style="font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 500; letter-spacing: 0.2px;">
              <i class="mdi mdi-check-circle-outline me-1"></i>Tersedia: {{ $product->stok }}
          </span>
      @endif
  </div>
  ```

#### 2. Penyatuan Diskon Berdampingan dengan Harga pada Body Kartu
* **Sebelumnya (Hanya Harga dan Harga Coret Kecil):**
  ```blade
  <div class="d-flex align-items-baseline gap-2 mt-1">
      <span class="font-weight-bold text-primary" style="font-size: 14px;">Rp. {{ number_format($finalPrice, 0, ',', '.') }}</span>
      @if($hasDiscount)
          <small class="text-muted text-decoration-line-through" style="font-size: 11px;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</small>
      @endif
  </div>
  ```
* **Sesudahnya (Harga Final, Harga Coret, dan Chip Badge Diskon Terpadu):**
  ```blade
  <div class="mt-1">
      <div class="d-flex align-items-center flex-wrap gap-2">
          <span class="font-weight-bold text-primary" style="font-size: 14px;">Rp. {{ number_format($finalPrice, 0, ',', '.') }}</span>
          @if($hasDiscount)
              <small class="text-muted text-decoration-line-through" style="font-size: 11px;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</small>
              <span class="badge d-inline-flex align-items-center" style="font-size: 10px; font-weight: 600; padding: 2px 6px; border-radius: 4px; background-color: #fee2e2; color: #dc2626 !important; border: 1px solid #fca5a5;">
                  <i class="mdi mdi-tag-outline me-1" style="font-size: 11px;"></i>{{ $product->diskon_badge ?? 'Diskon' }}
              </span>
          @endif
      </div>
  </div>
  ```

---

### C. Automated Test: `tests/Feature/ProductStockReductionTest.php`

Menambahkan pengujian fitur untuk memastikan sorting prioritas stok di POS bekerja tanpa regresi:
```php
public function test_pos_page_prioritizes_products_with_stock()
{
    $kasir = User::create([
        'nama' => 'Kasir Priority Test',
        'email' => 'kasirpriority@zyhcorner.com',
        'no_hp' => '08123456782',
        'password' => bcrypt('password'),
        'role' => 'kasir',
    ]);

    $kategori = \App\Models\Kategori::create([
        'nama_kategori' => 'Kategori Priority',
        'gambar_kategori' => 'dummy.png',
    ]);

    // Dibuat baru tetapi stok 0
    $outOfStock = Produk::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_produk' => 'Kue Habis Baru',
        'harga' => 30000,
        'status_produk' => '0',
        'jenis_produk' => 'showcase',
        'created_at' => now(),
    ]);

    // Dibuat lebih lama tetapi stok 10
    $inStock = Produk::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_produk' => 'Kue Tersedia Lama',
        'harga' => 40000,
        'status_produk' => '10',
        'jenis_produk' => 'showcase',
        'created_at' => now()->subDay(),
    ]);

    $response = $this->actingAs($kasir)->get(route('pos.index'));
    $response->assertStatus(200);

    // Produk yang memiliki stok wajib muncul terlebih dahulu
    $products = $response->viewData('products');
    $this->assertEquals($inStock->id_produk, $products->first()->id_produk);
    $this->assertEquals($outOfStock->id_produk, $products->last()->id_produk);

    $response->assertSee('Tersedia: 10');
    $response->assertSee('Stok Habis');
}
```

---

## 4. Hasil Verifikasi & Uji Regresi

1. **Blade Views Compilation**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Sukses (`Compiled views cleared successfully.` & `Blade templates cached successfully.`).

2. **Automated Feature Test**:
   ```bash
   php artisan test --filter=ProductStockReductionTest
   ```
   *Hasil:* **8 passed (33 assertions)**.

3. **Full Regression Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil:* **147 passed (741 assertions)** (100% *Green* tanpa kegagalan).

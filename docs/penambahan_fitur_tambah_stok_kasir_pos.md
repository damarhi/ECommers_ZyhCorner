# Dokumentasi: Penambahan Tombol & Modal Form Tambah Stok Produk pada Halaman Kasir Offline (POS)

## 1. Latar Belakang & Analisis Kebutuhan

Pada operasional toko fisik Zyh Corner:
* Kasir melayani transaksi langsung di etalase kue (*showcase*). Produk kue basah, roti, dan bolu sering kali baru selesai dipanggang (*freshly baked*) dan langsung didistribusikan ke etalase toko saat jam operasional berlangsung.
* **Masalah Sebelumnya**:
  1. Jika stok produk di kasir habis (`stok = 0`), kartu produk terkunci nonaktif (*disabled*) dan sistem POS menolak transaksi dengan pesan error 400.
  2. Untuk menambah stok produk tersebut, kasir atau admin sebelumnya **harus meninggalkan halaman kasir POS**, membuka menu Kelola Produk Showcase (`/admin/products`), mencari produk, membuka form edit, mengisi angka stok baru, lalu kembali ke halaman POS.
  3. Alur panjang ini sangat tidak efisien dan memperlambat pelayanan antrean kasir.
* **Solusi**: Menyediakan tombol aksi cepat **`+ Stok`** pada setiap kartu produk dan tombol **`Restock`** pada kartu produk yang habis, yang membuka modal formulir penambahan stok interaktif secara instan tanpa perlu meninggalkan halaman kasir.

---

## 2. Berkas yang Dimodifikasi

| No | Berkas | Tipe | Rincian Perubahan |
| :---: | :--- | :---: | :--- |
| 1 | `routes/web.php` | Routing | Mendaftarkan rute `POST admin/pos/products/{id}/add-stock` dengan middleware `auth`, `role:admin,kasir`, dan `preventBackHistory`. |
| 2 | `app/Http/Controllers/Admin/PosController.php` | Controller | Menambahkan method `addStock()` untuk memvalidasi input stok tambahan, menghitung total stok baru, memperbarui tanggal kadaluarsa (opsional), dan mengembalikan respons JSON/redirect. |
| 3 | `resources/views/Admin/kelola_penjualan/pos.blade.php` | Blade View & JS | Menambahkan tombol pil `+ Stok` di pojok kanan atas setiap kartu produk, tombol `Restock` pada kartu stok habis, modal form dinamis `#addStockModal` dengan kalkulator estimasi stok dan pil cepat (+5, +10, +20, +50), serta skrip AJAX. |
| 4 | `tests/Feature/ProductStockReductionTest.php` | Automated Test | Menambahkan 2 unit/feature test: `test_kasir_can_add_stock_from_pos_modal` dan `test_add_stock_from_pos_validates_input`. |

---

## 3. Rincian Perubahan Kode (Before vs After)

### A. `routes/web.php`
* **Sebelumnya:**
  ```php
  Route::get('admin/pos', [PosController::class, 'index'])->name('pos.index');
  Route::post('admin/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
  ```
* **Sesudahnya:**
  ```php
  Route::get('admin/pos', [PosController::class, 'index'])->name('pos.index');
  Route::post('admin/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
  Route::post('admin/pos/products/{id}/add-stock', [PosController::class, 'addStock'])->name('pos.add-stock');
  ```

---

### B. `app/Http/Controllers/Admin/PosController.php`
* **Menambahkan method `addStock()`:**
  ```php
  public function addStock(Request $request, $id)
  {
      $request->validate([
          'additional_stock' => 'required|integer|min:1|max:99999',
          'tanggal_expired' => 'nullable|date',
      ]);

      $product = Produk::findOrFail($id);

      $currentStock = $product->stok;
      $additional = (int) $request->input('additional_stock');
      $newStock = $currentStock + $additional;

      $product->status_produk = (string) $newStock;

      if ($request->filled('tanggal_expired')) {
          $product->tanggal_expired = $request->input('tanggal_expired');
      }

      $product->save();

      if ($request->ajax() || $request->wantsJson()) {
          return response()->json([
              'success' => true,
              'message' => "Stok produk '{$product->nama_produk}' berhasil ditambah {$additional} item (Total stok: {$newStock}).",
              'new_stock' => $newStock,
              'product_id' => $product->id_produk,
          ]);
      }

      return redirect()->back()->with('success', "Stok produk '{$product->nama_produk}' berhasil diperbarui.");
  }
  ```

---

### C. `resources/views/Admin/kelola_penjualan/pos.blade.php`

#### 1. Tombol `+ Stok` di Pojok Kanan Atas Kartu
```blade
<!-- Quick Restock Button (Top Right) -->
<div class="position-absolute top-0 end-0 p-2" style="z-index: 5;">
    <button type="button" 
            class="btn btn-sm btn-white text-primary shadow-sm rounded-pill d-inline-flex align-items-center px-2 py-1 btn-quick-stock" 
            onclick="openAddStockModal({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $product->stok }}, '{{ $product->tanggal_expired ?? '' }}', '{{ $productImg }}')"
            title="Tambah Stok Produk"
            style="font-size: 11px; font-weight: 600; background: rgba(255,255,255,0.95); border: 1px solid rgba(0,0,0,0.08); gap: 3px; cursor: pointer;">
        <i class="mdi mdi-plus-box-outline text-success" style="font-size: 14px;"></i> + Stok
    </button>
</div>
```

#### 2. Tombol `Restock` pada Overlay Kartu Habis
```blade
@if($isOutOfStock)
    <button class="btn btn-secondary btn-sm px-3 mb-2 disabled" style="cursor: not-allowed; opacity: 0.9;" disabled>
        <i class="mdi mdi-cancel me-1"></i>Stok Habis
    </button>
    <button type="button" class="btn btn-success btn-sm px-3 shadow-sm font-weight-500" onclick="openAddStockModal({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $product->stok }}, '{{ $product->tanggal_expired ?? '' }}', '{{ $productImg }}')">
        <i class="mdi mdi-package-variant-plus me-1"></i>Restock
    </button>
@else
```

#### 3. Modal Form Tambah Stok Interaktif (`#addStockModal`)
Modal dinamis tunggal di bagian bawah halaman yang memuat:
* **Pratinjau Produk**: Gambar, nama produk, dan badge jumlah stok saat ini (*0 Pcs (Habis)* / *N Pcs*).
* **Input Jumlah Tambahan**: Input numerik dengan tombol cepat (*quick pills*) **`+5`**, **`+10`**, **`+20`**, **`+50`**.
* **Kalkulator Otomatis**: Kotak info biru yang menghitung langsung estimasi stok baru: `Stok saat ini + Tambahan = Total Baru`.
* **Field Tanggal Kadaluarsa (Opsional)**: Untuk memperbarui tanggal kadaluarsa batch kue yang baru dimasak.
* **Proses AJAX**: Menyimpan data di latar belakang dengan indikator *loading*, SweetAlert sukses, lalu memuat ulang halaman secara bersih.

---

### D. `tests/Feature/ProductStockReductionTest.php`

Menambahkan unit test untuk memastikan fitur restock bekerja sempurna dan tervalidasi:
```php
public function test_kasir_can_add_stock_from_pos_modal()
{
    $kasir = User::create([
        'nama' => 'Kasir Restock Test',
        'email' => 'kasirrestock@zyhcorner.com',
        'no_hp' => '08123456784',
        'password' => bcrypt('password'),
        'role' => 'kasir',
    ]);

    $kategori = \App\Models\Kategori::create([
        'nama_kategori' => 'Kue Basah',
        'gambar_kategori' => 'dummy.png',
    ]);

    $product = Produk::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_produk' => 'Bolu Gulung',
        'harga' => 35000,
        'status_produk' => '5',
        'jenis_produk' => 'showcase',
        'tanggal_expired' => '2026-11-01',
    ]);

    $response = $this->actingAs($kasir)->postJson(route('pos.add-stock', $product->id_produk), [
        'additional_stock' => 15,
        'tanggal_expired' => '2026-11-05',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'new_stock' => 20,
        'product_id' => $product->id_produk,
    ]);

    $product->refresh();
    $this->assertEquals(20, $product->stok);
    $this->assertEquals('2026-11-05', $product->tanggal_expired);
}

public function test_add_stock_from_pos_validates_input()
{
    $kasir = User::create([
        'nama' => 'Kasir Invalid Restock',
        'email' => 'kasirinvalid@zyhcorner.com',
        'no_hp' => '08123456783',
        'password' => bcrypt('password'),
        'role' => 'kasir',
    ]);

    $kategori = \App\Models\Kategori::create([
        'nama_kategori' => 'Kue Kering',
        'gambar_kategori' => 'dummy.png',
    ]);

    $product = Produk::create([
        'id_kategori' => $kategori->id_kategori,
        'nama_produk' => 'Kue Nastar',
        'harga' => 45000,
        'status_produk' => '2',
        'jenis_produk' => 'showcase',
    ]);

    $response = $this->actingAs($kasir)->postJson(route('pos.add-stock', $product->id_produk), [
        'additional_stock' => 0,
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['additional_stock']);
}
```

---

## 4. Hasil Verifikasi & Pengujian

1. **Blade Views Compilation**:
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Berhasil (`Blade templates cached successfully`).

2. **Automated Feature Test**:
   ```bash
   php artisan test --filter=ProductStockReductionTest
   ```
   *Hasil:* **7 passed (28 assertions)**.

3. **Full Regression Test Suite**:
   ```bash
   php artisan test
   ```
   *Hasil:* **146 passed (736 assertions)** (100% *Green*).

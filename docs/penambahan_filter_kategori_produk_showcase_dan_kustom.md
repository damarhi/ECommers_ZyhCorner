# Penambahan Fitur Filter Kategori pada Produk Showcase dan Produk Kustom

## 1. Ringkasan Eksekutif

Fitur penyaringan (filter) pada halaman **Produk Showcase** (`/admin/products`) dan **Produk Kustom** (`/admin/products/custom`) telah ditingkatkan dengan menambahkan **Filter Kategori Produk**.

Sebelumnya, dropdown filter pada kedua halaman tersebut hanya menyediakan opsi penyaringan berdasarkan *Status Stok* (Tersedia / Habis) dan *Fitur Tambahan* (Sedang Diskon / Produk Terbaru), sedangkan penyaringan kategori belum tersedia di level controller maupun dropdown tampilan. 

Kini, admin dapat memfilter produk berdasarkan satu atau beberapa kategori sekaligus (multi-checkbox), dengan counter badge yang menghitung total filter aktif secara otomatis, tautan pagination yang mempertahankan query string kategori (`kategori[]`), dan tombol reset netral yang mengembalikan tampilan ke seluruh kategori.

---

## 2. Berkas yang Diubah

| No | Berkas | Deskripsi Perubahan |
|---|---|---|
| 1 | `app/Http/Controllers/Admin/ProdukController.php` | Menambahkan pemrosesan filter `kategori` (`whereIn('id_kategori', ...)`) pada method `index` dan `customIndex`, serta mengirimkan `$kategoris` ke view. |
| 2 | `resources/views/Admin/kelola_produk/products.blade.php` | Memperbarui perhitungan `$activeFilterCount`, menambahkan seksi checkbox kategori scrollable di dalam `#filterDropdown`, dan menyesuaikan lebar dropdown menjadi 250px. |
| 3 | `resources/views/Admin/kelola_produk/custom_products.blade.php` | Memperbarui perhitungan `$activeFilterCount`, menambahkan seksi checkbox kategori scrollable di dalam `#filterDropdown`, dan menyesuaikan lebar dropdown menjadi 250px. |
| 4 | `tests/Feature/AdminFilterStandardizationTest.php` | Menambahkan unit/feature test otomatis untuk memverifikasi fungsionalitas filter kategori showcase dan produk kustom. |

---

## 3. Rincian Perubahan Kode

### 3.1. `app/Http/Controllers/Admin/ProdukController.php`

Pada method `index()` dan `customIndex()`:
- Menambahkan logika filter `whereIn('id_kategori', $kategoriIds)` jika parameter `kategori` dikirimkan (baik berupa array checkbox maupun nilai tunggal).
- Mengambil seluruh daftar kategori terurut alfabetis (`Kategori::orderBy('nama_kategori', 'asc')->get()`) dan meneruskannya ke view melalui `compact('produks', 'kategoris')`.

```php
// app/Http/Controllers/Admin/ProdukController.php (method index)

public function index(Request $request)
{
    Produk::checkAndEmptyExpiredProducts();

    $query = Produk::with(['kategori', 'diskonAktif'])
        ->where(function($q) {
            $q->where('jenis_produk', 'showcase')
              ->orWhereNull('jenis_produk');
        });

    if ($request->has('search') && $request->search != '') {
        $searchTerm = '%' . $request->search . '%';
        $query->where(function($q) use ($searchTerm) {
            $q->where('nama_produk', 'like', $searchTerm)
              ->orWhere('deskripsi', 'like', $searchTerm);
        });
    }

    // --- TAMBAHAN FILTER KATEGORI ---
    if ($request->has('kategori') && !empty($request->kategori)) {
        $kategoriIds = is_array($request->kategori) ? $request->kategori : [$request->kategori];
        $query->whereIn('id_kategori', $kategoriIds);
    }

    if ($request->has('status') && is_array($request->status)) {
        $query->whereIn('status_produk', $request->status);
    }

    if ($request->has('diskon')) {
        $query->whereHas('diskonAktif');
    }

    if ($request->has('terbaru')) {
        $query->latest();
    } else {
        $query->latest();
    }

    $produks = $query->paginate(10)->withQueryString();
    $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
    return view('Admin.kelola_produk.products', compact('produks', 'kategoris'));
}
```

Logika yang identik juga diterapkan pada `customIndex()` untuk memastikan halaman produk kustom mendapatkan kemampuan filter kategori yang sama:

```php
// app/Http/Controllers/Admin/ProdukController.php (method customIndex)

public function customIndex(Request $request)
{
    $query = Produk::with(['kategori', 'diskonAktif'])
        ->where('jenis_produk', 'kustom');

    if ($request->has('search') && $request->search != '') {
        $searchTerm = '%' . $request->search . '%';
        $query->where(function($q) use ($searchTerm) {
            $q->where('nama_produk', 'like', $searchTerm)
              ->orWhere('deskripsi', 'like', $searchTerm);
        });
    }

    // --- TAMBAHAN FILTER KATEGORI ---
    if ($request->has('kategori') && !empty($request->kategori)) {
        $kategoriIds = is_array($request->kategori) ? $request->kategori : [$request->kategori];
        $query->whereIn('id_kategori', $kategoriIds);
    }

    if ($request->has('status') && is_array($request->status)) {
        $query->whereIn('status_produk', $request->status);
    }

    if ($request->has('diskon')) {
        $query->whereHas('diskonAktif');
    }

    if ($request->has('terbaru')) {
        $query->latest();
    } else {
        $query->latest();
    }

    $produks = $query->paginate(10)->withQueryString();
    $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
    return view('Admin.kelola_produk.custom_products', compact('produks', 'kategoris'));
}
```

---

### 3.2. `resources/views/Admin/kelola_produk/products.blade.php`

1. **Pembaruan Kalkulasi `$activeFilterCount`**:
   Menghitung jumlah kategori yang dipilih ke dalam total filter aktif:
   ```php
   @php
       $activeFilterCount = (is_array(request('status')) ? count(request('status')) : (request('status') ? 1 : 0))
           + (is_array(request('kategori')) ? count(request('kategori')) : (request('kategori') ? 1 : 0))
           + (request('diskon') ? 1 : 0)
           + (request('terbaru') ? 1 : 0);
       $hasActiveFilters = $activeFilterCount > 0 || request('search');
   @endphp
   ```

2. **Penambahan Seksi Kategori di Dropdown Menu**:
   Dropdown menu diperlebar menjadi `250px` dengan scroll vertikal otomatis (`max-height: 180px; overflow-y: auto;`) agar daftar kategori panjang tetap rapi dan tidak merusak layout viewport:
   ```html
   <div class="dropdown-menu p-3 shadow-lg border-0" aria-labelledby="filterDropdown"
       style="width: 250px; border-radius: 10px; margin-top: 6px; z-index: 1050;">
       @if(isset($kategoris) && $kategoris->count() > 0)
           <h6 class="dropdown-header px-0 mb-2 text-primary"
               style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Kategori Produk</h6>
           <div style="max-height: 180px; overflow-y: auto;" class="mb-3">
               @foreach($kategoris as $kategori)
               <div class="form-check mb-2">
                   <label class="form-check-label text-muted" style="cursor: pointer; font-size: 13px;">
                       <input type="checkbox" name="kategori[]" value="{{ $kategori->id_kategori }}"
                           class="form-check-input"
                           {{ is_array(request('kategori')) && in_array($kategori->id_kategori, request('kategori')) ? 'checked' : '' }}>
                       {{ $kategori->nama_kategori }}
                   </label>
               </div>
               @endforeach
           </div>

           <div class="dropdown-divider mb-3"></div>
       @endif

       <h6 class="dropdown-header px-0 mb-2 text-primary" ...>Status Stok</h6>
       ...
   ```

---

### 3.3. `resources/views/Admin/kelola_produk/custom_products.blade.php`

Menerapkan pembaruan yang sama pada halaman Produk Kustom:
- Update perhitungan `$activeFilterCount` menyertakan `request('kategori')`.
- Menambahkan blok loop `$kategoris` dengan checkbox `name="kategori[]"`, state `checked` otomatis, dan styling scrollable list yang konsisten.

---

### 3.4. `tests/Feature/AdminFilterStandardizationTest.php`

Menambahkan dua pengujian otomatis:
1. `test_products_filter_by_category()`: Memastikan produk showcase tersaring akurat berdasarkan kategori yang dipilih, produk di luar kategori tidak tampil, badge counter aktif muncul, dan tombol reset tersedia.
2. `test_custom_products_filter_by_category()`: Memastikan produk kustom tersaring akurat sesuai kategori yang dipilih.

```php
public function test_products_filter_by_category(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $kat1 = Kategori::create(['nama_kategori' => 'Kue Kering', 'gambar_kategori' => 'kue_kering.jpg']);
    $kat2 = Kategori::create(['nama_kategori' => 'Minuman', 'gambar_kategori' => 'minuman.jpg']);

    Produk::create([
        'id_kategori' => $kat1->id_kategori,
        'nama_produk' => 'Nastar Keju Spesial',
        'harga' => 65000,
        'jenis_produk' => 'showcase',
        'status_produk' => 'Tersedia',
    ]);

    Produk::create([
        'id_kategori' => $kat2->id_kategori,
        'nama_produk' => 'Es Teh Manis Segar',
        'harga' => 10000,
        'jenis_produk' => 'showcase',
        'status_produk' => 'Tersedia',
    ]);

    $response = $this->actingAs($admin)->get(route('products.index', [
        'kategori' => [$kat1->id_kategori],
    ]));

    $response->assertStatus(200);
    $response->assertSee('Nastar Keju Spesial');
    $response->assertDontSee('Es Teh Manis Segar');
    $response->assertSee('admin-filter-badge');
    $response->assertSee('Reset');
}

public function test_custom_products_filter_by_category(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $kat1 = Kategori::create(['nama_kategori' => 'Kue Pengantin', 'gambar_kategori' => 'wedding.jpg']);
    $kat2 = Kategori::create(['nama_kategori' => 'Snack Box', 'gambar_kategori' => 'snack.jpg']);

    Produk::create([
        'id_kategori' => $kat1->id_kategori,
        'nama_produk' => 'Custom Wedding Cake 3 Tingkat',
        'harga' => 1500000,
        'jenis_produk' => 'kustom',
        'status_produk' => 'Tersedia',
    ]);

    Produk::create([
        'id_kategori' => $kat2->id_kategori,
        'nama_produk' => 'Custom Box Katering Mini',
        'harga' => 35000,
        'jenis_produk' => 'kustom',
        'status_produk' => 'Tersedia',
    ]);

    $response = $this->actingAs($admin)->get(route('products.custom', [
        'kategori' => [$kat1->id_kategori],
    ]));

    $response->assertStatus(200);
    $response->assertSee('Custom Wedding Cake 3 Tingkat');
    $response->assertDontSee('Custom Box Katering Mini');
    $response->assertSee('admin-filter-badge');
    $response->assertSee('Reset');
}
```

---

## 4. Hasil Verifikasi

1. **Uji Otomatisasi (Test Suite)**:
   ```bash
   php artisan test && git clean -fd public/uploads/
   ```
   **Hasil**: **218 passed** (1173 assertions), durasi ~22 detik, **100% lulus tanpa ada satupun kegagalan/regresi**.
   
2. **Kompilasi Aset (Vite Build)**:
   ```bash
   npm run build
   ```
   **Hasil**: Berhasil (`✓ built in 679ms`), tidak ada aset atau styling CSS yang rusak.

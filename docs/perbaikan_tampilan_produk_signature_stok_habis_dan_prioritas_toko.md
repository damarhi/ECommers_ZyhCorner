# Laporan Eksekusi: Perbaikan Tampilan Produk Signature (Stok Habis & Prioritas Ketersediaan)

## 1. Latar Belakang & Akar Masalah (*Root Cause*)

Setelah penambahan fitur kurasi "Zyh Signatures", produk yang telah ditandai sebagai **Signature** oleh Admin di dasbor kelola produk (seperti *Butter Croissant Paris*, *Pain Au Chocolat*, dan *Black Forest Cake Classic*) tidak muncul di beranda pada kolom **Zyh Signatures**.

Setelah dilakukan inspeksi basis data dan kode controller:
1. **Kondisi Data**: Ketiga produk yang ditandai Signature memiliki nilai `status_produk = '0'` (stok sedang kosong/habis).
2. **Klausa Query Terlalu Kaku**: Pada `app/Http/Controllers/Pelanggan/PelangganController.php`, query pengambilan produk signature menggunakan:
   ```php
   ->where('status_produk', '!=', '0')
   ```
   Klausa ini secara otomatis mendiskualifikasi seluruh produk signature yang stoknya sedang 0.
3. **Kegagalan Fallback Penuh**: Query *fallback* pelengkap 3 kartu juga menyaring `status_produk != '0'`. Karena hanya ada 2 produk etalase showcase di database yang memiliki stok (`Cheese Sago Cookies` stok 6 dan `Chocolate Almond Biscotti` stok 10), kolom beranda hanya menampilkan 2 kartu tanpa ada satu pun lencana signature.

---

## 2. Rincian Perubahan Kode

### A. Controller Pelanggan (`app/Http/Controllers/Pelanggan/PelangganController.php`)

Menghapus filter kaku `status_produk != '0'` pada kurasi produk signature maupun produk pengisi (*fallback*). Menambahkan pengurutan cerdas ketersediaan stok (*Smart Stock Prioritization*) yang kompatibel lintas database (MySQL & SQLite):

```php
// SEBELUM:
$signatureProducts = Produk::with(['kategori', 'diskonAktif'])
    ->where('is_signature', true)
    ->where(function($q) {
        $q->where('jenis_produk', 'showcase')->orWhereNull('jenis_produk');
    })
    ->where('status_produk', '!=', '0') // MENGELIMINASI PRODUK STOK 0
    ->orderBy('id_produk', 'desc')
    ->limit(3)
    ->get();

// SESUDAH:
// Zyh Signatures: Curated master recipes with smart fallback
$signatureProducts = Produk::with(['kategori', 'diskonAktif'])
    ->where('is_signature', true)
    ->where(function($q) {
        $q->where('jenis_produk', 'showcase')
          ->orWhereNull('jenis_produk');
    })
    ->orderByRaw("CASE
        WHEN LOWER(status_produk) = 'tersedia' THEN 1
        WHEN status_produk IS NOT NULL AND status_produk != '' AND status_produk != '0' AND LOWER(status_produk) != 'habis' THEN 1
        ELSE 0
    END DESC")
    ->orderBy('id_produk', 'desc')
    ->limit(3)
    ->get();

if ($signatureProducts->count() < 3) {
    $excludeIds = $signatureProducts->pluck('id_produk')->toArray();
    $fillers = Produk::with(['kategori', 'diskonAktif'])
        ->whereNotIn('id_produk', $excludeIds)
        ->where(function($q) {
            $q->where('jenis_produk', 'showcase')
              ->orWhereNull('jenis_produk');
        })
        ->orderByRaw("CASE
            WHEN LOWER(status_produk) = 'tersedia' THEN 1
            WHEN status_produk IS NOT NULL AND status_produk != '' AND status_produk != '0' AND LOWER(status_produk) != 'habis' THEN 1
            ELSE 0
        END DESC")
        ->orderBy('harga', 'desc')
        ->orderBy('id_produk', 'desc')
        ->limit(3 - $signatureProducts->count())
        ->get();

    $signatureProducts = $signatureProducts->concat($fillers);
}
$topRated = $signatureProducts;
```

---

### B. Tampilan Beranda Pelanggan (`resources/views/Pelanggan/home.blade.php`)

Menangani kondisi lencana ganda (*dual-badge*) ketika produk berstatus **Signature** namun stoknya sedang habis atau memiliki diskon:

1. **Kolom Kartu Kecil (Zyh Signatures, Best Seller, Hot Deals)**:
   * Lencana mahkota emas `.signature-badge` diposisikan di sudut kiri atas thumbnail (`top: 4px; left: 4px;`).
   * Jika stok habis, lencana hitam `out-of-stock` ("Habis") diposisikan di sudut kanan bawah (`bottom: 4px; right: 4px;`).
   * Jika memiliki diskon, lencana diskon diposisikan di sudut kanan bawah (`bottom: 4px; right: 4px;`).
   * Tombol aksi: Tombol keranjang belanja otomatis dinonaktifkan (`disabled`, ikon `fa-ban`), sedangkan tombol Quick View (ikon mata `fa-regular fa-eye`) tetap aktif agar pelanggan dapat melihat rincian mahakarya kue tersebut.

2. **Featured Products & Newest Products**:
   * Lencana `.signature-badge` ditampilkan di sudut kiri atas (`top: 10px; left: 10px;`).
   * Lencana status stok atau diskon diletakkan di bawah lencana signature (`top: 38px; left: 10px;`), mencegah benturan visual dengan tombol quick view di kanan atas.

---

### C. Pengujian Otomatis (`tests/Feature/SignatureProductsTest.php`)

Menambahkan pengujian baru untuk memastikan produk signature yang stoknya 0 tetap muncul dan produk dengan stok tersedia selalu diprioritaskan di atas:

```php
public function test_signature_products_are_displayed_even_when_out_of_stock_and_prioritizes_stock(): void
{
    $kategori = Kategori::create([
        'nama_kategori' => 'Artisan Pastry',
        'gambar_kategori' => 'test.jpg'
    ]);

    // 2 produk signature dengan stok 0
    $sigOutOfStock1 = Produk::create([
        'nama_produk' => 'Butter Croissant Paris',
        'id_kategori' => $kategori->id_kategori,
        'jenis_produk' => 'showcase',
        'harga' => 22000,
        'status_produk' => '0',
        'is_signature' => true,
    ]);

    $sigOutOfStock2 = Produk::create([
        'nama_produk' => 'Pain Au Chocolat',
        'id_kategori' => $kategori->id_kategori,
        'jenis_produk' => 'showcase',
        'harga' => 26000,
        'status_produk' => '0',
        'is_signature' => true,
    ]);

    // 1 produk signature dengan stok tersedia
    $sigInStock = Produk::create([
        'nama_produk' => 'Black Forest Cake Classic',
        'id_kategori' => $kategori->id_kategori,
        'jenis_produk' => 'showcase',
        'harga' => 125000,
        'status_produk' => '5',
        'is_signature' => true,
    ]);

    $response = $this->get(route('home'));
    $response->assertStatus(200);

    $signatureProducts = $response->viewData('signatureProducts');
    $this->assertCount(3, $signatureProducts);

    // Produk dengan stok > 0 harus di posisi terdepan
    $this->assertEquals('Black Forest Cake Classic', $signatureProducts->first()->nama_produk);

    // Produk stok 0 tetap terdaftar
    $names = $signatureProducts->pluck('nama_produk')->toArray();
    $this->assertContains('Butter Croissant Paris', $names);
    $this->assertContains('Pain Au Chocolat', $names);

    // Tampilan merender nama produk dan lencana Habis
    $response->assertSee('Butter Croissant Paris');
    $response->assertSee('Pain Au Chocolat');
    $response->assertSee('Black Forest Cake Classic');
    $response->assertSee('Habis');
}
```

---

## 3. Hasil Pengujian & Verifikasi

```text
PASS Tests\Feature\SignatureProductsTest
✓ admin can create product with signature flag
✓ admin can update product signature flag
✓ homepage displays zyh signatures column and badge
✓ signature products uses smart fallback when less than three
✓ signature products are displayed even when out of stock and prioritizes stock

Tests: 5 passed (24 assertions)
Full Suite: 207 passed (1122 assertions) 100% PASS
```
Semua 207 test cases pada sistem berjalan sukses dengan **zero regression**.
Kolom Zyh Signatures pada beranda kini dipastikan menampilkan produk resep andalan yang ditandai admin dengan susunan kartu simetris dan rapi.

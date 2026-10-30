# Laporan Eksekusi Fitur Pencarian Berdasarkan Nama Produk pada Halaman Data Seluruh Penjualan

**Tanggal**: 30 Oktober 2026  
**Status**: Selesai & Terverifikasi Penuh (*TDD Red-to-Green, Headless Browser Smoke Tested, Full Test Suite 100% Green*)  
**Alur Eksekusi**: *Ask Matt Flow (Idea → Ship via TDD & Code Verification)*

---

## 1. Latar Belakang & Analisis Masalah

Sebelum pembaruan ini, searchbar pada halaman **Data Seluruh Penjualan** (`/admin/all-sales`) hanya dapat mencari transaksi berdasarkan:
- Nomor pesanan (`order_number`)
- Nama pembeli (`customer_name`)
- Email pembeli (`email_pembeli`)

Admin kasir dan pemilik toko tidak dapat mencari transaksi berdasarkan nama kue atau produk yang dibeli (misalnya mencari `"Black Forest"`, `"Red Velvet"`, atau `"Matcha"`). Keterbatasan ini terjadi pada dua tingkatan sistem (*two-tier search limitation*):

1. **Server-Side Search (Backend - `PesananController`)**:
   Klausa pencarian Eloquent hanya memeriksa 3 field teks biasa (`order_number`, `customer_name`, `email_pembeli`), sementara rincian produk tersimpan dalam kolom JSON array `items` pada tabel `pesanans`.
2. **Client-Side Live Instant Search (Frontend - `all_sales.blade.php` + `live-instant-search.js`)**:
   Atribut `data-search` pada tag `<tr>` tidak memuat nama-nama produk pesanan, sehingga pengetikan instan (*real-time typing*) pada searchbar tidak dapat menyaring produk secara langsung di sisi peramban.

---

## 2. Berkas yang Diubah

| No | Berkas | Lokasi Kode | Ringkasan Perubahan |
| :--- | :--- | :--- | :--- |
| 1 | `app/Http/Controllers/Admin/PesananController.php` | Method `allSales()`, `salesReport()`, dan `exportAllSales()` | Menambahkan klausul `->orWhere('items', 'like', $searchTerm)` sehingga pencarian berbasis server memeriksa isi JSON produk di database. |
| 2 | `resources/views/Admin/kelola_penjualan/all_sales.blade.php` | Baris 95–97 & 120–127 | - Memperbarui placeholder input menjadi `"Cari pesanan, pelanggan, atau produk..."`.<br>- Mengekstrak nama-nama produk dari `$order->items` ke dalam atribut `data-search` pada tag `<tr>`. |
| 3 | `tests/Feature/AdminOrderTest.php` | Method `test_admin_can_search_all_sales_by_product_name()` | Menambahkan unit/feature test TDD untuk memastikan pencarian produk berjalan akurat baik di database maupun di antarmuka HTML. |
| 4 | `tests/Feature/LiveInstantSearchTest.php` | Method `test_admin_tables_render_instant_search_attributes()` | Memperluas assertion untuk memverifikasi atribut `data-search` pada tabel Seluruh Penjualan memuat nama produk dalam huruf kecil. |
| 5 | `docs/eksekusi_pencarian_nama_produk_seluruh_penjualan.md` | Seluruh berkas | Dokumentasi teknis komprehensif terkait perubahan kode dan hasil pengujian. |

---

## 3. Rincian Perubahan Kode

### A. Backend Controller: `app/Http/Controllers/Admin/PesananController.php`

Pada method `allSales()` (baris 243–251):
```diff
         if ($request->has('search') && $request->search != '') {
             $searchTerm = '%' . $request->search . '%';
             $query->where(function($q) use ($searchTerm) {
                 $q->where('order_number', 'like', $searchTerm)
                   ->orWhere('customer_name', 'like', $searchTerm)
-                  ->orWhere('email_pembeli', 'like', $searchTerm);
+                  ->orWhere('email_pembeli', 'like', $searchTerm)
+                  ->orWhere('items', 'like', $searchTerm);
             });
         }
```

Hal yang sama juga diselaraskan pada method `salesReport()` (baris 332–340) dan `exportAllSales()` (baris 598–606) agar pencarian produk tetap konsisten saat admin mencetak laporan penjualan atau mengekspor CSV.

---

### B. Frontend Blade View: `resources/views/Admin/kelola_penjualan/all_sales.blade.php`

1. **Placeholder Input Search**:
```diff
                             <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                 <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
-                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari transaksi penjualan..."
+                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari pesanan, pelanggan, atau produk..."
                                     style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                 <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                             </div>
```

2. **Ekstraksi Nama Produk ke `data-search`**:
```diff
                             <tbody>
                                 @forelse($sales as $order)
-                                <tr data-search="{{ strtolower($order->order_number . ' ' . $order->customer_name . ' ' . $order->jenis_penjualan . ' ' . $order->metode_pembayaran . ' ' . $order->status_pembayaran . ' ' . $order->status_pesanan) }}">
+                                @php
+                                    $productNames = collect(is_array($order->items) ? $order->items : [])
+                                        ->map(fn($item) => $item['nama_produk'] ?? $item['name'] ?? '')
+                                        ->filter()
+                                        ->implode(' ');
+                                @endphp
+                                <tr data-search="{{ strtolower($order->order_number . ' ' . $order->customer_name . ' ' . $productNames . ' ' . $order->jenis_penjualan . ' ' . $order->metode_pembayaran . ' ' . $order->status_pembayaran . ' ' . $order->status_pesanan) }}">
```

---

### C. Feature Tests: `tests/Feature/AdminOrderTest.php`

```php
    public function test_admin_can_search_all_sales_by_product_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Order 1 with "Black Forest Cake Classic" (using nama_produk key)
        $order1 = Pesanan::create([
            'order_number' => '#ORD-BF-01',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Budi Santoso',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Cash',
            'total' => 125000,
            'items' => [
                ['id' => 1, 'nama_produk' => 'Black Forest Cake Classic', 'price' => 125000, 'qty' => 1]
            ]
        ]);

        // Order 2 with "Red Velvet Special Cream Cheese" (using name key)
        $order2 = Pesanan::create([
            'order_number' => '#ORD-RV-02',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Siti Aminah',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Cash',
            'total' => 140000,
            'items' => [
                ['id' => 2, 'name' => 'Red Velvet Special Cream Cheese', 'price' => 140000, 'qty' => 1]
            ]
        ]);

        // 1. Search by "Black Forest"
        $response = $this->actingAs($admin)->get(route('all-sales.index', ['search' => 'Black Forest']));
        $response->assertStatus(200);
        $response->assertSee('#ORD-BF-01');
        $response->assertDontSee('#ORD-RV-02');
        $response->assertSee('black forest cake classic');

        // 2. Search by "Red Velvet"
        $response2 = $this->actingAs($admin)->get(route('all-sales.index', ['search' => 'Red Velvet']));
        $response2->assertStatus(200);
        $response2->assertSee('#ORD-RV-02');
        $response2->assertDontSee('#ORD-BF-01');
        $response2->assertSee('red velvet special cream cheese');
    }
```

---

## 4. Hasil Verifikasi & Pengujian

### A. Siklus TDD (Red → Green)
1. **Fase Merah (Red)**: Test dijalankan sebelum perubahan backend controller.
   * Hasil: `FAILED` karena query belum mencari kolom `items`.
2. **Fase Hijau (Green)**: Perubahan controller dan blade diterapkan.
   * Hasil: `PASS Tests\Feature\AdminOrderTest > admin can search all sales by product name (8 assertions)`.

### B. Verifikasi Peramban Headless (Smoke Testing)
1. **Live Instant Search (Client-Side)**:
   * Query: `"matcha"`
   * Total baris: 10
   * Baris yang tampil: 1 (`#K9PHAF`) yang memuat `"matcha green tea layer cake"`.
   * Waktu respon: Instan (< 100ms) tanpa reload halaman.
2. **Server-Side Query (`?search=Forest`)**:
   * Query: `"Forest"`
   * Baris ditemukan: 2 pesanan (`#MVJHQ0` dan `#ZYH001`).
   * Seluruh kartu ringkasan omset dan paginasi menyesuaikan hasil filter dengan tepat.

### C. Test Suite Regresi (`php artisan test`)
* Seluruh test suite (199 tests, 1076 assertions) lulus **100% Green** tanpa adanya regresi sistem.

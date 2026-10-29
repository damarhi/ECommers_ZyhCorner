# Dokumentasi: Perbaikan Logika Transaksi & Keamanan Stok Kasir Offline (POS)

## 1. Ringkasan & Temuan Masalah

Berdasarkan audit menyeluruh terhadap modul Kasir Offline (`/admin/pos`), ditemukan sejumlah celah logika kritis dan anomali alur data:

### Masalah Utama (Mengapa Produk Habis Dapat Di-Checkout?)
1. **Tidak Ada Validasi Stok di Sisi Server (*Backend*):**
   Pada `PosController@checkout`, controller langsung menghitung total dan membuat record `Pesanan` tanpa pernah memeriksa ketersediaan stok fisik di database.
2. **Kartu Produk Tidak Memiliki Indikator Stok:**
   Kartu produk pada `pos.blade.php` tidak menampilkan jumlah stok tersisa, tidak menampilkan badge *"Stok Habis"*, dan tombol *"Tambah"* tetap aktif dapat diklik walaupun stok bernilai `0`.
3. **JavaScript `addToCart` Tidak Memvalidasi Stok Maksimal:**
   Fungsi JavaScript tidak memiliki batasan kuantitas berdasarkan stok database, sehingga kasir dapat menambah kuantitas tanpa batas.

### 9 Celah Logika Lain yang Berhasil Diperbaiki
1. **Kegagalan Pemotongan Stok dari Browser:**
   Objek keranjang `cart[id]` di JavaScript sebelumnya tidak menyertakan key `'id'`, sehingga saat diproses oleh `Produk::reduceStockForOrder()`, `$item['id']` bernilai `null` dan stok di database sama sekali tidak pernah berkurang saat checkout dari web browser.
2. **Metode Pembayaran Tidak Tersimpan:**
   Kasir memilih opsi pembayaran (*Cash* / *Transfer*), namun `PosController@checkout` tidak menyimpan field `payment_method` ke database, menyebabkan invoice kasir menampilkan metode bayar kosong (`-`).
3. **Penyimpanan Keranjang Tidak Persisten (*Cart Reset on Navigation*):**
   Keranjang disimpan murni di variabel memori JS. Setiap kali kasir mencari produk, memfilter kategori, atau berpindah halaman (*pagination*), seluruh isi keranjang langsung terhapus.
4. **Produk Kustom Pre-Order Muncul di Kasir Offline:**
   Query `PosController@index` memuat seluruh jenis produk termasuk kue kustom tingkat dan hampers *pre-order* yang memiliki rentang harga dan bukan barang siap saji etalase toko.
5. **Tidak Ada Sinkronisasi Produk Kadaluarsa:**
   `PosController` tidak memanggil `Produk::checkAndEmptyExpiredProducts()`, sehingga produk basi/kadaluarsa tidak terdeteksi otomatis saat halaman POS dibuka.
6. **Diskon Aktif Tidak Diterapkan:**
   Produk yang memiliki promo diskon tidak menampilkan harga promo maupun badge diskon di kasir POS.
7. **Risiko Manipulasi Harga Input Klien:**
   Total harga dihitung langsung dari nilai `price` yang dikirim dari browser. Diperbaiki dengan memvalidasi harga satuan resmi langsung dari database (`$product->harga_diskon ?? $product->harga`).
8. **Item Transaksi POS Tidak Terpetakan ke Laporan Penjualan:**
   Karena key `'id'` tidak ada di array items, transaksi kasir offline tidak terdeteksi pada filter kategori dan daftar produk terlaris di *Sales Report*.
9. **Tombol "List Orders" Adalah Tombol Mati:**
   Elemen button *"List Orders"* di header tidak memiliki tautan atau event klik. Digantikan dengan tautan menuju halaman *"Riwayat Penjualan"* (`route('all-sales.index')`).

---

## 2. Berkas yang Dimodifikasi

| No | Berkas | Tipe | Rincian Perubahan |
| :---: | :--- | :---: | :--- |
| 1 | `app/Http/Controllers/Admin/PosController.php` | Controller | Menambahkan sinkronisasi kadaluarsa, filter produk showcase, validasi stok habis & stok tidak mencukupi, verifikasi harga asli database, normalisasi array item dengan ID lengkap, penyimpanan `payment_method`, dan pembungkusan transaksi dengan `DB::transaction`. |
| 2 | `resources/views/Admin/kelola_penjualan/pos.blade.php` | Blade View & JS | Menambahkan badge stok (*Tersedia*, *Sisa X*, *Stok Habis*), efek visual redup & tombol nonaktif untuk produk habis, tampilan diskon, validasi stok maksimal di JS, persistensi keranjang via `localStorage`, dan perbaikan tombol link riwayat penjualan. |
| 3 | `tests/Feature/ProductStockReductionTest.php` | Automated Test | Menambahkan 3 pengujian otomatis baru: penolakan checkout stok habis, penolakan kuantitas berlebih, dan verifikasi kelengkapan payload keyed item dari browser beserta penyimpanan metode pembayaran. |

---

## 3. Rincian Perubahan Kode (Before vs After)

### A. `app/Http/Controllers/Admin/PosController.php`

#### 1. Sinkronisasi Kadaluarsa & Filter Produk Showcase di `index()`
* **Sebelumnya (Query Bebas Tanpa Filter):**
  ```php
  public function index(Request $request)
  {
      $query = Produk::query();
      ...
      $products = $query->latest()->paginate(20);
      $kategoris = Kategori::all();
      return view('Admin.kelola_penjualan.pos', compact('products', 'kategoris'));
  }
  ```
* **Sesudahnya (Auto Expire & Filter Showcase):**
  ```php
  public function index(Request $request)
  {
      // Auto sync expired products
      Produk::checkAndEmptyExpiredProducts();

      // Only display showcase products (ready to sell in offline store)
      $query = Produk::with(['kategori', 'diskonAktif'])
          ->where(function($q) {
              $q->where('jenis_produk', 'showcase')
                ->orWhereNull('jenis_produk');
          });
      ...
  }
  ```

#### 2. Validasi Stok, Integritas Harga, & Penyimpanan Data di `checkout()`
* **Sebelumnya (Tanpa Validasi Stok, Harga Diambil dari Klien):**
  ```php
  public function checkout(Request $request)
  {
      $items = $request->input('items');
      ...
      $total = 0;
      foreach ($items as $item) {
          $total += $item['price'] * $item['quantity'];
      }

      $customerName = $request->input('customer_name') ?: 'Walk-in Customer';
      ...
      $pesanan = Pesanan::create([
          'order_number' => $orderNumber,
          'jenis_penjualan' => 'Offline',
          'customer_name' => $customerName,
          'email_pembeli' => null,
          'payment_status' => 'Paid',
          'order_status' => 'selesai',
          'total' => $total,
          'items' => $items,
      ]);

      Produk::reduceStockForOrder($pesanan);
      ...
  }
  ```
* **Sesudahnya (Ketat, Atomik, & Aman):**
  ```php
  public function checkout(Request $request)
  {
      $items = $request->input('items');
      if (empty($items)) {
          return response()->json([
              'success' => false,
              'message' => 'Keranjang belanja kosong!'
          ], 400);
      }

      $normalizedItems = [];
      $total = 0;

      foreach ($items as $key => $item) {
          $productId = $item['id'] ?? $item['id_produk'] ?? $key;
          $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 1);

          if ($quantity <= 0) continue;

          $product = Produk::with('diskonAktif')->find($productId);
          if (!$product) {
              return response()->json([
                  'success' => false,
                  'message' => 'Produk tidak ditemukan di database!'
              ], 404);
          }

          if ($product->stok <= 0) {
              return response()->json([
                  'success' => false,
                  'message' => 'Produk "' . $product->nama_produk . '" sudah habis!'
              ], 400);
          }

          if ($product->stok < $quantity) {
              return response()->json([
                  'success' => false,
                  'message' => 'Stok produk "' . $product->nama_produk . '" tidak mencukupi (tersisa ' . $product->stok . ' item).'
              ], 400);
          }

          $unitPrice = $product->harga_diskon ?? $product->harga;
          $subtotal = $unitPrice * $quantity;
          $total += $subtotal;

          $normalizedItems[] = [
              'id' => $product->id_produk,
              'id_produk' => $product->id_produk,
              'name' => $product->nama_produk,
              'price' => $unitPrice,
              'quantity' => $quantity,
              'qty' => $quantity,
              'subtotal' => $subtotal,
              'image' => $product->foto_1 ? asset('storage/' . $product->foto_1) : ($item['image'] ?? null)
          ];
      }

      $customerName = $request->input('customer_name') ?: 'Walk-in Customer';
      $paymentMethod = $request->input('payment_method', 'Cash');

      do {
          $orderNumber = '#' . strtoupper(Str::random(6));
      } while (Pesanan::where('order_number', $orderNumber)->exists());

      try {
          $pesanan = DB::transaction(function () use ($orderNumber, $customerName, $paymentMethod, $total, $normalizedItems) {
              $pesanan = Pesanan::create([
                  'order_number' => $orderNumber,
                  'jenis_penjualan' => 'Offline',
                  'customer_name' => $customerName,
                  'payment_method' => $paymentMethod,
                  'email_pembeli' => null,
                  'payment_status' => 'Paid',
                  'order_status' => 'selesai',
                  'total' => $total,
                  'items' => $normalizedItems,
              ]);

              Produk::reduceStockForOrder($pesanan);

              return $pesanan;
          });

          return response()->json([
              'success' => true,
              'message' => 'Checkout berhasil dengan nomor order ' . $orderNumber,
              'order' => $pesanan
          ]);
      } catch (\Exception $e) {
          ...
      }
  }
  ```

---

### B. `resources/views/Admin/kelola_penjualan/pos.blade.php`

#### 1. Header Action Button
* Mengganti tombol mati `List Orders` menjadi:
  ```blade
  <a href="{{ route('all-sales.index') }}" class="btn btn-outline-primary btn-sm px-3 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; font-weight: 500;" title="Lihat riwayat seluruh transaksi penjualan">
      <i class="mdi mdi-history me-1" style="font-size: 16px;"></i> Riwayat Penjualan
  </a>
  ```

#### 2. Badge Stok & Kondisi Produk Habis pada Kartu Produk
* Menampilkan badge stok di sudut kiri atas gambar produk:
  ```blade
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
  ```
* Jika stok habis (`$product->stok <= 0`), gambar diberikan efek *grayscale* dan tombol hover diubah menjadi tombol abu-abu nonaktif:
  ```blade
  @if($isOutOfStock)
      <button class="btn btn-secondary btn-sm px-3 disabled" style="cursor: not-allowed; opacity: 0.9;" disabled>
          <i class="mdi mdi-cancel me-1"></i>Stok Habis
      </button>
  @else
      <button class="btn btn-light btn-sm mb-2 px-3 shadow-sm font-weight-500" onclick="addToCart({{ $product->id_produk }}, '{{ addslashes($product->nama_produk) }}', {{ $finalPrice }}, '{{ $productImg }}', {{ $product->stok }})">
          <i class="mdi mdi-plus me-1 text-primary"></i>Tambah
      </button>
      <button class="btn btn-outline-light btn-sm px-3" onclick="removeFromCart({{ $product->id_produk }})">
          <i class="mdi mdi-minus me-1"></i>Kurang
      </button>
  @endif
  ```

#### 3. Logika JavaScript Keranjang & Persistensi `localStorage`
* Menyimpan ID produk, nama, harga, gambar, kuantitas, serta batas `maxStock` pada setiap item keranjang.
* Menyimpan state keranjang ke `localStorage` (`zyh_pos_cart`) saat mutasi item, dan memuatnya kembali saat halaman di-refresh / dipaginate.
* Menampilkan SweetAlert peringatan jika pengguna mencoba menambah item melebihi stok yang tersedia di toko.

---

### C. `tests/Feature/ProductStockReductionTest.php`

Menambahkan unit & feature test komprehensif untuk mencegah regresi:
1. `test_pos_offline_checkout_rejects_out_of_stock_product`: Memvalidasi respons HTTP 400 saat mencoba checkout produk dengan stok 0.
2. `test_pos_offline_checkout_rejects_excessive_quantity`: Memvalidasi respons HTTP 400 saat kuantitas yang dipesan melebihi stok fisik.
3. `test_pos_offline_checkout_works_with_browser_keyed_items`: Memverifikasi payload keyed-object yang dikirim browser diterima dengan baik, mengurangi stok di database, dan menyimpan `payment_method = 'Transfer'`.

---

## 4. Hasil Verifikasi & Pengujian

1. **Blade Template Cache:**
   ```bash
   php artisan view:clear && php artisan view:cache
   ```
   *Hasil:* Sukses (`Compiled views cleared successfully.` & `Blade templates cached successfully.`).

2. **Automated Test Suite:**
   ```bash
   php artisan test
   ```
   *Hasil Pengujian:*
   - **Total Tests:** 144 passed (+3 test baru)
   - **Total Assertions:** 724 assertions (+14 assertions)
   - **Status:** 100% Green (Semua pengujian lolos tanpa kegagalan).

---

## 5. Kesimpulan

Dengan perbaikan menyeluruh ini:
1. Penjualan produk yang sudah habis atau melebihi stok fisik **mustahil terjadi**, terlindungi secara ganda di antarmuka web dan server backend.
2. Stok di database kini **100% berkurang secara akurat** saat transaksi kasir offline dilakukan dari peramban web (*browser*).
3. Transaksi kasir offline terintegrasi penuh dengan pencatatan metode pembayaran, harga promo diskon, serta laporan penjualan harian dan bulanan.

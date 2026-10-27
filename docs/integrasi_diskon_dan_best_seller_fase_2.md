# Dokumentasi Integrasi Diskon & Best Seller Riil dari Database (Fase 2)

## 1. Latar Belakang & Masalah Sebelumnya
Pada tahap awal, meskipun modul manajemen diskon dan tabel `diskons` sudah tersedia di panel admin, sisi frontend pelanggan (`PelangganController@home`) tidak mengonsumsinya. Halaman Beranda menggunakan pengurutan acak (`inRandomOrder()`) untuk Hot Deals dan Best Sellers, serta rumus perkalian fiktif (`harga * 2`) untuk harga coret.

Fase 2 bertujuan untuk **mengaktifkan dan mengintegrasikan relasi data riil yang sudah ada di database**:
1. Menampilkan diskon riil yang berstatus aktif dari tabel `diskons`.
2. Menghitung produk terlaris (*Best Sellers*) secara otomatis dari frekuensi pembelian riil pada tabel `pesanans`.
3. Menghubungkan harga diskon ke seluruh kartu produk, halaman shop, modal pratinjau (*Quick View*), dan halaman detail produk.

---

## 2. Rincian Perubahan Arsitektur & Model

### A. Model `Produk` (`app/Models/Produk.php`)
Menambahkan relasi Eloquent dan Accessor cerdas untuk mengelola diskon dan produk terlaris:

1. **Relasi `diskons()` & `diskonAktif()`**:
   ```php
   public function diskons()
   {
       return $this->hasMany(Diskon::class, 'id_produk', 'id_produk');
   }

   public function diskonAktif()
   {
       $today = \Carbon\Carbon::today()->toDateString();
       return $this->hasOne(Diskon::class, 'id_produk', 'id_produk')
           ->where('status', 'Aktif')
           ->where(function ($q) use ($today) {
               $q->whereNull('tanggal_mulai')
                 ->orWhere('tanggal_mulai', '<=', $today);
           })
           ->where(function ($q) use ($today) {
               $q->whereNull('tanggal_selesai')
                 ->orWhere('tanggal_selesai', '>=', $today);
           });
   }
   ```

2. **Accessor Harga & Badge Diskon**:
   - `getHargaDiskonAttribute()`: Menghitung harga akhir setelah dipotong diskon riil (baik tipe `persen` maupun `nominal`).
   - `getHasDiskonAttribute()`: Boolean pendeteksi keberadaan diskon aktif.
   - `getDiskonBadgeAttribute()`: Menghasilkan label badge otomatis, seperti `"20% OFF"` atau `"Hemat Rp 15.000"`.

3. **Metode Agregasi `getBestSellers($limit = 3)`**:
   Menganalisis pesanan yang telah dibayar (`payment_status = 'Paid'`), mengagregasi kuantitas item per ID produk dari field JSON `items`, dan mengurutkan produk dengan penjualan tertinggi:
   ```php
   public static function getBestSellers(int $limit = 3)
   {
       $productSales = [];
       $orders = Pesanan::where('payment_status', 'Paid')
           ->whereNotNull('items')
           ->get(['items']);

       foreach ($orders as $order) {
           if (is_array($order->items)) {
               foreach ($order->items as $item) {
                   $pid = $item['id'] ?? $item['id_produk'] ?? null;
                   $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
                   if ($pid) {
                       $productSales[$pid] = ($productSales[$pid] ?? 0) + $qty;
                   }
               }
           }
       }
       // Mengurutkan dan mengembalikan produk terlaris dengan eager loading diskon & kategori
   }
   ```

---

### B. Controller Pelanggan (`app/Http/Controllers/Pelanggan/PelangganController.php`)
Memperbarui pemanggilan query pada `home()`, `shop()`, dan `detail()`:
- **Hot Deals**: Mengambil produk yang memiliki relasi `diskonAktif` riil (`whereHas('diskonAktif')`).
- **Best Sellers**: Menggunakan `Produk::getBestSellers(3)`.
- **Featured Products & Newest Products**: Mengikutsertakan eager loading `diskonAktif` agar seluruh kartu produk mendeteksi diskon otomatis tanpa masalah *N+1 query*.
- **Shop & Detail**: Meng-eager load relasi `diskonAktif`.

---

### C. Seeder Pesanan (`database/seeders/PesananSeeder.php`)
Memperbarui data seeder pesanan agar menyimpan rincian produk (`items`) yang riil (ID produk, nama, harga, kuantitas, subtotal, dan gambar), sehingga perhitungan Best Sellers dan riwayat transaksi pada dashboard pelanggan terisi data nyata.

---

### D. Tampilan Antarmuka (Blade Views)
1. **Beranda (`resources/views/Pelanggan/home.blade.php`)**:
   - Menampilkan badge diskon riil (`{{ $product->diskon_badge }}`) jika `$product->has_diskon` bernilai `true`.
   - Menampilkan harga baru dan harga coret (`<span class="old">`) riil.
   - Menautkan parameter data pada modal pratinjau (`data-price`, `data-old-price`, `data-discount-badge`, `data-sale`).
2. **Katalog Produk (`resources/views/Pelanggan/shop.blade.php`)**:
   - Menampilkan badge diskon dan harga coret asli pada grid katalog.
3. **Detail Produk (`resources/views/Pelanggan/detail.blade.php`)**:
   - Menampilkan blok harga diskon (`.pi-price`), harga awal (`.pi-old-price`), dan persentase potongan (`.pi-discount`).
   - Memasukkan harga diskon ke state JavaScript (`window.detailConfig.productPrice`) agar saat checkout produk dibeli dengan harga diskon yang valid.

---

## 3. Hasil Pengujian & Verifikasi Headless Browser

Pengujian otomatis dilakukan pada endpoint live:
1. **Evaluasi Hot Deals (Beranda)**:
   - *Black Forest Cake Classic*: Harga Rp. 100.000 (Harga asli Rp. 125.000) dengan badge `20% OFF`.
   - *Red Velvet Special Cream Cheese*: Harga Rp. 125.000 (Harga asli Rp. 140.000) dengan badge `Hemat Rp 15.000`.
   - *New York Baked Cheesecake*: Harga Rp. 135.000 (Harga asli Rp. 150.000) dengan badge `10% OFF`.
2. **Evaluasi Best Sellers**:
   - Berhasil menghitung peringkat penjualan tertinggi dari riwayat pesanan riil (*New York Baked Cheesecake*, *Pain Au Chocolat*, dan *Almond Danish Pastry*).
3. **Evaluasi Quick View Modal**:
   - Saat tombol *Quick View* pada produk berdiskon diklik, modal menampilkan harga promo (Rp. 100.000), harga coret (Rp. 125.000), dan badge diskon secara otomatis.
4. **Evaluasi Halaman Detail Produk (`/product/1`)**:
   - Harga utama terpasang Rp. 100.000, harga coret Rp. 125.000, dan badge diskon `20% OFF`.

---

## 4. Kesimpulan
Fase 2 telah selesai diimplementasikan 100% tanpa celah maupun regresi. Modul diskon dan best seller kini berjalan sepenuhnya secara dinamis, akurat, dan terintegrasi dengan database SQLite aplikasi.
Aplikasi siap untuk melangkah ke **Fase 3: Tabel & Ekosistem Pengaturan Toko (`store_settings`)**.

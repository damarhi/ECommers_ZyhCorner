# Dokumentasi Eksekusi Pemecahan Fungsi Monolitik `EksporController.php` Menjadi Private Helpers

## 1. Latar Belakang Refaktorisasi
Setelah sebelumnya berkas `PesananController` (1.014 baris) dipecah menjadi 4 controller spesialis, berkas `app/Http/Controllers/Admin/EksporController.php` menampung fungsi ekspor penjualan master dan pesanan aktif. Namun, fungsi `exportAllSales()` di dalamnya masih memiliki sifat **"God Method"** berukuran **~430 baris**.

Method monolitik tersebut mencampuradukkan 5 tanggung jawab yang berbeda:
1. Pengecekan redirect Google Sheets master.
2. Filter query transaksi (jenis penjualan, status bayar, rentang tanggal/preset, pencarian keyword, dan status promo).
3. Kalkulasi metrik keuangan & agregasi kerugian produk kadaluarsa serta retur selesai.
4. Serialisasi data format teks TSV (*Tab-Separated Values*) dengan BOM UTF-8 untuk kebutuhan clipboard paste atau respon AJAX.
5. Pembangun dokumen format XML Excel Spreadsheet 2003 (`.xls` multi-tabel), manajemen I/O berkas storage, dan *binary download response*.

Sesuai dengan keputusan arsitektur, seluruh logika tetap dipertahankan di dalam satu berkas `EksporController.php` tanpa menambah file baru, dengan memecah fungsi monolitik tersebut menjadi **1 Controller Orchestrator** dan **4 Private Helper Methods**.

---

## 2. Struktur Baru `EksporController.php`

```
app/Http/Controllers/Admin/EksporController.php
├── public function exportAllSales(Request $request)
│     └── (Orchestrator ringkas ~25 baris)
├── private function filterSalesQuery(Request $request): array
│     └── (Khusus penyiapan query Eloquent, filtering multi-kriteria, & deteksi promo)
├── private function calculateFinancialMetrics($sales, ?string $startDate, ?string $endDate): array
│     └── (Khusus kalkulasi omset bruto, omset net, diskon, akumulasi expired, & retur)
├── private function buildTsvData($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string
│     └── (Khusus pembuatan teks TSV 3 tabel untuk AJAX / clipboard paste)
├── private function generateXmlSpreadsheet($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string
│     └── (Khusus penyusunan XML Worksheet .xls & penyimpanan ke storage)
└── public function exportOrders()
      └── (Streaming ekspor CSV pesanan online aktif)
```

---

## 3. Rincian Implementasi Kode

### A. Controller Orchestrator: `exportAllSales(Request $request)`
Fungsi utama kini berukuran sangat ringkas dan deklaratif, hanya mengatur alur orkestrasi:

```php
public function exportAllSales(Request $request)
{
    // 1. Redirect jika target Google Sheet aktif
    if ($request->has('gsheet') || $request->input('target') === 'google') {
        return redirect('https://docs.google.com/spreadsheets/d/1RzbBH2PJvgOCiCJ_HgpaI88q87uJQKDlAUwJn3XH4_E/edit?usp=sharing');
    }

    // 2. Filter data penjualan
    [$sales, $startDate, $endDate, $discountedProductIds] = $this->filterSalesQuery($request);

    // 3. Hitung metrik keuangan, produk expired, & retur
    $metrics = $this->calculateFinancialMetrics($sales, $startDate, $endDate);

    // 4. Jika meminta format teks TSV untuk AJAX / Clipboard
    if ($request->wantsJson() || $request->ajax() || $request->input('format') === 'raw') {
        $tsv = $this->buildTsvData($sales, $metrics, $discountedProductIds, $startDate, $endDate);
        return response()->json(['success' => true, 'data' => $tsv]);
    }

    // 5. Generate berkas Excel XML Spreadsheet (.xls) & unduh
    $filePath = $this->generateXmlSpreadsheet($sales, $metrics, $discountedProductIds, $startDate, $endDate);

    return response()->download($filePath, 'Laporan_Penjualan_Master.xls', [
        'Content-Type' => 'application/vnd.ms-excel',
        'Cache-Control' => 'no-cache, must-revalidate',
        'Pragma' => 'no-cache',
    ]);
}
```

### B. Helper 1: `filterSalesQuery(Request $request): array`
* **Tanggung Jawab**: Menjalankan query Eloquent ke tabel `pesanans` dan menerapkan filter berantai:
  * Filter `jenis_penjualan`: Online (hanya status `selesai`), Offline, atau All.
  * Filter `payment_status`: Pending / Paid.
  * Filter rentang tanggal berdasarkan preset (`today`, `last_7_days`, `this_month`, `this_year`) atau input tanggal manual.
  * Filter pencarian keyword pada `order_number`, `customer_name`, `email_pembeli`, dan string `items`.
  * Filter diskon (`has_discount = Yes/No`) yang mencocokkan `id` item dengan daftar `id_produk` aktif di tabel `diskons`.
* **Kembalian**: `[$sales, $startDate, $endDate, $discountedProductIds]`

### C. Helper 2: `calculateFinancialMetrics($sales, ?string $startDate, ?string $endDate): array`
* **Tanggung Jawab**: Menghitung seluruh angka dan agregasi finansial toko:
  * `totalPemasukkanSetelahDiskon`: Jumlah total net pesanan (`$sales->sum('total')`).
  * `totalPemasukkanAsli`: Nilai bruto asli pesanan berdasarkan perbandingan harga master produk (`Produk::all()`).
  * `totalPotonganDiskon`: Selisih potongan diskon toko.
  * Agregasi produk kadaluwarsa (`ProdukKadaluarsa`): jumlah stok dan total kerugian biaya.
  * Agregasi produk retur (`Retur`): filter status selesai (`done`, `accepted`, `completed`, `selesai`, `disetujui`) dan penghitungan estimasi nilai retur.
* **Kembalian**: Array asosiatif `$metrics` lengkap.

### D. Helper 3: `buildTsvData($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string`
* **Tanggung Jawab**: Membangun string teks TSV berawalan BOM UTF-8 (`\xEF\xBB\xBF`) yang memuat:
  1. Header ringkasan metrik omset dan kerugian.
  2. Tabel 1: Rincian transaksi penjualan toko.
  3. Tabel 2: Rincian produk kadaluarsa (jika ada data).
  4. Tabel 3: Rincian produk retur selesai (jika ada data).
* **Kembalian**: String teks siap salin atau siap respon JSON.

### E. Helper 4: `generateXmlSpreadsheet($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string`
* **Tanggung Jawab**: Membangun struktur XML Excel Spreadsheet 2003:
  * Mendefinisikan `<Styles>` (Default, Header latar hijau `#00B207`, SubHeader latar biru `#0284C7`).
  * Menyusun `<Worksheet>` bernama dinamis sesuai timestamp (`Laporan dd-mm HH.ii.ss`).
  * Menyimpan atau menyisipkan worksheet baru ke dalam berkas master di `storage/app/reports/Laporan_Penjualan_Master.xls`.
* **Kembalian**: String path absolut ke file master untuk dikirim via `response()->download()`.

---

## 4. Matriks Perbandingan Sebelum vs Sesudah

| Aspek Pengukuran | Sebelum Refaktorisasi | Sesudah Refaktorisasi | Dampak / Manfaat |
| :--- | :---: | :---: | :--- |
| **Panjang `exportAllSales()`** | **428 baris** | **25 baris** | **Penurunan 94.1%** pada method utama |
| **Jumlah Fungsi** | 2 fungsi monolitik | 6 fungsi (2 public + 4 private) | Kode terurai rapi berdasarkan tugas |
| **Pemisahan Tanggung Jawab** | Bercampur dalam 1 method | Terisolasi per method helper | Memudahkan perbaikan bug dan pemeliharaan |
| **Kemudahan Membaca Kode** | Sulit dibaca (*spaghetti*) | Sangat deklaratif (*clean code*) | Memudahkan pengembang baru memahami alur |
| **Ketergantungan Eksternal** | Tidak ada | Tetap tidak ada | Tidak menambah dependensi baru |

---

## 5. Hasil Verifikasi Pengujian Otomatis

Seluruh 211 test suite pada Laravel (`php artisan test`) dijalankan dan menghasilkan **100% PASS**:

```text
Tests\Feature\AdminExportTest
  ✓ admin can export all sales csv
  ✓ admin can export filtered sales csv
  ✓ admin can export orders csv
  ✓ admin can export products showcase csv
  ✓ admin can export products kustom csv
  ✓ admin can export customers csv
  ✓ admin can export returs csv

Tests:    211 passed (1139 assertions)
Duration: 51.55s
```

Semua endpoint ekspor (`export.all-sales` dan `export.orders`) baik via browser, AJAX, maupun download berkas fisik berfungsi normal tanpa ada regresi.

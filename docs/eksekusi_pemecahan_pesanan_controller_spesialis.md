# Dokumentasi Eksekusi Pemecahan `PesananController.php` Menjadi 4 Sub-Controller Spesialis

## 1. Ringkasan Eksekutif
Refaktorisasi arsitektur controller ini dilakukan untuk mengatasi masalah *God Object* pada `app/Http/Controllers/Admin/PesananController.php` yang sebelumnya membengkak hingga **1.014 baris kode**. Berkas tersebut memikul terlalu banyak tanggung jawab yang saling bercampur: manajemen CRUD pesanan aktif, pembuatan lembar cetak invoice, konfirmasi cepat dapur, verifikasi & penolakan bukti transfer bank, perutean laporan penjualan multi-halaman & AJAX live search, serta mesin ekspor data penjualan/pesanan berbasis file spreadsheet dan format TSV/CSV.

Sesuai dengan kesepakatan konvensi penamaan Bahasa Indonesia yang baku, pendek, dan intuitif (selaras dengan `ProdukController`, `KategoriController`, `ReturController`, dan `DiskonController`), `PesananController.php` berhasil dipecah menjadi **4 Controller Spesialis**.

Semua nama rute pada `routes/web.php` (`orders.*`, `all-sales.*`, `export.*`) dipertahankan 100% persis sama guna menjamin tidak adanya kerusakan (*zero regressions*) pada antarmuka Blade, AJAX request, maupun pengujian otomatis (*automated test suite*).

---

## 2. Struktur Arsitektur Baru

```
app/Http/Controllers/Admin/
├── PesananController.php     (200 baris)  -> Manajemen pesanan online, invoice, konfirmasi cepat dapur, bulk delete
├── LaporanController.php     (314 baris)  -> Master penjualan, live AJAX search, dan laporan komprehensif keuangan
├── EksporController.php      (492 baris)  -> Ekspor data penjualan (XML Spreadsheet, TSV, GSheet) & pesanan (CSV)
└── BuktiBayarController.php  (98 baris)   -> Verifikasi bukti transfer bank & penolakan bukti berpenjelasan
```

---

## 3. Detail Tanggung Jawab & Method Tiap Controller

### A. `PesananController.php` (~200 Baris)
Fokus murni pada siklus hidup data transaksi pesanan online dan tindakan operasional toko:
* `index(Request $request)`: Menampilkan data pesanan online dengan filter pencarian dan status pembayaran (paginasi 10 item).
* `store(Request $request)`: Redirect aman ke kasir POS untuk penambahan transaksi manual.
* `update(Request $request, $id)`: Pembaruan status pesanan (`order_status`) dan pembayaran (`payment_status`).
* `destroy($id)`: Penghapusan pesanan tunggal.
* `quickConfirm(Request $request, $id)`: Konfirmasi instan 1-klik untuk pesanan online agar langsung masuk status `dikemas` (antrean dapur).
* `printInvoice($id)`: Penyusunan lembar cetak invoice pesanan untuk admin/kasir.
* `bulkDelete(Request $request)`: Penghapusan pesanan secara massal dengan validasi multi-ID.

### B. `LaporanController.php` (~314 Baris)
Fokus pada agregasi data, analisis performa penjualan, filter multi-dimensi, dan endpoint AJAX:
* `allSales(Request $request)`: Menampilkan master data seluruh transaksi toko (Online & Offline), didukung live AJAX search tabel terpisah untuk format HTML dan JSON (metrik total omset, counter online, counter offline).
* `salesReport(Request $request)`: Halaman laporan analitik penjualan komprehensif dengan filter preset tanggal (*today*, *last 7 days*, *this month*, *this year*), status promo diskon, serta perhitungan metrik kerugian stok kadaluwarsa (*expired*) dan barang retur (*completed returns*).

### C. `EksporController.php` (~492 Baris)
Fokus pada pengolahan format data tabular untuk kebutuhan pengunduhan laporan dan integrasi pihak ketiga:
* `exportAllSales(Request $request)`:
  * Redirect ke Google Spreadsheet master saat parameter `gsheet` / `target=google` aktif.
  * Penyusunan data TSV (*Tab-Separated Values*) dengan BOM UTF-8 untuk kebutuhan AJAX copy-paste langsung ke Google Sheet/Excel.
  * *Generator* berkas XML Spreadsheet Workbook (`.xls`) multi-tabel (Rincian Penjualan, Rincian Stok Kadaluarsa, Rincian Retur) tersimpan di `storage/app/reports/Laporan_Penjualan_Master.xls`.
* `exportOrders()`: Streaming ekspor berkas CSV UTF-8 pesanan online aktif dengan header *RFC-compliant*.

### D. `BuktiBayarController.php` (~98 Baris)
Fokus pada audit perbankan dan transaksi transfer manual:
* `confirmProof(Request $request, $id)`: Verifikasi bukti bayar transfer bank, pengubahan status pesanan menjadi `dikemas`, status pembayaran menjadi `Paid`, pencatatan timestamp konfirmasi, serta penghapusan alasan penolakan sebelumnya.
* `rejectProof(Request $request, $id)`: Penolakan bukti transfer bank dengan pencatatan `alasan_penolakan` wajib, mereset status pesanan menjadi `menunggu pembayaran`, serta mengizinkan pelanggan mengunggah ulang bukti baru.

---

## 4. Matriks Perbandingan Baris Kode

| Controller | Sebelum Pemecahan | Sesudah Pemecahan | Status |
| :--- | :---: | :---: | :---: |
| `PesananController.php` | 1.014 baris | 200 baris | **Turun 80.3% (-814 baris)** |
| `LaporanController.php` | *(Belum ada)* | 314 baris | Baru (*Dedicated*) |
| `EksporController.php` | *(Belum ada)* | 492 baris | Baru (*Dedicated*) |
| `BuktiBayarController.php` | *(Belum ada)* | 98 baris | Baru (*Dedicated*) |
| **Total Keseluruhan** | **1.014 baris** | **1.104 baris** | Terstruktur & modular |

*(Catatan: Sedikit pertambahan total baris secara agregat disebabkan oleh penambahan docblocks lengkap, deklarasi kelas mandiri, dan namespace imports terisolasi pada masing-masing berkas)*.

---

## 5. Pemetaan Rute pada `routes/web.php`

Seluruh pemetaan rute lama diarahkan ke controller baru masing-masing dengan menjaga alias rute (`->name(...)`) tetap sama persis:

```php
// Export Routes -> EksporController
Route::get('admin/all-sales/export', [EksporController::class, 'exportAllSales'])->name('export.all-sales');
Route::get('admin/orders/export', [EksporController::class, 'exportOrders'])->name('export.orders');

// Pesanan & Invoice Routes -> PesananController
Route::post('admin/orders/bulk-delete', [PesananController::class, 'bulkDelete'])->name('orders.bulk-delete');
Route::resource('admin/orders', PesananController::class)->names('orders');
Route::get('admin/orders/{id}/invoice', [PesananController::class, 'printInvoice'])->name('orders.invoice');
Route::post('admin/orders/{id}/quick-confirm', [PesananController::class, 'quickConfirm'])->name('orders.quickConfirm');

// Laporan Penjualan Routes -> LaporanController
Route::get('admin/all-sales', [LaporanController::class, 'allSales'])->name('all-sales.index');
Route::get('admin/all-sales/report', [LaporanController::class, 'salesReport'])->name('all-sales.report');

// Bukti Bayar Bank Routes -> BuktiBayarController
Route::post('admin/orders/{id}/confirm-proof', [BuktiBayarController::class, 'confirmProof'])->name('orders.confirm-proof');
Route::post('admin/orders/{id}/reject-proof', [BuktiBayarController::class, 'rejectProof'])->name('orders.reject-proof');
Route::post('admin/orders/{id}/confirmProof', [BuktiBayarController::class, 'confirmProof'])->name('orders.confirmProof');
Route::post('admin/orders/{id}/rejectProof', [BuktiBayarController::class, 'rejectProof'])->name('orders.rejectProof');
```

---

## 6. Verifikasi Pengujian Otomatis

Eksekusi menyeluruh dari pengujian fitur dan unit (`php artisan test`) membuktikan bahwa refaktorisasi ini **100% aman dan kompatibel**:

```text
Tests:    211 passed (1139 assertions)
Duration: 50.02s
```

Semua pengujian terkait pesanan dan sub-modul lulus tanpa kendala:
- `Tests\Feature\AdminOrderTest`: Lulus 10/10 pengujian (all sales, invoice, sales report, filters, live AJAX search).
- `Tests\Feature\AdminExportTest`: Lulus 7/7 pengujian (export all sales csv/tsv/excel, export orders csv).
- `Tests\Feature\BankTransferPaymentProofTest`: Lulus 12/12 pengujian (konfirmasi bukti, penolakan bukti dengan alasan, sanitasi rekening bank).
- `Tests\Feature\OrderConfirmationWorkflowTest`: Lulus 8/8 pengujian (quick confirm, pembatalan pesanan, transisi status antrean dapur).
- `Tests\Feature\AdminBulkDeleteTest`: Lulus pengujian bulk delete orders.

# Dokumentasi Teknis: Perbaikan Pencarian Retur Produk Admin (Nama Pelanggan, Pesanan, ID Pesanan, Alasan, dan Status)

## 1. Identifikasi Masalah & Akar Penyebab (*Root Cause*)

Sebelum perbaikan ini, pengguna melaporkan bahwa fitur pencarian di halaman **Kelola Retur Produk Admin (`/admin/returs`)** tidak dapat menemukan data saat mengetik nama pelanggan, nomor/ID pesanan, nama produk, maupun alasan retur.

Setelah dilakukan penelusuran (*debugging*) mendalam, ditemukan 2 akar penyebab:

### A. Sisi Frontend (*Attribute Mismatch pada data-search*)
Pada berkas view `resources/views/Admin/kelola_penjualan/returs.blade.php` (baris 143), baris tabel retur mendefinisikan atribut pencarian lokal sebagai berikut:
```html
<!-- KODE LAMA BERMASALAH -->
<tr data-search="{{ strtolower($retur->nama_pelanggan . ' ' . $retur->order_number . ' ' . $retur->alasan . ' ' . $retur->status_retur) }}">
```
* **Masalah**: Kolom pada tabel database `returs` dan atribut model Eloquent `App\Models\Retur` menggunakan nama bahasa Inggris:
  - `customer_name` (bukan `nama_pelanggan`)
  - `order_id` (bukan `order_number`)
  - `reason` (bukan `alasan`)
  - `product_name`
  - `status` (bukan `status_retur`)
* **Dampak**: Seluruh properti tersebut bernilai `null`, sehingga atribut HTML yang dirender menjadi `data-search="   "` (string spasi kosong). Mesin live filter (`live-instant-search.js`) membaca string kosong ini dan menganggap tidak ada baris yang cocok dengan kata kunci pengguna, sehingga **seluruh baris langsung disembunyikan seketika**.

### B. Sisi Backend (*Cakupan Query Pencarian Controller Terbatas*)
Pada `app/Http/Controllers/Admin/ReturController.php` (baris 21–28):
- Query pencarian lama hanya memeriksa kolom `customer_name`, `order_id`, dan `product_name`.
- Kolom `reason` (alasan retur) dan `status` belum disertakan dalam klausa pencarian.
- Pengguna yang mengetik status dalam bahasa Indonesia (seperti *"Selesai"*, *"Diterima"*, *"Diproses"*, *"Ditolak"*, atau *"Baru"*) tidak mendapatkan hasil karena di database tersimpan dalam bahasa Inggris (`Done`, `Accepted`, `Processed`, `Rejected`, `New`).

---

## 2. Rincian Perubahan Kode

### A. Perbaikan View Tabel Retur
**Berkas:** `resources/views/Admin/kelola_penjualan/returs.blade.php`
Mengubah atribut `data-search` agar membaca atribut asli model `Retur` beserta label status bahasa Indonesia:

```html
<!-- KODE BARU -->
<tr data-search="{{ strtolower($retur->customer_name . ' ' . $retur->order_id . ' ' . $retur->product_name . ' ' . $retur->reason . ' ' . $retur->status . ' ' . $retur->status_label) }}">
```

### B. Penambahan *Defensive Accessors* pada Model `Retur`
**Berkas:** `app/Models/Retur.php`
Menambahkan alias accessors untuk menjamin *backward compatibility* dan mencegah *null error* jika ada komponen lain yang memanggil atribut dalam bahasa Indonesia:

```php
    /**
     * Alias accessors for backward compatibility and Indonesian attributes
     */
    public function getNamaPelangganAttribute(): ?string
    {
        return $this->customer_name;
    }

    public function getOrderNumberAttribute(): ?string
    {
        return $this->order_id;
    }

    public function getAlasanAttribute(): ?string
    {
        return $this->reason;
    }

    public function getStatusReturAttribute(): ?string
    {
        return $this->status;
    }
```

### C. Penyempurnaan Query Filter pada `ReturController`
**Berkas:** `app/Http/Controllers/Admin/ReturController.php`
Memperluas cakupan filter pencarian server-side dengan `filled('search')`, menyertakan kolom `reason` dan `status`, serta memetakan kata kunci status bahasa Indonesia ke nilai database:

```php
        if ($request->filled('search')) {
            $rawTerm = trim($request->search);
            $searchTerm = '%' . $rawTerm . '%';
            
            $statusMap = [
                'baru'      => 'New',
                'diproses'  => 'Processed',
                'diterima'  => 'Accepted',
                'disetujui' => 'Accepted',
                'ditolak'   => 'Rejected',
                'selesai'   => 'Done',
            ];
            $mappedStatus = $statusMap[strtolower($rawTerm)] ?? null;

            $query->where(function($q) use ($searchTerm, $mappedStatus) {
                $q->where('customer_name', 'like', $searchTerm)
                  ->orWhere('order_id', 'like', $searchTerm)
                  ->orWhere('product_name', 'like', $searchTerm)
                  ->orWhere('reason', 'like', $searchTerm)
                  ->orWhere('status', 'like', $searchTerm);
                  
                if ($mappedStatus) {
                    $q->orWhere('status', $mappedStatus);
                }
            });
        }
```

### D. Penambahan Automated Feature Tests
**Berkas:** `tests/Feature/AdminReturStatusTest.php`
Menambahkan 2 method pengujian baru:
1. `test_admin_can_search_returs_by_customer_name_order_id_and_reason()`:
   - Memvalidasi pencarian retur berdasarkan nama pelanggan, ID pesanan, dan alasan retur.
2. `test_retur_table_renders_valid_data_search_attributes_and_accessors()`:
   - Memvalidasi accessor alias model `Retur` dan atribut HTML `data-search` pada tabel.

---

## 3. Hasil Verifikasi Pengujian

Pengujian otomatis dijalankan menggunakan PHPUnit:

```bash
php artisan test --filter=AdminReturStatusTest
```

**Hasil Pengujian:**
```text
PASS  Tests\Feature\AdminReturStatusTest
  ✓ admin can view returs index with status tabs and counts                      1.27s
  ✓ admin can filter returs by status                                            0.03s
  ✓ admin can quick update retur status                                          0.02s
  ✓ retur status mutator normalizes case and aliases                             0.02s
  ✓ admin can update status and catatan admin                                    0.02s
  ✓ admin can search returs by customer name order id and reason                 0.04s
  ✓ retur table renders valid data search attributes and accessors               0.02s

Tests:    7 passed (43 assertions)
Duration: 1.63s
```

Pengujian seluruh rangkaian test aplikasi (*full test suite*):
```bash
php artisan test
```

**Hasil:**
```text
Tests:    174 passed (889 assertions)
Duration: 44.09s
```
Semua 174 pengujian lulus 100% tanpa ada regresi.
Fitur pencarian retur produk kini berfungsi secara instan dan akurat baik di sisi klien (as-you-type) maupun sisi server.

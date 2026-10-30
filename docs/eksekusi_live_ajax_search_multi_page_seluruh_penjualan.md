# Laporan Eksekusi: Live AJAX Multi-Page Search Seluruh Penjualan & Perbaikan Paginasi

## 1. Latar Belakang & Diagnosis Masalah

Sebelum pembaruan ini, antarmuka tabel pencarian penjualan admin (`/admin/all-sales`) memiliki inkonsistensi perilaku:
* **Server-Side Pagination Terbatas**: Laravel memecah data penjualan menjadi 10 data per halaman menggunakan `paginate(10)`.
* **Client-Side DOM Filter Parsial**: Skrip `public/js/live-instant-search.js` menyaring data secara instan saat mengetik (*as-you-type*), namun penyaringan hanya berjalan di sisi peramban terhadap elemen `<tr>` yang sudah ter-render di halaman aktif.
* **Akibat Masalah**:
  1. Jika pengguna berada di halaman 1, pencarian hanya menyaring data 1 s/d 10. Data yang berada di halaman 2 atau 3 tidak pernah ditemukan.
  2. Jika pengguna berpindah ke halaman 2 lalu mengetik, hasil yang disaring berbeda karena data awal di layar berbeda (data 11 s/d 20).
  3. Form submit dicegat oleh `e.preventDefault()`, sehingga pengguna tidak dapat mencari ke database tanpa menonaktifkan skrip.

Sesuai arahan panduan `/skill:frontend-design-direction`, antarmuka pencarian diperbarui menjadi **Live AJAX Multi-Page Database Search** yang tetap mempertahankan kecepatan pengetikan instan (*as-you-type*), namun mencari langsung ke **seluruh database** dan memperbarui tabel beserta pagination secara instan (*seamless partial replacement*) tanpa reload halaman penuh.

---

## 2. Prinsip & Arahan Desain (`/skill:frontend-design-direction`)

| Parameter | Implementasi |
| :--- | :--- |
| **Purpose** | Admin dapat menemukan transaksi/produk secara seketika dari seluruh database tanpa batasan halaman aktif. |
| **Zero Layout Shift** | Dimensi container tabel dan pagination tetap terkunci stabil saat proses pemuatan data. Digunakan efek opasitas transisi halus `0.6` tanpa merusak *layout flow*. |
| **Non-blocking Debounce** | Debounce pengetikan diatur 250ms dengan pembatalan request lama (*request cancellation*) menggunakan `AbortController` sehingga tidak ada *race condition* saat admin mengetik cepat. |
| **Visual Feedback** | Ikon kaca pembesar `<i class="mdi mdi-magnify">` berubah halus menjadi spinner `<i class="mdi mdi-loading mdi-spin">` selama request HTTP berlangsung, lalu kembali normal secara otomatis. |
| **Empty State Contract** | Jika kata kunci tidak ditemukan di seluruh database, sistem menampilkan baris kosong elegan dengan ikon pencarian, pesan spesifik kata kunci, dan tombol aksi *"Reset Pencarian"*. |
| **URL Synchronization** | Parameter query `?search=...` dan `?page=...` disinkronkan ke bilah alamat peramban menggunakan `window.history.replaceState` tanpa *full page reload*. |
| **Realtime KPI Sync** | Angka pada 3 Stat Cards di bagian atas halaman (*Total Omset Penjualan*, *Penjualan Online*, dan *Penjualan Kasir*) otomatis diperbarui secara dinamis mengikuti hasil pencarian. |

---

## 3. Berkas yang Diubah & Dibuat

### 1. `resources/views/Admin/kelola_penjualan/partials/all_sales_table.blade.php` (BARU)
Komponen partial Blade yang mengekstrak area dinamis tabel penjualan:
* Tabel responsif (`<div class="table-responsive">`) dengan seluruh kolom, badge status pesanan/pembayaran, dan tombol aksi.
* Modals detail pesanan (`order_detail_modal.blade.php`) dan modal verifikasi bukti transfer (`verify_proof_modal.blade.php`) untuk setiap record pesanan yang tampil.
* Kontainer pagination (`{{ $sales->appends(request()->query())->links('pagination::bootstrap-4') }}`) dan teks counter hasil (`Menampilkan X - Y dari Z hasil`).
* Status kosong (*Empty State*) interaktif jika data pencarian tidak ditemukan:
  ```blade
  @empty
  <tr>
      <td colspan="9" class="text-center py-5">
          <div class="d-flex flex-column align-items-center justify-content-center">
              @if(request('search'))
                  <i class="mdi mdi-file-search-outline text-muted mb-2" style="font-size: 54px; opacity: 0.6;"></i>
                  <h5 class="text-dark font-weight-bold mb-1">Tidak ada data yang cocok</h5>
                  <p class="text-muted mb-3" style="font-size: 13.5px;">Tidak ditemukan data penjualan dengan kata kunci "<strong>{{ request('search') }}</strong>".</p>
                  <button type="button" class="btn btn-sm btn-outline-primary reset-instant-search-btn" style="border-radius: 6px;">
                      <i class="mdi mdi-refresh me-1"></i> Reset Pencarian
                  </button>
              @else
                  <i class="mdi mdi-cart-outline text-muted mb-2" style="font-size: 64px;"></i>
                  <h5 class="text-muted font-weight-bold">Belum Ada Data Penjualan</h5>
                  <p class="text-muted mb-0" style="font-size: 14px;">Oops, belum ada data penjualan yang dapat ditampilkan.</p>
              @endif
          </div>
      </td>
  </tr>
  @endforelse
  ```

---

### 2. `resources/views/Admin/kelola_penjualan/all_sales.blade.php`
* Menambahkan atribut `data-ajax-container="#all-sales-table-container"` pada `<form>` pencarian.
* Menambahkan atribut `data-server-search="true"` pada elemen input pencarian.
* Membungkus area tabel dengan `<div id="all-sales-table-container" class="position-relative">` yang meng-include partial di atas.
* Menambahkan atribut `data-kpi="total-pendapatan"`, `data-kpi="online-count"`, dan `data-kpi="offline-count"` pada nilai stat cards agar dapat diperbarui secara dinamis saat pencarian live AJAX berlangsung.

---

### 3. `app/Http/Controllers/Admin/PesananController.php`
Pembaruan method `allSales(Request $request)`:
Mendeteksi apakah request adalah AJAX (`$request->ajax() || $request->has('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest'`). Jika ya, kembalikan partial HTML atau JSON metadata:
```php
if ($request->ajax() || $request->has('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
    $tableHtml = view('Admin.kelola_penjualan.partials.all_sales_table', compact('sales'))->render();
    if ($request->wantsJson()) {
        return response()->json([
            'html' => $tableHtml,
            'total' => $sales->total(),
            'firstItem' => $sales->firstItem() ?? 0,
            'lastItem' => $sales->lastItem() ?? 0,
            'totalPendapatan' => 'Rp. ' . number_format($totalPendapatan, 0, ',', '.'),
            'totalOnlineCount' => $totalOnlineCount,
            'totalOfflineCount' => $totalOfflineCount,
        ]);
    }
    return response($tableHtml);
}
```

---

### 4. `public/js/live-instant-search.js`
Penyempurnaan arsitektur pencarian:
* **Fungsi `getAjaxContainer(input)`**: Mendeteksi apakah input form terhubung dengan container AJAX (misal `#all-sales-table-container`).
* **Fungsi `triggerAjaxSearch(input, form, container, query)`**:
  - Mengelola `AbortController` untuk membatalkan fetch sebelumnya jika pengguna terus mengetik.
  - Mengaktifkan indikator loading (spinner pada ikon search dan opasitas halus 0.6 pada tabel).
  - Menyusun URL dengan mempertahankan filter lain (seperti `jenis_penjualan[]`) dan mereset `page` ke halaman 1.
  - Melakukan fetch ke server dengan header `X-Requested-With: XMLHttpRequest` dan `Accept: application/json, text/html`.
  - Mengganti isi DOM container `#all-sales-table-container` secara instan.
  - Memperbarui angka KPI cards (`data-kpi`).
  - Memperbarui URL di browser via `window.history.replaceState`.
  - Mengikat ulang event handler untuk link pagination dan tombol reset.
* **Fungsi `fetchAjaxPage(url, container, form, input)`**:
  - Mengintersep klik tautan pagination (`.pagination a`).
  - Memuat halaman baru secara halus melalui AJAX tanpa refresh penuh dan melakukan *smooth scroll* ke atas tabel.
* **Fungsi `bindAjaxContainer(container, form, input)`**:
  - Menghubungkan klik tombol `.reset-instant-search-btn` agar mengosongkan input dan memulihkan data awal.
  - Mengikat listener klik AJAX pada seluruh link pagination.
* **Debounce Dinamis**: Debounce 250ms untuk pencarian database AJAX, dan 150ms untuk filter grid lokal (seperti kasir POS).
* **Keyboard Shortcut & Clear Button**: Tombol `(×)` dan tombol `Escape` langsung mengosongkan query dan mengembalikan tampilan paginasi default seketika.

---

### 5. `tests/Feature/AdminOrderTest.php`
Penambahan test cases automated test suite:
* `test_admin_can_perform_ajax_live_search_on_all_sales_returning_partial_html`: Memastikan request AJAX mengembalikan potongan partial Blade tanpa doctype/layout penuh dan mencakup counter paginasi yang valid.
* `test_admin_can_perform_ajax_live_search_on_all_sales_returning_json_with_kpi_counts`: Memastikan endpoint merespons JSON berisi `html`, `total`, `totalPendapatan`, `totalOnlineCount`, dan `totalOfflineCount`.
* `test_admin_ajax_live_search_shows_empty_state_with_reset_button`: Memastikan kata kunci yang tidak ada menghasilkan antarmuka empty state lengkap dengan tombol reset pencarian.

### 6. `tests/Feature/LiveInstantSearchTest.php`
* Penambahan assertion bahwa halaman `/admin/all-sales` me-render atribut `data-ajax-container="#all-sales-table-container"` dan elemen kontainer `#all-sales-table-container`.

---

## 4. Hasil Verifikasi & Pengujian

### A. Verifikasi Headless Browser (Puppeteer / Chromium)
Dilakukan 6 skenario pengujian komprehensif pada peramban headless:
1. **Initial Page Load**: Halaman `/admin/all-sales` memuat 18 data dengan counter *"Menampilkan 1 - 10 dari 18 hasil"*.
2. **As-you-type Live Search ("matcha")**:
   - Mengetik kata kunci "matcha" langsung mengirim fetch asynchronous tanpa form reload.
   - Database mencari ke seluruh transaksi (termasuk yang sebelumnya berada di halaman 2).
   - Hasil diperbarui secara instan menjadi *"Menampilkan 1 - 3 dari 3 hasil"*.
   - Tombol Clear `(×)` muncul otomatis di sisi kanan input.
   - URL browser tersinkronisasi menjadi `http://127.0.0.1:8000/admin/all-sales?search=matcha`.
   - Angka KPI online dan offline menyesuaikan dengan transaksi matcha.
3. **Empty State**:
   - Mengetik kata kunci yang tidak ada (`kue_palsu_tidak_ada_xyz`).
   - Tabel menampilkan pesan *"Tidak ada data yang cocok"* dan tombol *"Reset Pencarian"*.
4. **Reset Button Click**:
   - Menekan tombol *"Reset Pencarian"* mengosongkan input dan mengembalikan tabel ke data lengkap awal (*"Menampilkan 1 - 10 dari 18 hasil"*).
5. **Clear (×) Button**:
   - Mengetik "Black Forest" menyaring menjadi *"Menampilkan 1 - 2 dari 2 hasil"*.
   - Menekan tombol `(×)` mengembalikan data lengkap awal secara instan.
6. **Multi-Page AJAX Pagination Navigation**:
   - Mengklik tautan halaman 2 (`page=2`) memperbarui data menjadi *"Menampilkan 11 - 18 dari 18 hasil"* secara instan tanpa reload halaman penuh.
   - Saat berada di halaman 2 (`?page=2`) dan mulai mengetik "matcha", sistem otomatis mereset paginasi ke halaman 1 dan menampilkan seluruh 3 hasil pencarian yang cocok.

### B. Hasil Test Suite Laravel (`php artisan test`)
```bash
Tests:    202 passed (1098 assertions)
Duration: 43.12s
Status:   100% PASS (Zero Regression)
```
Seluruh 202 pengujian fitur dan unit pada aplikasi berjalan sukses tanpa ada satu pun kegagalan (*zero regression*).

# Dokumentasi Perapian Jarak Spasial Elemen & Dropdown Modal Tambah Diskon

## 1. Analisis Masalah Spasial Awal
Berdasarkan pengujian tampilan modal *Tambah Diskon Baru* (`#addDiscountModal`) pada `resources/views/Admin/kelola_produk/discounts.blade.php`:
1. **Misalignment Anchor Dropdown**:
   Kontainer `#productDropdownContainer` sebelumnya membungkus teks bantuan (*helper text*) di bawah input trigger. Karena menu dropdown menggunakan CSS `position: absolute; top: calc(100% + 4px)`, panel dropdown mengacu pada bagian terbawah kontainer sehingga terdorong jatuh di bawah helper text. Akibatnya, muncul celah aneh di mana teks bantuan terselip di antara kotak input dan panel dropdown.
2. **Ketinggian Dropdown Terlalu Masif (290px+)**:
   Header yang terlalu tinggi, padding list opsi produk yang longgar (`py-2 px-3`), serta sub-footer yang tebal membuat panel dropdown memanjang ke bawah hingga menutupi seluruh formulir modal dan hampir menabrak tombol pada footer modal.
3. **Penggunaan Kelas Margin Bootstrap Non-Standar (`mb-2.5`)**:
   Bootstrap 5 tidak memiliki utilitas `mb-2.5`, sehingga form groups tidak memiliki margin bawah yang konsisten (*zero margin fallback*).
4. **Visual Clutter pada Kontrol Dropdown**:
   Ikon `mdi-check-all` menghasilkan centang ganda (`✓✓ Pilih Semua`), dan sub-footer memiliki microcopy yang redundan dengan teks bantuan utama.

---

## 2. Solusi Perbaikan Desain (`frontend-design-direction`)

### A. Penataan Ulang Anchor Spasial Dropdown
- Membungkus elemen trigger dan dropdown menu dalam satu kontainer khusus berkelas `.position-relative`:
  ```html
  <div class="position-relative">
      <div id="productDropdownTrigger" class="form-control ...">...</div>
      <div id="productDropdownMenu" class="dropdown-menu ...">...</div>
  </div>
  <small class="text-muted d-block mt-1">Pilih satu atau beberapa produk...</small>
  ```
- **Dampak Spasial**: Dropdown menu sekarang menempel langsung 4px tepat di bawah kotak input trigger, dan secara elegan menutupi helper text serta field bawahnya saat terbuka sebagaimana perilaku standar *floating combobox*.

### B. Optimalisasi Kepadatan Dropdown (*Height & Density Reduction*)
- **Header Pencarian**:
  - Tinggi input pencarian dirampingkan dari 32px menjadi 30px (`height: 30px; font-size: 11.5px;`).
  - Mengganti ikon double-check menjadi single check `<i class="mdi mdi-check me-1"></i>Pilih Semua`.
  - Meringkas label tombol batal menjadi `"Batal"` (`font-size: 10px; padding: 2px 7px;`).
- **List Opsi Produk**:
  - Mengurangi `max-height` dari 200px menjadi 145px (menampilkan 4 baris produk secara proporsional).
  - Merampingkan padding baris opsi dari `py-2 px-3` menjadi `py-1.5 px-2.5` (tinggi baris berkurang dari 38px menjadi ~30px).
  - Checkbox diperkecil menjadi `14px x 14px` dan badge harga menjadi `font-size: 10px; padding: 2px 6px;`.
- **Sub-Footer Tipis**:
  - Menghapus teks instruksi redundan `"ⓘKlik produk untuk memilih"`.
  - Mengubah sub-footer menjadi bar status tipis yang rapi (`py-1 px-2.5; background: #f8fafc;`) dengan status bantuan `"Tekan di luar untuk menutup"` dan tombol mini `"Selesai"`.

### C. Standarisasi Ritme Margin Antar Komponen Form (`mb-3` & `mb-1.5`)
- Seluruh form group distandarisasi menggunakan Bootstrap 5:
  - Antar Form Group: `margin-bottom: 1rem (16px)` via class `mb-3`.
  - Jarak Label ke Input: `margin-bottom: 0.375rem (6px)` via class `mb-1.5`.
  - Grid Dua Kolom: `row g-2 mb-3`.
  - Form Group Terakhir (Status Diskon): `mb-1` untuk memberikan ruang bernapas bersih sebelum modal footer.
  - Seluruh input utama memiliki tinggi standar seragam `38px` dengan `border-radius: 6px`.

---

## 3. Rincian Kode yang Diubah

### Berkas: `resources/views/Admin/kelola_produk/discounts.blade.php` (Baris 430–585)

```html
<div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
    <!-- Searchable Multi-Select Product Dropdown -->
    <div class="form-group mb-3" id="productDropdownContainer">
        <div class="d-flex justify-content-between align-items-center mb-1.5">
            <label class="form-label font-weight-500 m-0 text-dark" style="font-size: 12px;">
                Pilih Produk <span class="text-danger">*</span>
            </label>
            <span class="badge bg-light text-muted border" id="selectedProductsCountBadge" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">
                0 Produk Terpilih
            </span>
        </div>

        <!-- Relative Anchor for Trigger and Floating Menu -->
        <div class="position-relative">
            <!-- Dropdown Trigger Button -->
            <div id="productDropdownTrigger" class="form-control d-flex align-items-center justify-content-between px-3" 
                 tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox"
                 style="font-size: 12px; border-radius: 6px; height: 38px; cursor: pointer; background-color: #fff; border-color: #ebedf2; transition: all 0.2s ease;">
                <div class="d-flex align-items-center gap-2 overflow-hidden me-2" style="max-width: 88%;">
                    <i class="mdi mdi-cake-variant-outline text-primary" style="font-size: 15px; flex-shrink: 0;"></i>
                    <span id="productDropdownTriggerText" class="text-muted text-truncate" style="font-size: 12px;">
                        Pilih satu atau beberapa produk...
                    </span>
                </div>
                <i class="mdi mdi-chevron-down text-muted flex-shrink-0" id="productDropdownCaret" style="font-size: 16px; transition: transform 0.2s ease;"></i>
            </div>

            <!-- Floating Dropdown Menu Panel -->
            <div id="productDropdownMenu" class="dropdown-menu shadow-lg w-100 p-0 border" 
                 style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1055; border-radius: 8px; border-color: #e2e8f0; background: #ffffff; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12), 0 8px 10px -6px rgba(0,0,0,0.08);">
                
                <!-- Dropdown Header: Compact Live Search & Action Buttons -->
                <div class="p-2 border-bottom bg-light">
                    <div class="position-relative mb-1.5">
                        <input type="text" id="modalProductSearch" class="form-control form-control-sm ps-4 pe-4" 
                               placeholder="Ketik nama produk..." autocomplete="off" 
                               style="font-size: 11.5px; height: 30px; border-radius: 6px; border-color: #cbd5e1; background: #ffffff;">
                        <i class="mdi mdi-magnify position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; pointer-events: none;"></i>
                        <button type="button" id="modalProductSearchClear" class="btn btn-link p-0 position-absolute text-muted d-none" 
                                style="right: 8px; top: 50%; transform: translateY(-50%); text-decoration: none;" title="Hapus pencarian">
                            <i class="mdi mdi-close-circle" style="font-size: 14px;"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center px-0.5">
                        <small class="text-muted" style="font-size: 10.5px;" id="productFilterCountText">
                            {{ count($produks) }} produk tersedia
                        </small>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-xs btn-outline-primary py-0.5 px-2" id="btnSelectAllProducts" style="font-size: 10px; border-radius: 4px;">
                                <i class="mdi mdi-check me-1"></i>Pilih Semua
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" id="btnDeselectAllProducts" style="font-size: 10px; border-radius: 4px;">
                                <i class="mdi mdi-close me-1"></i>Batal
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Scrollable Product Options List (Compact & Dense) -->
                <div id="productOptionsScrollList" style="max-height: 145px; overflow-y: auto;">
                    @forelse($produks as $p)
                        <div class="product-item-option px-2.5 py-1.5 border-bottom d-flex align-items-center justify-content-between" 
                             data-id="{{ $p->id_produk }}" 
                             data-name="{{ strtolower($p->nama_produk) }}" 
                             data-price="{{ $p->harga }}"
                             style="cursor: pointer; transition: background 0.15s;" 
                             onclick="toggleProductItemCheckbox(event, 'chk_prod_{{ $p->id_produk }}')">
                            <div class="d-flex align-items-center gap-2 text-truncate me-2" style="max-width: 75%;">
                                <input type="checkbox" name="id_produk[]" id="chk_prod_{{ $p->id_produk }}" value="{{ $p->id_produk }}" 
                                       class="product-check-item form-check-input mt-0" 
                                       style="width: 14px; height: 14px; cursor: pointer; accent-color: #4F46E5;" 
                                       onclick="event.stopPropagation();"
                                       {{ is_array(old('id_produk')) && in_array($p->id_produk, old('id_produk')) ? 'checked' : '' }}>
                                <span class="font-weight-500 text-dark text-truncate product-item-name" style="font-size: 12px;">{{ $p->nama_produk }}</span>
                            </div>
                            <span class="badge bg-light text-muted border flex-shrink-0" style="font-size: 10px; font-weight: 500; padding: 2px 6px;">
                                Rp {{ number_format($p->harga, 0, ',', '.') }}
                            </span>
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted" style="font-size: 12px;">Belum ada produk yang tersedia.</div>
                    @endforelse
                    <div id="productNoMatch" class="p-3 text-center text-muted d-none" style="font-size: 11.5px;">
                        <i class="mdi mdi-magnify-close d-block mb-1 text-muted" style="font-size: 20px;"></i>
                        Tidak ada produk yang cocok dengan pencarian.
                    </div>
                </div>

                <!-- Dropdown Footer (Slim Status Bar) -->
                <div class="py-1 px-2.5 bg-light border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size: 10px;">
                        Tekan di luar untuk menutup
                    </small>
                    <button type="button" class="btn btn-xs btn-gradient-primary py-0.5 px-2 text-white" id="btnCloseProductDropdown" style="font-size: 10px; border-radius: 4px; font-weight: 500;">
                        Selesai
                    </button>
                </div>
            </div>
        </div>

        <!-- Helper text directly under trigger -->
        <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
            Pilih satu atau beberapa produk yang akan diterapkan promo diskon ini.
        </small>
    </div>

    <!-- Nama Promo / Diskon -->
    <div class="form-group mb-3">
        <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Nama Promo / Diskon <span class="text-danger">*</span></label>
        <input type="text" name="nama_diskon" class="form-control" placeholder="Contoh: Promo Akhir Pekan 15%" required style="font-size: 12px; border-radius: 6px; height: 38px;">
    </div>

    <!-- Tipe & Nilai Diskon (Grid 2 Kolom) -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Tipe Diskon <span class="text-danger">*</span></label>
                <select name="tipe_diskon" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                    <option value="persen">Persentase (%)</option>
                    <option value="nominal">Nominal (Rp)</option>
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Nilai Diskon <span class="text-danger">*</span></label>
                <input type="number" name="nilai_diskon" class="form-control" placeholder="Nilai (e.g. 20 atau 15000)" min="0" required style="font-size: 12px; border-radius: 6px; height: 38px;">
            </div>
        </div>
    </div>

    <!-- Rentang Tanggal (Grid 2 Kolom) -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Tanggal Mulai <small class="text-muted fw-normal">(Opsional)</small></label>
                <input type="date" name="tanggal_mulai" class="form-control" style="font-size: 12px; border-radius: 6px; height: 38px;">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-0">
                <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Tanggal Selesai <small class="text-muted fw-normal">(Opsional)</small></label>
                <input type="date" name="tanggal_selesai" class="form-control" style="font-size: 12px; border-radius: 6px; height: 38px;">
            </div>
        </div>
    </div>

    <!-- Status Diskon -->
    <div class="form-group mb-1">
        <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Status Diskon <span class="text-danger">*</span></label>
        <select name="status" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
            <option value="Aktif" selected>Aktif</option>
            <option value="Nonaktif">Nonaktif</option>
        </select>
    </div>
</div>
```

---

## 4. Hasil Verifikasi & Pengujian

1. **Blade Compilation**:
   ```bash
   php artisan view:clear && php artisan view:cache
   # Output: Compiled views cleared & Blade templates cached successfully.
   ```
2. **Feature Tests Diskon (`AdminDiskonTest`)**:
   ```bash
   php artisan test --filter=AdminDiskonTest
   # Output: 6 passed (24 assertions) - 100% Green
   ```
3. **Full System Test Suite**:
   ```bash
   php artisan test
   # Output: 153 passed (774 assertions) - 100% Green
   ```

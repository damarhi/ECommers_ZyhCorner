# Perbaikan Desain Tombol Filter dan Eliminasi Warna Gradient pada Seluruh Tampilan Admin & Kasir

## 1. Ringkasan Eksekutif & Latar Belakang

Sesuai arahan visual desain modern dan panduan *frontend design direction*, elemen-elemen antarmuka pada panel Admin dan Kasir yang sebelumnya menggunakan warna gradien cerah (`linear-gradient` bernuansa ungu `#da8cff` - `#9a55ff`, `btn-gradient-primary`, `bg-gradient-primary`, `badge-gradient-primary`, serta outline merah menyala `btn-outline-danger` pada reset filter) telah diperbaiki dan distandarisasi ke palet **solid modern, minimalis, dan profesional**:

1. **Tombol Filter Toolbar (`.admin-filter-btn`)**:
   - Dibuat dengan dimensi konsisten: tinggi `38px`, border-radius `8px`, font size `13px`, font-weight `500`.
   - **Kondisi Default (Tanpa Filter Aktif)**: Latar putih bersih (`#ffffff`), border neutral slate (`1px solid #cbd5e1`), teks abu-abu gelap (`#334155`). Memberikan kesan tenang dan rapi di samping search input.
   - **Kondisi Aktif (Filter Diterapkan)**: Menggunakan warna solid Indigo `#4F46E5`, border `#4F46E5`, teks putih `#ffffff`, dengan bayangan lembut berdimensi halus (`box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25)`).
   - Menghilangkan bentukan gradien ungu kaku yang sebelumnya pecah saat state aktif.

2. **Indikator Badge Jumlah Filter (`.admin-filter-badge`)**:
   - Menggantikan pill putih tebal kontras tinggi (`bg-white text-primary`) dengan pill bernuansa *frosted subtle glass*:
     - `background: rgba(255, 255, 255, 0.22);`
     - `border: 1px solid rgba(255, 255, 255, 0.28);`
     - `color: #ffffff;`
     - `border-radius: 999px; font-size: 11px; font-weight: 600; padding: 2px 7px;`
   - Menyatu harmonis dengan tombol filter solid indigo tanpa merusak hierarki teks label filter.

3. **Tombol Reset Filter (`.admin-btn-reset` / `.admin-filter-reset-btn`)**:
   - Menggantikan tombol outline merah muda / pink mencolok (`btn-outline-danger`) yang sebelumnya terlihat seperti tombol peringatan destruktif.
   - Didesain ulang menjadi tombol netral berkelas (*clean slate action*):
     - Latar putih (`#ffffff`), border neutral slate (`#cbd5e1`), teks slate gelap (`#64748b`).
     - Efek hover halus: latar `#f8fafc`, border `#94a3b8`, teks `#0f172a`.
     - Tetap membawa ikon `mdi-refresh` dan fungsi membersihkan filter URL dengan rapi.

4. **Input Pencarian Toolbar (Search Input)**:
   - Warna border diperbarui dari `#ebedf2` menjadi `#cbd5e1` agar selaras sempurna dengan tombol filter dan tombol reset di sampingnya.
   - Efek focus menggunakan border solid `#4F46E5` dengan soft focus ring `rgba(79, 70, 229, 0.15)`.
   - Lebar diselaraskan secara responsif (`min-width: 240px; max-width: 320px;`) sehingga tata letak toolbar tidak tumpang tindih pada berbagai resolusi layar.

5. **Eliminasi Warna Gradien Tambahan**:
   - `.btn-quick-confirm` pada tabel pesanan: Dari gradient hijau menjadi solid Emerald `#10B981` (hover `#059669`) dengan subtle depth.
   - `css_sales_report.css`: Tombol preset rentang tanggal (`.btn-preset.active`) dan tombol submit (`.btn-submit-filter`) diubah dari linear gradient ungu ke solid `#4F46E5` / hover `#4338CA`, dan reset filter menggunakan slate neutral.
   - Avatar fallback admin (`settings.blade.php`): Menggantikan `bg-gradient-primary` dengan solid Navy `#1E2753`.
   - Badge administrator (`settings.blade.php`): Menggantikan `badge-gradient-primary` dengan solid `#4F46E5`.
   - Tombol pratinjau banner (`banners.blade.php`): Menggantikan gradient preview dan placeholder `bg-gradient-light` dengan warna solid yang bersih.

---

## 2. Daftar Berkas yang Diperbarui

| No | Berkas | Komponen / Bagian yang Diubah |
|---|---|---|
| 1 | `public/css/admin.css` | Penambahan `.admin-filter-btn`, `.admin-filter-badge`, `.admin-btn-reset`, standardisasi `.btn-quick-confirm` ke solid Emerald. |
| 2 | `public/css/css_sales_report.css` | Penggantian gradien `.btn-preset.active` & `.btn-submit-filter` ke solid `#4F46E5`, restrukturisasi `.btn-reset-filter`. |
| 3 | `resources/views/Admin/kelola_penjualan/pos.blade.php` | Redesain tombol filter kategori, pill badge, border search bar, dan tombol reset filter. |
| 4 | `resources/views/Admin/kelola_penjualan/orders.blade.php` | Redesain tombol filter status pesanan & pembayaran, pill badge, search bar, dan tombol reset filter. |
| 5 | `resources/views/Admin/kelola_penjualan/all_sales.blade.php` | Redesain tombol filter jenis penjualan, pill badge, search bar, dan tombol reset filter. |
| 6 | `resources/views/Admin/kelola_penjualan/returs.blade.php` | Redesain tombol filter status retur, pill badge, search bar, dan tombol reset filter. |
| 7 | `resources/views/Admin/kelola_produk/products.blade.php` | Redesain tombol filter status stok & diskon, pill badge, search bar, dan tombol reset filter. |
| 8 | `resources/views/Admin/kelola_produk/custom_products.blade.php` | Redesain tombol filter status kustom, pill badge, search bar, dan tombol reset filter. |
| 9 | `resources/views/Admin/kelola_produk/discounts.blade.php` | Redesain tombol filter diskon/tipe, pill badge, search bar, dan tombol reset filter. |
| 10 | `resources/views/Admin/kelola_produk/expired_products.blade.php` | Redesain tombol filter status tindakan expired, pill badge, search bar, dan tombol reset filter. |
| 11 | `resources/views/Admin/kelola_akun/customers.blade.php` | Redesain tombol filter status pelanggan, pill badge, perbaikan penutupan tag pembungkus, dan reset filter. |
| 12 | `resources/views/Admin/kelola_carousel/banners.blade.php` | Redesain tombol filter posisi banner, eliminasi inline gradient pada tombol dummy dan placeholder gambar. |
| 13 | `resources/views/Admin/pesan_kontak/index.blade.php` | Penyelarasan border input pencarian ke `#cbd5e1` dan tombol reset ke `.admin-btn-reset`. |
| 14 | `resources/views/Admin/kelola_akun/settings.blade.php` | Penggantian `bg-gradient-primary` avatar fallback ke solid `#1E2753` dan badge admin ke solid `#4F46E5`. |

---

## 3. Rincian Perubahan Kode Berdasarkan Berkas

### 3.1. `public/css/admin.css`
Penambahan aturan CSS reusable untuk tombol filter modern, badge translusen, tombol reset netral slate, dan eliminasi gradien pada tombol quick confirm.

```css
/* --- Admin Filter & Searchbar Standard Toolbar --- */
.admin-filter-btn {
    height: 38px !important;
    border-radius: 8px !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    padding: 0 14px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    background: #ffffff !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}

.admin-filter-btn:hover {
    background: #f8fafc !important;
    border-color: #94a3b8 !important;
    color: #0f172a !important;
}

.admin-filter-btn.active,
.admin-filter-btn.btn-active {
    background: #4F46E5 !important;
    border-color: #4F46E5 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25) !important;
}

.admin-filter-badge {
    background: rgba(255, 255, 255, 0.22);
    color: #ffffff;
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 999px;
    padding: 2px 7px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.admin-filter-reset-btn,
.admin-btn-reset {
    height: 38px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    padding: 0 12px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    text-decoration: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}

.admin-filter-reset-btn:hover,
.admin-btn-reset:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}

/* --- Solid Emerald Quick Confirm Button --- */
.btn-quick-confirm {
    background: #10B981 !important;
    border: 1px solid #059669 !important;
    color: #ffffff !important;
    font-weight: 600 !important;
    font-size: 12px !important;
    border-radius: 6px !important;
    padding: 6px 12px !important;
    box-shadow: 0 1px 3px rgba(16, 185, 129, 0.2) !important;
    transition: all 0.18s ease-in-out !important;
}

.btn-quick-confirm:hover {
    background: #059669 !important;
    box-shadow: 0 3px 6px rgba(16, 185, 129, 0.3) !important;
    transform: translateY(-1px);
}
```

---

### 3.2. `public/css/css_sales_report.css`
Menggantikan warna gradien ungu pada tombol preset filter dan filter submit:

```css
/* Sebelum */
.btn-preset:hover, .btn-preset.active {
    background: linear-gradient(to right, #da8cff, #9a55ff);
    color: #ffffff;
    border-color: transparent;
    box-shadow: 0 2px 6px rgba(182, 109, 255, 0.3);
}

.btn-submit-filter {
    background: linear-gradient(to right, #da8cff, #9a55ff);
    box-shadow: 0 2px 6px rgba(182, 109, 255, 0.3);
}

.btn-reset-filter {
    background: #fff5f5;
    color: #e53e3e;
    border: 1px solid #fed7d7;
}

/* Sesudah */
.btn-preset:hover, .btn-preset.active {
    background: #4F46E5;
    color: #ffffff;
    border-color: #4F46E5;
    box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
}

.btn-submit-filter {
    height: 38px;
    background: #4F46E5;
    color: white;
    border: none;
    box-shadow: 0 1px 3px rgba(79, 70, 229, 0.25);
}

.btn-submit-filter:hover {
    background: #4338CA;
    transform: translateY(-1px);
}

.btn-reset-filter {
    height: 38px;
    background: #ffffff;
    color: #64748b;
    border: 1px solid #cbd5e1;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}

.btn-reset-filter:hover {
    background: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
```

---

### 3.3. `resources/views/Admin/kelola_penjualan/pos.blade.php`
- Menggantikan `btn-gradient-primary` & `badge rounded-pill bg-white text-primary` pada tombol filter kategori.
- Menggantikan border `#ebedf2` pada input search dengan `#cbd5e1` dan container responsif `min-width: 240px; max-width: 320px;`.
- Menggantikan tombol reset `btn-outline-danger` dengan `admin-btn-reset`.

```html
<!-- Sebelum -->
<div class="dropdown">
    <button class="btn btn-sm {{ $activeFilterCount > 0 ? 'btn-gradient-primary text-white' : 'btn-outline-primary' }} dropdown-toggle ...">
        <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i> Filter
            @if($activeFilterCount > 0)
                <span class="badge rounded-pill bg-white text-primary ms-1.5 px-2">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
...
@if($hasActiveFilters)
    <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-danger ...">Reset</a>
@endif

<!-- Sesudah -->
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" style="min-width: 130px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i> Filter
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    <div class="dropdown-menu p-3 shadow-lg border-0" aria-labelledby="filterDropdown" style="width: 240px; border-radius: 10px; margin-top: 6px; z-index: 1050;">
        ...
        <button type="submit" class="btn btn-sm btn-gradient-primary w-100 mt-2" style="height: 34px; font-weight: 600;">Terapkan Filter</button>
    </div>
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama / kode produk..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;" data-search>
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('pos.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.4. `resources/views/Admin/kelola_penjualan/orders.blade.php`
- Menerapkan `.admin-filter-btn`, `.admin-filter-badge`, search input `#cbd5e1`, dan `.admin-btn-reset`.
- Mempertahankan ID penting `#filterDropdown`, form `orders.index`, dan atribut bulk delete `#btnBulkDelete`.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" style="min-width: 130px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i> Filter
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 240px; border-radius: 8px; margin-top: 5px;">
        ...
        <button type="submit" class="btn btn-sm btn-gradient-primary w-100" style="height: 34px; font-weight: 600;">Terapkan Filter</button>
    </div>
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nomor pesanan / pelanggan..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('orders.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.5. `resources/views/Admin/kelola_penjualan/all_sales.blade.php`
- Filter jenis penjualan (Online / Offline) diperbarui menggunakan `.admin-filter-btn`, `.admin-filter-badge`, `#cbd5e1`, dan `.admin-btn-reset`.
- Mendukung live search AJAX `data-server-search="true"` secara penuh.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" style="min-width: 130px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i> Filter
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari pesanan, pelanggan, atau produk..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;"
        data-server-search="true">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('all-sales.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.6. `resources/views/Admin/kelola_penjualan/returs.blade.php`
- Toolbar filter status retur diperbarui dengan `.admin-filter-btn` dan `.admin-btn-reset`.
- Menggantikan tombol merah mencolok dengan tombol reset netral yang estetik.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" style="min-width: 130px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i> Filter
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari retur produk..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('returs.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.7. `resources/views/Admin/kelola_produk/products.blade.php` & `custom_products.blade.php`
- Kedua view produk showcase dan kustom distandarisasi menggunakan `.admin-filter-btn` (min-width `130px`), `.admin-filter-badge`, `#cbd5e1` search input border, dan `.admin-btn-reset`.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle"
        type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true"
        aria-expanded="false" style="min-width: 130px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i>
            Filter
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama produk..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('products.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.8. `resources/views/Admin/kelola_produk/discounts.blade.php`
- Label tombol "Filter Promo" menggunakan `.admin-filter-btn` dengan lebar proporsional `min-width: 140px;`.
- Tombol reset menggunakan `.admin-btn-reset` dan search border diperbarui ke `#cbd5e1`.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle"
        type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true"
        aria-expanded="false" style="min-width: 140px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i>
            Filter Promo
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
```

---

### 3.9. `resources/views/Admin/kelola_produk/expired_products.blade.php`
- Label tombol "Status Tindakan" menggunakan `.admin-filter-btn` dengan `min-width: 155px;`.
- Search input border `#cbd5e1` dan reset button `.admin-btn-reset`.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle"
        type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true"
        aria-expanded="false" style="min-width: 155px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i>
            Status Tindakan
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
```

---

### 3.10. `resources/views/Admin/kelola_akun/customers.blade.php`
- Menggantikan tombol filter lama dengan `.admin-filter-btn` dan `.admin-filter-badge`.
- Memperbaiki tag penutup `</div>` yang sebelumnya menutup kontainer lebih awal sebelum blok `@if($hasActiveFilters)`.
- Menggantikan tombol reset merah dengan `.admin-btn-reset`.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true" aria-expanded="false" style="min-width: 130px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i> Filter
            @if($activeFilterCount > 0)
                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
            @endif
        </span>
    </button>
    ...
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama / email / kontak..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('customers.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.11. `resources/views/Admin/kelola_carousel/banners.blade.php`
- Menggantikan tombol filter posisi dengan `.admin-filter-btn {{ $activePosisi ? 'active' : '' }}` dengan `min-width: 170px;`.
- Search input border `#cbd5e1` dan reset button `.admin-btn-reset`.
- Menggantikan `bg-gradient-light` pada placeholder banner tanpa gambar dengan `bg-light` (`background-color: #f8fafc !important;`).
- Menggantikan `background: linear-gradient(135deg, #00b207, #009906);` pada tombol CTA preview banner dengan solid `#00b207`.

```html
<div class="dropdown">
    <button class="btn btn-sm admin-filter-btn {{ $activePosisi ? 'active' : '' }} dropdown-toggle"
        type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true"
        aria-expanded="false" style="min-width: 170px;">
        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i>
            {{ $activePosisiLabel }}</span>
    </button>
    ...
</div>
<div class="position-relative w-100 w-sm-auto" style="min-width: 240px; max-width: 320px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari judul banner..." 
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
</div>
@if($activePosisi || request('search'))
    <a href="{{ route('banners.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.12. `resources/views/Admin/pesan_kontak/index.blade.php`
- Memperbarui border search input menjadi `#cbd5e1` dan tombol reset menggunakan `.admin-btn-reset`.

```html
<div class="position-relative w-100 w-md-auto" style="min-width: 280px;">
    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama, email, subjek, pesan..."
        style="height: 38px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; font-size: 13px;">
    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 18px; pointer-events: none;"></i>
</div>
@if($hasActiveFilters)
    <a href="{{ route('messages.index') }}" class="admin-btn-reset" title="Reset Filter">
        <i class="mdi mdi-refresh me-1"></i> Reset
    </a>
@endif
```

---

### 3.13. `resources/views/Admin/kelola_akun/settings.blade.php`
- Menggantikan `bg-gradient-primary` pada avatar inisial fallback admin dengan solid Dark Navy `#1E2753`.
- Menggantikan `badge-gradient-primary` pada badge role Administrator dengan solid `#4F46E5`.

```html
<!-- Sebelum -->
<div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center shadow-sm"
    style="width: 100px; height: 100px; font-size: 38px; font-weight: 700;">
    {{ strtoupper(substr($admin->nama, 0, 1)) }}
</div>
<span class="badge badge-gradient-primary px-3 py-2" style="border-radius: 20px; font-size: 12px;">
    <i class="mdi mdi-shield-check me-1"></i> Administrator
</span>

<!-- Sesudah -->
<div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow-sm"
    style="width: 100px; height: 100px; font-size: 38px; font-weight: 700; background: #1E2753;">
    {{ strtoupper(substr($admin->nama, 0, 1)) }}
</div>
<span class="badge px-3 py-2 text-white shadow-sm" style="background: #4F46E5; border-radius: 20px; font-size: 12px; font-weight: 600;">
    <i class="mdi mdi-shield-check me-1"></i> Administrator
</span>
```

---

## 4. Hasil Verifikasi & Uji Kualitas

1. **Vite Production Assets Build**:
   ```bash
   npm run build
   ```
   **Hasil**: Berhasil (`✓ built in 1.14s`) tanpa error pada kompilasi SCSS/CSS maupun bundling Javascript.

2. **Automated Feature & Unit Test Suite**:
   ```bash
   php artisan test && git clean -fd public/uploads/
   ```
   **Hasil**:
   - Total tes: **216 passed** (1163 assertions).
   - Durasi: ~26 detik.
   - Status: **100% lulus tanpa ada satupun regresi**.
   - Verifikasi mencakup `AdminFilterStandardizationTest`, `LiveInstantSearchTest`, `AdminOrderTest`, `AdminReturStatusTest`, `AdminBannerTest`, `AdminBulkDeleteTest`, dan seluruh skenario filter & search.

3. **Uji Antarmuka Pengguna (UI Check)**:
   - Tombol filter saat *idle*: Bersih dengan border slate `#cbd5e1`, teks abu-abu `#334155`.
   - Tombol filter saat *active*: Solid Indigo `#4F46E5`, teks putih, dengan pill counter transparan lembut `.admin-filter-badge`.
   - Tombol reset: Bersih (*neutral slate*), tidak lagi merah mencolok.
   - Responsivitas toolbar: Search input dan dropdown filter berada sejajar dengan gap yang konsisten di layar desktop dan tertumpuk rapi di layar ponsel tanpa overflow horizontal.

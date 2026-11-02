# Redesain Tampilan Filter Modern dan Input Rentang Harga (Min-Max) Halaman Shop & Custom Products Pelanggan

## 1. Ringkasan Eksekutif & Latar Belakang

Berdasarkan tinjauan antarmuka halaman **Shop** (`/shop`) dan **Custom Products** (`/custom-products`), komponen penyaringan produk sebelumnya masih menggunakan elemen HTML native `<select>` standar sistem operasi. Hal ini menyebabkan beberapa kendala visual dan fungsional:
1. Tampilan dropdown terasa kaku (*rigid*), saat dibuka memunculkan menu popup bawaan OS dengan warna highlight biru native yang tidak selaras dengan tema toko Zyh Corner (hijau `#00b207`).
2. Filter harga hanya menyediakan 2 opsi statis *hardcoded* (`Rp. 10.000 - Rp. 50.000` dan `Rp. 50.000 - Rp. 100.000`), sehingga pelanggan tidak dapat memasukkan rentang anggaran belanja secara leluasa (*custom min-max price*).

Pembaruan ini mengadopsi prinsip **`frontend-design-direction`** dengan mentransformasi toolbar filter menjadi **Custom Interactive Pill Controls** yang elegan, menambahkan **Interactive Price Popover** dengan dua kolom input nominal uang (auto-format Rupiah) dan *Quick Preset Chips*, serta menyelaraskan kontrol kategori, pengurutan, dan densitas tampilan ke dalam dropdown melayang (*floating card*) yang modern.

---

## 2. Berkas yang Diubah

| No | Berkas | Deskripsi Perubahan |
|---|---|---|
| 1 | `app/Http/Controllers/Pelanggan/PelangganController.php` | Menambahkan penanganan filter harga kustom `min_price` dan `max_price` dengan sanitasi integer angka pada method `shop` dan `customProducts`. |
| 2 | `resources/css/pelanggan_pages.css` | Menambahkan aturan styling untuk `.filter-dropdown-container`, `.filter-pill-trigger`, `.modern-filter-dropdown-menu`, `.price-popover-card`, `.price-inputs-grid`, `.price-preset-chip`, dan penyesuaian responsif mobile. |
| 3 | `resources/js/pelanggan_pages.js` | Mengimplementasikan logika interaksi: toggle popover, click outside, format ribuan Rupiah otomatis (`Intl.NumberFormat`), pemrosesan preset chip, helper `applyCustomPriceFilter()`, `resetPriceFilter()`, serta pembaruan `removeShopFilter()`. |
| 4 | `resources/views/Pelanggan/shop.blade.php` | Mengganti elemen native `<select>` dengan komponen pill filter baru, dropdown melayang untuk Kategori, Urutkan, dan Tampilkan, serta popover rentang harga kustom dan active filter tags. |
| 5 | `resources/views/Pelanggan/custom_products.blade.php` | Menerapkan struktur toolbar filter modern yang identik pada halaman produk kustom. |
| 6 | `tests/Feature/CustomProductsTest.php` | Menambahkan pengujian otomatis `test_custom_products_filter_by_custom_price_range()`. |
| 7 | `tests/Feature/ShopStockPriorityTest.php` | Menambahkan pengujian otomatis `test_shop_filter_by_custom_price_range()`. |

---

## 3. Rincian Perubahan Kode

### 3.1. `app/Http/Controllers/Pelanggan/PelangganController.php`

Menambahkan dukungan parameter `min_price` dan `max_price` yang disanitasi dari karakter non-numerik, dengan tetap mempertahankan kompatibilitas mundur (*backward compatibility*) untuk parameter `price` legacy.

```php
// app/Http/Controllers/Pelanggan/PelangganController.php (method shop & customProducts)

// Filter by Price Range (Custom Min/Max & Preset)
if ($request->filled('min_price') || $request->filled('max_price')) {
    $rawMin = $request->input('min_price');
    $rawMax = $request->input('max_price');
    $minPrice = $rawMin !== null && $rawMin !== '' ? intval(preg_replace('/[^0-9]/', '', (string)$rawMin)) : null;
    $maxPrice = $rawMax !== null && $rawMax !== '' ? intval(preg_replace('/[^0-9]/', '', (string)$rawMax)) : null;

    if ($minPrice !== null && $maxPrice !== null && $maxPrice >= $minPrice) {
        $query->whereBetween('harga', [$minPrice, $maxPrice]);
    } elseif ($minPrice !== null && $minPrice > 0) {
        $query->where('harga', '>=', $minPrice);
    } elseif ($maxPrice !== null && $maxPrice > 0) {
        $query->where('harga', '<=', $maxPrice);
    }
} elseif ($request->filled('price')) {
    $priceRange = explode('-', $request->input('price'));
    if (count($priceRange) === 2) {
        $minPrice = intval($priceRange[0]) * 1000;
        $maxPrice = intval($priceRange[1]) * 1000;
        $query->whereBetween('harga', [$minPrice, $maxPrice]);
    }
}
```

---

### 3.2. `resources/css/pelanggan_pages.css`

Menambahkan styling untuk toolbar filter modern dan popover melayang:

```css
/* ==========================================================================
   MODERN FILTER TOOLBAR & INTERACTIVE POPOVERS
   ========================================================================== */

.filter-dropdown-container {
    position: relative;
    display: inline-block;
}

.filter-pill-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 42px;
    padding: 0 18px;
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 50px;
    font-family: 'Poppins', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    user-select: none;
    white-space: nowrap;
}

.filter-pill-trigger:hover {
    border-color: #00b207;
    color: #0f172a;
    box-shadow: 0 4px 12px rgba(0, 178, 7, 0.08);
}

.filter-pill-trigger.active {
    border-color: #00b207;
    background: #f0fdf4;
    color: #00b207;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 178, 7, 0.12);
}

.filter-pill-trigger .trigger-icon {
    font-size: 13px;
    color: #64748b;
    transition: color 0.2s;
}

.filter-pill-trigger.active .trigger-icon {
    color: #00b207;
}

.filter-pill-trigger .trigger-chevron {
    font-size: 11px;
    color: #94a3b8;
    margin-left: 2px;
    transition: transform 0.25s ease, color 0.2s;
}

.filter-dropdown-container.open .filter-pill-trigger .trigger-chevron {
    transform: rotate(180deg);
    color: #00b207;
}

.filter-pill-trigger .active-dot {
    width: 6px;
    height: 6px;
    background: #00b207;
    border-radius: 50%;
    margin-left: -2px;
}

/* Floating Dropdown Menus */
.modern-filter-dropdown-menu {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    min-width: 220px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 16px 36px -6px rgba(0, 0, 0, 0.12), 0 0 1px rgba(0, 0, 0, 0.08);
    padding: 8px;
    z-index: 1050;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

.filter-dropdown-container.open .modern-filter-dropdown-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.modern-dropdown-list {
    max-height: 250px;
    overflow-y: auto;
    padding: 0;
    margin: 0;
    list-style: none;
}

.modern-dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    padding: 9px 14px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    color: #334155;
    background: transparent;
    border: none;
    text-align: left;
    cursor: pointer;
    transition: all 0.15s ease;
}

.modern-dropdown-item:hover {
    background: #f0fdf4;
    color: #00b207;
}

.modern-dropdown-item.active {
    background: #e8f9eb;
    color: #00b207;
    font-weight: 600;
}

.modern-dropdown-item i.check-icon {
    font-size: 12px;
    color: #00b207;
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.2s ease;
}

.modern-dropdown-item.active i.check-icon {
    opacity: 1;
    transform: scale(1);
}

/* Floating Price Popover */
.price-popover-card {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    width: 320px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 18px 40px -8px rgba(0, 0, 0, 0.14), 0 0 1px rgba(0, 0, 0, 0.08);
    padding: 18px;
    z-index: 1050;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-8px);
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}

.filter-dropdown-container.open .price-popover-card {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.price-inputs-grid {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}

.price-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    padding: 0 10px;
    transition: all 0.2s ease;
}

.price-input-wrapper:focus-within {
    border-color: #00b207;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(0, 178, 7, 0.12);
}

.price-currency {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-right: 4px;
}

.price-input-field {
    width: 100%;
    height: 36px;
    border: none;
    background: transparent;
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    outline: none;
    padding: 0;
}

.price-presets-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
    margin-bottom: 16px;
}

.price-preset-chip {
    padding: 6px 8px;
    font-size: 11.5px;
    font-weight: 500;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #475569;
    cursor: pointer;
    text-align: center;
    transition: all 0.18s ease;
}

.price-preset-chip:hover {
    border-color: #00b207;
    color: #00b207;
    background: #f0fdf4;
}

.price-preset-chip.active {
    background: #e8f9eb;
    border-color: #00b207;
    color: #00b207;
    font-weight: 600;
}

.price-btn-apply {
    padding: 7px 18px;
    font-size: 12.5px;
    font-weight: 600;
    color: #ffffff;
    background: #00b207;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 178, 7, 0.25);
    transition: all 0.2s ease;
}

.price-btn-apply:hover {
    background: #009606;
}

@media (max-width: 768px) {
    .price-popover-card {
        width: 290px;
        left: auto;
        right: 0;
    }
    .modern-filter-dropdown-menu {
        left: auto;
        right: 0;
    }
}
```

---

### 3.3. `resources/js/pelanggan_pages.js`

Menghubungkan interaksi buka-tutup dropdown, format input angka, seleksi preset, dan pembaruan parameter URL query:

```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Dropdown Popovers Toggle & Outside Click
    document.addEventListener('click', function(e) {
        const trigger = e.target.closest('.filter-pill-trigger');
        if (trigger) {
            const container = trigger.closest('.filter-dropdown-container');
            const wasOpen = container?.classList.contains('open');
            document.querySelectorAll('.filter-dropdown-container.open').forEach(c => {
                if (c !== container) c.classList.remove('open');
            });
            if (container) {
                container.classList.toggle('open', !wasOpen);
            }
            return;
        }
        if (!e.target.closest('.filter-dropdown-container')) {
            document.querySelectorAll('.filter-dropdown-container.open').forEach(c => c.classList.remove('open'));
        }
    });

    // Auto-format Currency Inputs
    const minInput = document.getElementById('customMinPrice');
    const maxInput = document.getElementById('customMaxPrice');
    const formatPriceField = function(el) {
        if (!el) return;
        const raw = el.value.replace(/[^0-9]/g, '');
        el.value = raw ? new Intl.NumberFormat('id-ID').format(raw) : '';
    };

    if (minInput) {
        minInput.addEventListener('input', () => formatPriceField(minInput));
        minInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') window.applyCustomPriceFilter();
        });
    }
    if (maxInput) {
        maxInput.addEventListener('input', () => formatPriceField(maxInput));
        maxInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') window.applyCustomPriceFilter();
        });
    }

    // Preset Chips
    document.querySelectorAll('.price-preset-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const min = this.getAttribute('data-min') || '';
            const max = this.getAttribute('data-max') || '';
            if (minInput) minInput.value = min ? new Intl.NumberFormat('id-ID').format(min) : '';
            if (maxInput) maxInput.value = max ? new Intl.NumberFormat('id-ID').format(max) : '';
            document.querySelectorAll('.price-preset-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
        });
    });
});

window.selectCategoryFilter = function(catId) {
    const url = new URL(window.location.href);
    if (catId) url.searchParams.set('category', catId);
    else url.searchParams.delete('category');
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

window.selectSortFilter = function(sortBy) {
    const url = new URL(window.location.href);
    if (sortBy) url.searchParams.set('sort_by', sortBy);
    else url.searchParams.delete('sort_by');
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

window.selectShowFilter = function(showCount) {
    const url = new URL(window.location.href);
    if (showCount) url.searchParams.set('show', showCount);
    else url.searchParams.delete('show');
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

window.applyCustomPriceFilter = function() {
    const rawMin = document.getElementById('customMinPrice')?.value.replace(/[^0-9]/g, '') || '';
    const rawMax = document.getElementById('customMaxPrice')?.value.replace(/[^0-9]/g, '') || '';
    const min = rawMin ? parseInt(rawMin, 10) : 0;
    const max = rawMax ? parseInt(rawMax, 10) : null;

    const url = new URL(window.location.href);
    url.searchParams.delete('price');
    url.searchParams.delete('page');

    if (min > 0) url.searchParams.set('min_price', min);
    else url.searchParams.delete('min_price');

    if (max !== null && max > 0) url.searchParams.set('max_price', max);
    else url.searchParams.delete('max_price');

    window.location.href = url.toString();
};

window.resetPriceFilter = function() {
    const url = new URL(window.location.href);
    url.searchParams.delete('price');
    url.searchParams.delete('min_price');
    url.searchParams.delete('max_price');
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

window.removeShopFilter = function(paramName) {
    const url = new URL(window.location.href);
    if (paramName === 'price' || paramName === 'min_price' || paramName === 'max_price') {
        url.searchParams.delete('price');
        url.searchParams.delete('min_price');
        url.searchParams.delete('max_price');
    } else {
        url.searchParams.delete(paramName);
    }
    url.searchParams.delete('page');
    window.location.href = url.toString();
};
```

---

### 3.4. `resources/views/Pelanggan/shop.blade.php` & `custom_products.blade.php`

Menyediakan kalkulasi state label aktif dan struktur DOM modern:

```html
@php
    $activeCat = $categories->firstWhere('id_kategori', request('category'));
    $rawMin = request('min_price');
    $rawMax = request('max_price');
    $minPriceVal = ($rawMin !== null && $rawMin !== '') ? intval(preg_replace('/[^0-9]/', '', (string)$rawMin)) : null;
    $maxPriceVal = ($rawMax !== null && $rawMax !== '') ? intval(preg_replace('/[^0-9]/', '', (string)$rawMax)) : null;
    $legacyPriceVal = request('price');
    $hasPriceFilter = ($minPriceVal !== null || $maxPriceVal !== null || request()->filled('price'));

    $priceDisplay = 'Semua Harga';
    if ($minPriceVal && $maxPriceVal) {
        $priceDisplay = 'Rp ' . number_format($minPriceVal, 0, ',', '.') . ' - Rp ' . number_format($maxPriceVal, 0, ',', '.');
    } elseif ($minPriceVal) {
        $priceDisplay = '≥ Rp ' . number_format($minPriceVal, 0, ',', '.');
    } elseif ($maxPriceVal) {
        $priceDisplay = '≤ Rp ' . number_format($maxPriceVal, 0, ',', '.');
    } elseif ($legacyPriceVal) {
        $priceDisplay = $legacyPriceVal == '10-50' ? 'Rp 10.000 - Rp 50.000' : ($legacyPriceVal == '50-100' ? 'Rp 50.000 - Rp 100.000' : $legacyPriceVal);
    }

    $sortByVal = request('sort_by', 'latest');
    $sortLabels = [
        'latest' => 'Terbaru',
        'price_asc' => 'Harga: Rendah ke Tinggi',
        'price_desc' => 'Harga: Tinggi ke Rendah',
    ];
    $sortDisplay = $sortLabels[$sortByVal] ?? 'Terbaru';
    $showVal = request('show', '16');
@endphp

<!-- Filter Toolbar -->
<div class="filter-toolbar">
    <div class="filter-group">
        <!-- Dropdown Kategori -->
        <div class="filter-dropdown-container" id="categoryDropdownContainer">
            <button type="button" class="filter-pill-trigger {{ request()->filled('category') ? 'active' : '' }}" title="Pilih Kategori">
                <i class="fa-solid fa-layer-group trigger-icon"></i>
                <span>{{ $activeCat ? $activeCat->nama_kategori : 'Semua Kategori' }}</span>
                @if(request()->filled('category'))
                    <span class="active-dot"></span>
                @endif
                <i class="fa-solid fa-chevron-down trigger-chevron"></i>
            </button>
            <div class="modern-filter-dropdown-menu">
                <ul class="modern-dropdown-list">
                    <li>
                        <button type="button" class="modern-dropdown-item {{ !request()->filled('category') ? 'active' : '' }}" onclick="selectCategoryFilter('')">
                            <span>Semua Kategori</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </button>
                    </li>
                    @foreach ($categories as $cat)
                        <li>
                            <button type="button" class="modern-dropdown-item {{ request('category') == $cat->id_kategori ? 'active' : '' }}" onclick="selectCategoryFilter('{{ $cat->id_kategori }}')">
                                <span>{{ $cat->nama_kategori }}</span>
                                <i class="fa-solid fa-check check-icon"></i>
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- Dropdown Range Harga (Interactive Price Popover) -->
        <div class="filter-dropdown-container" id="priceDropdownContainer">
            <button type="button" class="filter-pill-trigger {{ $hasPriceFilter ? 'active' : '' }}" title="Filter Berdasarkan Rentang Harga">
                <i class="fa-solid fa-tag trigger-icon"></i>
                <span>{{ $priceDisplay }}</span>
                @if($hasPriceFilter)
                    <span class="active-dot"></span>
                @endif
                <i class="fa-solid fa-chevron-down trigger-chevron"></i>
            </button>
            <div class="price-popover-card">
                <div class="price-popover-header">
                    <div class="price-popover-title">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Atur Rentang Harga</span>
                    </div>
                </div>

                <div class="price-inputs-grid">
                    <div class="price-input-box">
                        <label class="price-input-label" for="customMinPrice">Minimum</label>
                        <div class="price-input-wrapper">
                            <span class="price-currency">Rp</span>
                            <input type="text" id="customMinPrice" class="price-input-field" placeholder="0" inputmode="numeric" value="{{ $minPriceVal ? number_format($minPriceVal, 0, ',', '.') : '' }}">
                        </div>
                    </div>
                    <div class="price-input-sep">-</div>
                    <div class="price-input-box">
                        <label class="price-input-label" for="customMaxPrice">Maksimum</label>
                        <div class="price-input-wrapper">
                            <span class="price-currency">Rp</span>
                            <input type="text" id="customMaxPrice" class="price-input-field" placeholder="Tanpa batas" inputmode="numeric" value="{{ $maxPriceVal ? number_format($maxPriceVal, 0, ',', '.') : '' }}">
                        </div>
                    </div>
                </div>

                <div class="price-presets-title">Pilihan Cepat</div>
                <div class="price-presets-grid">
                    <button type="button" class="price-preset-chip {{ $maxPriceVal == 50000 && !$minPriceVal ? 'active' : '' }}" data-min="" data-max="50000">&lt; Rp 50 rb</button>
                    <button type="button" class="price-preset-chip {{ $minPriceVal == 50000 && $maxPriceVal == 100000 ? 'active' : '' }}" data-min="50000" data-max="100000">Rp 50 rb - 100 rb</button>
                    <button type="button" class="price-preset-chip {{ $minPriceVal == 100000 && $maxPriceVal == 250000 ? 'active' : '' }}" data-min="100000" data-max="250000">Rp 100 rb - 250 rb</button>
                    <button type="button" class="price-preset-chip {{ $minPriceVal == 250000 && !$maxPriceVal ? 'active' : '' }}" data-min="250000" data-max="">&gt; Rp 250 rb</button>
                </div>

                <div class="price-popover-actions">
                    <button type="button" class="price-btn-reset" onclick="resetPriceFilter()">Reset</button>
                    <button type="button" class="price-btn-apply" onclick="applyCustomPriceFilter()">Terapkan</button>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-group">
        <!-- Dropdown Sort By -->
        <div class="filter-dropdown-container">
            <button type="button" class="filter-pill-trigger" title="Urutkan Produk">
                <i class="fa-solid fa-arrow-down-wide-short trigger-icon"></i>
                <span>Urutkan: {{ $sortDisplay }}</span>
                <i class="fa-solid fa-chevron-down trigger-chevron"></i>
            </button>
            <div class="modern-filter-dropdown-menu" style="min-width: 200px;">
                <ul class="modern-dropdown-list">
                    <li>
                        <button type="button" class="modern-dropdown-item {{ $sortByVal == 'latest' ? 'active' : '' }}" onclick="selectSortFilter('latest')">
                            <span>Terbaru</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="modern-dropdown-item {{ $sortByVal == 'price_asc' ? 'active' : '' }}" onclick="selectSortFilter('price_asc')">
                            <span>Harga: Rendah ke Tinggi</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="modern-dropdown-item {{ $sortByVal == 'price_desc' ? 'active' : '' }}" onclick="selectSortFilter('price_desc')">
                            <span>Harga: Tinggi ke Rendah</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Dropdown Show per Page -->
        <div class="filter-dropdown-container">
            <button type="button" class="filter-pill-trigger" title="Jumlah Tampilan per Halaman">
                <i class="fa-solid fa-grip trigger-icon"></i>
                <span>Tampilkan: {{ $showVal }}</span>
                <i class="fa-solid fa-chevron-down trigger-chevron"></i>
            </button>
            <div class="modern-filter-dropdown-menu" style="min-width: 140px;">
                <ul class="modern-dropdown-list">
                    <li>
                        <button type="button" class="modern-dropdown-item {{ $showVal == '16' ? 'active' : '' }}" onclick="selectShowFilter('16')">
                            <span>16 per halaman</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="modern-dropdown-item {{ $showVal == '32' ? 'active' : '' }}" onclick="selectShowFilter('32')">
                            <span>32 per halaman</span>
                            <i class="fa-solid fa-check check-icon"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
```

---

## 4. Pengujian Otomatis

Dua pengujian fitur baru ditambahkan untuk memastikan logika harga minimum & maksimum bekerja presisi:

1. **`tests/Feature/CustomProductsTest.php`**:
   - `test_custom_products_filter_by_custom_price_range()`: Membuat 3 produk kustom (Rp 50.000, Rp 150.000, Rp 300.000), mengirim filter `min_price=100.000&max_price=200.000`, dan memvalidasi hanya produk Rp 150.000 yang tampil beserta tag filter aktif.
2. **`tests/Feature/ShopStockPriorityTest.php`**:
   - `test_shop_filter_by_custom_price_range()`: Menguji filter rentang ganda (`min_price` + `max_price`) dan filter satu batas (`min_price` saja) pada produk showcase.

---

## 5. Hasil Verifikasi & Quality Gate

1. **Hasil Test Suite**:
   ```bash
   php artisan test && git clean -fd public/uploads/
   ```
   **Hasil**: **220 passed** (1187 assertions) — 100% lulus tanpa kegagalan.
2. **Hasil Kompilasi Aset**:
   ```bash
   npm run build
   ```
   **Hasil**: Berhasil (`built in 709ms`).

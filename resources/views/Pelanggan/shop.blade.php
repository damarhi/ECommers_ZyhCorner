@extends('Layout.layout_pelanggan')

@section('title', 'Shop - Zyh Corner')

@section('breadcrumb')
    <a href="#">Categories</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Cake</span>
@endsection



@section('content')
    <div class="shop-container">

        <!-- Promo Banner -->
        @if(isset($shopBanners) && $shopBanners->count() > 1)
            @include('Pelanggan.partials.multi_banner_grid', ['banners' => $shopBanners])
        @elseif(isset($shopBanner) && $shopBanner && $shopBanner->status === 'Aktif')
            <div class="shop-promo-banner" @if($shopBanner->gambar_banner && Storage::disk('public')->exists($shopBanner->gambar_banner)) style="background-image: url('{{ asset('storage/' . $shopBanner->gambar_banner) }}'); background-size: cover; background-position: center right;" @endif>
                <div class="spb-content">
                    <span class="spb-pill-badge badge-shop">
                        <i class="fa-solid fa-tags"></i> {{ $shopBanner->subjudul ?? 'Exclusive Pastry' }}
                    </span>
                    <h2 class="spb-title">{{ $shopBanner->judul_banner }}</h2>
                    <p class="spb-desc">Pilihan produk roti segar, kue ulang tahun premium, dan pastry berkualitas tinggi langsung dari oven.</p>
                    @if($shopBanner->link_url)
                        <a href="{{ $shopBanner->link_url }}" class="spb-btn">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    @else
                        <button class="spb-btn" type="button">Shop Now <i class="fa-solid fa-arrow-right ms-2"></i></button>
                    @endif
                </div>
            </div>
        @endif

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

        <!-- Active Filters -->
        @if (request()->filled('category') || $hasPriceFilter || request()->filled('search'))
            <div class="active-filters-row">
                <div class="active-filters">
                    <span>Active Filters:</span>
                    @if (request()->filled('search'))
                        <div class="filter-tag" onclick="removeShopFilter('search')">
                            Search: "{{ request('search') }}" <i class="fa-solid fa-xmark"></i>
                        </div>
                    @endif
                    @if (request()->filled('category') && $activeCat)
                        <div class="filter-tag" onclick="removeShopFilter('category')">
                            {{ $activeCat->nama_kategori }} <i class="fa-solid fa-xmark"></i>
                        </div>
                    @endif
                    @if ($hasPriceFilter)
                        <div class="filter-tag" onclick="removeShopFilter('price')">
                            {{ $priceDisplay }} <i class="fa-solid fa-xmark"></i>
                        </div>
                    @endif
                    <a href="{{ route('shop') }}" class="clear-all-filters-btn">Clear All</a>
                </div>
                <div class="results-count">
                    <span>{{ number_format($products->total(), 0, ',', '.') }}</span> Results found.
                </div>
            </div>
        @else
            <div class="active-filters-row" style="justify-content: flex-end;">
                <div class="results-count">
                    <span>{{ number_format($products->total(), 0, ',', '.') }}</span> Results found.
                </div>
            </div>
        @endif

        <!-- Product Grid -->
        <div class="product-grid">
            @forelse ($products as $index => $product)
                <div class="product-card shop-product-col {{ $product->stok == 0 ? 'is-out-of-stock' : '' }} {{ $product->is_signature ? 'is-signature' : '' }}" style="cursor: pointer;"
                    data-search="{{ strtolower($product->nama_produk . ' ' . ($product->kategori->nama_kategori ?? '') . ' ' . $product->deskripsi) }}"
                    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}'">
                    <div class="product-image">
                        @if ($product->has_diskon)
                            <div class="badge" style="background-color: #ff8a00; color: white;">{{ $product->diskon_badge }}</div>
                        @elseif ($product->stok == 0)
                            <div class="badge out-of-stock">Habis</div>
                        @endif

                        <div class="product-actions">
                            <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>

                        <a href="{{ route('product.detail', $product->id_produk) }}" style="display:block;" onclick="event.stopPropagation();">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        @if ($product->is_signature || $product->stok == 0)
                            <div class="product-meta-eyebrow mb-1">
                                @if ($product->is_signature)
                                    <span class="signature-badge eyebrow-signature"><i class="fa-solid fa-crown"></i> Signature</span>
                                @endif
                                @if ($product->stok == 0)
                                    <span class="stock-status-inline">Habis</span>
                                @endif
                            </div>
                        @endif
                        <a href="{{ route('product.detail', $product->id_produk) }}"
                            class="product-name" onclick="event.stopPropagation();">{{ $product->nama_produk }}</a>
                        <div class="product-price">
                            @if ($product->has_diskon)
                                Rp. {{ number_format($product->harga_diskon, 0, ',', '.') }}
                                <span class="old" style="font-size: 13px; text-decoration: line-through; color: #999; margin-left: 5px;">Rp. {{ number_format($product->harga, 0, ',', '.') }}</span>
                            @else
                                Rp. {{ number_format($product->harga, 0, ',', '.') }}
                            @endif
                        </div>

                        @if ($product->stok > 0)
                            <div class="cart-btn" title="Lihat Preview & Pesan"
                                onclick="event.stopPropagation(); openProductModal(this);"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga_diskon }}"
                                data-old-price="{{ $product->has_diskon ? 'Rp. ' . number_format($product->harga, 0, ',', '.') : '' }}"
                                data-discount-badge="{{ $product->diskon_badge ?? '' }}"
                                data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $product->has_diskon ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-is-signature="{{ $product->is_signature ? '1' : '0' }}"
                                data-expired="{{ $product->tanggal_expired ? \Carbon\Carbon::parse($product->tanggal_expired)->format('d M Y') : '' }}"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-solid fa-bag-shopping"></i>
                            </div>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis" onclick="event.stopPropagation();">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 50px 20px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                    <i class="fa-solid fa-magnifying-glass" style="font-size: 42px; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px;">Produk tidak ditemukan</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
                        @if(request()->filled('search'))
                            Tidak ada produk yang cocok dengan kata kunci "{{ request('search') }}".
                        @else
                            Belum ada produk yang tersedia untuk filter ini.
                        @endif
                    </p>
                    <a href="{{ route('shop') }}" class="btn" style="background: #00b207; color: white; border-radius: 30px; padding: 10px 24px; font-weight: 500; text-decoration: none; display: inline-block;">
                        Lihat Semua Produk
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if ($products->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination">
                    @if (!$products->onFirstPage())
                        <a href="{{ $products->previousPageUrl() }}" class="page-arrow"><i
                                class="fa-solid fa-chevron-left"></i></a>
                    @endif

                    @for ($i = 1; $i <= $products->lastPage(); $i++)
                        @if ($i == $products->currentPage())
                            <span class="page-link active">{{ $i }}</span>
                        @else
                            <a href="{{ $products->url($i) }}" class="page-link">{{ $i }}</a>
                        @endif
                    @endfor

                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="page-arrow"><i
                                class="fa-solid fa-chevron-right"></i></a>
                    @endif
                </div>
            </div>
        @endif

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof gsap !== 'undefined') {
                gsap.from('.shop-promo-banner, .single-banner-card, .banner-grid-card', {
                    y: 40,
                    opacity: 0,
                    scale: 0.96,
                    duration: 0.85,
                    stagger: 0.15,
                    ease: 'power3.out'
                });

                gsap.from('.spb-content h4, .spb-content h2, .spb-content .spb-btn', {
                    y: 25,
                    opacity: 0,
                    duration: 0.65,
                    stagger: 0.12,
                    ease: 'back.out(1.6)',
                    delay: 0.2
                });
            }
        });
    </script>
@endsection

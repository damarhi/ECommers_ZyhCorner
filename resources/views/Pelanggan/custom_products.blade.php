@extends('Layout.layout_pelanggan')

@section('title', 'Custom Products - Zyh Corner')


@section('content')
    <div class="shop-container">

        <!-- Promo Banner -->
        @if(isset($customBanners) && $customBanners->count() > 1)
            @include('Pelanggan.partials.multi_banner_grid', ['banners' => $customBanners])
        @elseif(isset($customBanner) && $customBanner && $customBanner->status === 'Aktif')
            <div class="shop-promo-banner" @if($customBanner->gambar_banner && Storage::disk('public')->exists($customBanner->gambar_banner)) style="background-image: url('{{ asset('storage/' . $customBanner->gambar_banner) }}'); background-size: cover; background-position: center right;" @endif>
                <div class="spb-content">
                    <span class="spb-pill-badge badge-custom">
                        <i class="fa-solid fa-cake-candles"></i> {{ $customBanner->subjudul ?? 'Custom Cake Studio' }}
                    </span>
                    <h2 class="spb-title">{{ $customBanner->judul_banner }}</h2>
                    <p class="spb-desc">Wujudkan kue impian untuk perayaan spesial Anda. Bebas tentukan tema, desain, rasa, dan ukuran.</p>
                    @if($customBanner->link_url)
                        <a href="{{ $customBanner->link_url }}" class="spb-btn">Pesan Sekarang <i class="fa-solid fa-arrow-right"></i></a>
                    @else
                        <button class="spb-btn" type="button">Pesan Sekarang <i class="fa-solid fa-arrow-right"></i></button>
                    @endif
                </div>
            </div>
        @endif

        <!-- Filter Toolbar -->
        <div class="filter-toolbar">
            <div class="filter-group">
                <div class="select-wrapper">
                    <select name="category" id="filterCategory" class="filter-select">
                        <option value="">Select Category</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id_kategori }}" {{ request('category') == $cat->id_kategori ? 'selected' : '' }}>
                                {{ $cat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="select-wrapper">
                    <select name="price" id="filterPrice" class="filter-select">
                        <option value="">Select Price</option>
                        <option value="10-50" {{ request('price') == '10-50' ? 'selected' : '' }}>Rp. 10.000 - Rp. 50.000</option>
                        <option value="50-100" {{ request('price') == '50-100' ? 'selected' : '' }}>Rp. 50.000 - Rp. 100.000</option>
                    </select>
                </div>
            </div>

            <div class="filter-group">
                <div class="select-wrapper">
                    <select name="sort_by" id="filterSortBy" class="filter-select" style="min-width: 150px;">
                        <option value="latest" {{ request('sort_by', 'latest') == 'latest' ? 'selected' : '' }}>Sort by: Latest</option>
                        <option value="price_asc" {{ request('sort_by') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_desc" {{ request('sort_by') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
                <div class="select-wrapper">
                    <select name="show" id="filterShow" class="filter-select" style="min-width: 100px;">
                        <option value="16" {{ request('show', '16') == '16' ? 'selected' : '' }}>Show: 16</option>
                        <option value="32" {{ request('show') == '32' ? 'selected' : '' }}>Show: 32</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Active Filters -->
        @if (request()->filled('category') || request()->filled('price') || request()->filled('search'))
            <div class="active-filters-row">
                <div class="active-filters">
                    <span>Active Filters:</span>
                    @if (request()->filled('search'))
                        <div class="filter-tag" onclick="removeShopFilter('search')">
                            Search: "{{ request('search') }}" <i class="fa-solid fa-xmark"></i>
                        </div>
                    @endif
                    @if (request()->filled('category'))
                        @php
                            $activeCat = $categories->firstWhere('id_kategori', request('category'));
                        @endphp
                        @if ($activeCat)
                            <div class="filter-tag" onclick="removeShopFilter('category')">
                                {{ $activeCat->nama_kategori }} <i class="fa-solid fa-xmark"></i>
                            </div>
                        @endif
                    @endif
                    @if (request()->filled('price'))
                        @php
                            $priceVal = request('price');
                            $priceLabel = $priceVal == '10-50' ? 'Rp. 10.000 - Rp. 50.000' : ($priceVal == '50-100' ? 'Rp. 50.000 - Rp. 100.000' : $priceVal);
                        @endphp
                        <div class="filter-tag" onclick="removeShopFilter('price')">
                            {{ $priceLabel }} <i class="fa-solid fa-xmark"></i>
                        </div>
                    @endif
                    <a href="{{ route('custom-products') }}" class="clear-all-filters-btn">Clear All</a>
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
                <div class="product-card" style="cursor: pointer;"
                    onclick="window.location.href='{{ route('product.detail', $product->id_produk) }}?custom=1'">
                    <div class="product-image">
                        @if ($product->stok == 0)
                            <div class="badge out-of-stock">Habis</div>
                        @endif

                        <div class="product-actions">
                            <div class="action-btn" title="Quick View" onclick="event.stopPropagation(); openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="0" data-stock="{{ $product->stok }}"
                                data-custom="1"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>

                        <a href="{{ route('product.detail', $product->id_produk) }}?custom=1" style="display:block;" onclick="event.stopPropagation();">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        <a href="{{ route('product.detail', $product->id_produk) }}?custom=1"
                            class="product-name" onclick="event.stopPropagation();">{{ $product->nama_produk }}</a>
                        <div class="product-price">
                            @if ($product->harga_max && $product->harga_max > $product->harga)
                                Rp. {{ number_format($product->harga, 0, ',', '.') }} - Rp. {{ number_format($product->harga_max, 0, ',', '.') }}
                            @else
                                Rp. {{ number_format($product->harga, 0, ',', '.') }}
                            @endif
                        </div>

                        @if ($product->stok > 0)
                            @php
                                $storeName = $storeSettings['store_name'] ?? 'Zyh Corner';
                                $message = urlencode("Halo " . $storeName . ", saya tertarik untuk memesan Custom Product: " . $product->nama_produk . " (Harga: Rp. " . number_format($product->harga, 0, ',', '.') . "). Mohon info lebih lanjut.");
                                $waUrl = "https://wa.me/" . ($storeSettings['wa_number'] ?? '628123456789') . "?text=" . $message;
                            @endphp
                            <a href="{{ $waUrl }}" target="_blank" class="cart-btn" style="background-color: #25d366; color: white;" title="Pesan via WhatsApp" onclick="event.stopPropagation();">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
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
                    <h3 style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 8px;">Produk Kustom tidak ditemukan</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 20px;">
                        @if(request()->filled('search'))
                            Tidak ada produk kustom yang cocok dengan kata kunci "{{ request('search') }}".
                        @else
                            Belum ada produk kustom yang tersedia untuk filter ini.
                        @endif
                    </p>
                    <a href="{{ route('custom-products') }}" class="btn" style="background: #00b207; color: white; border-radius: 30px; padding: 10px 24px; font-weight: 500; text-decoration: none; display: inline-block;">
                        Lihat Semua Produk Kustom
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

                gsap.from('.product-card', {
                    y: 35,
                    opacity: 0,
                    duration: 0.75,
                    stagger: 0.08,
                    ease: 'power3.out',
                    delay: 0.3
                });
            }
        });
    </script>
@endsection

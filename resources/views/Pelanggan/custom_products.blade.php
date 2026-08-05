@extends('Layout.layout_pelanggan')

@section('title', 'Custom Products - Zyh Corner')


@section('content')
    <div class="shop-container">

        <!-- Promo Banner -->
        @if(isset($customBanners) && $customBanners->count() > 1)
            @include('Pelanggan.partials.multi_banner_grid', ['banners' => $customBanners])
        @elseif(isset($customBanner) && $customBanner && $customBanner->status === 'Aktif')
            <div class="shop-promo-banner" @if($customBanner->gambar_banner && Storage::disk('public')->exists($customBanner->gambar_banner)) style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('{{ asset('storage/' . $customBanner->gambar_banner) }}'); background-size: cover; background-position: center;" @endif>
                <div class="spb-content">
                    @if($customBanner->subjudul)
                        <h4>{{ $customBanner->subjudul }}</h4>
                    @endif
                    <h2>{{ $customBanner->judul_banner }}</h2>
                    @if($customBanner->link_url)
                        <a href="{{ $customBanner->link_url }}" class="spb-btn text-decoration-none d-inline-flex align-items-center mt-3">Pesan Sekarang <i class="fa-solid fa-arrow-right ms-2"></i></a>
                    @else
                        <button class="spb-btn mt-3">Pesan Sekarang <i class="fa-solid fa-arrow-right"></i></button>
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
        @if (request()->filled('category') || request()->filled('price'))
            <div class="active-filters-row">
                <div class="active-filters">
                    <span>Active Filters:</span>
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
            @foreach ($products as $index => $product)
                <div class="product-card">
                    <div class="product-image">
                        @if ($product->stok == 0)
                            <div class="badge out-of-stock">Habis</div>
                        @elseif($index == 3 || $index == 7)
                            <div class="badge sale">Sale 50%</div>
                        @endif

                        <div class="product-actions">
                            <div class="action-btn"><i class="fa-regular fa-heart"></i></div>
                            <div class="action-btn" title="Quick View" onclick="openProductModal(this)"
                                data-id="{{ $product->id_produk }}" data-name="{{ $product->nama_produk }}"
                                data-price="{{ $product->harga }}" data-desc="{{ $product->deskripsi }}"
                                data-cat="{{ $product->kategori->nama_kategori ?? 'Uncategorized' }}"
                                data-sale="{{ $index == 3 || $index == 7 ? '1' : '0' }}" data-stock="{{ $product->stok }}"
                                data-custom="1"
                                data-f1="{{ $product->foto_1 ? asset('storage/' . $product->foto_1) : '' }}"
                                data-f2="{{ $product->foto_2 ? asset('storage/' . $product->foto_2) : '' }}"
                                data-f3="{{ $product->foto_3 ? asset('storage/' . $product->foto_3) : '' }}"
                                data-f4="{{ $product->foto_4 ? asset('storage/' . $product->foto_4) : '' }}">
                                <i class="fa-regular fa-eye"></i>
                            </div>
                        </div>

                        <a href="{{ route('product.detail', $product->id_produk) }}?custom=1" style="display:block;">
                            @if ($product->foto_1)
                                <img src="{{ asset('storage/' . $product->foto_1) }}" alt="{{ $product->nama_produk }}">
                            @else
                                <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=400&auto=format&fit=crop"
                                    alt="Placeholder">
                            @endif
                        </a>
                    </div>
                    <div class="product-info">
                        <a href="{{ route('product.detail', $product->id_produk) }}?custom=1"
                            class="product-name">{{ $product->nama_produk }}</a>
                        <div class="product-price">
                            @if ($product->harga_max && $product->harga_max > $product->harga)
                                Rp. {{ number_format($product->harga, 0, ',', '.') }} - Rp. {{ number_format($product->harga_max, 0, ',', '.') }}
                            @else
                                Rp. {{ number_format($product->harga, 0, ',', '.') }}
                                @if ($index == 3 || $index == 7)
                                    <span class="old">Rp. {{ number_format($product->harga * 2, 0, ',', '.') }}</span>
                                @endif
                            @endif
                        </div>

                        @if ($product->stok > 0)
                            @php
                                $message = urlencode("Halo Zyh Corner, saya tertarik untuk memesan Custom Product: " . $product->nama_produk . " (Harga: Rp. " . number_format($product->harga, 0, ',', '.') . "). Mohon info lebih lanjut.");
                                $waUrl = "https://wa.me/628123456789?text=" . $message;
                            @endphp
                            <a href="{{ $waUrl }}" target="_blank" class="cart-btn" style="background-color: #25d366; color: white;" title="Pesan via WhatsApp">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>
                        @else
                            <div class="cart-btn disabled" title="Stok Habis">
                                <i class="fa-solid fa-ban"></i>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
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

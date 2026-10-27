@if(isset($banners) && $banners->count() > 0)
    @php
        $count = $banners->count();
    @endphp

    <div class="multi-banner-container my-4">
        {{-- LAYOUT 1 BANNER --}}
        @if($count === 1)
            @php $b = $banners->first(); @endphp
            <div class="single-banner-card overflow-hidden shadow-sm position-relative text-white p-4 p-md-5 d-flex flex-column justify-content-center"
                style="background-image: url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center right;">
                <div class="banner-content" style="max-width: 600px; z-index: 2;">
                    @if($b->subjudul)
                        <span class="spb-pill-badge badge-shop mb-2">
                            <i class="fa-solid fa-tags"></i> {{ $b->subjudul }}
                        </span>
                    @endif
                    <h2 class="font-weight-bold text-white mb-3" style="font-size: 32px; line-height: 1.25; letter-spacing: -0.5px;">{{ $b->judul_banner }}</h2>
                    @if($b->link_url)
                        <a href="{{ $b->link_url }}" class="spb-btn d-inline-flex align-items-center">
                            Lihat Promo <i class="fa-solid fa-arrow-right ms-2"></i>
                        </a>
                    @endif
                </div>
            </div>

        {{-- LAYOUT 2 BANNERS (2 COLUMNS) --}}
        @elseif($count === 2)
            <div class="row g-3">
                @foreach($banners as $b)
                    <div class="col-md-6">
                        <div class="banner-grid-card overflow-hidden shadow-sm position-relative text-white p-4 d-flex flex-column justify-content-between h-100"
                            style="min-height: 240px; background-image: url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center;">
                            <div class="banner-top-content position-relative" style="z-index: 2;">
                                @if($b->subjudul)
                                    <span class="spb-pill-badge badge-shop mb-2" style="font-size: 11px; padding: 4px 12px;">
                                        <i class="fa-solid fa-tag"></i> {{ $b->subjudul }}
                                    </span>
                                @endif
                                <h4 class="font-weight-bold text-white mb-2" style="font-size: 22px; line-height: 1.3; letter-spacing: -0.3px;">{{ $b->judul_banner }}</h4>
                            </div>
                            @if($b->link_url)
                                <div class="banner-bottom-content position-relative mt-3" style="z-index: 2;">
                                    <a href="{{ $b->link_url }}" class="spb-btn d-inline-flex align-items-center" style="padding: 10px 22px; font-size: 13px;">
                                        Belanja Sekarang <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        {{-- LAYOUT 3 BANNERS (3 COLUMNS / TRIPLE FEATURE) --}}
        @elseif($count === 3)
            <div class="row g-3">
                @foreach($banners as $index => $b)
                    <div class="col-md-4">
                        <div class="banner-grid-card overflow-hidden shadow-sm position-relative text-white p-4 d-flex flex-column justify-content-between h-100"
                            style="min-height: 230px; background-image: url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center;">
                            <div class="banner-top-content position-relative" style="z-index: 2;">
                                <span class="spb-pill-badge badge-shop mb-2" style="font-size: 10px; padding: 3px 10px;">
                                    <i class="fa-solid fa-tag"></i> {{ $b->subjudul ?? 'PROMO #' . ($index + 1) }}
                                </span>
                                <h5 class="font-weight-bold text-white mb-2" style="font-size: 18px; line-height: 1.35; letter-spacing: -0.2px;">{{ $b->judul_banner }}</h5>
                            </div>
                            @if($b->link_url)
                                <div class="banner-bottom-content position-relative mt-3" style="z-index: 2;">
                                    <a href="{{ $b->link_url }}" class="spb-btn d-inline-flex align-items-center" style="padding: 8px 18px; font-size: 12px;">
                                        Cek Promo <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        {{-- LAYOUT 4 BANNERS (4 COLUMNS / QUAD GRID) --}}
        @else
            <div class="row g-3">
                @foreach($banners->take(4) as $index => $b)
                    <div class="col-6 col-md-3">
                        <div class="banner-grid-card overflow-hidden shadow-sm position-relative text-white p-3 d-flex flex-column justify-content-between h-100"
                            style="min-height: 200px; background-image: url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center;">
                            <div class="banner-top-content position-relative" style="z-index: 2;">
                                @if($b->subjudul)
                                    <span class="spb-pill-badge badge-shop mb-1" style="font-size: 9.5px; padding: 2px 8px;">
                                        {{ Str::limit($b->subjudul, 20) }}
                                    </span>
                                @endif
                                <h6 class="font-weight-bold text-white mb-2" style="font-size: 14.5px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $b->judul_banner }}
                                </h6>
                            </div>
                            @if($b->link_url)
                                <div class="banner-bottom-content position-relative mt-2" style="z-index: 2;">
                                    <a href="{{ $b->link_url }}" class="spb-btn d-inline-flex align-items-center" style="padding: 7px 14px; font-size: 11px;">
                                        Lihat <i class="fa-solid fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif

@if(isset($banners) && $banners->count() > 0)
    @php
        $count = $banners->count();
    @endphp

    <div class="multi-banner-container my-4">
        {{-- LAYOUT 1 BANNER --}}
        @if($count === 1)
            @php $b = $banners->first(); @endphp
            <div class="single-banner-card overflow-hidden shadow-sm position-relative text-white p-4 p-md-5 d-flex flex-column justify-content-center"
                style="border-radius: 16px; min-height: 240px; background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; transition: transform 0.3s ease;">
                <div class="banner-content" style="max-width: 600px; z-index: 2;">
                    @if($b->subjudul)
                        <span class="badge bg-warning text-dark font-weight-bold mb-2 px-3 py-1" style="border-radius: 20px; font-size: 12px;">{{ $b->subjudul }}</span>
                    @endif
                    <h2 class="font-weight-bold text-white mb-3" style="font-size: 28px; line-height: 1.3;">{{ $b->judul_banner }}</h2>
                    @if($b->link_url)
                        <a href="{{ $b->link_url }}" class="btn btn-light text-dark font-weight-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center" style="font-size: 14px;">
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
                            style="border-radius: 16px; min-height: 220px; background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; transition: all 0.3s ease;">
                            <div>
                                @if($b->subjudul)
                                    <small class="text-warning font-weight-bold text-uppercase d-block mb-1" style="letter-spacing: 1px; font-size: 11px;">{{ $b->subjudul }}</small>
                                @endif
                                <h4 class="font-weight-bold text-white mb-2" style="font-size: 22px; line-height: 1.3;">{{ $b->judul_banner }}</h4>
                            </div>
                            @if($b->link_url)
                                <div>
                                    <a href="{{ $b->link_url }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 font-weight-bold d-inline-flex align-items-center" style="font-size: 12.5px;">
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
                            style="border-radius: 16px; min-height: 210px; background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; transition: all 0.3s ease;">
                            <div>
                                <span class="badge bg-danger text-white px-2 py-1 mb-2" style="border-radius: 6px; font-size: 10px;">PROMO #{{ $index + 1 }}</span>
                                @if($b->subjudul)
                                    <small class="text-light d-block mb-1" style="font-size: 11.5px;">{{ $b->subjudul }}</small>
                                @endif
                                <h5 class="font-weight-bold text-white mb-2" style="font-size: 18px; line-height: 1.3;">{{ $b->judul_banner }}</h5>
                            </div>
                            @if($b->link_url)
                                <div>
                                    <a href="{{ $b->link_url }}" class="btn btn-sm btn-light text-dark rounded-pill px-3 py-1 font-weight-bold d-inline-flex align-items-center" style="font-size: 12px;">
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
                            style="border-radius: 14px; min-height: 195px; background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; transition: all 0.3s ease;">
                            <div>
                                @if($b->subjudul)
                                    <small class="text-warning font-weight-bold d-block mb-1" style="font-size: 10.5px;">{{ Str::limit($b->subjudul, 25) }}</small>
                                @endif
                                <h6 class="font-weight-bold text-white mb-2" style="font-size: 15px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ $b->judul_banner }}
                                </h6>
                            </div>
                            @if($b->link_url)
                                <div>
                                    <a href="{{ $b->link_url }}" class="btn btn-sm btn-outline-light rounded-pill px-2 py-1 font-weight-bold d-inline-flex align-items-center" style="font-size: 11px;">
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

    <style>
        .banner-grid-card:hover, .single-banner-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15) !important;
        }
    </style>
@endif

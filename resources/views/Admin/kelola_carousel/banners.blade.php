@extends('Layout.layout_pAdmin')

@section('title', 'Kelola Banner - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-image-multiple"></i>
                </span> Kelola Banner Pelanggan
            </h3>
            <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
                <button type="button" class="btn btn-gradient-primary btn-sm px-4"
                    data-bs-toggle="modal" data-bs-target="#addBannerModal">
                    <i class="mdi mdi-plus me-1"></i> Tambah Banner Baru
                </button>
            </div>
        </div>
        @php
            $posisiMap = [
                'home_hero' => 'Home Hero Banner',
                'shop_header' => 'Header Showcase',
                'custom_header' => 'Header Produk Kustom',
                'popup_promo' => 'Pop-up Promo',
            ];
            $activePosisi = request('posisi');
            $activePosisiLabel = $activePosisi && isset($posisiMap[$activePosisi]) ? $posisiMap[$activePosisi] : 'Filter Posisi';
        @endphp

        <!-- Toolbar & Filter Controls -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; background: #ffffff;">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('banners.index') }}"
                    class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div class="d-flex flex-wrap align-items-center gap-3 w-100 w-md-auto">
                        <!-- Select All Checkbox -->
                        <div class="form-check m-0 d-flex align-items-center pe-3 border-end">
                            <label class="form-check-label font-weight-bold text-dark me-2" style="cursor: pointer;" for="checkAllBanners">
                                <input type="checkbox" id="checkAllBanners" class="form-check-input me-2" style="cursor: pointer;">
                                Pilih Semua
                            </label>
                        </div>

                        <!-- Position Filter Dropdown -->
                        <div class="dropdown">
                            <button
                                class="btn btn-sm {{ $activePosisi ? 'btn-gradient-primary text-white' : 'btn-outline-primary' }} dropdown-toggle d-inline-flex align-items-center justify-content-between"
                                type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" style="height: 38px; padding: 0 15px; min-width: 170px; border-radius: 8px;">
                                <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i>
                                    {{ $activePosisiLabel }}</span>
                            </button>
                            <div class="dropdown-menu p-3 shadow-sm" aria-labelledby="filterDropdown"
                                style="width: 270px; border-radius: 10px; margin-top: 5px;">
                                <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Posisi Penempatan</h6>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="" class="form-check-input" {{ !$activePosisi ? 'checked' : '' }}>
                                        Semua Posisi
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="home_hero" class="form-check-input" {{ $activePosisi === 'home_hero' ? 'checked' : '' }}>
                                        Home Hero Banner
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="shop_header" class="form-check-input" {{ $activePosisi === 'shop_header' ? 'checked' : '' }}>
                                        Header Showcase (Shop)
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="custom_header" class="form-check-input" {{ $activePosisi === 'custom_header' ? 'checked' : '' }}>
                                        Header Produk Kustom
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="popup_promo" class="form-check-input" {{ $activePosisi === 'popup_promo' ? 'checked' : '' }}>
                                        Pop-up Promo / Diskon
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                            </div>
                        </div>

                        <!-- Search Input -->
                        <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                            <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                class="form-control form-control-sm ps-5 pe-3" placeholder="Cari judul banner..." 
                                style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                            <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                        </div>
                    </div>

                    <!-- Bulk Delete Button -->
                    <button type="button" id="btnBulkDelete" class="btn btn-gradient-danger btn-sm d-none align-items-center justify-content-center px-3" style="height: 38px; border-radius: 8px;">
                        <i class="mdi mdi-delete me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </form>
            </div>
        </div>
        <!-- Visual Banner Cards List View -->
        <form action="{{ route('banners.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
            @csrf
            <div id="bulkDeleteInputs"></div>
        </form>

        <div class="row">
                @forelse($banners as $b)
                    <div class="col-md-6 col-lg-6 col-xl-4 grid-margin stretch-card mb-4 banner-col-item" data-search="{{ strtolower($b->judul_banner . ' ' . $b->subjudul_banner . ' ' . $b->posisi_banner) }}">
                        <div class="card border-0 shadow-sm overflow-hidden h-100 banner-card position-relative" style="border-radius: 14px; background: #ffffff; transition: transform 0.25s ease, box-shadow 0.25s ease;">

                            <!-- Banner Image Header Container -->
                            <div class="position-relative overflow-hidden" style="height: 190px; background-color: #f1f5f9;">
                                @if($b->gambar_banner && Storage::disk('public')->exists($b->gambar_banner))
                                    <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="{{ $b->judul_banner }}"
                                        class="w-100 h-100 banner-img" style="object-fit: cover; transition: transform 0.3s ease;">
                                @else
                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-gradient-light text-muted">
                                        <i class="mdi mdi-image-off" style="font-size: 42px;"></i>
                                        <small class="mt-1">Gambar Tidak Tersedia</small>
                                    </div>
                                @endif

                                <!-- Top Left Badge: Posisi Banner -->
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-dark bg-opacity-75 text-white px-3 py-2 shadow-sm" style="border-radius: 20px; font-size: 11.5px; backdrop-filter: blur(4px);">
                                        <i class="mdi mdi-pin me-1 text-warning"></i> {{ $b->posisi_label }}
                                    </span>
                                </div>

                                <!-- Top Right Badge: Status -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    @if($b->status === 'Aktif')
                                        <span class="badge bg-success text-white px-3 py-2 shadow-sm" style="border-radius: 20px; font-size: 11.5px;">
                                            <i class="mdi mdi-check-circle me-1"></i> Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white px-3 py-2 shadow-sm" style="border-radius: 20px; font-size: 11.5px;">
                                            <i class="mdi mdi-close-circle me-1"></i> Nonaktif
                                        </span>
                                    @endif
                                </div>

                                <!-- Bottom Right Badge: Urutan -->
                                <div class="position-absolute bottom-0 end-0 m-3">
                                    <span class="badge bg-primary text-white px-2 py-1 shadow-sm" style="border-radius: 6px; font-size: 11px;">
                                        Urutan #{{ $b->urutan }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-start gap-2 mb-2">
                                        <!-- Row Selection Checkbox -->
                                        <div class="form-check m-0 pt-1">
                                            <label class="form-check-label" style="cursor: pointer;">
                                                <input type="checkbox" name="ids[]" value="{{ $b->id_banner }}" class="form-check-input banner-row-checkbox" style="cursor: pointer;">
                                            </label>
                                        </div>
                                        <div>
                                            <h5 class="card-title font-weight-bold text-dark mb-1" style="font-size: 16px; line-height: 1.4;">
                                                {{ $b->judul_banner }}
                                            </h5>
                                            <p class="text-muted mb-0" style="font-size: 13px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $b->subjudul ?? 'Tidak ada subjudul/deskripsi tambahan.' }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($b->link_url)
                                        <div class="mt-3 pt-2 border-top">
                                            <small class="text-muted d-block mb-1" style="font-size: 11px;">Target URL:</small>
                                            <a href="{{ $b->link_url }}" target="_blank" class="text-primary text-decoration-none font-weight-500 d-inline-flex align-items-center" style="font-size: 12.5px;">
                                                <i class="mdi mdi-link-variant me-1"></i> {{ Str::limit($b->link_url, 35) }}
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <!-- Card Action Footer -->
                                <div class="d-flex align-items-center justify-content-between mt-4 pt-3 border-top">
                                    <!-- Toggle Status Action -->
                                    <form action="{{ route('banners.toggle', $b->id_banner) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $b->status === 'Aktif' ? 'btn-outline-warning' : 'btn-outline-success' }} px-3 py-1 d-inline-flex align-items-center" style="border-radius: 20px; font-size: 12px;">
                                            <i class="mdi {{ $b->status === 'Aktif' ? 'mdi-pause' : 'mdi-play' }} me-1"></i>
                                            {{ $b->status === 'Aktif' ? 'Sembunyikan' : 'Tampilkan' }}
                                        </button>
                                    </form>

                                    <div class="d-flex align-items-center gap-2">
                                        <!-- Eye Icon Preview Modal Trigger -->
                                        <button type="button" class="btn btn-inverse-primary btn-sm p-2 rounded-circle d-inline-flex align-items-center justify-content-center"
                                            style="width: 34px; height: 34px;" title="Prinjau Banner di Pelanggan"
                                            data-bs-toggle="modal" data-bs-target="#previewBannerModal{{ $b->id_banner }}">
                                            <i class="mdi mdi-eye" style="font-size: 16px;"></i>
                                        </button>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button" class="btn btn-inverse-info btn-sm p-2 rounded-circle d-inline-flex align-items-center justify-content-center"
                                            style="width: 34px; height: 34px;" title="Edit Banner"
                                            data-bs-toggle="modal" data-bs-target="#editBannerModal{{ $b->id_banner }}">
                                            <i class="mdi mdi-pencil" style="font-size: 16px;"></i>
                                        </button>

                                        <!-- Delete Form -->
                                        <form action="{{ route('banners.destroy', $b->id_banner) }}" method="POST" class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-inverse-danger btn-sm p-2 rounded-circle d-inline-flex align-items-center justify-content-center"
                                                style="width: 34px; height: 34px;" title="Hapus Banner">
                                                <i class="mdi mdi-delete" style="font-size: 16px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Preview Banner #{{ $b->id_banner }} -->
                    <div class="modal fade" id="previewBannerModal{{ $b->id_banner }}" tabindex="-1" aria-labelledby="previewBannerModalLabel{{ $b->id_banner }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 720px; margin: 1.75rem auto; width: 95%;">
                            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                                <!-- Header Modal -->
                                <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 34px; height: 34px; background-color: #ede9fe; color: #4f46e5 !important;">
                                            <i class="mdi mdi-eye-outline" style="font-size: 18px;"></i>
                                        </span>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h6 class="modal-title font-weight-bold mb-0 text-dark" id="previewBannerModalLabel{{ $b->id_banner }}" style="font-size: 15px;">
                                                    Pratinjau Banner Pelanggan
                                                </h6>
                                                <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">{{ $b->posisi_label }}</span>
                                            </div>
                                            <small class="text-muted" style="font-size: 11px;">Simulasi rendering visual persis pada antarmuka pelanggan.</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                                </div>
                                <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-primary px-3 py-1" style="font-size: 12px;">Tampilan Halaman Pelanggan</span>
                                            <small class="text-muted"><i class="mdi mdi-cellphone-link me-1"></i> Area: {{ $b->posisi_label }}</small>
                                        </div>

                                        <!-- Live Rendered Banner Preview Frame (1:1 Mirrored with Front-End Redesign) -->
                                        @if($b->posisi_banner === 'home_hero')
                                            @php
                                                $subjudulPreview = $b->subjudul;
                                                $badgeTextPreview = 'Zyh Corner';
                                                $calloutPrefixPreview = null;
                                                $calloutHighlightPreview = null;
                                                $descTextPreview = null;

                                                if ($subjudulPreview) {
                                                    if (str_contains($subjudulPreview, '-')) {
                                                        $parts = explode('-', $subjudulPreview, 2);
                                                        $badgeTextPreview = trim($parts[0]);
                                                        $candidate = trim($parts[1]);
                                                    } else {
                                                        $candidate = trim($subjudulPreview);
                                                    }

                                                    if (preg_match('/^(.*?)(\d+%\s*OFF|\d+%\s*diskon|potongan\s*\d+%|diskon\s*\d+%.*|\d+%.*)$/i', $candidate, $m)) {
                                                        $calloutPrefixPreview = trim($m[1]) ?: null;
                                                        $calloutHighlightPreview = trim($m[2]);
                                                    } else {
                                                        $descTextPreview = $candidate;
                                                    }
                                                }
                                            @endphp
                                            <div class="shadow-sm position-relative overflow-hidden"
                                                style="background: #edf2ee; border-radius: 18px; border: 1px solid #e2eae3; min-height: 220px; display: grid; grid-template-columns: 65fr 35fr; align-items: center;">
                                                <div class="p-3 ps-4" style="z-index: 2;">
                                                    <span class="badge mb-2 d-inline-block" style="background: rgba(0, 178, 7, 0.10); color: #008a05; border: 1px solid rgba(0, 178, 7, 0.25); border-radius: 30px; font-weight: 700; padding: 5px 14px; font-size: 11px; text-transform: uppercase;">
                                                        <i class="mdi mdi-sparkles me-1"></i> {{ $badgeTextPreview }}
                                                    </span>
                                                    <h4 class="font-weight-bold mb-1" style="color: #1a1a1a; letter-spacing: -0.3px;">{{ $b->judul_banner }}</h4>
                                                    @if($calloutHighlightPreview)
                                                        <div style="font-size: 13.5px; font-weight: 600; color: #1a1a1a; margin-bottom: 6px;">
                                                            @if($calloutPrefixPreview)
                                                                <span>{{ $calloutPrefixPreview }}</span>
                                                            @endif
                                                            <span style="color: #ff8a00; font-weight: 700;">{{ $calloutHighlightPreview }}</span>
                                                        </div>
                                                    @endif
                                                    @if($descTextPreview)
                                                        <p class="text-muted mb-3" style="font-size: 12px; line-height: 1.5;">{{ $descTextPreview }}</p>
                                                    @endif
                                                    <span class="btn btn-sm text-white rounded-pill px-4 shadow-sm" style="background: #00b207; font-weight: 600; font-size: 12.5px;">Shop Now <i class="mdi mdi-arrow-right ms-1"></i></span>
                                                </div>
                                                <div class="position-relative p-0" style="background: transparent; z-index: 2; height: 100%; min-height: 220px; overflow: hidden;">
                                                    <div style="position: absolute; left: 0; top: 0; bottom: 0; width: 60px; background: linear-gradient(to right, #edf2ee 0%, rgba(237, 242, 238, 0.9) 25%, rgba(237, 242, 238, 0) 100%); z-index: 3; pointer-events: none;"></div>
                                                    <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="Preview" style="position: absolute; top: 0; right: 0; width: 100%; height: 100%; object-fit: cover; object-position: center; border-radius: 0 18px 18px 0; display: block; -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 15%, rgba(0,0,0,1) 45%); mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,0.4) 15%, rgba(0,0,0,1) 45%);">
                                                </div>
                                            </div>
                                        @elseif($b->posisi_banner === 'popup_promo')
                                            <div class="p-3 text-center d-flex flex-column align-items-center justify-content-between position-relative overflow-hidden"
                                                style="border-radius: 18px; min-height: 250px; background: #ffffff; border: 1.5px solid #dbe8dd;">
                                                <!-- Radial Spotlight Gradient Overlay -->
                                                <div style="position: absolute; inset: 0; background: radial-gradient(ellipse 150% 115% at 50% 15%, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.92) 34%, rgba(255, 255, 255, 0.65) 60%, rgba(255, 255, 255, 0.15) 82%, rgba(255, 255, 255, 0) 100%); z-index: 2; pointer-events: none;"></div>
                                                <div class="d-flex flex-column align-items-center w-100" style="position: relative; z-index: 3;">
                                                    <span class="badge mb-1 text-uppercase fw-bold" style="background: rgba(255, 138, 0, 0.14); color: #ff8a00; border: 1px solid rgba(255, 138, 0, 0.32); border-radius: 20px; font-size: 10px; letter-spacing: 1.4px; padding: 4px 10px;">
                                                        <i class="mdi mdi-creation me-1"></i> {{ $b->subjudul ?? 'SUMMER SALE' }}
                                                    </span>
                                                    <h3 class="fw-bold mb-1" style="color: #00b207; font-size: 26px; letter-spacing: -0.5px;">{{ $b->judul_banner }}</h3>
                                                    <span class="btn btn-sm text-white fw-bold rounded-pill px-3 shadow-sm mb-1" style="background: linear-gradient(135deg, #00b207, #009906); font-size: 11.5px;">
                                                        Shop Now <i class="mdi mdi-arrow-right ms-1"></i>
                                                    </span>
                                                </div>
                                                <!-- Full-Bleed Image Wrap -->
                                                <div class="w-100 position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 1; overflow: hidden;">
                                                    <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="{{ $b->judul_banner }}" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; object-position: center bottom;">
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-4 text-start text-white shadow-sm d-flex flex-column justify-content-center position-relative overflow-hidden"
                                                style="border-radius: 18px; min-height: 200px; background-image: linear-gradient(90deg, rgba(26, 13, 8, 0.88) 0%, rgba(26, 13, 8, 0.65) 50%, rgba(26, 13, 8, 0.25) 100%), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center;">
                                                <div style="max-width: 70%; z-index: 2;">
                                                    <span class="badge bg-warning text-dark font-weight-bold px-3 py-1 mb-2 rounded-pill shadow-sm" style="font-size: 11px; letter-spacing: 0.5px;">
                                                        <i class="mdi {{ $b->posisi_banner === 'custom_header' ? 'mdi-cake-variant' : 'mdi-tag' }} me-1"></i> {{ $b->subjudul ?? ($b->posisi_banner === 'custom_header' ? 'Custom Bakery Studio' : 'Exclusive Pastry') }}
                                                    </span>
                                                    <h3 class="font-weight-bold text-white mb-2">{{ $b->judul_banner }}</h3>
                                                    <span class="btn btn-sm text-white rounded-pill px-4 mt-2 shadow-sm" style="background: linear-gradient(135deg, #00b207, #009906); font-weight: 700; font-size: 12.5px;">
                                                        {{ $b->posisi_banner === 'custom_header' ? 'Pesan Sekarang' : 'Shop Now' }} <i class="mdi mdi-arrow-right ms-1"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between mt-3 px-1">
                                        <div class="text-muted" style="font-size: 13px;">
                                            <i class="mdi mdi-information-outline text-primary me-1"></i>
                                            Status Banner: <strong class="{{ $b->status === 'Aktif' ? 'text-success' : 'text-danger' }}">{{ $b->status }}</strong>
                                        </div>

                                        @php
                                            $targetPage = route('home');
                                            if($b->posisi_banner === 'shop_header') $targetPage = route('shop');
                                            if($b->posisi_banner === 'custom_header') $targetPage = route('custom-products');
                                        @endphp
                                        <a href="{{ $targetPage }}" target="_blank" class="btn btn-sm btn-outline-primary px-3" style="border-radius: 8px;">
                                            <i class="mdi mdi-open-in-new me-1"></i> Buka Halaman Pelanggan Asli
                                        </a>
                                    </div>
                                </div>
                                <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end" style="border-radius: 0 0 12px 12px;">
                                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Banner #{{ $b->id_banner }} -->
                    <div class="modal fade" id="editBannerModal{{ $b->id_banner }}" tabindex="-1" aria-labelledby="editBannerModalLabel{{ $b->id_banner }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 860px; margin: 1.75rem auto; width: 94%;">
                            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 14px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                                <!-- Header Modal -->
                                <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-white flex-shrink-0">
                                    <div class="d-flex align-items-center gap-2.5">
                                        <span class="d-inline-flex align-items-center justify-content-center text-info rounded-circle flex-shrink-0" style="width: 38px; height: 38px; background-color: #e0f2fe; color: #0284c7 !important;">
                                            <i class="mdi mdi-image-edit-outline" style="font-size: 20px;"></i>
                                        </span>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-0.5">
                                                <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editBannerModalLabel{{ $b->id_banner }}" style="font-size: 15px;">
                                                    Edit Banner Promo
                                                </h6>
                                                <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 6px;">#{{ $b->id_banner }}</span>
                                            </div>
                                            <small class="text-muted" style="font-size: 11.5px;">Sesuaikan visual promosi, penempatan posisi, dan target rute URL.</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                                </div>
                                <form action="{{ route('banners.update', $b->id_banner) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                                        <div class="row g-4">
                                            <!-- Kolom Kiri: Pratinjau Visual & Dropzone Upload -->
                                            <div class="col-lg-5 d-flex flex-column gap-3">
                                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="font-weight-600 text-dark d-inline-flex align-items-center gap-1.5" style="font-size: 12px;">
                                                            <i class="mdi mdi-eye-outline text-primary" style="font-size: 15px;"></i> Pratinjau Banner
                                                        </span>
                                                        <span class="badge bg-white text-secondary border px-2 py-0.5" style="font-size: 10px; font-weight: 600; border-radius: 4px;">Rasio 16:9</span>
                                                    </div>
                                                    <div class="position-relative overflow-hidden rounded-2 shadow-sm bg-dark" style="aspect-ratio: 16/9; max-height: 175px; display: flex; align-items: center; justify-content: center;">
                                                        <img src="{{ asset('storage/' . $b->gambar_banner) }}" 
                                                             id="editPreviewImg{{ $b->id_banner }}" 
                                                             alt="Preview" 
                                                             class="w-100 h-100 object-fit-cover"
                                                             data-original-src="{{ asset('storage/' . $b->gambar_banner) }}">
                                                        <div class="position-absolute top-0 start-0 m-2">
                                                            <span class="badge bg-dark bg-opacity-75 text-white px-2 py-1" id="badgePosisi{{ $b->id_banner }}" style="font-size: 10.5px; font-weight: 500; backdrop-filter: blur(4px); border-radius: 6px;">
                                                                @if($b->posisi_banner === 'home_hero') Home Hero
                                                                @elseif($b->posisi_banner === 'shop_header') Header Showcase
                                                                @elseif($b->posisi_banner === 'custom_header') Header Kustom
                                                                @else Pop-up Promo
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="p-3 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                                        <span class="font-weight-600 text-dark d-inline-flex align-items-center gap-1.5" style="font-size: 12px;">
                                                            <i class="mdi mdi-cloud-upload-outline text-primary" style="font-size: 15px;"></i> Ganti Gambar
                                                        </span>
                                                        <button type="button" class="btn btn-link text-danger p-0 d-none" id="btnResetImg{{ $b->id_banner }}"
                                                                style="font-size: 11px; text-decoration: none;"
                                                                onclick="resetEditBanner('{{ $b->id_banner }}')">
                                                            <i class="mdi mdi-undo me-0.5"></i> Batal
                                                        </button>
                                                    </div>
                                                    <div class="upload-dropzone position-relative border border-dashed rounded-3 p-3 text-center bg-light"
                                                         id="dropzoneEdit{{ $b->id_banner }}" style="border-color: #cbd5e1 !important; cursor: pointer;">
                                                        <input type="file" name="gambar_banner" id="inputGambarEdit{{ $b->id_banner }}"
                                                               class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                                               accept="image/jpeg,image/png,image/webp,image/jpg,image/svg+xml" style="cursor: pointer;"
                                                               onchange="previewEditBanner(event, '{{ $b->id_banner }}')">
                                                        <i class="mdi mdi-image-plus text-primary mb-1" style="font-size: 26px; display: block;"></i>
                                                        <div class="fw-semibold text-dark" style="font-size: 11.5px;">Pilih atau seret gambar baru</div>
                                                        <small class="text-muted d-block mt-0.5" style="font-size: 10.5px;">Maks. 4MB (JPG, PNG, WEBP)</small>
                                                    </div>
                                                    <small class="text-muted mt-2 d-block" style="font-size: 10.5px; line-height: 1.4;">
                                                        <i class="mdi mdi-information-outline me-0.5 text-muted"></i> Kosongkan bila gambar tidak ingin diubah.
                                                    </small>
                                                </div>
                                            </div>

                                            <!-- Kolom Kanan: Metadata & Pengaturan -->
                                            <div class="col-lg-7 d-flex flex-column">
                                                <!-- Seksi 1: Informasi Konten -->
                                                <div class="d-flex align-items-center gap-1.5 text-uppercase font-weight-bold text-muted pb-1.5 mb-2.5 border-bottom" style="font-size: 11px; letter-spacing: 0.5px;">
                                                    <i class="mdi mdi-card-text-outline text-primary" style="font-size: 15px;"></i>
                                                    <span>Informasi Konten</span>
                                                </div>
                                                
                                                <div class="mb-3">
                                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Judul Banner <span class="text-danger">*</span></label>
                                                    <input type="text" name="judul_banner" class="form-control" 
                                                           value="{{ $b->judul_banner }}" placeholder="Contoh: Promo Spesial Weekend Cake" required 
                                                           style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                                </div>

                                                <div class="mb-3.5">
                                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">
                                                        Subjudul / Deskripsi Singkat <span class="text-muted fw-normal ms-1" style="font-size: 11px;">(Opsional)</span>
                                                    </label>
                                                    <input type="text" name="subjudul" class="form-control" 
                                                           value="{{ $b->subjudul }}" placeholder="Contoh: Dapatkan diskon hingga 30%" 
                                                           style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                                </div>

                                                <!-- Seksi 2: Penempatan & Status -->
                                                <div class="d-flex align-items-center gap-1.5 text-uppercase font-weight-bold text-muted pb-1.5 mb-2.5 border-bottom" style="font-size: 11px; letter-spacing: 0.5px;">
                                                    <i class="mdi mdi-tune-vertical-variant text-primary" style="font-size: 15px;"></i>
                                                    <span>Penempatan & Status</span>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Posisi Penempatan <span class="text-danger">*</span></label>
                                                    <select name="posisi_banner" class="form-control form-select select-posisi" 
                                                            data-banner-id="{{ $b->id_banner }}" 
                                                            onchange="updatePosisiBadge(this, '{{ $b->id_banner }}')" required 
                                                            style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                                        <option value="home_hero" {{ $b->posisi_banner === 'home_hero' ? 'selected' : '' }}>Beranda Utama (Home Hero Slider)</option>
                                                        <option value="shop_header" {{ $b->posisi_banner === 'shop_header' ? 'selected' : '' }}>Halaman Showcase (Shop Header)</option>
                                                        <option value="custom_header" {{ $b->posisi_banner === 'custom_header' ? 'selected' : '' }}>Halaman Produk Kustom (Custom Header)</option>
                                                        <option value="popup_promo" {{ $b->posisi_banner === 'popup_promo' ? 'selected' : '' }}>Pop-up Promo Spesial</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">
                                                        Target URL Tujuan <span class="text-muted fw-normal ms-1" style="font-size: 11px;">(Opsional)</span>
                                                    </label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                                            <i class="mdi mdi-link-variant"></i>
                                                        </span>
                                                        <input type="text" name="link_url" class="form-control border-start-0" 
                                                               value="{{ $b->link_url }}" placeholder="Contoh: /shop" 
                                                               style="border-radius: 0 8px 8px 0; font-size: 12.5px; height: 38px; border-color: #cbd5e1;">
                                                    </div>
                                                </div>

                                                <div class="row g-2.5">
                                                    <div class="col-sm-5">
                                                        <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Urutan</label>
                                                        <input type="number" name="urutan" class="form-control" value="{{ $b->urutan }}" min="0" 
                                                               style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                                    </div>
                                                    <div class="col-sm-7">
                                                        <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Status Tayang</label>
                                                        <select name="status" class="form-control form-select" required 
                                                                style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                                            <option value="Aktif" {{ $b->status === 'Aktif' ? 'selected' : '' }}>Aktif (Ditayangkan)</option>
                                                            <option value="Nonaktif" {{ $b->status === 'Nonaktif' ? 'selected' : '' }}>Nonaktif (Draf / Arsip)</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-top bg-light py-2.5 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 14px 14px;">
                                        <button type="button" class="btn btn-light btn-sm px-3.5 py-1.5 border" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 500; font-size: 12.5px;">Batal</button>
                                        <button type="submit" class="btn btn-gradient-primary btn-sm px-3.5 py-1.5 shadow-sm d-inline-flex align-items-center" style="border-radius: 8px; font-weight: 500; font-size: 12.5px;">
                                            <i class="mdi mdi-check-circle-outline me-1.5" style="font-size: 15px;"></i> Perbarui Banner
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm text-center py-5" style="border-radius: 14px;">
                            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                <i class="mdi mdi-image-multiple-outline text-muted mb-3" style="font-size: 64px;"></i>
                                @if(request('posisi') || request('search'))
                                    <h5 class="text-dark font-weight-bold mb-1">Tidak Ditemukan Banner yang Sesuai</h5>
                                    <p class="text-muted mb-3" style="font-size: 14px;">Tidak ada banner untuk kriteria filter yang sedang aktif.</p>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('banners.index') }}" class="btn btn-sm btn-gradient-primary rounded-pill px-4">
                                            <i class="mdi mdi-refresh me-1"></i> Tampilkan Semua Posisi
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                                            <i class="mdi mdi-plus me-1"></i> Tambah Banner di Posisi Ini
                                        </button>
                                    </div>
                                @else
                                    <h5 class="text-muted font-weight-bold">Belum Ada Data Banner</h5>
                                    <p class="text-muted mb-3" style="font-size: 14px;">Klik tombol "Tambah Banner Baru" untuk menambahkan visual promo pelanggan.</p>
                                    <button type="button" class="btn btn-sm btn-gradient-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addBannerModal">
                                        <i class="mdi mdi-plus me-1"></i> Tambah Banner Baru
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </form>

        <!-- Pagination -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 gap-3">
            <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
                {{ $banners->links('pagination::bootstrap-4') }}
            </div>
            <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                {{ $banners->total() }} Hasil Banner
            </div>
        </div>
    </div>

    <!-- Modal Tambah Banner -->
    <div class="modal fade" id="addBannerModal" tabindex="-1" aria-labelledby="addBannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 860px; margin: 1.75rem auto; width: 94%;">
            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 14px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                <!-- Header Modal -->
                <div class="modal-header border-bottom py-3 px-3 px-sm-4 bg-white flex-shrink-0">
                    <div class="d-flex align-items-center gap-2.5">
                        <span class="d-inline-flex align-items-center justify-content-center text-success rounded-circle flex-shrink-0" style="width: 38px; height: 38px; background-color: #dcfce7; color: #16a34a !important;">
                            <i class="mdi mdi-image-plus-outline" style="font-size: 20px;"></i>
                        </span>
                        <div>
                            <h6 class="modal-title font-weight-bold mb-0 text-dark" id="addBannerModalLabel" style="font-size: 15px;">
                                Tambah Banner Baru
                            </h6>
                            <small class="text-muted" style="font-size: 11.5px;">Unggah visual promosi dan konfigurasikan penempatan banner.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                        <div class="row g-4">
                            <!-- Kolom Kiri: Pratinjau Visual & Dropzone Upload -->
                            <div class="col-lg-5 d-flex flex-column gap-3">
                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="font-weight-600 text-dark d-inline-flex align-items-center gap-1.5" style="font-size: 12px;">
                                            <i class="mdi mdi-eye-outline text-primary" style="font-size: 15px;"></i> Pratinjau Banner
                                        </span>
                                        <span class="badge bg-white text-secondary border px-2 py-0.5" style="font-size: 10px; font-weight: 600; border-radius: 4px;">Rasio 16:9</span>
                                    </div>
                                    <div class="position-relative overflow-hidden rounded-2 shadow-sm bg-light border d-flex align-items-center justify-content-center" 
                                         id="addPreviewContainer" style="aspect-ratio: 16/9; max-height: 175px;">
                                        <img src="" id="addPreviewImg" alt="Preview" class="w-100 h-100 object-fit-cover d-none">
                                        <div id="addPreviewPlaceholder" class="text-center p-3 text-muted">
                                            <i class="mdi mdi-image-filter-hdr" style="font-size: 38px; opacity: 0.35;"></i>
                                            <div class="fw-semibold mt-1" style="font-size: 12px; color: #64748b;">Pratinjau Banner Baru</div>
                                            <small style="font-size: 10.5px; color: #94a3b8;">Gambar muncul setelah dipilih</small>
                                        </div>
                                        <div class="position-absolute top-0 start-0 m-2">
                                            <span class="badge bg-dark bg-opacity-75 text-white px-2 py-1" id="badgePosisiAdd" style="font-size: 10.5px; font-weight: 500; backdrop-filter: blur(4px); border-radius: 6px;">
                                                Home Hero
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-3 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="font-weight-600 text-dark d-inline-flex align-items-center gap-1.5" style="font-size: 12px;">
                                            <i class="mdi mdi-cloud-upload-outline text-primary" style="font-size: 15px;"></i> Upload Gambar <span class="text-danger">*</span>
                                        </span>
                                        <button type="button" class="btn btn-link text-danger p-0 d-none" id="btnResetAddImg"
                                                style="font-size: 11px; text-decoration: none;"
                                                onclick="resetAddBanner()">
                                            <i class="mdi mdi-undo me-0.5"></i> Batal
                                        </button>
                                    </div>
                                    <div class="upload-dropzone position-relative border border-dashed rounded-3 p-3 text-center bg-light"
                                         id="dropzoneAdd" style="border-color: #cbd5e1 !important; cursor: pointer;">
                                        <input type="file" name="gambar_banner" id="inputGambarAdd"
                                               class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                               accept="image/jpeg,image/png,image/webp,image/jpg,image/svg+xml" style="cursor: pointer;"
                                               onchange="previewAddBanner(event)" required>
                                        <i class="mdi mdi-cloud-upload text-success mb-1" style="font-size: 26px; display: block;"></i>
                                        <div class="fw-semibold text-dark" style="font-size: 11.5px;">Pilih atau seret gambar banner</div>
                                        <small class="text-muted d-block mt-0.5" style="font-size: 10.5px;">Maks. 4MB (JPG, PNG, WEBP)</small>
                                    </div>
                                    <small class="text-muted mt-2 d-block" style="font-size: 10.5px; line-height: 1.4;">
                                        <i class="mdi mdi-information-outline me-0.5 text-muted"></i> Disarankan gambar lanskap horizontal (16:9).
                                    </small>
                                </div>
                            </div>

                            <!-- Kolom Kanan: Metadata & Pengaturan -->
                            <div class="col-lg-7 d-flex flex-column">
                                <!-- Seksi 1: Informasi Konten -->
                                <div class="d-flex align-items-center gap-1.5 text-uppercase font-weight-bold text-muted pb-1.5 mb-2.5 border-bottom" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-card-text-outline text-primary" style="font-size: 15px;"></i>
                                    <span>Informasi Konten</span>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Judul Banner <span class="text-danger">*</span></label>
                                    <input type="text" name="judul_banner" class="form-control" 
                                           placeholder="Contoh: Promo Spesial Kategori Kue" required 
                                           style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                </div>

                                <div class="mb-3.5">
                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">
                                        Subjudul / Deskripsi Singkat <span class="text-muted fw-normal ms-1" style="font-size: 11px;">(Opsional)</span>
                                    </label>
                                    <input type="text" name="subjudul" class="form-control" 
                                           placeholder="Contoh: Dapatkan diskon hingga 50%" 
                                           style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                </div>

                                <!-- Seksi 2: Penempatan & Status -->
                                <div class="d-flex align-items-center gap-1.5 text-uppercase font-weight-bold text-muted pb-1.5 mb-2.5 border-bottom" style="font-size: 11px; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-tune-vertical-variant text-primary" style="font-size: 15px;"></i>
                                    <span>Penempatan & Status</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Posisi Penempatan <span class="text-danger">*</span></label>
                                    <select name="posisi_banner" class="form-control form-select select-posisi-add" 
                                            onchange="updatePosisiBadgeAdd(this)" required 
                                            style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                        <option value="home_hero" selected>Beranda Utama (Home Hero Slider)</option>
                                        <option value="shop_header">Halaman Showcase (Shop Header)</option>
                                        <option value="custom_header">Halaman Produk Kustom (Custom Header)</option>
                                        <option value="popup_promo">Pop-up Promo Spesial</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">
                                        Target URL Tujuan <span class="text-muted fw-normal ms-1" style="font-size: 11px;">(Opsional)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0" style="border-radius: 8px 0 0 8px; border-color: #cbd5e1;">
                                            <i class="mdi mdi-link-variant"></i>
                                        </span>
                                        <input type="text" name="link_url" class="form-control border-start-0" 
                                               placeholder="Contoh: /shop" 
                                               style="border-radius: 0 8px 8px 0; font-size: 12.5px; height: 38px; border-color: #cbd5e1;">
                                    </div>
                                </div>

                                <div class="row g-2.5">
                                    <div class="col-sm-5">
                                        <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Urutan</label>
                                        <input type="number" name="urutan" class="form-control" value="1" min="0" 
                                               style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                    </div>
                                    <div class="col-sm-7">
                                        <label class="form-label font-weight-600 text-dark mb-1.5" style="font-size: 12px;">Status Tayang</label>
                                        <select name="status" class="form-control form-select" required 
                                                style="font-size: 12.5px; border-radius: 8px; height: 38px; border: 1px solid #cbd5e1;">
                                            <option value="Aktif" selected>Aktif (Ditayangkan)</option>
                                            <option value="Nonaktif">Nonaktif (Draf / Arsip)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light py-2.5 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 14px 14px;">
                        <button type="button" class="btn btn-light btn-sm px-3.5 py-1.5 border" data-bs-dismiss="modal" style="border-radius: 8px; font-weight: 500; font-size: 12.5px;">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary btn-sm px-3.5 py-1.5 shadow-sm d-inline-flex align-items-center" style="border-radius: 8px; font-weight: 500; font-size: 12.5px;">
                            <i class="mdi mdi-check-circle-outline me-1.5" style="font-size: 15px;"></i> Simpan Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .banner-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
        }
        .banner-card:hover .banner-img {
            transform: scale(1.04);
        }
        .upload-dropzone {
            transition: all 0.2s ease-in-out;
        }
        .upload-dropzone:hover {
            border-color: #00b207 !important;
            background-color: #f0fdf4 !important;
        }
        .object-fit-cover {
            object-fit: cover;
        }
    </style>

    <script>
        // Helper: Update Badge Posisi Edit
        function updatePosisiBadge(selectEl, id) {
            const badge = document.getElementById('badgePosisi' + id);
            if (!badge) return;
            const labels = {
                'home_hero': 'Home Hero',
                'shop_header': 'Header Showcase',
                'custom_header': 'Header Kustom',
                'popup_promo': 'Pop-up Promo'
            };
            badge.textContent = labels[selectEl.value] || 'Banner';
        }

        // Helper: Update Badge Posisi Add
        function updatePosisiBadgeAdd(selectEl) {
            const badge = document.getElementById('badgePosisiAdd');
            if (!badge) return;
            const labels = {
                'home_hero': 'Home Hero',
                'shop_header': 'Header Showcase',
                'custom_header': 'Header Kustom',
                'popup_promo': 'Pop-up Promo'
            };
            badge.textContent = labels[selectEl.value] || 'Banner';
        }

        // Live Preview: Edit Banner
        function previewEditBanner(event, id) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('editPreviewImg' + id);
                const btnReset = document.getElementById('btnResetImg' + id);
                if (img) img.src = e.target.result;
                if (btnReset) btnReset.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        // Reset Preview: Edit Banner
        function resetEditBanner(id) {
            const input = document.getElementById('inputGambarEdit' + id);
            const img = document.getElementById('editPreviewImg' + id);
            const btnReset = document.getElementById('btnResetImg' + id);
            if (input) input.value = '';
            if (img && img.dataset.originalSrc) img.src = img.dataset.originalSrc;
            if (btnReset) btnReset.classList.add('d-none');
        }

        // Live Preview: Add Banner
        function previewAddBanner(event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('addPreviewImg');
                const placeholder = document.getElementById('addPreviewPlaceholder');
                const btnReset = document.getElementById('btnResetAddImg');
                if (img) {
                    img.src = e.target.result;
                    img.classList.remove('d-none');
                }
                if (placeholder) placeholder.classList.add('d-none');
                if (btnReset) btnReset.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        }

        // Reset Preview: Add Banner
        function resetAddBanner() {
            const input = document.getElementById('inputGambarAdd');
            const img = document.getElementById('addPreviewImg');
            const placeholder = document.getElementById('addPreviewPlaceholder');
            const btnReset = document.getElementById('btnResetAddImg');
            if (input) input.value = '';
            if (img) {
                img.src = '';
                img.classList.add('d-none');
            }
            if (placeholder) placeholder.classList.remove('d-none');
            if (btnReset) btnReset.classList.add('d-none');
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Check All & Bulk Delete Logic
            const checkAllHeader = document.getElementById('checkAllBanners');
            const rowCheckboxes = document.querySelectorAll('.banner-row-checkbox');
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');

            function updateBulkDeleteState() {
                const checkedRows = document.querySelectorAll('.banner-row-checkbox:checked');
                const count = checkedRows.length;

                if (count > 0) {
                    btnBulkDelete.classList.remove('d-none');
                    btnBulkDelete.classList.add('d-flex');
                    selectedCountSpan.textContent = count;
                } else {
                    btnBulkDelete.classList.remove('d-flex');
                    btnBulkDelete.classList.add('d-none');
                    selectedCountSpan.textContent = '0';
                }

                if (checkAllHeader) {
                    checkAllHeader.checked = rowCheckboxes.length > 0 && count === rowCheckboxes.length;
                }
            }

            if (checkAllHeader) {
                checkAllHeader.addEventListener('change', function() {
                    rowCheckboxes.forEach(cb => cb.checked = this.checked);
                    updateBulkDeleteState();
                });
            }

            rowCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkDeleteState);
            });

            if (btnBulkDelete && bulkDeleteForm) {
                btnBulkDelete.addEventListener('click', function(e) {
                    e.preventDefault();
                    const selectedCheckboxes = document.querySelectorAll('.banner-row-checkbox:checked');
                    const count = selectedCheckboxes.length;
                    if (count === 0) return;

                    Swal.fire({
                        title: 'Hapus Banner Terpilih?',
                        text: `Apakah Anda yakin ingin menghapus ${count} banner yang dipilih?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#9CA3AF',
                        confirmButtonText: 'Ya, Hapus Terpilih!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-4',
                            confirmButton: 'btn btn-gradient-danger',
                            cancelButton: 'btn btn-light'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const inputsContainer = document.getElementById('bulkDeleteInputs');
                            inputsContainer.innerHTML = '';
                            selectedCheckboxes.forEach(cb => {
                                const input = document.createElement('input');
                                input.type = 'hidden';
                                input.name = 'ids[]';
                                input.value = cb.value;
                                inputsContainer.appendChild(input);
                            });
                            bulkDeleteForm.submit();
                        }
                    });
                });
            }
        });
    </script>
@endsection

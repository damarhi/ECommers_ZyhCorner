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
                                class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between"
                                type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false" style="height: 38px; padding: 0 15px; min-width: 160px; border-radius: 8px;">
                                <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i>
                                    Filter Posisi</span>
                            </button>
                            <div class="dropdown-menu p-3 shadow-sm" aria-labelledby="filterDropdown"
                                style="width: 260px; border-radius: 10px; margin-top: 5px;">
                                <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Posisi Penempatan</h6>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="" class="form-check-input" {{ request('posisi') === null || request('posisi') === '' ? 'checked' : '' }}>
                                        Semua Posisi
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="home_hero" class="form-check-input" {{ request('posisi') === 'home_hero' ? 'checked' : '' }}>
                                        Home Hero Banner
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="shop_header" class="form-check-input" {{ request('posisi') === 'shop_header' ? 'checked' : '' }}>
                                        Header Showcase
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="custom_header" class="form-check-input" {{ request('posisi') === 'custom_header' ? 'checked' : '' }}>
                                        Header Produk Kustom
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <label class="form-check-label text-muted" style="cursor: pointer;">
                                        <input type="radio" name="posisi" value="popup_promo" class="form-check-input" {{ request('posisi') === 'popup_promo' ? 'checked' : '' }}>
                                        Pop-up Promo
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                            </div>
                        </div>

                        <!-- Search Input -->
                        <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                            <input type="text" name="search" value="{{ request('search') }}"
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
                    <div class="col-md-6 col-lg-6 col-xl-4 grid-margin stretch-card mb-4">
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
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content" style="border-radius: 14px;">
                                <div class="modal-header border-0 pb-0">
                                    <div>
                                        <h5 class="modal-title font-weight-bold" id="previewBannerModalLabel{{ $b->id_banner }}">
                                            <i class="mdi mdi-eye text-primary me-2"></i> Preview Banner Pelanggan
                                        </h5>
                                        <small class="text-muted">Simulasi tampilan pada posisi <strong>{{ $b->posisi_label }}</strong></small>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <!-- Preview Container Box -->
                                    <div class="p-3 bg-light rounded-3 mb-3 border">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-primary px-3 py-1" style="font-size: 12px;">Tampilan Halaman Pelanggan</span>
                                            <small class="text-muted"><i class="mdi mdi-cellphone-link me-1"></i> Area: {{ $b->posisi_label }}</small>
                                        </div>

                                        <!-- Live Rendered Banner Preview Frame -->
                                        @if($b->posisi_banner === 'home_hero')
                                            <div class="p-4 text-center text-md-start d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 rounded-3 shadow-sm"
                                                style="background-image: linear-gradient(rgba(255,255,255,0.88), rgba(255,255,255,0.88)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; border: 1px solid #e2e8f0; min-height: 220px;">
                                                <div style="max-width: 60%;">
                                                    <h6 class="text-primary font-weight-bold mb-1">{{ $b->subjudul ?? 'Welcome to Zyh Corner' }}</h6>
                                                    <h3 class="font-weight-bold text-dark mb-2">{{ $b->judul_banner }}</h3>
                                                    <p class="text-muted mb-3" style="font-size: 13px;">Sale up to 30% OFF - Free shipping on all orders</p>
                                                    <span class="btn btn-sm btn-gradient-primary rounded-pill px-4">Shop Now <i class="mdi mdi-arrow-right ms-1"></i></span>
                                                </div>
                                                <div>
                                                    <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="Preview" class="rounded-circle shadow-sm" style="width: 140px; height: 140px; object-fit: cover; border: 3px solid #ffffff;">
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-4 text-center text-white rounded-3 shadow-sm d-flex flex-column align-items-center justify-content-center"
                                                style="background-image: linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)), url('{{ asset('storage/' . $b->gambar_banner) }}'); background-size: cover; background-position: center; min-height: 200px;">
                                                <h5 class="text-warning font-weight-bold mb-1">{{ $b->subjudul ?? 'Best Deals' }}</h5>
                                                <h2 class="font-weight-bold text-white mb-3">{{ $b->judul_banner }}</h2>
                                                <span class="btn btn-sm btn-success rounded-pill px-4">Shop Now <i class="mdi mdi-arrow-right ms-1"></i></span>
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
                            </div>
                        </div>
                    </div>

                    <!-- Modal Edit Banner #{{ $b->id_banner }} -->
                    <div class="modal fade" id="editBannerModal{{ $b->id_banner }}" tabindex="-1" aria-labelledby="editBannerModalLabel{{ $b->id_banner }}" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content" style="border-radius: 12px;">
                                <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title font-weight-bold" id="editBannerModalLabel{{ $b->id_banner }}">Edit Banner</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('banners.update', $b->id_banner) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-500 mb-1">Judul Banner</label>
                                            <input type="text" name="judul_banner" class="form-control" value="{{ $b->judul_banner }}" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-500 mb-1">Subjudul / Deskripsi Singkat</label>
                                            <input type="text" name="subjudul" class="form-control" value="{{ $b->subjudul }}">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-500 mb-1">Posisi Penempatan Banner</label>
                                            <select name="posisi_banner" class="form-select" required>
                                                <option value="home_hero" {{ $b->posisi_banner === 'home_hero' ? 'selected' : '' }}>Home Hero Banner</option>
                                                <option value="shop_header" {{ $b->posisi_banner === 'shop_header' ? 'selected' : '' }}>Header Showcase</option>
                                                <option value="custom_header" {{ $b->posisi_banner === 'custom_header' ? 'selected' : '' }}>Header Produk Kustom</option>
                                                <option value="popup_promo" {{ $b->posisi_banner === 'popup_promo' ? 'selected' : '' }}>Pop-up Promo</option>
                                            </select>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-8 mb-3">
                                                <label class="font-weight-500 mb-1">Target Link URL (Opsional)</label>
                                                <input type="text" name="link_url" class="form-control" value="{{ $b->link_url }}" placeholder="Contoh: /shop">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="font-weight-500 mb-1">Urutan Tampil</label>
                                                <input type="number" name="urutan" class="form-control" value="{{ $b->urutan }}" min="0">
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-500 mb-1">Gambar Banner</label>
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $b->gambar_banner) }}" alt="Preview" class="img-thumbnail rounded" style="max-height: 90px;">
                                            </div>
                                            <input type="file" name="gambar_banner" class="form-control" accept="image/*">
                                            <small class="form-text text-muted" style="font-size: 11px;">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="font-weight-500 mb-1">Status Banner</label>
                                            <select name="status" class="form-select" required>
                                                <option value="Aktif" {{ $b->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                <option value="Nonaktif" {{ $b->status === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                        <button type="submit" class="btn btn-gradient-primary">Perbarui Banner</button>
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
                                <h5 class="text-muted font-weight-bold">Belum Ada Data Banner</h5>
                                <p class="text-muted mb-0" style="font-size: 14px;">Klik tombol "Tambah Banner Baru" di atas untuk menambahkan visual promo pelanggan.</p>
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
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="addBannerModalLabel">Tambah Banner Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label class="font-weight-500 mb-1">Judul Banner</label>
                            <input type="text" name="judul_banner" class="form-control" placeholder="Contoh: Promo Spesial Kategori Kue" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-500 mb-1">Subjudul / Deskripsi Singkat (Opsional)</label>
                            <input type="text" name="subjudul" class="form-control" placeholder="Contoh: Dapatkan diskon hingga 50%">
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-500 mb-1">Posisi Penempatan Banner</label>
                            <select name="posisi_banner" class="form-select" required>
                                <option value="home_hero">Home Hero Banner</option>
                                <option value="shop_header">Header Showcase</option>
                                <option value="custom_header">Header Produk Kustom</option>
                                <option value="popup_promo">Pop-up Promo</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="font-weight-500 mb-1">Target Link URL (Opsional)</label>
                                <input type="text" name="link_url" class="form-control" placeholder="Contoh: /shop">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-500 mb-1">Urutan Tampil</label>
                                <input type="number" name="urutan" class="form-control" value="1" min="0">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-500 mb-1">Upload Gambar Banner</label>
                            <input type="file" name="gambar_banner" class="form-control" accept="image/*" required>
                            <small class="form-text text-muted" style="font-size: 11px;">Format: JPG, PNG, WEBP (Disarankan rasio landscape 16:9 atau banner lebar).</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-500 mb-1">Status Banner</label>
                            <select name="status" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary">Simpan Banner</button>
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
    </style>

    <script>
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

@extends('Layout.layout_pAdmin')

@section('title', 'Produk - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cube-outline"></i>
                </span> Produk Showcase
            </h3>
            <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
                <a href="{{ route('products.create', ['jenis' => 'showcase']) }}" class="btn btn-gradient-primary btn-sm px-4">
                    <i class="mdi mdi-plus me-1"></i> Tambah Produk
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        @php
                            $activeFilterCount = (is_array(request('status')) ? count(request('status')) : (request('status') ? 1 : 0))
                                + (is_array(request('kategori')) ? count(request('kategori')) : (request('kategori') ? 1 : 0))
                                + (request('diskon') ? 1 : 0)
                                + (request('terbaru') ? 1 : 0);
                            $hasActiveFilters = $activeFilterCount > 0 || request('search');
                        @endphp
                        <form method="GET" action="{{ route('products.index') }}"
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2 w-100 w-md-auto flex-wrap">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm admin-filter-btn {{ $activeFilterCount > 0 ? 'active' : '' }} dropdown-toggle"
                                        type="button" id="filterDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-haspopup="true"
                                        aria-expanded="false" style="min-width: 130px;">
                                        <span class="d-inline-flex align-items-center"><i class="mdi mdi-filter-variant me-1.5"></i>
                                            Filter
                                            @if($activeFilterCount > 0)
                                                <span class="admin-filter-badge ms-1.5">{{ $activeFilterCount }}</span>
                                            @endif
                                        </span>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-lg border-0" aria-labelledby="filterDropdown"
                                        style="width: 250px; border-radius: 10px; margin-top: 6px; z-index: 1050;">
                                        @if(isset($kategoris) && $kategoris->count() > 0)
                                            <h6 class="dropdown-header px-0 mb-2 text-primary"
                                                style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Kategori Produk</h6>
                                            <div style="max-height: 180px; overflow-y: auto;" class="mb-3">
                                                @foreach($kategoris as $kategori)
                                                <div class="form-check mb-2">
                                                    <label class="form-check-label text-muted" style="cursor: pointer; font-size: 13px;">
                                                        <input type="checkbox" name="kategori[]" value="{{ $kategori->id_kategori }}"
                                                            class="form-check-input"
                                                            {{ is_array(request('kategori')) && in_array($kategori->id_kategori, request('kategori')) ? 'checked' : '' }}>
                                                        {{ $kategori->nama_kategori }}
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>

                                            <div class="dropdown-divider mb-3"></div>
                                        @endif

                                        <h6 class="dropdown-header px-0 mb-2 text-primary"
                                            style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Status Stok</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer; font-size: 13px;">
                                                <input type="checkbox" name="status[]" value="Tersedia"
                                                    class="form-check-input"
                                                    {{ is_array(request('status')) && in_array('Tersedia', request('status')) ? 'checked' : '' }}>
                                                Tersedia
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer; font-size: 13px;">
                                                <input type="checkbox" name="status[]" value="Habis"
                                                    class="form-check-input"
                                                    {{ is_array(request('status')) && in_array('Habis', request('status')) ? 'checked' : '' }}>
                                                Habis
                                            </label>
                                        </div>

                                        <div class="dropdown-divider mb-3"></div>

                                        <h6 class="dropdown-header px-0 mb-2 text-primary"
                                            style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Fitur Tambahan</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer; font-size: 13px;">
                                                <input type="checkbox" name="diskon" value="1"
                                                    class="form-check-input" {{ request('diskon') ? 'checked' : '' }}>
                                                Sedang Diskon
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer; font-size: 13px;">
                                                <input type="checkbox" name="terbaru" value="1"
                                                    class="form-check-input" {{ request('terbaru') ? 'checked' : '' }}>
                                                Produk Terbaru
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-gradient-primary w-100" style="height: 34px; font-weight: 600;">Terapkan Filter</button>
                                    </div>
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
                            </div>
                            <button type="button" id="btnBulkDelete"
                                class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center align-self-end align-self-md-center px-3"
                                style="height: 38px;" title="Hapus Terpilih">
                                <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                            </button>
                        </form>

                        <form action="{{ route('products.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                            @csrf
                            <div id="bulkDeleteInputs"></div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;">
                                            <div class="form-check m-0">
                                                <label class="form-check-label">
                                                    <input type="checkbox" id="checkAllProducts" class="form-check-input" style="cursor: pointer;">
                                                <i class="input-helper"></i></label>
                                            </div>
                                        </th>
                                        <th>Produk</th>
                                        <th>Stok Produk</th>
                                        <th>Diskon</th>
                                        <th>Harga Asli</th>
                                        <th class="text-center" style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($produks as $p)
                                        <tr data-search="{{ strtolower($p->nama_produk . ' ' . ($p->kategori->nama_kategori ?? '') . ' ' . $p->id_produk) }}">
                                            <td>
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" name="ids[]" value="{{ $p->id_produk }}" class="form-check-input product-row-checkbox" style="cursor: pointer;">
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($p->foto_1)
                                                        <img src="{{ asset('storage/' . $p->foto_1) }}"
                                                            alt="{{ $p->nama_produk }}" class="me-3"
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                    @else
                                                        <div class="me-3 d-flex align-items-center justify-content-center bg-secondary text-white"
                                                            style="width: 50px; height: 50px; border-radius: 8px;">
                                                            <i class="mdi mdi-image text-white"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $p->nama_produk }}</h6>
                                                        <small class="text-muted">{{ $p->kategori ? $p->kategori->nama_kategori : 'Tanpa Kategori' }}</small>
                                                        @if ($p->bisa_custom_teks)
                                                            <span class="badge badge-soft-info ms-1" style="font-size: 10px; padding: 2px 6px;" title="Dapat diberi tulisan di atas kue"><i class="mdi mdi-lead-pencil me-1"></i>Custom Teks</span>
                                                        @endif
                                                        @if ($p->is_signature)
                                                            <span class="badge ms-1" style="background: rgba(217, 119, 6, 0.12); color: #d97706; font-size: 10px; padding: 2px 6px;" title="Produk Signature / Resep Unggulan Toko"><i class="mdi mdi-crown me-1"></i>Signature</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($p->status_produk == 'Tersedia' || $p->status_produk == 'Available')
                                                    <label
                                                        class="badge badge-soft-success">{{ $p->status_produk }}</label>
                                                @else
                                                    <label
                                                        class="badge badge-soft-warning">{{ $p->status_produk }}</label>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($p->has_diskon)
                                                    <span class="badge {{ ($p->diskonAktif && $p->diskonAktif->tipe_diskon === 'persen') ? 'badge-soft-danger' : 'badge-soft-success' }}" style="font-size: 11px; font-weight: 600; padding: 4px 8px; border-radius: 4px;">
                                                        <i class="mdi mdi-tag-outline me-1"></i>{{ $p->diskon_badge }}
                                                    </span>
                                                    @if ($p->diskonAktif && $p->diskonAktif->nama_diskon)
                                                        <small class="text-muted d-block mt-1 text-truncate" style="max-width: 140px; font-size: 10.5px;" title="{{ $p->diskonAktif->nama_diskon }}">
                                                            {{ $p->diskonAktif->nama_diskon }}
                                                        </small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($p->has_diskon)
                                                    <div class="d-flex flex-column" style="line-height: 1.35;">
                                                        <span class="font-weight-600 text-success" style="font-size: 13.5px;">
                                                            Rp. {{ number_format($p->harga_diskon, 0, ',', '.') }}
                                                        </span>
                                                        <small class="text-muted text-decoration-line-through" style="font-size: 11px;">
                                                            Rp. {{ number_format($p->harga, 0, ',', '.') }}
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="font-weight-600 text-dark">Rp. {{ number_format($p->harga, 0, ',', '.') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('products.edit', $p->id_produk) }}"
                                                    class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1"
                                                    style="width: 32px; height: 32px; padding: 0;" title="Edit">
                                                    <i class="mdi mdi-pencil" style="margin: 0; font-size: 16px;"></i>
                                                </a>
                                                <form action="{{ route('products.destroy', $p->id_produk) }}"
                                                    method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                                        <i class="mdi mdi-delete" style="margin: 0; font-size: 16px;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="mdi mdi-cube-outline text-muted mb-2"
                                                        style="font-size: 64px;"></i>
                                                    @if($hasActiveFilters)
                                                        <h5 class="text-dark font-weight-bold mb-1">Tidak Ditemukan Produk yang Sesuai</h5>
                                                        <p class="text-muted mb-3" style="font-size: 14px;">Tidak ada produk untuk kriteria filter yang sedang aktif.</p>
                                                        <a href="{{ route('products.index') }}" class="btn btn-sm btn-gradient-primary rounded-pill px-4">
                                                            <i class="mdi mdi-refresh me-1"></i> Reset Filter & Pencarian
                                                        </a>
                                                    @else
                                                        <h5 class="text-muted font-weight-bold">Belum Ada Produk</h5>
                                                        <p class="text-muted mb-0" style="font-size: 14px;">Tambahkan produk pertama Anda sekarang!</p>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                            <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
                                {{ $produks->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                            <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                                {{ $produks->total() }} Hasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


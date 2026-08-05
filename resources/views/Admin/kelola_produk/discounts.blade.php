@extends('Layout.layout_pAdmin')

@section('title', 'Kelola Diskon - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h3 class="page-title d-inline-flex align-items-center m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-sale"></i>
                </span>
                <span class="text-nowrap">Diskon & Promo</span>
            </h3>
            <button type="button" class="btn btn-gradient-primary btn-sm d-flex align-items-center justify-content-center"
                data-bs-toggle="modal" data-bs-target="#addDiscountModal">
                <i class="mdi mdi-plus me-1"></i> Tambah Diskon
            </button>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row mt-4">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-coral border-0 text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="mdi mdi-sale"></i>
                        </div>
                        <div>
                            <p class="stat-label mb-0">Diskon Aktif</p>
                            <h3 class="stat-value">{{ $totalAktif }}</h3>
                            <p class="stat-change mb-0">Promo sedang berjalan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-indigo border-0 text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="mdi mdi-cube-outline"></i>
                        </div>
                        <div>
                            <p class="stat-label mb-0">Produk Berdiskon</p>
                            <h3 class="stat-value">{{ $totalProdukBerdiskon }}</h3>
                            <p class="stat-change mb-0">Item mendapatkan potongan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-teal border-0 text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="mdi mdi-percent"></i>
                        </div>
                        <div>
                            <p class="stat-label mb-0">Rata-rata Potongan</p>
                            <h3 class="stat-value">{{ number_format($avgDiskonPersen, 1) }}%</h3>
                            <p class="stat-change mb-0">Persentase rata-rata promo</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card shadow-sm" style="border-radius: 12px;">
                    <div class="card-body">
                        <!-- Filter & Search Form -->
                        <form method="GET" action="{{ route('discounts.index') }}"
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100"
                                        type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" style="height: 36px; padding: 0 15px; min-width: 130px;">
                                        <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i>
                                            Filter Promo</span>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-sm" aria-labelledby="filterDropdown"
                                        style="width: 240px; border-radius: 8px; margin-top: 5px;">
                                        <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Diskon</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="" class="form-check-input" {{ request('status') === null || request('status') === '' ? 'checked' : '' }}>
                                                Semua Status
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="Aktif" class="form-check-input" {{ request('status') === 'Aktif' ? 'checked' : '' }}>
                                                Aktif
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="Nonaktif" class="form-check-input" {{ request('status') === 'Nonaktif' ? 'checked' : '' }}>
                                                Nonaktif
                                            </label>
                                        </div>

                                        <div class="dropdown-divider mb-3"></div>

                                        <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Tipe Potongan</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="tipe" value="" class="form-check-input" {{ request('tipe') === null || request('tipe') === '' ? 'checked' : '' }}>
                                                Semua Tipe
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="tipe" value="persen" class="form-check-input" {{ request('tipe') === 'persen' ? 'checked' : '' }}>
                                                Persentase (%)
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="tipe" value="nominal" class="form-check-input" {{ request('tipe') === 'nominal' ? 'checked' : '' }}>
                                                Nominal (Rp)
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                                    </div>
                                </div>
                                <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari diskon / produk..." 
                                        style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                                </div>
                                <button type="button" id="btnBulkDelete" class="btn btn-gradient-danger btn-sm d-none align-items-center justify-content-center ms-auto" style="height: 36px;">
                                    <i class="mdi mdi-delete me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                        </form>

                        <!-- Table with Bulk Delete Form -->
                        <form action="{{ route('discounts.bulk-delete') }}" method="POST" id="bulkDeleteForm">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <div class="form-check m-0">
                                                    <label class="form-check-label" style="cursor: pointer;">
                                                        <input type="checkbox" id="checkAllDiscounts" class="form-check-input" style="cursor: pointer;">
                                                    </label>
                                                </div>
                                            </th>
                                            <th>Nama Promo</th>
                                            <th>Produk</th>
                                            <th>Besar Diskon</th>
                                            <th>Skema Harga</th>
                                            <th>Periode</th>
                                            <th>Status</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($diskons as $d)
                                            <tr>
                                                <td>
                                                    <div class="form-check m-0">
                                                        <label class="form-check-label" style="cursor: pointer;">
                                                            <input type="checkbox" name="ids[]" value="{{ $d->id_diskon }}" class="form-check-input discount-row-checkbox" style="cursor: pointer;">
                                                        </label>
                                                    </div>
                                                </td>
                                            <td>
                                                <h6 class="mb-0 font-weight-bold text-dark">{{ $d->nama_diskon }}</h6>
                                                <small class="text-muted" style="font-size: 11px;">Dibuat {{ $d->created_at->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                @if($d->produk)
                                                    <div class="d-flex align-items-center">
                                                        @if ($d->produk->foto_1)
                                                            <img src="{{ asset('storage/' . $d->produk->foto_1) }}"
                                                                alt="{{ $d->produk->nama_produk }}" class="me-2"
                                                                style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px;">
                                                        @else
                                                            <div class="me-2 d-flex align-items-center justify-content-center bg-secondary text-white"
                                                                style="width: 42px; height: 42px; border-radius: 8px;">
                                                                <i class="mdi mdi-image text-white"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <span class="font-weight-bold d-block text-truncate" style="max-width: 160px; font-size: 14px;">{{ $d->produk->nama_produk }}</span>
                                                            <small class="text-muted" style="font-size: 11px;">{{ $d->produk->kategori ? $d->produk->kategori->nama_kategori : 'Tanpa Kategori' }}</small>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted italic">Produk dihapus</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->tipe_diskon === 'persen')
                                                    <span class="badge badge-gradient-danger px-3 py-2" style="font-size: 13px; border-radius: 20px;">
                                                        <i class="mdi mdi-tag-outline me-1"></i>{{ number_format($d->nilai_diskon, 0) }}% OFF
                                                    </span>
                                                @else
                                                    <span class="badge badge-gradient-success px-3 py-2" style="font-size: 13px; border-radius: 20px;">
                                                        Potongan Rp {{ number_format($d->nilai_diskon, 0, ',', '.') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->produk)
                                                    <div class="d-flex flex-column">
                                                        <span class="text-muted text-decoration-line-through" style="font-size: 12px;">
                                                            Rp {{ number_format($d->produk->harga, 0, ',', '.') }}
                                                        </span>
                                                        <span class="font-weight-bold text-success" style="font-size: 14px;">
                                                            Rp {{ number_format($d->harga_setelah_diskon, 0, ',', '.') }}
                                                        </span>
                                                    </div>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->tanggal_mulai || $d->tanggal_selesai)
                                                    <small class="d-block text-dark font-weight-500" style="font-size: 12px;">
                                                        {{ $d->tanggal_mulai ? \Carbon\Carbon::parse($d->tanggal_mulai)->format('d M Y') : 'Mulai' }}
                                                        s/d
                                                        {{ $d->tanggal_selesai ? \Carbon\Carbon::parse($d->tanggal_selesai)->format('d M Y') : 'Selesai' }}
                                                    </small>
                                                @else
                                                    <span class="badge bg-light text-dark font-weight-normal" style="font-size: 11px;">Permanen / Tanpa Batas</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->status === 'Aktif')
                                                    <label class="badge badge-soft-success">Aktif</label>
                                                @else
                                                    <label class="badge badge-soft-danger">Nonaktif</label>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <!-- Toggle Status -->
                                                <form action="{{ route('discounts.toggle', $d->id_diskon) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $d->status === 'Aktif' ? 'btn-inverse-warning' : 'btn-inverse-success' }} d-inline-flex align-items-center justify-content-center me-1"
                                                        style="width: 32px; height: 32px; padding: 0;" title="{{ $d->status === 'Aktif' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <i class="mdi {{ $d->status === 'Aktif' ? 'mdi-pause' : 'mdi-play' }}" style="margin: 0; font-size: 16px;"></i>
                                                    </button>
                                                </form>

                                                <!-- Edit Modal Trigger -->
                                                <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1"
                                                    style="width: 32px; height: 32px; padding: 0;" title="Edit"
                                                    data-bs-toggle="modal" data-bs-target="#editDiscountModal{{ $d->id_diskon }}">
                                                    <i class="mdi mdi-pencil" style="margin: 0; font-size: 16px;"></i>
                                                </button>

                                                <!-- Delete Form -->
                                                <form action="{{ route('discounts.destroy', $d->id_diskon) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                                        <i class="mdi mdi-delete" style="margin: 0; font-size: 16px;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit Diskon #{{ $d->id_diskon }} -->
                                        <div class="modal fade" id="editDiscountModal{{ $d->id_diskon }}" tabindex="-1" aria-labelledby="editDiscountModalLabel{{ $d->id_diskon }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content" style="border-radius: 12px;">
                                                    <div class="modal-header border-0 pb-0">
                                                        <h5 class="modal-title font-weight-bold" id="editDiscountModalLabel{{ $d->id_diskon }}">Edit Diskon</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('discounts.update', $d->id_diskon) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-500 mb-1">Pilih Produk</label>
                                                                <select name="id_produk" class="form-select" required>
                                                                    <option value="">-- Pilih Produk --</option>
                                                                    @foreach($produks as $p)
                                                                        <option value="{{ $p->id_produk }}" {{ $d->id_produk == $p->id_produk ? 'selected' : '' }}>
                                                                            {{ $p->nama_produk }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-500 mb-1">Nama Promo / Diskon</label>
                                                                <input type="text" name="nama_diskon" class="form-control" value="{{ $d->nama_diskon }}" required>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="font-weight-500 mb-1">Tipe Diskon</label>
                                                                    <select name="tipe_diskon" class="form-select" required>
                                                                        <option value="persen" {{ $d->tipe_diskon === 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                                                                        <option value="nominal" {{ $d->tipe_diskon === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="font-weight-500 mb-1">Nilai Diskon</label>
                                                                    <input type="number" name="nilai_diskon" class="form-control" value="{{ (int)$d->nilai_diskon }}" min="0" required>
                                                                </div>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="font-weight-500 mb-1">Tanggal Mulai</label>
                                                                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ $d->tanggal_mulai }}">
                                                                </div>
                                                                <div class="col-md-6 mb-3">
                                                                    <label class="font-weight-500 mb-1">Tanggal Selesai</label>
                                                                    <input type="date" name="tanggal_selesai" class="form-control" value="{{ $d->tanggal_selesai }}">
                                                                </div>
                                                            </div>

                                                            <div class="form-group mb-0">
                                                                <label class="font-weight-500 mb-1">Status Diskon</label>
                                                                <select name="status" class="form-select" required>
                                                                    <option value="Aktif" {{ $d->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                                    <option value="Nonaktif" {{ $d->status === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-0 pt-0">
                                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-gradient-primary">Perbarui Diskon</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="mdi mdi-sale text-muted mb-2" style="font-size: 64px;"></i>
                                                    <h5 class="text-muted font-weight-bold">Belum Ada Diskon</h5>
                                                    <p class="text-muted mb-0" style="font-size: 14px;">Tambahkan promo diskon produk pertama Anda sekarang!</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        </form>

                        <!-- Pagination -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 gap-3">
                            <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
                                {{ $diskons->links('pagination::bootstrap-4') }}
                            </div>
                            <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                                {{ $diskons->total() }} Hasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Diskon -->
    <div class="modal fade" id="addDiscountModal" tabindex="-1" aria-labelledby="addDiscountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="addDiscountModalLabel">Tambah Diskon Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('discounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="font-weight-500 m-0">Pilih Produk (Bisa Pilih Banyak)</label>
                                <div>
                                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2" id="btnSelectAllProducts" style="font-size: 11px;">Pilih Semua</button>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2" id="btnDeselectAllProducts" style="font-size: 11px;">Batal Pilih</button>
                                </div>
                            </div>
                             <div class="position-relative mb-2">
                                 <input type="text" id="modalProductSearch" class="form-control form-control-sm ps-5 pe-3" placeholder="Cari produk di list..." style="font-size: 12px; height: 34px; border-radius: 6px; border: 1px solid #ebedf2;">
                                 <i class="mdi mdi-magnify position-absolute text-muted" style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 15px; pointer-events: none;"></i>
                             </div>
                            <div class="border rounded" style="max-height: 200px; overflow-y: auto; background: #ffffff;">
                                @forelse($produks as $p)
                                    <div class="product-item-option p-2 border-bottom d-flex align-items-center justify-content-between" style="cursor: pointer; transition: background 0.15s;" onclick="toggleProductItemCheckbox(event, 'chk_prod_{{ $p->id_produk }}')">
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="checkbox" name="id_produk[]" id="chk_prod_{{ $p->id_produk }}" value="{{ $p->id_produk }}" class="product-check-item" style="width: 17px; height: 17px; cursor: pointer; accent-color: #4F46E5;" onclick="event.stopPropagation();">
                                            <span class="font-weight-500 text-dark" style="font-size: 13.5px;">{{ $p->nama_produk }}</span>
                                        </div>
                                        <span class="text-muted" style="font-size: 12px;">Rp {{ number_format($p->harga, 0, ',', '.') }}</span>
                                    </div>
                                @empty
                                    <div class="p-3 text-center text-muted" style="font-size: 13px;">Belum ada produk yang tersedia.</div>
                                @endforelse
                            </div>
                            <small class="form-text text-muted mt-1" style="font-size: 11px;">Klik baris atau centang produk yang ingin diberi diskon promo ini.</small>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-500 mb-1">Nama Promo / Diskon</label>
                            <input type="text" name="nama_diskon" class="form-control" placeholder="Contoh: Diskon Akhir Tahun 20%" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-500 mb-1">Tipe Diskon</label>
                                <select name="tipe_diskon" class="form-select" required>
                                    <option value="persen">Persentase (%)</option>
                                    <option value="nominal">Nominal (Rp)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-500 mb-1">Nilai Diskon</label>
                                <input type="number" name="nilai_diskon" class="form-control" placeholder="Nilai (e.g. 20 atau 15000)" min="0" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-500 mb-1">Tanggal Mulai (Opsional)</label>
                                <input type="date" name="tanggal_mulai" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-500 mb-1">Tanggal Selesai (Opsional)</label>
                                <input type="date" name="tanggal_selesai" class="form-control">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-500 mb-1">Status Diskon</label>
                            <select name="status" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary">Simpan Diskon</button>
                    </div>
                </form>
    <script>
        function toggleProductItemCheckbox(event, checkboxId) {
            const checkbox = document.getElementById(checkboxId);
            if (checkbox && event.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const selectAllBtn = document.getElementById('btnSelectAllProducts');
            const deselectAllBtn = document.getElementById('btnDeselectAllProducts');
            const searchInput = document.getElementById('modalProductSearch');

            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('.product-check-item').forEach(cb => {
                        if (cb.closest('.product-item-option').style.display !== 'none') {
                            cb.checked = true;
                        }
                    });
                });
            }

            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    document.querySelectorAll('.product-check-item').forEach(cb => cb.checked = false);
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const term = this.value.toLowerCase().trim();
                    document.querySelectorAll('.product-item-option').forEach(item => {
                        const text = item.textContent.toLowerCase();
                        item.style.display = text.includes(term) ? 'flex' : 'none';
                    });
                });
            }

            // --- Check All & Bulk Delete Logic ---
            const checkAllHeader = document.getElementById('checkAllDiscounts');
            const rowCheckboxes = document.querySelectorAll('.discount-row-checkbox');
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');

            function updateBulkDeleteState() {
                const checkedRows = document.querySelectorAll('.discount-row-checkbox:checked');
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
                    const count = document.querySelectorAll('.discount-row-checkbox:checked').length;
                    if (count === 0) return;

                    Swal.fire({
                        title: 'Hapus Diskon Terpilih?',
                        text: `Apakah Anda yakin ingin menghapus ${count} diskon yang dipilih?`,
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
                            bulkDeleteForm.submit();
                        }
                    });
                });
            }
        });
    </script>
@endsection

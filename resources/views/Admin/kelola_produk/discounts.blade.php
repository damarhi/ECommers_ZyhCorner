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
                                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari diskon / produk..." 
                                        style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                                </div>
                                <button type="button" id="btnBulkDelete" class="btn btn-gradient-danger btn-sm d-none align-items-center justify-content-center ms-auto" style="height: 36px;">
                                    <i class="mdi mdi-delete me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                        </form>

                        <!-- Table Bulk Delete Form (Outside Table) -->
                        <form action="{{ route('discounts.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                            @csrf
                            <div id="bulkDeleteInputs"></div>
                        </form>

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
                                            <th class="text-center" style="width: 140px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($diskons as $d)
                                            <tr data-search="{{ strtolower($d->nama_diskon . ' ' . ($d->produk->nama_produk ?? '') . ' ' . $d->status) }}">
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
                                                    <span class="badge badge-soft-danger">
                                                        <i class="mdi mdi-tag-outline me-1"></i>{{ number_format($d->nilai_diskon, 0) }}% OFF
                                                    </span>
                                                @else
                                                    <span class="badge badge-soft-success">
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
                                                    <span class="badge badge-soft-dark">Permanen</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($d->status === 'Aktif')
                                                    <label class="badge badge-soft-success">Aktif</label>
                                                @else
                                                    <label class="badge badge-soft-danger">Nonaktif</label>
                                                @endif
                                            </td>
                                            <td class="text-center">
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
                                            <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; margin: 1.75rem auto; width: 95%;">
                                                <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                                                    <!-- Header Modal -->
                                                    <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 34px; height: 34px; background-color: #ede9fe; color: #4f46e5 !important;">
                                                                <i class="mdi mdi-percent-outline" style="font-size: 18px;"></i>
                                                            </span>
                                                            <div>
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editDiscountModalLabel{{ $d->id_diskon }}" style="font-size: 15px;">
                                                                        Edit Diskon Produk
                                                                    </h6>
                                                                    <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">#{{ $d->id_diskon }}</span>
                                                                </div>
                                                                <small class="text-muted" style="font-size: 11px;">Perbarui nilai diskon atau masa berlaku promo.</small>
                                                            </div>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                                                    </div>
                                                    <form action="{{ route('discounts.update', $d->id_diskon) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                                                            <!-- Pilih Produk -->
                                                            <div class="form-group mb-2.5">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Produk Terkait <span class="text-danger">*</span></label>
                                                                <select name="id_produk" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                                                    <option value="">-- Pilih Produk --</option>
                                                                    @foreach($produks as $p)
                                                                        <option value="{{ $p->id_produk }}" {{ $d->id_produk == $p->id_produk ? 'selected' : '' }}>
                                                                            {{ $p->nama_produk }} (Rp {{ number_format($p->harga, 0, ',', '.') }})
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>

                                                            <!-- Nama Diskon -->
                                                            <div class="form-group mb-2.5">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Promo / Diskon <span class="text-danger">*</span></label>
                                                                <input type="text" name="nama_diskon" class="form-control" value="{{ $d->nama_diskon }}" required style="font-size: 12px; border-radius: 6px;">
                                                            </div>

                                                            <!-- Tipe & Nilai Diskon (Grid 2 Kolom) -->
                                                            <div class="row g-2 mb-2.5">
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-0">
                                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Tipe Diskon <span class="text-danger">*</span></label>
                                                                        <select name="tipe_diskon" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                                                            <option value="persen" {{ $d->tipe_diskon === 'persen' ? 'selected' : '' }}>Persentase (%)</option>
                                                                            <option value="nominal" {{ $d->tipe_diskon === 'nominal' ? 'selected' : '' }}>Nominal (Rp)</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-0">
                                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nilai Diskon <span class="text-danger">*</span></label>
                                                                        <input type="number" name="nilai_diskon" class="form-control" value="{{ (int)$d->nilai_diskon }}" min="0" required style="font-size: 12px; border-radius: 6px;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Rentang Tanggal (Grid 2 Kolom) -->
                                                            <div class="row g-2 mb-2.5">
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-0">
                                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Tanggal Mulai</label>
                                                                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ $d->tanggal_mulai }}" style="font-size: 12px; border-radius: 6px;">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group mb-0">
                                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Tanggal Selesai</label>
                                                                        <input type="date" name="tanggal_selesai" class="form-control" value="{{ $d->tanggal_selesai }}" style="font-size: 12px; border-radius: 6px;">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Status Diskon -->
                                                            <div class="form-group mb-0">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Status Diskon <span class="text-danger">*</span></label>
                                                                <select name="status" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                                                    <option value="Aktif" {{ $d->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                                                    <option value="Nonaktif" {{ $d->status === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                                                            <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                                                            <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                                                                <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Perbarui Diskon
                                                            </button>
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
        <div class="modal-dialog modal-dialog-centered" style="max-width: 580px; margin: 1.75rem auto; width: 95%;">
            <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                <!-- Header Modal -->
                <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center text-success rounded-circle" style="width: 34px; height: 34px; background-color: #dcfce7; color: #16a34a !important;">
                            <i class="mdi mdi-tag-percent-outline" style="font-size: 18px;"></i>
                        </span>
                        <div>
                            <h6 class="modal-title font-weight-bold mb-0 text-dark" id="addDiscountModalLabel" style="font-size: 15px;">
                                Tambah Diskon Baru
                            </h6>
                            <small class="text-muted" style="font-size: 11px;">Terapkan potongan promo ke satu atau banyak produk sekaligus.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <form action="{{ route('discounts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                        <!-- Searchable Multi-Select Product Dropdown -->
                        <div class="form-group mb-3" id="productDropdownContainer">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <label class="form-label font-weight-500 m-0 text-dark" style="font-size: 12px;">
                                    Pilih Produk <span class="text-danger">*</span>
                                </label>
                                <span class="badge bg-light text-muted border" id="selectedProductsCountBadge" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">
                                    0 Produk Terpilih
                                </span>
                            </div>

                            <!-- Relative Anchor for Trigger and Floating Menu -->
                            <div class="position-relative">
                                <!-- Dropdown Trigger Button -->
                                <div id="productDropdownTrigger" class="form-control d-flex align-items-center justify-content-between px-3" 
                                     tabindex="0" role="combobox" aria-expanded="false" aria-haspopup="listbox"
                                     style="font-size: 12px; border-radius: 6px; height: 38px; cursor: pointer; background-color: #fff; border-color: #ebedf2; transition: all 0.2s ease;">
                                    <div class="d-flex align-items-center gap-2 overflow-hidden me-2" style="max-width: 88%;">
                                        <i class="mdi mdi-cake-variant-outline text-primary" style="font-size: 15px; flex-shrink: 0;"></i>
                                        <span id="productDropdownTriggerText" class="text-muted text-truncate" style="font-size: 12px;">
                                            Pilih satu atau beberapa produk...
                                        </span>
                                    </div>
                                    <i class="mdi mdi-chevron-down text-muted flex-shrink-0" id="productDropdownCaret" style="font-size: 16px; transition: transform 0.2s ease;"></i>
                                </div>

                                <!-- Floating Dropdown Menu Panel -->
                                <div id="productDropdownMenu" class="dropdown-menu shadow-lg w-100 p-0 border" 
                                     style="display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0; z-index: 1055; border-radius: 8px; border-color: #e2e8f0; background: #ffffff; overflow: hidden; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12), 0 8px 10px -6px rgba(0,0,0,0.08);">
                                    
                                    <!-- Dropdown Header: Compact Live Search & Action Buttons -->
                                    <div class="p-2 border-bottom bg-light">
                                        <div class="position-relative mb-1.5">
                                            <input type="text" id="modalProductSearch" class="form-control form-control-sm ps-4 pe-4" 
                                                   placeholder="Ketik nama produk..." autocomplete="off" 
                                                   style="font-size: 11.5px; height: 30px; border-radius: 6px; border-color: #cbd5e1; background: #ffffff;">
                                            <i class="mdi mdi-magnify position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 14px; pointer-events: none;"></i>
                                            <button type="button" id="modalProductSearchClear" class="btn btn-link p-0 position-absolute text-muted d-none" 
                                                    style="right: 8px; top: 50%; transform: translateY(-50%); text-decoration: none;" title="Hapus pencarian">
                                                <i class="mdi mdi-close-circle" style="font-size: 14px;"></i>
                                            </button>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center px-0.5">
                                            <small class="text-muted" style="font-size: 10.5px;" id="productFilterCountText">
                                                {{ count($produks) }} produk tersedia
                                            </small>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0.5 px-2" id="btnSelectAllProducts" style="font-size: 10px; border-radius: 4px;">
                                                    <i class="mdi mdi-check me-1"></i>Pilih Semua
                                                </button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2" id="btnDeselectAllProducts" style="font-size: 10px; border-radius: 4px;">
                                                    <i class="mdi mdi-close me-1"></i>Batal
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Scrollable Product Options List (Compact & Dense) -->
                                    <div id="productOptionsScrollList" style="max-height: 145px; overflow-y: auto;">
                                        @forelse($produks as $p)
                                            <div class="product-item-option px-2.5 py-1.5 border-bottom d-flex align-items-center justify-content-between" 
                                                 data-id="{{ $p->id_produk }}" 
                                                 data-name="{{ strtolower($p->nama_produk) }}" 
                                                 data-price="{{ $p->harga }}"
                                                 style="cursor: pointer; transition: background 0.15s;" 
                                                 onclick="toggleProductItemCheckbox(event, 'chk_prod_{{ $p->id_produk }}')">
                                                <div class="d-flex align-items-center gap-2 text-truncate me-2" style="max-width: 75%;">
                                                    <input type="checkbox" name="id_produk[]" id="chk_prod_{{ $p->id_produk }}" value="{{ $p->id_produk }}" 
                                                           class="product-check-item form-check-input mt-0" 
                                                           style="width: 14px; height: 14px; cursor: pointer; accent-color: #4F46E5;" 
                                                           onclick="event.stopPropagation();"
                                                           {{ is_array(old('id_produk')) && in_array($p->id_produk, old('id_produk')) ? 'checked' : '' }}>
                                                    <span class="font-weight-500 text-dark text-truncate product-item-name" style="font-size: 12px;">{{ $p->nama_produk }}</span>
                                                </div>
                                                <span class="badge bg-light text-muted border flex-shrink-0" style="font-size: 10px; font-weight: 500; padding: 2px 6px;">
                                                    Rp {{ number_format($p->harga, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        @empty
                                            <div class="p-3 text-center text-muted" style="font-size: 12px;">Belum ada produk yang tersedia.</div>
                                        @endforelse
                                        <div id="productNoMatch" class="p-3 text-center text-muted d-none" style="font-size: 11.5px;">
                                            <i class="mdi mdi-magnify-close d-block mb-1 text-muted" style="font-size: 20px;"></i>
                                            Tidak ada produk yang cocok dengan pencarian.
                                        </div>
                                    </div>

                                    <!-- Dropdown Footer (Slim Status Bar) -->
                                    <div class="py-1 px-2.5 bg-light border-top d-flex justify-content-between align-items-center">
                                        <small class="text-muted" style="font-size: 10px;">
                                            Tekan di luar untuk menutup
                                        </small>
                                        <button type="button" class="btn btn-xs btn-gradient-primary py-0.5 px-2 text-white" id="btnCloseProductDropdown" style="font-size: 10px; border-radius: 4px; font-weight: 500;">
                                            Selesai
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Helper text directly under trigger -->
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">
                                Pilih satu atau beberapa produk yang akan diterapkan promo diskon ini.
                            </small>
                        </div>

                        <!-- Nama Promo / Diskon -->
                        <div class="form-group mb-3">
                            <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Nama Promo / Diskon <span class="text-danger">*</span></label>
                            <input type="text" name="nama_diskon" class="form-control" placeholder="Contoh: Promo Akhir Pekan 15%" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                        </div>

                        <!-- Tipe & Nilai Diskon (Grid 2 Kolom) -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Tipe Diskon <span class="text-danger">*</span></label>
                                    <select name="tipe_diskon" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                        <option value="persen">Persentase (%)</option>
                                        <option value="nominal">Nominal (Rp)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Nilai Diskon <span class="text-danger">*</span></label>
                                    <input type="number" name="nilai_diskon" class="form-control" placeholder="Nilai (e.g. 20 atau 15000)" min="0" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                </div>
                            </div>
                        </div>

                        <!-- Rentang Tanggal (Grid 2 Kolom) -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Tanggal Mulai <small class="text-muted fw-normal">(Opsional)</small></label>
                                    <input type="date" name="tanggal_mulai" class="form-control" style="font-size: 12px; border-radius: 6px; height: 38px;">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Tanggal Selesai <small class="text-muted fw-normal">(Opsional)</small></label>
                                    <input type="date" name="tanggal_selesai" class="form-control" style="font-size: 12px; border-radius: 6px; height: 38px;">
                                </div>
                            </div>
                        </div>

                        <!-- Status Diskon -->
                        <div class="form-group mb-1">
                            <label class="form-label font-weight-500 mb-1.5 text-dark" style="font-size: 12px;">Status Diskon <span class="text-danger">*</span></label>
                            <select name="status" class="form-control form-select" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                <option value="Aktif" selected>Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                            <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Diskon
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function toggleProductItemCheckbox(event, checkboxId) {
            const checkbox = document.getElementById(checkboxId);
            if (checkbox && event.target !== checkbox) {
                checkbox.checked = !checkbox.checked;
            }
            if (typeof updateProductDropdownSummary === 'function') {
                updateProductDropdownSummary();
            }
        }

        let updateProductDropdownSummary;

        document.addEventListener('DOMContentLoaded', function() {
            const dropdownContainer = document.getElementById('productDropdownContainer');
            const dropdownTrigger = document.getElementById('productDropdownTrigger');
            const dropdownMenu = document.getElementById('productDropdownMenu');
            const dropdownCaret = document.getElementById('productDropdownCaret');
            const triggerText = document.getElementById('productDropdownTriggerText');
            const countBadge = document.getElementById('selectedProductsCountBadge');
            const searchInput = document.getElementById('modalProductSearch');
            const searchClearBtn = document.getElementById('modalProductSearchClear');
            const selectAllBtn = document.getElementById('btnSelectAllProducts');
            const deselectAllBtn = document.getElementById('btnDeselectAllProducts');
            const closeDropdownBtn = document.getElementById('btnCloseProductDropdown');
            const filterCountText = document.getElementById('productFilterCountText');
            const noMatchNotice = document.getElementById('productNoMatch');
            const productOptions = document.querySelectorAll('.product-item-option');
            const productCheckboxes = document.querySelectorAll('.product-check-item');
            const addDiscountModal = document.getElementById('addDiscountModal');

            // --- Dropdown Open & Close Logic ---
            function openProductDropdown() {
                if (!dropdownMenu) return;
                dropdownMenu.style.display = 'block';
                if (dropdownTrigger) {
                    dropdownTrigger.style.borderColor = '#4f46e5';
                    dropdownTrigger.style.boxShadow = '0 0 0 0.2rem rgba(79, 70, 229, 0.15)';
                    dropdownTrigger.setAttribute('aria-expanded', 'true');
                }
                if (dropdownCaret) {
                    dropdownCaret.style.transform = 'rotate(180deg)';
                }
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 60);
                }
            }

            function closeProductDropdown() {
                if (!dropdownMenu) return;
                dropdownMenu.style.display = 'none';
                if (dropdownTrigger) {
                    dropdownTrigger.style.borderColor = '#ebedf2';
                    dropdownTrigger.style.boxShadow = 'none';
                    dropdownTrigger.setAttribute('aria-expanded', 'false');
                }
                if (dropdownCaret) {
                    dropdownCaret.style.transform = 'rotate(0deg)';
                }
            }

            function isProductDropdownOpen() {
                return dropdownMenu && dropdownMenu.style.display === 'block';
            }

            if (dropdownTrigger) {
                dropdownTrigger.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (isProductDropdownOpen()) {
                        closeProductDropdown();
                    } else {
                        openProductDropdown();
                    }
                });

                dropdownTrigger.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                        e.preventDefault();
                        openProductDropdown();
                    } else if (e.key === 'Escape') {
                        closeProductDropdown();
                    }
                });
            }

            if (closeDropdownBtn) {
                closeDropdownBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeProductDropdown();
                    if (dropdownTrigger) dropdownTrigger.focus();
                });
            }

            // Close on click outside container
            document.addEventListener('click', function(e) {
                if (dropdownContainer && !dropdownContainer.contains(e.target)) {
                    closeProductDropdown();
                }
            });

            // Prevent clicks inside dropdown menu from closing it
            if (dropdownMenu) {
                dropdownMenu.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }

            // Hover styling for options
            productOptions.forEach(opt => {
                opt.addEventListener('mouseenter', () => opt.style.backgroundColor = '#f8fafc');
                opt.addEventListener('mouseleave', () => opt.style.backgroundColor = '');
            });

            // --- Update Summary Text & Badge ---
            updateProductDropdownSummary = function() {
                const checkedItems = Array.from(document.querySelectorAll('.product-check-item:checked'));
                const count = checkedItems.length;

                if (!triggerText || !countBadge) return;

                if (count === 0) {
                    triggerText.textContent = 'Pilih satu atau beberapa produk...';
                    triggerText.className = 'text-muted text-truncate';
                    countBadge.textContent = '0 Produk Terpilih';
                    countBadge.className = 'badge bg-light text-muted border';
                } else if (count === 1) {
                    const optionEl = checkedItems[0].closest('.product-item-option');
                    const name = optionEl ? optionEl.querySelector('.product-item-name').textContent.trim() : '1 Produk';
                    triggerText.textContent = name;
                    triggerText.className = 'text-dark font-weight-500 text-truncate';
                    countBadge.textContent = '1 Produk Terpilih';
                    countBadge.className = 'badge bg-light text-primary border';
                } else if (count === 2) {
                    const names = checkedItems.map(cb => {
                        const opt = cb.closest('.product-item-option');
                        return opt ? opt.querySelector('.product-item-name').textContent.trim() : '';
                    }).join(', ');
                    triggerText.textContent = names;
                    triggerText.className = 'text-dark font-weight-500 text-truncate';
                    countBadge.textContent = `${count} Produk Terpilih`;
                    countBadge.className = 'badge bg-light text-primary border';
                } else {
                    const firstTwo = checkedItems.slice(0, 2).map(cb => {
                        const opt = cb.closest('.product-item-option');
                        return opt ? opt.querySelector('.product-item-name').textContent.trim() : '';
                    }).join(', ');
                    triggerText.textContent = `${firstTwo}... (+${count - 2} lainnya)`;
                    triggerText.className = 'text-dark font-weight-500 text-truncate';
                    countBadge.textContent = `${count} Produk Terpilih`;
                    countBadge.className = 'badge bg-light text-primary border';
                }
            };

            productCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateProductDropdownSummary);
            });

            // Initial calculation
            updateProductDropdownSummary();

            // --- Live Search & Filter Logic ---
            function applyProductSearch(term) {
                const q = term.toLowerCase().trim();
                let visibleCount = 0;

                productOptions.forEach(item => {
                    const name = (item.dataset.name || item.textContent).toLowerCase();
                    if (q === '' || name.includes(q)) {
                        item.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                if (searchClearBtn) {
                    if (q.length > 0) {
                        searchClearBtn.classList.remove('d-none');
                    } else {
                        searchClearBtn.classList.add('d-none');
                    }
                }

                if (filterCountText) {
                    if (q === '') {
                        filterCountText.textContent = `${productOptions.length} produk tersedia`;
                    } else {
                        filterCountText.textContent = `${visibleCount} dari ${productOptions.length} produk`;
                    }
                }

                if (noMatchNotice) {
                    if (visibleCount === 0 && productOptions.length > 0) {
                        noMatchNotice.classList.remove('d-none');
                    } else {
                        noMatchNotice.classList.add('d-none');
                    }
                }
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    applyProductSearch(this.value);
                });
            }

            if (searchClearBtn) {
                searchClearBtn.addEventListener('click', function() {
                    if (searchInput) {
                        searchInput.value = '';
                        searchInput.focus();
                        applyProductSearch('');
                    }
                });
            }

            // --- Select All & Deselect All ---
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    productOptions.forEach(opt => {
                        if (!opt.classList.contains('d-none') && opt.style.display !== 'none') {
                            const cb = opt.querySelector('.product-check-item');
                            if (cb) cb.checked = true;
                        }
                    });
                    updateProductDropdownSummary();
                });
            }

            if (deselectAllBtn) {
                deselectAllBtn.addEventListener('click', function() {
                    productCheckboxes.forEach(cb => cb.checked = false);
                    updateProductDropdownSummary();
                });
            }

            // --- Form Submit Validation ---
            const discountForm = addDiscountModal ? addDiscountModal.querySelector('form') : null;
            if (discountForm) {
                discountForm.addEventListener('submit', function(e) {
                    const checkedCount = document.querySelectorAll('.product-check-item:checked').length;
                    if (checkedCount === 0) {
                        e.preventDefault();
                        openProductDropdown();
                        if (dropdownTrigger) {
                            dropdownTrigger.style.borderColor = '#ef4444';
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Produk Belum Dipilih',
                                text: 'Silakan pilih minimal 1 produk untuk menerapkan diskon promo ini.',
                                confirmButtonColor: '#4f46e5'
                            });
                        } else {
                            alert('Silakan pilih minimal 1 produk untuk menerapkan diskon promo ini.');
                        }
                    }
                });
            }

            // Reset state when modal is closed
            if (addDiscountModal) {
                addDiscountModal.addEventListener('hidden.bs.modal', function() {
                    closeProductDropdown();
                    if (searchInput) {
                        searchInput.value = '';
                        applyProductSearch('');
                    }
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
                    const selectedCheckboxes = document.querySelectorAll('.discount-row-checkbox:checked');
                    const count = selectedCheckboxes.length;
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

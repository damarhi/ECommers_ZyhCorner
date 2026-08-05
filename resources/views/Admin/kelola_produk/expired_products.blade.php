@extends('Layout.layout_pAdmin')

@section('title', 'Produk Kadaluarsa - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h3 class="page-title d-inline-flex align-items-center m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-calendar-remove"></i>
                </span>
                <span class="text-nowrap">Produk Kadaluarsa</span>
            </h3>
            <span class="badge bg-soft-primary text-primary px-3 py-2 font-weight-500" style="font-size: 13px; border-radius: 20px;">
                <i class="mdi mdi-sync me-1"></i> Terdeteksi Otomatis dari Produk Showcase
            </span>
        </div>

        <!-- Summary KPI Cards -->
        <div class="row mt-4">
            <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-coral border-0 text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="mdi mdi-calendar-alert"></i>
                        </div>
                        <div>
                            <p class="stat-label mb-0">Total Kadaluarsa</p>
                            <h3 class="stat-value">{{ $totalExpired }}</h3>
                            <p class="stat-change mb-0">Produk melewati tanggal expired</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-indigo border-0 text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="mdi mdi-alert-circle-outline"></i>
                        </div>
                        <div>
                            <p class="stat-label mb-0">Perlu Tindakan</p>
                            <h3 class="stat-value">{{ $perluTindakan }}</h3>
                            <p class="stat-change mb-0">Belum diproses / dimusnahkan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4 stretch-card grid-margin">
                <div class="card stat-card stat-card-teal border-0 text-white">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="stat-icon">
                            <i class="mdi mdi-check-circle-outline"></i>
                        </div>
                        <div>
                            <p class="stat-label mb-0">Sudah Ditindak</p>
                            <h3 class="stat-value">{{ $sudahDitindak }}</h3>
                            <p class="stat-change mb-0">Dimusnahkan, diretur, atau diarsipkan</p>
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
                        <form method="GET" action="{{ route('products.expired.index') }}"
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100"
                                        type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" style="height: 36px; padding: 0 15px; min-width: 150px;">
                                        <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i>
                                            Status Tindakan</span>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow-sm" aria-labelledby="filterDropdown"
                                        style="width: 230px; border-radius: 8px; margin-top: 5px;">
                                        <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Tindakan</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="" class="form-check-input" {{ request('status') === null || request('status') === '' ? 'checked' : '' }}>
                                                Semua Status
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="Perlu Tindakan" class="form-check-input" {{ request('status') === 'Perlu Tindakan' ? 'checked' : '' }}>
                                                Perlu Tindakan
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="Dimusnahkan" class="form-check-input" {{ request('status') === 'Dimusnahkan' ? 'checked' : '' }}>
                                                Dimusnahkan
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="Diretur" class="form-check-input" {{ request('status') === 'Diretur' ? 'checked' : '' }}>
                                                Diretur
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="radio" name="status" value="Diarsipkan" class="form-check-input" {{ request('status') === 'Diarsipkan' ? 'checked' : '' }}>
                                                Diarsipkan
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                                    </div>
                                </div>
                                <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama produk..." 
                                        style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                                </div>
                                <button type="button" id="btnBulkDelete" class="btn btn-gradient-danger btn-sm d-none align-items-center justify-content-center ms-auto" style="height: 36px;">
                                    <i class="mdi mdi-delete me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                                </button>
                            </div>
                        </form>

                        <!-- Table Bulk Delete Form -->
                        <form action="{{ route('products.expired.bulk-delete') }}" method="POST" id="bulkDeleteForm">
                            @csrf
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th width="40">
                                                <div class="form-check m-0">
                                                    <label class="form-check-label" style="cursor: pointer;">
                                                        <input type="checkbox" id="checkAllExpired" class="form-check-input" style="cursor: pointer;">
                                                    </label>
                                                </div>
                                            </th>
                                            <th>Produk</th>
                                            <th>Tanggal Kadaluarsa</th>
                                            <th>Stok Terakhir</th>
                                            <th>Status Tindakan</th>
                                            <th>Catatan</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($expiredItems as $item)
                                            <tr>
                                                <td>
                                                    <div class="form-check m-0">
                                                        <label class="form-check-label" style="cursor: pointer;">
                                                            <input type="checkbox" name="ids[]" value="{{ $item->id_expired }}" class="form-check-input expired-row-checkbox" style="cursor: pointer;">
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($item->produk)
                                                        <div class="d-flex align-items-center">
                                                            @if ($item->produk->foto_1)
                                                                <img src="{{ asset('storage/' . $item->produk->foto_1) }}"
                                                                    alt="{{ $item->produk->nama_produk }}" class="me-2"
                                                                    style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px;">
                                                            @else
                                                                <div class="me-2 d-flex align-items-center justify-content-center bg-secondary text-white"
                                                                    style="width: 42px; height: 42px; border-radius: 8px;">
                                                                    <i class="mdi mdi-image text-white"></i>
                                                                </div>
                                                            @endif
                                                            <div>
                                                                <span class="font-weight-bold d-block text-truncate" style="max-width: 180px; font-size: 14px;">{{ $item->produk->nama_produk }}</span>
                                                                <small class="text-muted" style="font-size: 11px;">{{ $item->produk->kategori ? $item->produk->kategori->nama_kategori : 'Tanpa Kategori' }}</small>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="text-muted italic">Produk dihapus</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-gradient-danger px-3 py-2" style="font-size: 12px; border-radius: 20px;">
                                                        <i class="mdi mdi-calendar-remove me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_expired)->format('d M Y') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="font-weight-bold text-dark" style="font-size: 13px;">{{ $item->jumlah_stok ?? '-' }}</span>
                                                </td>
                                                <td>
                                                    @if($item->status_tindakan === 'Perlu Tindakan')
                                                        <label class="badge badge-soft-danger">Perlu Tindakan</label>
                                                    @elseif($item->status_tindakan === 'Dimusnahkan')
                                                        <label class="badge badge-soft-secondary">Dimusnahkan</label>
                                                    @elseif($item->status_tindakan === 'Diretur')
                                                        <label class="badge badge-soft-warning">Diretur</label>
                                                    @else
                                                        <label class="badge badge-soft-info">Diarsipkan</label>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted" style="font-size: 12px;">{{ $item->catatan ?? '-' }}</small>
                                                </td>
                                                <td class="text-end">
                                                    <!-- Edit Status Modal Trigger -->
                                                    <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1"
                                                        style="width: 32px; height: 32px; padding: 0;" title="Edit Tindakan"
                                                        data-bs-toggle="modal" data-bs-target="#editExpiredModal{{ $item->id_expired }}">
                                                        <i class="mdi mdi-pencil" style="margin: 0; font-size: 16px;"></i>
                                                    </button>

                                                    <!-- Delete Form -->
                                                    <form action="{{ route('products.expired.destroy', $item->id_expired) }}" method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center"
                                                            style="width: 32px; height: 32px; padding: 0;" title="Hapus Data">
                                                            <i class="mdi mdi-delete" style="margin: 0; font-size: 16px;"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                            <!-- Modal Edit Status Tindakan #{{ $item->id_expired }} -->
                                            <div class="modal fade" id="editExpiredModal{{ $item->id_expired }}" tabindex="-1" aria-labelledby="editExpiredModalLabel{{ $item->id_expired }}" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content" style="border-radius: 12px;">
                                                        <div class="modal-header border-0 pb-0">
                                                            <h5 class="modal-title font-weight-bold" id="editExpiredModalLabel{{ $item->id_expired }}">Edit Tindakan Produk Kadaluarsa</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('products.expired.update', $item->id_expired) }}" method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="form-group mb-3">
                                                                    <label class="font-weight-500 mb-1">Nama Produk</label>
                                                                    <input type="text" class="form-control" value="{{ $item->produk->nama_produk ?? 'Produk Dihapus' }}" disabled>
                                                                </div>

                                                                <div class="form-group mb-3">
                                                                    <label class="font-weight-500 mb-1">Status Tindakan</label>
                                                                    <select name="status_tindakan" class="form-select" required>
                                                                        <option value="Perlu Tindakan" {{ $item->status_tindakan === 'Perlu Tindakan' ? 'selected' : '' }}>Perlu Tindakan</option>
                                                                        <option value="Dimusnahkan" {{ $item->status_tindakan === 'Dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
                                                                        <option value="Diretur" {{ $item->status_tindakan === 'Diretur' ? 'selected' : '' }}>Diretur</option>
                                                                        <option value="Diarsipkan" {{ $item->status_tindakan === 'Diarsipkan' ? 'selected' : '' }}>Diarsipkan</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group mb-0">
                                                                    <label class="font-weight-500 mb-1">Catatan / Keterangan Tindakan</label>
                                                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Masukkan catatan tindakan...">{{ $item->catatan }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-0 pt-0">
                                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-gradient-primary">Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5">
                                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                                        <i class="mdi mdi-check-circle-outline text-success mb-2" style="font-size: 64px;"></i>
                                                        <h5 class="text-muted font-weight-bold">Tidak Ada Produk Kadaluarsa</h5>
                                                        <p class="text-muted mb-0" style="font-size: 14px;">Semua produk showcase terawat dengan baik dan aman!</p>
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
                                {{ $expiredItems->links('pagination::bootstrap-4') }}
                            </div>
                            <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                                {{ $expiredItems->total() }} Hasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check All & Bulk Delete Logic
            const checkAllHeader = document.getElementById('checkAllExpired');
            const rowCheckboxes = document.querySelectorAll('.expired-row-checkbox');
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');

            function updateBulkDeleteState() {
                const checkedRows = document.querySelectorAll('.expired-row-checkbox:checked');
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
                    const count = document.querySelectorAll('.expired-row-checkbox:checked').length;
                    if (count === 0) return;

                    Swal.fire({
                        title: 'Hapus Data Kadaluarsa Terpilih?',
                        text: `Apakah Anda yakin ingin menghapus ${count} data produk kadaluarsa yang dipilih?`,
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

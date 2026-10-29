@extends('Layout.layout_pAdmin')

@section('title', 'Produk Kustom - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-pencil-box-outline"></i>
                </span> Produk Kustom
            </h3>
            <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
                <a href="{{ route('products.create', ['jenis' => 'kustom']) }}" class="btn btn-gradient-primary btn-sm px-4">
                    <i class="mdi mdi-plus me-1"></i> Tambah Produk Kustom
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                    <div class="card-body p-4">
                        <form method="GET" action="{{ route('products.custom') }}"
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100"
                                        type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                                        <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i>
                                            Filter</span>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown"
                                        style="width: 220px; border-radius: 8px; margin-top: 5px;">
                                        <h6 class="dropdown-header px-0 mb-2 text-primary"
                                            style="font-size: 13px; font-weight: 600;">Status Stok</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="status[]" value="Tersedia"
                                                    class="form-check-input"
                                                    {{ is_array(request('status')) && in_array('Tersedia', request('status')) ? 'checked' : '' }}>
                                                Tersedia
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="status[]" value="Habis"
                                                    class="form-check-input"
                                                    {{ is_array(request('status')) && in_array('Habis', request('status')) ? 'checked' : '' }}>
                                                Habis
                                            </label>
                                        </div>

                                        <div class="dropdown-divider mb-3"></div>

                                        <h6 class="dropdown-header px-0 mb-2 text-primary"
                                            style="font-size: 13px; font-weight: 600;">Fitur Tambahan</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="diskon" value="1"
                                                    class="form-check-input" {{ request('diskon') ? 'checked' : '' }}>
                                                Sedang Diskon
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="terbaru" value="1"
                                                    class="form-check-input" {{ request('terbaru') ? 'checked' : '' }}>
                                                Produk Terbaru
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan
                                            Filter</button>
                                    </div>
                                </div>
                                <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari produk kustom..."
                                        style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                                </div>
                            </div>
                            <button type="button" id="btnBulkDelete"
                                class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center align-self-end align-self-md-center px-3"
                                style="height: 38px;" title="Hapus Terpilih">
                                <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                            </button>
                        </form>

                        <form action="{{ route('products.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                            @csrf
                            <input type="hidden" name="redirect_to" value="custom">
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
                                        <th>Jenis Produk</th>
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
                                                        <small
                                                            class="text-muted">{{ $p->kategori ? $p->kategori->nama_kategori : 'Tanpa Kategori' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <label class="badge badge-soft-info">{{ ucfirst($p->jenis_produk ?? 'kustom') }}</label>
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
                                            <td class="font-weight-600 text-dark">
                                                @if ($p->harga_max && $p->harga_max > $p->harga)
                                                    Rp. {{ number_format($p->harga, 0, ',', '.') }} - Rp. {{ number_format($p->harga_max, 0, ',', '.') }}
                                                @else
                                                    Rp. {{ number_format($p->harga, 0, ',', '.') }}
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
                                                    <i class="mdi mdi-pencil-box-outline text-muted mb-2"
                                                        style="font-size: 64px;"></i>
                                                    <h5 class="text-muted font-weight-bold">Belum Ada Produk Kustom</h5>
                                                    <p class="text-muted mb-0" style="font-size: 14px;">Tambahkan produk
                                                        kustom pertama Anda sekarang!</p>
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

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkAll = document.getElementById('checkAllProducts');
            const rowCheckboxes = document.querySelectorAll('.product-row-checkbox');
            const btnBulkDelete = document.getElementById('btnBulkDelete');
            const selectedCountSpan = document.getElementById('selectedCount');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');

            function updateBulkDeleteState() {
                const checkedCount = document.querySelectorAll('.product-row-checkbox:checked').length;
                if (selectedCountSpan) selectedCountSpan.textContent = checkedCount;

                if (btnBulkDelete) {
                    if (checkedCount > 0) {
                        btnBulkDelete.classList.remove('d-none');
                        btnBulkDelete.classList.add('d-inline-flex');
                    } else {
                        btnBulkDelete.classList.add('d-none');
                        btnBulkDelete.classList.remove('d-inline-flex');
                    }
                }

                if (checkAll) {
                    checkAll.checked = rowCheckboxes.length > 0 && checkedCount === rowCheckboxes.length;
                }
            }

            if (checkAll) {
                checkAll.addEventListener('change', function() {
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
                    const selectedCheckboxes = document.querySelectorAll('.product-row-checkbox:checked');
                    const count = selectedCheckboxes.length;
                    if (count === 0) return;

                    Swal.fire({
                        title: 'Hapus Produk Kustom Terpilih?',
                        text: `Apakah Anda yakin ingin menghapus ${count} produk kustom yang dipilih?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#9CA3AF',
                        confirmButtonText: 'Ya, Hapus Terpilih!',
                        cancelButtonText: 'Batal'
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

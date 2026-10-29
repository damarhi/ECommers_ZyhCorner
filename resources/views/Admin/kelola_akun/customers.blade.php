@extends('Layout.layout_pAdmin')

@section('title', 'Pelanggan - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-account-group-outline"></i>
            </span> Akun Pelanggan
        </h3>
        <span class="badge bg-soft-primary text-primary px-3 py-2 font-weight-500 d-inline-flex align-items-center" style="font-size: 13px; border-radius: 20px;">
            <i class="mdi mdi-sync me-1.5" style="font-size: 15px;"></i> Terdaftar Otomatis dari Pembeli Website
        </span>
    </div>



    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    
                    <!-- Tabs -->
                    <ul class="nav nav-tabs border-bottom-0 mb-4" style="gap: 20px;">
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == '' || request('filter') == 'All' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('customers.index', ['filter' => 'All', 'search' => request('search')]) }}">Semua Pelanggan</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Active' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('customers.index', ['filter' => 'Active', 'search' => request('search')]) }}">Aktif</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Blocked' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('customers.index', ['filter' => 'Blocked', 'search' => request('search')]) }}">Diblokir</a>
                        </li>
                    </ul>

                    <!-- Toolbar -->
                    <form method="GET" action="{{ route('customers.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                                    <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i> Filter</span>
                                </button>
                                <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 220px; border-radius: 8px; margin-top: 5px;">
                                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Pelanggan</h6>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="Active" class="form-check-input" {{ is_array(request('status')) && in_array('Active', request('status')) ? 'checked' : '' }}> Aktif
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="Blocked" class="form-check-input" {{ is_array(request('status')) && in_array('Blocked', request('status')) ? 'checked' : '' }}> Diblokir
                                        </label>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan Filter</button>
                                </div>
                            </div>
                            <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama / email / kontak..."
                                    style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                            </div>
                        </div>
                        <button type="button" id="btnBulkDelete" class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center align-self-end align-self-md-center px-3" style="height: 38px;" title="Hapus Terpilih">
                            <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                        </button>
                    </form>

                    <form action="{{ route('customers.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                        @csrf
                        <div id="bulkDeleteInputs"></div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                     <th style="width: 40px;">
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" id="checkAllCustomers" class="form-check-input" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                     </th>
                                    <th>Nama</th>
                                    <th>Lokasi</th>
                                    <th>Pesanan</th>
                                    <th>Pengeluaran</th>
                                    <th>Kontak</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                <tr data-search="{{ strtolower($customer->name . ' ' . $customer->location . ' ' . $customer->contact . ' ' . $customer->status) }}">
                                     <td>
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="ids[]" value="{{ $customer->id }}" class="form-check-input customer-row-checkbox" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                     </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder me-2" style="width: 34px; height: 34px; font-size: 14px;">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                            <span class="font-weight-600 text-dark">{{ $customer->name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $customer->location }}</td>
                                    <td class="font-weight-500 text-dark">{{ $customer->orders_count }}</td>
                                    <td class="font-weight-600 text-dark">Rp. {{ number_format($customer->spent, 0, ',', '.') }}</td>
                                    <td class="text-muted">{{ $customer->contact }}</td>
                                    <td>
                                        @if(in_array(strtolower($customer->status ?? ''), ['active', 'aktif']))
                                            <span class="badge badge-soft-success">Active</span>
                                        @elseif(in_array(strtolower($customer->status ?? ''), ['blocked', 'diblokir', 'nonaktif', 'inactive']))
                                            <span class="badge badge-soft-danger">Blocked</span>
                                        @else
                                            <span class="badge badge-soft-dark">{{ $customer->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $customer->id }}" style="width: 32px; height: 32px; padding: 0;" title="Edit Pelanggan">
                                                <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0;" title="Hapus Pelanggan">
                                                    <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Customer Modal -->
                                <div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1" aria-labelledby="editCustomerModalLabel{{ $customer->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; margin: 1.75rem auto; width: 95%;">
                                        <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                                            <!-- Header Modal -->
                                            <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="d-inline-flex align-items-center justify-content-center text-info rounded-circle" style="width: 34px; height: 34px; background-color: #e0f2fe; color: #0284c7 !important;">
                                                        <i class="mdi mdi-account-edit-outline" style="font-size: 18px;"></i>
                                                    </span>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editCustomerModalLabel{{ $customer->id }}" style="font-size: 15px;">
                                                                Edit Data Pelanggan
                                                            </h6>
                                                            <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">#{{ $customer->id }}</span>
                                                        </div>
                                                        <small class="text-muted" style="font-size: 11px;">Perbarui profil, kontak, atau status akun pelanggan.</small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                                            </div>
                                            <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="edit-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                                                    <!-- Nama Pelanggan -->
                                                    <div class="form-group mb-2.5">
                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Lengkap <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-account-outline"></i></span>
                                                            <input type="text" class="form-control border-start-0" name="name" value="{{ $customer->name }}" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                                        </div>
                                                    </div>
                                                    <!-- Kontak & Lokasi in Grid 2-Kolom -->
                                                    <div class="row g-2 mb-2.5">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Kontak / No. HP</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-phone-outline"></i></span>
                                                                    <input type="text" class="form-control border-start-0" name="contact" value="{{ $customer->contact }}" placeholder="08xxxxxxxxxx" style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Kota / Lokasi</label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-map-marker-outline"></i></span>
                                                                    <input type="text" class="form-control border-start-0" name="location" value="{{ $customer->location }}" placeholder="Contoh: Medan" style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Jumlah Pesanan & Total Pengeluaran -->
                                                    <div class="row g-2 mb-2.5">
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Jumlah Pesanan</label>
                                                                <input type="number" class="form-control" name="orders_count" value="{{ $customer->orders_count }}" min="0" style="font-size: 12px; border-radius: 6px;">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group mb-0">
                                                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Pengeluaran (Rp)</label>
                                                                <input type="number" class="form-control" name="spent" value="{{ $customer->spent }}" min="0" style="font-size: 12px; border-radius: 6px;">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Status -->
                                                    <div class="form-group mb-0">
                                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Status Akun <span class="text-danger">*</span></label>
                                                        <select class="form-control form-select" name="status" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                                            <option value="Active" {{ $customer->status == 'Active' ? 'selected' : '' }}>Aktif</option>
                                                            <option value="Blocked" {{ $customer->status == 'Blocked' ? 'selected' : '' }}>Diblokir</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                                                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                                                    <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                                                        <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Perbarui Pelanggan
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
                                            <i class="mdi mdi-account-multiple-outline text-muted mb-2" style="font-size: 64px;"></i>
                                            <h5 class="text-muted font-weight-bold">Belum Ada Pelanggan Terdaftar</h5>
                                            <p class="text-muted mb-0" style="font-size: 14px;">Pelanggan yang mendaftar atau berbelanja di website akan otomatis muncul di sini.</p>
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
                            {{ $customers->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                        <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                            {{ $customers->total() }} Hasil
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>




<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkAll = document.getElementById('checkAllCustomers');
        const rowCheckboxes = document.querySelectorAll('.customer-row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.customer-row-checkbox:checked').length;
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
                const selectedCheckboxes = document.querySelectorAll('.customer-row-checkbox:checked');
                const count = selectedCheckboxes.length;
                if (count === 0) return;

                Swal.fire({
                    title: 'Hapus Pelanggan Terpilih?',
                    text: `Apakah Anda yakin ingin menghapus ${count} data pelanggan yang dipilih?`,
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

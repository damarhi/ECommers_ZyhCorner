@extends('Layout.layout_pAdmin')

@section('title', 'Pelanggan - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="men"></i>
            </span>Pelanggan
        </h3>
        <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
            <button type="button" class="btn btn-outline-primary btn-sm px-4 bg-white" style="border-radius: 4px;">
                Ekspor
            </button>
            <button type="button" class="btn btn-gradient-primary btn-sm px-4 ms-2" data-bs-toggle="modal" data-bs-target="#addCustomerModal" style="border-radius: 4px;">
                <i class="mdi mdi-plus me-1"></i> Tambah Pelanggan
            </button>
        </div>
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
                            <div class="input-group w-100 w-sm-auto" style="max-width: 250px; height: 32px;">
                                <div class="input-group-prepend" style="height: 32px;">
                                    <span class="input-group-text bg-transparent border-right-0" style="padding: 0 10px; height: 32px;"><i class="mdi mdi-magnify text-muted"></i></span>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control border-left-0" placeholder="Cari..." style="height: 32px; padding-left: 0;">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center align-self-end align-self-md-center" style="width: 32px; height: 32px; padding: 0;" title="Hapus Terpilih">
                            <i class="mdi mdi-delete-outline" style="margin: 0; font-size: 16px;"></i>
                        </button>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table" style="border-bottom: 1px solid #e4e4e4;">
                            <thead>
                                <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
                                    <th style="padding: 15px 10px; width: 40px;">
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                            <i class="input-helper"></i></label>
                                        </div>
                                    </th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Nama</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Lokasi</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pesanan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pengeluaran</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Kontak</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Status</th>
                                    <th style="padding: 15px 10px; width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $customer)
                                <tr>
                                    <td style="padding: 15px 10px;">
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input">
                                            <i class="input-helper"></i></label>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 10px;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder" style="width: 40px; height: 40px; font-size: 16px; margin-right: 12px;">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </div>
                                            <span style="font-size: 14px; font-weight: 600; color: #2c2c2c;">{{ $customer->name }}</span>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $customer->location }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $customer->orders_count }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">Rp. {{ number_format($customer->spent, 0, ',', '.') }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $customer->contact }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $customer->status }}</td>
                                    <td style="padding: 15px 10px;">
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-sm btn-outline-info bg-white d-inline-flex align-items-center justify-content-center me-2" data-bs-toggle="modal" data-bs-target="#editCustomerModal{{ $customer->id }}" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;">
                                                <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0; color: #2b5cff;"></i>
                                            </button>
                                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-primary bg-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;">
                                                    <i class="mdi mdi-delete-outline" style="font-size: 16px; margin: 0; color: #2b5cff;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Customer Modal -->
                                <div class="modal fade" id="editCustomerModal{{ $customer->id }}" tabindex="-1" aria-labelledby="editCustomerModalLabel{{ $customer->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="border-radius: 8px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title font-weight-bold" id="editCustomerModalLabel{{ $customer->id }}">Edit Pelanggan</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('customers.update', $customer->id) }}" method="POST" class="edit-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Nama</label>
                                                        <input type="text" class="form-control" name="name" value="{{ $customer->name }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Lokasi</label>
                                                        <input type="text" class="form-control" name="location" value="{{ $customer->location }}">
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6 form-group mb-0">
                                                            <label class="font-weight-500 mb-2">Jumlah Pesanan</label>
                                                            <input type="number" class="form-control" name="orders_count" value="{{ $customer->orders_count }}" min="0">
                                                        </div>
                                                        <div class="col-md-6 form-group mb-0">
                                                            <label class="font-weight-500 mb-2">Pengeluaran (Rp)</label>
                                                            <input type="number" class="form-control" name="spent" value="{{ $customer->spent }}" min="0">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Kontak</label>
                                                        <input type="text" class="form-control" name="contact" value="{{ $customer->contact }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-500 mb-2">Status</label>
                                                        <select class="form-control form-select" name="status" required>
                                                            <option value="Active" {{ $customer->status == 'Active' ? 'selected' : '' }}>Aktif</option>
                                                            <option value="Blocked" {{ $customer->status == 'Blocked' ? 'selected' : '' }}>Diblokir</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-gradient-primary">Perbarui Pelanggan</button>
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
                                            <h5 class="text-muted font-weight-bold">Belum Ada Pelanggan</h5>
                                            <p class="text-muted mb-0" style="font-size: 14px;">Tambahkan data pelanggan pertama Anda.</p>
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

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="addCustomerModalLabel">Tambah Pelanggan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Nama</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Lokasi</label>
                        <input type="text" class="form-control" name="location">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-500 mb-2">Jumlah Pesanan</label>
                            <input type="number" class="form-control" name="orders_count" value="0" min="0">
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label class="font-weight-500 mb-2">Pengeluaran (Rp)</label>
                            <input type="number" class="form-control" name="spent" value="0" min="0">
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Kontak</label>
                        <input type="text" class="form-control" name="contact">
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-500 mb-2">Status</label>
                        <select class="form-control form-select" name="status" required>
                            <option value="Active">Aktif</option>
                            <option value="Blocked">Diblokir</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>
</div>



@if($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(old('_method') !== 'PUT')
            var myModal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
            myModal.show();
        @endif
    });
</script>
@endif
@endsection

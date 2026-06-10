@extends('Layout.layout_pAdmin')

@section('title', 'Retur Produk - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title font-weight-bold text-dark m-0 text-center text-md-start" style="font-size: 24px;">
            Retur Produk
        </h3>
        <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
            <button type="button" class="btn btn-gradient-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addReturModal">
                Ekspor
            </button>
            <button type="button" class="btn btn-gradient-primary btn-sm px-4 ms-2" data-bs-toggle="modal" data-bs-target="#addReturModal">
                <i class="mdi mdi-plus me-1"></i> Tambah Retur
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
                            <a class="nav-link {{ request('filter') == '' || request('filter') == 'All' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('returs.index', ['filter' => 'All', 'search' => request('search')]) }}">Semua Retur Produk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'New' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('returs.index', ['filter' => 'New', 'search' => request('search')]) }}">Retur Baru</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Accepted' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('returs.index', ['filter' => 'Accepted', 'search' => request('search')]) }}">Retur Diterima</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Processed' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2" href="{{ route('returs.index', ['filter' => 'Processed', 'search' => request('search')]) }}">Retur Diproses</a>
                        </li>
                    </ul>

                    <!-- Toolbar -->
                    <form method="GET" action="{{ route('returs.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <input type="hidden" name="filter" value="{{ request('filter') }}">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                                    <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i> Filter</span>
                                </button>
                                <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown" style="width: 220px; border-radius: 8px; margin-top: 5px;">
                                    <h6 class="dropdown-header px-0 mb-2 text-primary" style="font-size: 13px; font-weight: 600;">Status Retur</h6>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="New" class="form-check-input" {{ is_array(request('status')) && in_array('New', request('status')) ? 'checked' : '' }}> Baru
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="Processed" class="form-check-input" {{ is_array(request('status')) && in_array('Processed', request('status')) ? 'checked' : '' }}> Diproses
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="Accepted" class="form-check-input" {{ is_array(request('status')) && in_array('Accepted', request('status')) ? 'checked' : '' }}> Diterima
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="rejected" class="form-check-input" {{ is_array(request('status')) && in_array('rejected', request('status')) ? 'checked' : '' }}> Ditolak
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <label class="form-check-label text-muted" style="cursor: pointer;">
                                            <input type="checkbox" name="status[]" value="Done" class="form-check-input" {{ is_array(request('status')) && in_array('Done', request('status')) ? 'checked' : '' }}> Selesai
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
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table" style="border-bottom: 1px solid #e4e4e4;">
                            <thead>
                                <tr class="text-muted" style="border-top: 1px solid #e4e4e4; border-bottom: 1px solid #e4e4e4;">
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Pelanggan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">ID Pesanan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Alasan</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Tanggal</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Produk</th>
                                    <th style="font-size: 13px; font-weight: 500; padding: 15px 10px;">Status</th>
                                    <th style="padding: 15px 10px; width: 100px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returs as $retur)
                                <tr>
                                    <td style="padding: 15px 10px;">
                                        <div class="d-flex align-items-center">
                                            <div class="d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px; border-radius: 50%; background-color: #a8aebc; font-weight: bold; font-size: 18px; margin-right: 12px;">
                                                {{ strtoupper(substr($retur->customer_name, 0, 1)) }}
                                            </div>
                                            <span style="font-size: 14px; font-weight: 600; color: #2c2c2c;">{{ $retur->customer_name }}</span>
                                        </div>
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $retur->order_id }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $retur->reason }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $retur->return_date }}</td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">
                                        <div style="max-width: 150px; white-space: normal;">{{ $retur->product_name }}</div>
                                    </td>
                                    <td style="padding: 15px 10px; font-size: 14px; color: #444;">{{ $retur->status }}</td>
                                    <td style="padding: 15px 10px;">
                                        <div class="d-flex">
                                            <button type="button" class="btn btn-sm btn-outline-info bg-white d-inline-flex align-items-center justify-content-center me-2" data-bs-toggle="modal" data-bs-target="#editReturModal{{ $retur->id }}" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;">
                                                <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <form action="{{ route('returs.destroy', $retur->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-primary bg-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 4px;">
                                                    <i class="mdi mdi-delete-outline" style="font-size: 16px; margin: 0;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Edit Retur Modal -->
                                <div class="modal fade" id="editReturModal{{ $retur->id }}" tabindex="-1" aria-labelledby="editReturModalLabel{{ $retur->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content" style="border-radius: 8px;">
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title font-weight-bold" id="editReturModalLabel{{ $retur->id }}">Edit Retur</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('returs.update', $retur->id) }}" method="POST" class="edit-form">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Nama Pelanggan</label>
                                                        <input type="text" class="form-control" name="customer_name" value="{{ $retur->customer_name }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">ID Pesanan</label>
                                                        <input type="text" class="form-control" name="order_id" value="{{ $retur->order_id }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Alasan</label>
                                                        <input type="text" class="form-control" name="reason" value="{{ $retur->reason }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Nama Produk</label>
                                                        <input type="text" class="form-control" name="product_name" value="{{ $retur->product_name }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="font-weight-500 mb-2">Tanggal Retur</label>
                                                        <input type="date" class="form-control" name="return_date" value="{{ $retur->return_date }}" required>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-500 mb-2">Status</label>
                                                        <select class="form-control form-select" name="status" required>
                                                            <option value="New" {{ $retur->status == 'New' ? 'selected' : '' }}>Baru</option>
                                                            <option value="Processed" {{ $retur->status == 'Processed' ? 'selected' : '' }}>Diproses</option>
                                                            <option value="Accepted" {{ $retur->status == 'Accepted' ? 'selected' : '' }}>Diterima</option>
                                                            <option value="rejected" {{ $retur->status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                                            <option value="Done" {{ $retur->status == 'Done' ? 'selected' : '' }}>Selesai</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-gradient-primary">Perbarui Retur</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="d-flex flex-column align-items-center justify-content-center">
                                            <i class="mdi mdi-keyboard-return text-muted mb-2" style="font-size: 64px;"></i>
                                            <h5 class="text-muted font-weight-bold">Belum Ada Retur</h5>
                                            <p class="text-muted mb-0" style="font-size: 14px;">Belum ada pengembalian produk saat ini.</p>
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
                            {{ $returs->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                        <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                            {{ $returs->total() }} Hasil
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Retur Modal -->
<div class="modal fade" id="addReturModal" tabindex="-1" aria-labelledby="addReturModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="addReturModalLabel">Tambah Retur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('returs.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Nama Pelanggan</label>
                        <input type="text" class="form-control" name="customer_name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">ID Pesanan</label>
                        <input type="text" class="form-control" name="order_id" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Alasan</label>
                        <input type="text" class="form-control" name="reason" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Nama Produk</label>
                        <input type="text" class="form-control" name="product_name" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Tanggal Retur</label>
                        <input type="date" class="form-control" name="return_date" required>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-500 mb-2">Status</label>
                        <select class="form-control form-select" name="status" required>
                            <option value="New">Baru</option>
                            <option value="Processed">Diproses</option>
                            <option value="Accepted">Diterima</option>
                            <option value="rejected">Ditolak</option>
                            <option value="Done">Selesai</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">Simpan Retur</button>
                </div>
            </form>
        </div>
    </div>
</div>



@if($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        @if(old('_method') !== 'PUT')
            var myModal = new bootstrap.Modal(document.getElementById('addReturModal'));
            myModal.show();
        @endif
    });
</script>
@endif
@endsection

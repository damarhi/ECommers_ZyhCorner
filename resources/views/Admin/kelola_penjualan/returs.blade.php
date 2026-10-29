@extends('Layout.layout_pAdmin')

@section('title', 'Retur Produk - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-keyboard-return"></i>
            </span>Retur Produk
        </h3>
        <div class="d-flex justify-content-center justify-content-md-end w-100 w-md-auto">
            <button type="button" class="btn btn-gradient-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#addReturModal">
                <i class="mdi mdi-plus me-1"></i> Tambah Retur
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    
                    <!-- Tabs Navigasi Status Lengkap (Frontend Design Direction) -->
                    <ul class="nav nav-tabs border-bottom-0 mb-4 flex-wrap" style="gap: 16px;">
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == '' || request('filter') == 'All' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2 d-inline-flex align-items-center gap-1.5" href="{{ route('returs.index', ['filter' => 'All', 'search' => request('search')]) }}">
                                <span>Semua Retur</span>
                                <span class="badge rounded-pill bg-light text-dark border" style="font-size: 11px;">{{ $statusCounts['All'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'New' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2 d-inline-flex align-items-center gap-1.5" href="{{ route('returs.index', ['filter' => 'New', 'search' => request('search')]) }}">
                                <span>Baru</span>
                                <span class="badge rounded-pill badge-soft-warning" style="font-size: 11px;">{{ $statusCounts['New'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Processed' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2 d-inline-flex align-items-center gap-1.5" href="{{ route('returs.index', ['filter' => 'Processed', 'search' => request('search')]) }}">
                                <span>Diproses</span>
                                <span class="badge rounded-pill badge-soft-info" style="font-size: 11px;">{{ $statusCounts['Processed'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Accepted' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2 d-inline-flex align-items-center gap-1.5" href="{{ route('returs.index', ['filter' => 'Accepted', 'search' => request('search')]) }}">
                                <span>Diterima</span>
                                <span class="badge rounded-pill badge-soft-success" style="font-size: 11px;">{{ $statusCounts['Accepted'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Rejected' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2 d-inline-flex align-items-center gap-1.5" href="{{ route('returs.index', ['filter' => 'Rejected', 'search' => request('search')]) }}">
                                <span>Ditolak</span>
                                <span class="badge rounded-pill badge-soft-danger" style="font-size: 11px;">{{ $statusCounts['Rejected'] ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('filter') == 'Done' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-0 pb-2 d-inline-flex align-items-center gap-1.5" href="{{ route('returs.index', ['filter' => 'Done', 'search' => request('search')]) }}">
                                <span>Selesai</span>
                                <span class="badge rounded-pill badge-soft-dark" style="font-size: 11px;">{{ $statusCounts['Done'] ?? 0 }}</span>
                            </a>
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
                                            <input type="checkbox" name="status[]" value="Rejected" class="form-check-input" {{ is_array(request('status')) && (in_array('Rejected', request('status')) || in_array('rejected', request('status'))) ? 'checked' : '' }}> Ditolak
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
                            <div class="position-relative w-100 w-sm-auto" style="max-width: 280px;">
                                <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                                    class="form-control form-control-sm ps-5 pe-3" placeholder="Cari retur produk..."
                                    style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2; background: #ffffff; font-size: 13px;">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; pointer-events: none;"></i>
                            </div>
                            <button type="button" id="btnBulkDelete" class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center px-3 ms-auto" style="height: 38px;" title="Hapus Terpilih">
                                <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('returs.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
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
                                                <input type="checkbox" id="checkAllReturs" class="form-check-input" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                    </th>
                                    <th>Pelanggan</th>
                                    <th>ID Pesanan</th>
                                    <th>Alasan</th>
                                    <th>Tanggal</th>
                                    <th>Produk</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($returs as $retur)
                                <tr data-search="{{ strtolower($retur->customer_name . ' ' . $retur->order_id . ' ' . $retur->product_name . ' ' . $retur->reason . ' ' . $retur->status . ' ' . $retur->status_label) }}">
                                    <td>
                                        <div class="form-check m-0">
                                            <label class="form-check-label">
                                                <input type="checkbox" name="ids[]" value="{{ $retur->id }}" class="form-check-input retur-row-checkbox" style="cursor: pointer;">
                                            <i class="input-helper"></i></label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-placeholder me-2" style="width: 34px; height: 34px; font-size: 14px;">
                                                {{ strtoupper(substr($retur->customer_name, 0, 1)) }}
                                            </div>
                                            <span class="font-weight-600 text-dark">{{ $retur->customer_name }}</span>
                                        </div>
                                    </td>
                                    <td class="font-weight-500 text-dark">{{ $retur->order_id }}</td>
                                    <td class="text-muted">{{ $retur->reason }}</td>
                                    <td class="text-muted">{{ $retur->return_date }}</td>
                                    <td>
                                        <div style="max-width: 150px; white-space: normal;" class="text-dark">{{ $retur->product_name }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusLower = strtolower($retur->status ?? '');
                                        @endphp
                                        @if(in_array($statusLower, ['accepted', 'diterima', 'disetujui']))
                                            <span class="badge badge-soft-success" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Diterima</span>
                                        @elseif(in_array($statusLower, ['done', 'completed', 'selesai']))
                                            <span class="badge badge-soft-dark" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Selesai</span>
                                        @elseif(in_array($statusLower, ['processed', 'processing', 'diproses']))
                                            <span class="badge badge-soft-info" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Diproses</span>
                                        @elseif(in_array($statusLower, ['rejected', 'ditolak', 'batal']))
                                            <span class="badge badge-soft-danger" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Ditolak</span>
                                        @else
                                            <span class="badge badge-soft-warning" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Baru</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#detailReturModal{{ $retur->id }}" title="Detail Retur" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#editReturModal{{ $retur->id }}" title="Edit Retur" style="width: 32px; height: 32px; padding: 0;">
                                                <i class="mdi mdi-pencil" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <form action="{{ route('returs.destroy', $retur->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center" title="Hapus Retur" style="width: 32px; height: 32px; padding: 0;">
                                                    <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
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

<!-- Modals outside table element -->
@foreach($returs as $retur)
    <!-- Detail Retur Modal (Two-Column: Details Left, Photo Right) -->
    <div class="modal fade" id="detailReturModal{{ $retur->id }}" tabindex="-1" aria-labelledby="detailReturModalLabel{{ $retur->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 680px; margin: 1.75rem auto; width: 95%;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                <!-- Header Modal -->
                <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 34px; height: 34px; background-color: #ede9fe; color: #4f46e5 !important;">
                            <i class="mdi mdi-keyboard-return" style="font-size: 18px;"></i>
                        </span>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="modal-title font-weight-bold mb-0 text-dark" id="detailReturModalLabel{{ $retur->id }}" style="font-size: 15px;">
                                    Detail Retur #{{ $retur->id }}
                                </h6>
                                <span class="badge badge-soft-dark" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">Pesanan #{{ $retur->order_id }}</span>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Rincian pengajuan retur barang dari pelanggan.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                    <div class="row g-3">
                        <!-- Left Column: Detail Fields -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 h-100" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div class="text-uppercase text-muted font-weight-bold mb-2.5 d-flex align-items-center" style="font-size: 10px; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-information-outline me-1 text-primary" style="font-size: 14px;"></i> Data Pengajuan
                                </div>

                                <div class="mb-2 pb-1.5 border-bottom">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Nama Pelanggan</span>
                                    <strong class="text-dark" style="font-size: 12.5px;">{{ $retur->customer_name }}</strong>
                                </div>

                                <div class="mb-2 pb-1.5 border-bottom">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Nama Produk</span>
                                    <span class="text-dark font-weight-500" style="font-size: 12.5px;">{{ $retur->product_name }}</span>
                                </div>

                                <div class="mb-2 pb-1.5 border-bottom">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Tanggal Retur</span>
                                    <span class="text-dark" style="font-size: 12px;">{{ $retur->return_date }}</span>
                                </div>

                                <div class="mb-2 pb-1.5 border-bottom">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Status Retur</span>
                                    <div class="mt-0.5">
                                        @php $dStatus = strtolower($retur->status ?? ''); @endphp
                                        @if(in_array($dStatus, ['accepted', 'diterima', 'disetujui']))
                                            <span class="badge badge-soft-success" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">Diterima</span>
                                        @elseif(in_array($dStatus, ['done', 'completed', 'selesai']))
                                            <span class="badge badge-soft-dark" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">Selesai</span>
                                        @elseif(in_array($dStatus, ['rejected', 'ditolak']))
                                            <span class="badge badge-soft-danger" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">Ditolak</span>
                                        @elseif(in_array($dStatus, ['processed', 'processing', 'diproses']))
                                            <span class="badge badge-soft-info" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">Diproses</span>
                                        @else
                                            <span class="badge badge-soft-warning" style="font-size: 10px; font-weight: 600; padding: 2px 7px;">Baru</span>
                                        @endif
                                    </div>
                                </div>

                                @if($retur->catatan_admin)
                                    <div class="mb-2 pb-1.5 border-bottom">
                                        <span class="text-primary d-block font-weight-500" style="font-size: 10.5px;">
                                            <i class="mdi mdi-comment-account-outline me-1"></i>Catatan / Alasan Admin
                                        </span>
                                        <p class="text-dark mb-0 mt-0.5 fw-medium" style="font-size: 12px; line-height: 1.4;">
                                            {{ $retur->catatan_admin }}
                                        </p>
                                    </div>
                                @endif

                                <div>
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Alasan Pengajuan Pelanggan</span>
                                    <p class="text-dark mb-0 mt-1 fst-italic" style="font-size: 12px; line-height: 1.4;">
                                        "{{ $retur->reason }}"
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Proof Photo -->
                        <div class="col-md-6 d-flex flex-column">
                            <div class="text-uppercase text-muted font-weight-bold mb-2 d-flex align-items-center" style="font-size: 10px; letter-spacing: 0.5px;">
                                <i class="mdi mdi-camera-outline me-1 text-primary" style="font-size: 14px;"></i> Foto Bukti Retur
                            </div>
                            @if($retur->foto_retur)
                                <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center bg-light p-2.5 rounded-3 border" style="min-height: 200px;">
                                    <a href="{{ asset($retur->foto_retur) }}" target="_blank" title="Klik untuk membuka ukuran penuh">
                                        <img src="{{ asset($retur->foto_retur) }}" alt="Foto Bukti Retur" style="max-height: 220px; max-width: 100%; object-fit: contain; border-radius: 6px;">
                                    </a>
                                </div>
                                <a href="{{ asset($retur->foto_retur) }}" target="_blank" class="btn btn-xs btn-outline-primary w-100 mt-2 py-1" style="border-radius: 6px; font-size: 11px;">
                                    <i class="mdi mdi-open-in-new me-1"></i> Buka Foto Ukuran Penuh
                                </a>
                            @else
                                <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-center bg-light text-muted border rounded-3 p-3 text-center" style="min-height: 200px;">
                                    <i class="mdi mdi-image-off-outline" style="font-size: 40px; opacity: 0.35;"></i>
                                    <span class="mt-2" style="font-size: 11.5px;">Pelanggan tidak melampirkan foto bukti.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-between align-items-center flex-wrap gap-2" style="border-radius: 0 0 12px 12px;">
                    <div class="d-flex align-items-center gap-1 flex-wrap">
                        <span class="text-muted" style="font-size: 11px; margin-right: 4px;">Ubah Status:</span>
                        @if(strtolower($retur->status) !== 'accepted')
                            <form action="{{ route('returs.update-status', $retur->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Accepted">
                                <button type="submit" class="btn btn-xs btn-outline-success py-1 px-2" style="font-size: 10.5px; border-radius: 4px;" title="Terima Retur">
                                    <i class="mdi mdi-check"></i> Terima
                                </button>
                            </form>
                        @endif
                        @if(strtolower($retur->status) !== 'processed')
                            <form action="{{ route('returs.update-status', $retur->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Processed">
                                <button type="submit" class="btn btn-xs btn-outline-info py-1 px-2" style="font-size: 10.5px; border-radius: 4px;" title="Proses Retur">
                                    <i class="mdi mdi-progress-clock"></i> Proses
                                </button>
                            </form>
                        @endif
                        @if(strtolower($retur->status) !== 'rejected')
                            <form action="{{ route('returs.update-status', $retur->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Rejected">
                                <button type="submit" class="btn btn-xs btn-outline-danger py-1 px-2" style="font-size: 10.5px; border-radius: 4px;" title="Tolak Retur">
                                    <i class="mdi mdi-close"></i> Tolak
                                </button>
                            </form>
                        @endif
                        @if(strtolower($retur->status) !== 'done')
                            <form action="{{ route('returs.update-status', $retur->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="Done">
                                <button type="submit" class="btn btn-xs btn-outline-dark py-1 px-2" style="font-size: 10.5px; border-radius: 4px;" title="Tandai Selesai">
                                    <i class="mdi mdi-check-all"></i> Selesai
                                </button>
                            </form>
                        @endif
                    </div>
                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Retur Modal -->
    <div class="modal fade" id="editReturModal{{ $retur->id }}" tabindex="-1" aria-labelledby="editReturModalLabel{{ $retur->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 520px; margin: 1.75rem auto; width: 95%;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                <!-- Header Modal -->
                <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 34px; height: 34px; background-color: #ede9fe; color: #7c3aed !important;">
                            <i class="mdi mdi-clipboard-check-outline" style="font-size: 18px;"></i>
                        </span>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editReturModalLabel{{ $retur->id }}" style="font-size: 15px;">
                                    Keputusan Retur Produk
                                </h6>
                                <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">#{{ $retur->id }}</span>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">Tentukan status pengajuan retur dan berikan catatan alasan untuk pelanggan.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <form action="{{ route('returs.update', $retur->id) }}" method="POST" class="edit-form">
                    @csrf
                    @method('PUT')
                    <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                        <!-- Ringkasan Data Pengajuan Pelanggan (Read-Only / Terkunci) -->
                        <div class="bg-light border rounded-3 p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                <span class="text-uppercase text-muted font-weight-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                    <i class="mdi mdi-lock-outline me-1"></i>Data Pengajuan Pelanggan (Terkunci)
                                </span>
                                <span class="badge bg-white text-muted border" style="font-size: 10px;">ID Pesanan: {{ $retur->order_id }}</span>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-sm-6">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Nama Pelanggan</span>
                                    <span class="text-dark font-weight-500" style="font-size: 12px;">{{ $retur->customer_name }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Tanggal Retur</span>
                                    <span class="text-dark font-weight-500" style="font-size: 12px;">{{ $retur->return_date }}</span>
                                </div>
                                <div class="col-12">
                                    <span class="text-muted d-block" style="font-size: 10.5px;">Nama Produk</span>
                                    <span class="text-dark font-weight-500" style="font-size: 12px;">{{ $retur->product_name }}</span>
                                </div>
                            </div>
                            <div>
                                <span class="text-muted d-block" style="font-size: 10.5px;">Alasan Pengajuan Pelanggan:</span>
                                <p class="text-dark mb-0 mt-0.5 fst-italic" style="font-size: 11.5px; line-height: 1.4;">
                                    "{{ $retur->reason }}"
                                </p>
                            </div>
                            @if($retur->foto_retur)
                                <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between">
                                    <span class="text-muted" style="font-size: 11px;">
                                        <i class="mdi mdi-image-check text-success me-1"></i>Foto bukti tersedia
                                    </span>
                                    <a href="{{ asset($retur->foto_retur) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0.5 px-2" style="font-size: 10.5px; border-radius: 4px;">
                                        Lihat Foto Bukti
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Form Status & Catatan Keputusan Admin (Hanya ini yang diubah) -->
                        <div class="form-group mb-2.5">
                            <label class="form-label font-weight-bold mb-1 text-dark" style="font-size: 12px;">
                                Status Retur <span class="text-danger">*</span>
                            </label>
                            @php $eStatus = strtolower($retur->status ?? ''); @endphp
                            <select class="form-control form-select status-select-retur" name="status" id="statusSelect{{ $retur->id }}" required style="font-size: 12.5px; border-radius: 6px; height: 38px;">
                                <option value="New" {{ in_array($eStatus, ['new', 'baru']) ? 'selected' : '' }}>Baru (Menunggu Konfirmasi)</option>
                                <option value="Processed" {{ in_array($eStatus, ['processed', 'processing', 'diproses']) ? 'selected' : '' }}>Diproses (Pengecekan Fisik)</option>
                                <option value="Accepted" {{ in_array($eStatus, ['accepted', 'diterima', 'disetujui']) ? 'selected' : '' }}>Diterima (Setujui Retur)</option>
                                <option value="Rejected" {{ in_array($eStatus, ['rejected', 'ditolak']) ? 'selected' : '' }}>Ditolak (Klaim Ditolak)</option>
                                <option value="Done" {{ in_array($eStatus, ['done', 'completed', 'selesai']) ? 'selected' : '' }}>Selesai (Retur Ditutup)</option>
                            </select>
                        </div>

                        <!-- Catatan / Alasan Diterima atau Ditolak -->
                        <div class="form-group mb-0">
                            <label class="form-label font-weight-bold mb-1 text-dark d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                <span>Alasan Diterima / Ditolak (Catatan Admin)</span>
                                <small class="text-muted fw-normal">Terlihat oleh pelanggan</small>
                            </label>
                            <textarea class="form-control" name="catatan_admin" rows="3" placeholder="Tuliskan alasan persetujuan/penolakan retur, atau instruksi pengembalian barang..." style="font-size: 12px; border-radius: 6px; line-height: 1.4;">{{ $retur->catatan_admin }}</textarea>
                            <small class="text-muted mt-1 d-block" style="font-size: 11px;">
                                <i class="mdi mdi-information-outline me-0.5"></i>Catatan ini akan ditampilkan pada rincian retur di akun pelanggan.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                            <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<!-- Add Retur Modal -->
<div class="modal fade" id="addReturModal" tabindex="-1" aria-labelledby="addReturModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px; margin: 1.75rem auto; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
            <!-- Header Modal -->
            <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-success rounded-circle" style="width: 34px; height: 34px; background-color: #dcfce7; color: #16a34a !important;">
                        <i class="mdi mdi-plus-circle-outline" style="font-size: 18px;"></i>
                    </span>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-dark" id="addReturModalLabel" style="font-size: 15px;">
                            Tambah Retur Baru
                        </h6>
                        <small class="text-muted" style="font-size: 11px;">Catat pengajuan komplain atau retur barang pelanggan.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>
            <form action="{{ route('returs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                    @if(isset($completedOrders) && $completedOrders->count() > 0)
                        <!-- Pilihan Cepat dari Pesanan Selesai dengan Searchable Live-Filter Dropdown -->
                        <div class="form-group mb-2 position-relative" id="quickOrderComboboxContainer">
                            <label class="form-label font-weight-500 mb-1 text-dark d-flex align-items-center justify-content-between" style="font-size: 12px;">
                                <span><i class="mdi mdi-flash text-warning me-1"></i>Pilih Cepat Pesanan Selesai</span>
                                <small class="text-muted fw-normal">(Ketik untuk cari)</small>
                            </label>
                            <div class="position-relative">
                                <input type="text" class="form-control ps-4 pe-4" id="quickOrderSearchInput" placeholder="Ketik #ID pesanan atau nama pembeli..." autocomplete="off" style="font-size: 12px; border-radius: 6px; height: 38px;">
                                <i class="mdi mdi-magnify position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 15px; pointer-events: none;"></i>
                                <button type="button" id="quickOrderClearBtn" class="btn btn-link p-0 position-absolute text-muted d-none" style="right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;" title="Reset pilihan">
                                    <i class="mdi mdi-close-circle" style="font-size: 16px;"></i>
                                </button>
                            </div>
                            <!-- Container List Opsi Dropdown Hasil Filter -->
                            <div id="quickOrderDropdownList" class="dropdown-menu shadow-lg w-100 p-1 border" style="display: none; max-height: 220px; overflow-y: auto; border-radius: 6px; margin-top: 3px; z-index: 1055;">
                                @foreach($completedOrders as $co)
                                    <div class="dropdown-item quick-order-item py-1.5 px-2.5 rounded-1 cursor-pointer" 
                                         data-order="{{ $co->order_number }}" 
                                         data-customer="{{ $co->customer_name }}"
                                         data-date="{{ $co->created_at ? $co->created_at->format('d/m/Y') : '-' }}"
                                         style="cursor: pointer; font-size: 11.5px;">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="font-weight-600 text-dark">{{ $co->order_number }}</span>
                                            <small class="text-muted">{{ $co->created_at ? $co->created_at->format('d/m/Y') : '-' }}</small>
                                        </div>
                                        <div class="text-muted text-truncate" style="font-size: 11px;">
                                            <i class="mdi mdi-account-outline me-0.5"></i>{{ $co->customer_name }}
                                        </div>
                                    </div>
                                @endforeach
                                <div id="quickOrderNoResult" class="p-2 text-center text-muted d-none" style="font-size: 11px;">
                                    Tidak ada pesanan selesai yang cocok.
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Grid 2 Kolom: Pelanggan & ID Pesanan -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Pelanggan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="customer_name" id="addCustomerName" placeholder="Nama pemesan" required style="font-size: 12px; border-radius: 6px;">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">ID Pesanan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="order_id" id="addOrderId" placeholder="ID pesanan" required style="font-size: 12px; border-radius: 6px;">
                            </div>
                        </div>
                    </div>

                    <!-- Grid 2 Kolom: Produk & Tanggal Retur -->
                    <div class="row g-2 mb-2">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="product_name" id="addProductName" list="allProductsDatalist" placeholder="Pilih produk atau ketik nama kue" required style="font-size: 12px; border-radius: 6px;">
                                <datalist id="allProductsDatalist">
                                    @if(isset($allProducts))
                                        @foreach($allProducts as $prod)
                                            <option value="{{ $prod->nama_produk }}">
                                        @endforeach
                                    @endif
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Tanggal Retur <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" name="return_date" value="{{ date('Y-m-d') }}" required style="font-size: 12px; border-radius: 6px;">
                            </div>
                        </div>
                    </div>

                    <!-- Alasan Retur -->
                    <div class="form-group mb-2">
                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Alasan Retur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="reason" placeholder="Contoh: Kue rusak / topping lumer dalam pengiriman" required style="font-size: 12px; border-radius: 6px;">
                    </div>

                    <!-- Status Retur -->
                    <div class="form-group mb-2">
                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Status Retur <span class="text-danger">*</span></label>
                        <select class="form-control form-select" name="status" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                            <option value="New" selected>Baru</option>
                            <option value="Processed">Diproses</option>
                            <option value="Accepted">Diterima</option>
                            <option value="Rejected">Ditolak</option>
                            <option value="Done">Selesai</option>
                        </select>
                    </div>

                    <!-- Foto Bukti -->
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Upload Foto Bukti <small class="text-muted fw-normal">(Opsional)</small></label>
                        <input type="file" class="form-control" name="foto_retur" accept="image/*" style="font-size: 12px; border-radius: 6px;">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                        <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Retur
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const checkAll = document.getElementById('checkAllReturs');
        const rowCheckboxes = document.querySelectorAll('.retur-row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.retur-row-checkbox:checked').length;
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
                const selectedCheckboxes = document.querySelectorAll('.retur-row-checkbox:checked');
                const count = selectedCheckboxes.length;
                if (count === 0) return;

                Swal.fire({
                    title: 'Hapus Retur Terpilih?',
                    text: `Apakah Anda yakin ingin menghapus ${count} data retur yang dipilih?`,
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

        // Searchable Combobox logic for quickOrderSelect
        const searchInput = document.getElementById('quickOrderSearchInput');
        const dropdownList = document.getElementById('quickOrderDropdownList');
        const clearBtn = document.getElementById('quickOrderClearBtn');
        const orderItems = document.querySelectorAll('.quick-order-item');
        const noResult = document.getElementById('quickOrderNoResult');
        const orderIdInput = document.getElementById('addOrderId');
        const customerNameInput = document.getElementById('addCustomerName');

        if (searchInput && dropdownList) {
            searchInput.addEventListener('focus', function() {
                dropdownList.style.display = 'block';
            });

            searchInput.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                dropdownList.style.display = 'block';
                if (clearBtn) {
                    if (q.length > 0) clearBtn.classList.remove('d-none');
                    else clearBtn.classList.add('d-none');
                }

                let visibleCount = 0;
                orderItems.forEach(item => {
                    const orderNum = (item.dataset.order || '').toLowerCase();
                    const custName = (item.dataset.customer || '').toLowerCase();
                    const orderDate = (item.dataset.date || '').toLowerCase();

                    if (orderNum.includes(q) || custName.includes(q) || orderDate.includes(q)) {
                        item.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                if (noResult) {
                    if (visibleCount === 0) noResult.classList.remove('d-none');
                    else noResult.classList.add('d-none');
                }
            });

            orderItems.forEach(item => {
                item.addEventListener('click', function() {
                    const orderNum = this.dataset.order;
                    const custName = this.dataset.customer;
                    const orderDate = this.dataset.date;

                    searchInput.value = `${orderNum} - ${custName} (${orderDate})`;
                    if (orderIdInput) orderIdInput.value = orderNum;
                    if (customerNameInput) customerNameInput.value = custName;

                    dropdownList.style.display = 'none';
                    if (clearBtn) clearBtn.classList.remove('d-none');
                });
            });

            if (clearBtn) {
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    if (orderIdInput) orderIdInput.value = '';
                    if (customerNameInput) customerNameInput.value = '';
                    clearBtn.classList.add('d-none');
                    orderItems.forEach(item => item.classList.remove('d-none'));
                    if (noResult) noResult.classList.add('d-none');
                    searchInput.focus();
                });
            }

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdownList.contains(e.target)) {
                    dropdownList.style.display = 'none';
                }
            });
        }

        @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any() && old('_method') !== 'PUT')
            var myModal = new bootstrap.Modal(document.getElementById('addReturModal'));
            myModal.show();
        @endif
    });
</script>
@endsection

@extends('Layout.layout_pAdmin')

@section('title', 'Pesan Kontak Masuk - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <!-- Page Header -->
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <h3 class="page-title m-0 d-flex align-items-center">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-email-open-multiple-outline"></i>
            </span> Pesan Kontak Pelanggan
        </h3>
        <div>
            <span class="text-muted" style="font-size: 13px;">Kelola dan tanggapi pertanyaan atau pesan yang dikirim pelanggan dari form kontak.</span>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row mb-4">
        <!-- Total Pesan Masuk -->
        <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
            <div class="card stat-card stat-card-indigo border-0 text-white shadow-sm w-100 h-100" style="border-radius: 12px; min-height: 108px;">
                <div class="card-body d-flex align-items-center gap-3 p-3 p-sm-4 h-100">
                    <div class="stat-icon flex-shrink-0">
                        <i class="mdi mdi-email-multiple-outline"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <p class="stat-label mb-0 text-truncate" title="Total Pesan Masuk">Total Pesan Masuk</p>
                        <h3 class="stat-value my-1">{{ number_format($totalMessages, 0, ',', '.') }}</h3>
                        <p class="stat-change mb-0 text-truncate" title="Akumulasi seluruh pesan kontak">Akumulasi seluruh pesan kontak</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Belum Dibaca (Urgensi Perhatian) -->
        <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
            <div class="card stat-card stat-card-coral border-0 text-white shadow-sm w-100 h-100" style="border-radius: 12px; min-height: 108px;">
                <div class="card-body d-flex align-items-center gap-3 p-3 p-sm-4 h-100">
                    <div class="stat-icon flex-shrink-0">
                        <i class="mdi mdi-email-alert-outline"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <p class="stat-label mb-0 text-truncate" title="Belum Dibaca">Belum Dibaca</p>
                        <h3 class="stat-value my-1">{{ number_format($unreadMessages, 0, ',', '.') }}</h3>
                        <p class="stat-change mb-0 text-truncate" title="Memerlukan respon admin">Memerlukan respon admin</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sudah Dibalas -->
        <div class="col-12 col-md-4 stretch-card grid-margin mb-3 mb-md-0">
            <div class="card stat-card stat-card-teal border-0 text-white shadow-sm w-100 h-100" style="border-radius: 12px; min-height: 108px;">
                <div class="card-body d-flex align-items-center gap-3 p-3 p-sm-4 h-100">
                    <div class="stat-icon flex-shrink-0">
                        <i class="mdi mdi-email-check-outline"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <p class="stat-label mb-0 text-truncate" title="Sudah Dibalas">Sudah Dibalas</p>
                        <h3 class="stat-value my-1">{{ number_format($repliedMessages, 0, ',', '.') }}</h3>
                        <p class="stat-change mb-0 text-truncate" title="Pesan yang telah ditindaklanjuti">Pesan yang telah ditindaklanjuti</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
        <div class="card-body p-4">
            
            <!-- Filter Tabs -->
            @php
                $currentStatus = request('status');
            @endphp
            <ul class="nav nav-tabs border-bottom mb-4" style="gap: 15px;">
                <li class="nav-item">
                    <a class="nav-link {{ empty($currentStatus) ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-2 pb-2"
                       href="{{ route('messages.index', array_filter(['search' => request('search')])) }}">
                        Semua Pesan ({{ $totalMessages }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentStatus === 'Belum Dibaca' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-2 pb-2"
                       href="{{ route('messages.index', array_filter(['status' => 'Belum Dibaca', 'search' => request('search')])) }}">
                        Belum Dibaca ({{ $unreadMessages }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentStatus === 'Dibaca' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-2 pb-2"
                       href="{{ route('messages.index', array_filter(['status' => 'Dibaca', 'search' => request('search')])) }}">
                        Sudah Dibaca ({{ $readMessages ?? 0 }})
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentStatus === 'Dibalas' ? 'active text-primary font-weight-bold border-bottom border-primary border-2' : 'text-muted border-0' }} px-2 pb-2"
                       href="{{ route('messages.index', array_filter(['status' => 'Dibalas', 'search' => request('search')])) }}">
                        Sudah Dibalas ({{ $repliedMessages }})
                    </a>
                </li>
            </ul>

            <!-- Toolbar & Search Form -->
            <form method="GET" action="{{ route('messages.index') }}" class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnBulkDelete" class="btn btn-sm btn-gradient-danger d-none align-items-center justify-content-center px-3" style="height: 38px;" onclick="confirmBulkDelete()">
                        <i class="mdi mdi-delete me-1" style="font-size: 16px;"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <div class="position-relative w-100 w-md-auto" style="min-width: 280px;">
                    <input type="text" name="search" value="{{ request('search') }}" autocomplete="off"
                        class="form-control form-control-sm ps-5 pe-3" placeholder="Cari nama, email, subjek, pesan..."
                        style="height: 38px; border-radius: 8px; border: 1px solid #ebedf2;">
                    <i class="mdi mdi-magnify position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 18px; pointer-events: none;"></i>
                </div>
            </form>

            <!-- Bulk Delete Hidden Form -->
            <form id="bulkDeleteForm" action="{{ route('messages.bulk-delete') }}" method="POST" class="d-none">
                @csrf
                <input type="hidden" name="ids" id="bulkDeleteIds">
            </form>

            <!-- Messages Table -->
            <div class="table-responsive" style="min-height: 220px;">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input" style="cursor: pointer;">
                            </th>
                            <th>Pengirim</th>
                            <th>Subjek & Pesan</th>
                            <th class="text-center" style="width: 130px;">Status</th>
                            <th style="width: 150px;">Waktu</th>
                            <th class="text-center" style="width: 110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr class="{{ $msg->status === 'Belum Dibaca' ? 'table-warning bg-opacity-10 fw-bold' : '' }}" id="row-msg-{{ $msg->id }}" data-search="{{ strtolower($msg->nama . ' ' . $msg->email . ' ' . $msg->subjek . ' ' . $msg->pesan . ' ' . $msg->status) }}">
                                <td class="text-center">
                                    <input type="checkbox" class="form-check-input message-checkbox" value="{{ $msg->id }}" style="cursor: pointer;" onchange="updateBulkDeleteState()">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2 bg-light text-primary font-weight-bold d-flex align-items-center justify-content-center"
                                             style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e0e0e0; font-size: 14px;">
                                            {{ strtoupper(substr($msg->nama, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-dark font-weight-bold">{{ $msg->nama }}</div>
                                            <small class="text-muted">{{ $msg->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-dark font-weight-bold mb-1">{{ $msg->subjek }}</div>
                                    <p class="text-muted mb-0" style="font-size: 12.5px; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; max-width: 400px;">
                                        {{ $msg->pesan }}
                                    </p>
                                </td>
                                <td class="text-center">
                                    @if($msg->status === 'Belum Dibaca')
                                        <span class="badge badge-soft-warning">
                                            <i class="mdi mdi-email-alert-outline me-1"></i> Belum Dibaca
                                        </span>
                                    @elseif($msg->status === 'Dibaca')
                                        <span class="badge badge-soft-info">
                                            <i class="mdi mdi-email-open-outline me-1"></i> Dibaca
                                        </span>
                                    @else
                                        <span class="badge badge-soft-success">
                                            <i class="mdi mdi-check-all me-1"></i> Dibalas
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark font-weight-500">{{ $msg->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $msg->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center" style="gap: 6px;">
                                        <!-- Tombol Baca Modal -->
                                        <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                                                onclick="openMessageDetail({{ $msg->id }}, '{{ addslashes($msg->nama) }}', '{{ addslashes($msg->email) }}', '{{ addslashes($msg->subjek) }}', '{{ addslashes(preg_replace('/\r|\n/', ' ', $msg->pesan)) }}', '{{ $msg->status }}', '{{ $msg->created_at->format('d M Y, H:i') }} WIB')"
                                                title="Lihat Detail Pesan">
                                            <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
                                        </button>

                                        <!-- Dropdown Ubah Status -->
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    data-bs-popper-config='{"strategy":"fixed"}'
                                                    data-bs-boundary="viewport"
                                                    aria-expanded="false"
                                                    style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                                                    title="Ubah Status">
                                                <i class="mdi mdi-format-list-bulleted" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 12.5px; border-radius: 8px; min-width: 175px; padding: 6px;">
                                                <li>
                                                    <form action="{{ route('messages.update-status', $msg->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Belum Dibaca">
                                                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-2.5 {{ $msg->status === 'Belum Dibaca' ? 'active status-active-warning' : '' }}">
                                                            <span class="d-flex align-items-center">
                                                                <i class="mdi mdi-email-alert-outline text-warning me-2" style="font-size: 15px;"></i> Belum Dibaca
                                                            </span>
                                                            @if($msg->status === 'Belum Dibaca')
                                                                <i class="mdi mdi-check text-warning ms-2" style="font-size: 16px; font-weight: bold;"></i>
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('messages.update-status', $msg->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Dibaca">
                                                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-2.5 {{ $msg->status === 'Dibaca' ? 'active status-active-info' : '' }}">
                                                            <span class="d-flex align-items-center">
                                                                <i class="mdi mdi-email-open-outline text-info me-2" style="font-size: 15px;"></i> Dibaca
                                                            </span>
                                                            @if($msg->status === 'Dibaca')
                                                                <i class="mdi mdi-check text-info ms-2" style="font-size: 16px; font-weight: bold;"></i>
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('messages.update-status', $msg->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Dibalas">
                                                        <button type="submit" class="dropdown-item d-flex align-items-center justify-content-between py-2 px-2.5 {{ $msg->status === 'Dibalas' ? 'active status-active-success' : '' }}">
                                                            <span class="d-flex align-items-center">
                                                                <i class="mdi mdi-check-all text-success me-2" style="font-size: 15px;"></i> Dibalas
                                                            </span>
                                                            @if($msg->status === 'Dibalas')
                                                                <i class="mdi mdi-check text-success ms-2" style="font-size: 16px; font-weight: bold;"></i>
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline delete-form" id="deleteForm-{{ $msg->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px; padding: 0; border-radius: 6px;"
                                                    onclick="confirmDelete({{ $msg->id }})" title="Hapus Pesan">
                                                <i class="mdi mdi-delete" style="font-size: 16px; margin: 0;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="mdi mdi-email-outline d-block mb-2" style="font-size: 36px; opacity: 0.5;"></i>
                                    Tidak ada pesan kontak yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($messages->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <small class="text-muted">
                        Menampilkan {{ $messages->firstItem() }} - {{ $messages->lastItem() }} dari {{ $messages->total() }} pesan
                    </small>
                    <div>
                        {{ $messages->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

<!-- Modal Detail Pesan -->
<div class="modal fade" id="messageDetailModal" tabindex="-1" aria-labelledby="messageDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 620px; margin: 1.75rem auto; width: 95%;">
        <div class="modal-content border-0 shadow-lg text-start" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
            <!-- Header Modal -->
            <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 34px; height: 34px; background-color: #ede9fe; color: #4f46e5 !important;">
                        <i class="mdi mdi-email-open-outline" style="font-size: 18px;"></i>
                    </span>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-dark" id="messageDetailModalLabel" style="font-size: 15px;">
                            Detail Pesan Kontak
                        </h6>
                        <small class="text-muted" style="font-size: 11px;">Rincian pesan masuk dari formulir kontak pelanggan.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>
            <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                <!-- Sender Information Box -->
                <div class="p-3 rounded-3 mb-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="row g-2">
                        <div class="col-sm-6">
                            <span class="text-muted d-block" style="font-size: 11px;">Nama Pengirim</span>
                            <strong class="text-dark" id="modalSenderName" style="font-size: 13px;">-</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block" style="font-size: 11px;">Email Pengirim</span>
                            <a href="#" id="modalSenderEmailLink" class="text-primary font-weight-500 text-decoration-none" style="font-size: 12.5px;">-</a>
                        </div>
                        <div class="col-sm-6 pt-1 border-top">
                            <span class="text-muted d-block" style="font-size: 11px;">Waktu Terkirim</span>
                            <span class="text-dark" id="modalMessageTime" style="font-size: 12px;">-</span>
                        </div>
                        <div class="col-sm-6 pt-1 border-top">
                            <span class="text-muted d-block" style="font-size: 11px;">Status Pesan</span>
                            <div id="modalMessageStatusBadge" class="mt-0.5">-</div>
                        </div>
                    </div>
                </div>

                <!-- Subjek Pesan -->
                <div class="mb-2.5">
                    <span class="text-muted d-block mb-1" style="font-size: 11px; font-weight: 500;">Subjek Pesan</span>
                    <div class="p-2 rounded-2 border font-weight-bold text-dark" style="background: #ffffff; border-color: #e2e8f0 !important; font-size: 13px;" id="modalMessageSubject">
                        -
                    </div>
                </div>

                <!-- Isi Pesan -->
                <div class="mb-1">
                    <span class="text-muted d-block mb-1" style="font-size: 11px; font-weight: 500;">Isi Pesan Lengkap</span>
                    <div class="p-3 rounded-3 border text-dark" style="background: #ffffff; border-color: #e2e8f0 !important; min-height: 110px; max-height: 220px; overflow-y: auto; white-space: pre-wrap; font-size: 12.5px; line-height: 1.6;" id="modalMessageBody">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-between align-items-center" style="border-radius: 0 0 12px 12px;">
                <div>
                    <!-- Mark as replied form -->
                    <form id="markRepliedForm" action="#" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="Dibalas">
                        <button type="submit" class="btn btn-xs btn-outline-success py-1 px-2.5 d-inline-flex align-items-center" style="border-radius: 6px; font-size: 11.5px; font-weight: 500;">
                            <i class="mdi mdi-check-all me-1"></i> Tandai Sudah Dibalas
                        </button>
                    </form>
                </div>
                <div class="d-flex gap-2">
                    <a href="#" id="modalReplyMailto" class="btn btn-gradient-primary btn-sm px-3 py-1 d-inline-flex align-items-center shadow-sm" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                        <i class="mdi mdi-reply me-1" style="font-size: 14px;"></i> Balas via Email
                    </a>
                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Handle select all checkboxes
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const messageCheckboxes = document.querySelectorAll('.message-checkbox');
    const btnBulkDelete = document.getElementById('btnBulkDelete');
    const selectedCountSpan = document.getElementById('selectedCount');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            messageCheckboxes.forEach(cb => cb.checked = selectAllCheckbox.checked);
            updateBulkDeleteState();
        });
    }

    function updateBulkDeleteState() {
        const checked = document.querySelectorAll('.message-checkbox:checked');
        const count = checked.length;
        if (count > 0) {
            btnBulkDelete.classList.remove('d-none');
            btnBulkDelete.classList.add('d-inline-flex');
            selectedCountSpan.textContent = count;
        } else {
            btnBulkDelete.classList.add('d-none');
            btnBulkDelete.classList.remove('d-inline-flex');
            selectedCountSpan.textContent = 0;
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
        }
    }

    function confirmBulkDelete() {
        const checked = document.querySelectorAll('.message-checkbox:checked');
        if (checked.length === 0) return;

        const ids = Array.from(checked).map(cb => cb.value);

        Swal.fire({
            title: 'Hapus Pesan Terpilih?',
            text: `Apakah Anda yakin ingin menghapus ${ids.length} pesan terpilih? Tindakan ini tidak dapat dibatalkan.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#fe7096',
            cancelButtonColor: '#9c9fa6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('bulkDeleteIds').value = ids.join(',');
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    }

    function confirmDelete(id) {
        Swal.fire({
            title: 'Hapus Pesan Ini?',
            text: 'Pesan kontak ini akan dihapus secara permanen dari database.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#fe7096',
            cancelButtonColor: '#9c9fa6',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('deleteForm-' + id).submit();
            }
        });
    }

    function openMessageDetail(id, nama, email, subjek, pesan, status, time) {
        document.getElementById('modalSenderName').textContent = nama;
        const emailLink = document.getElementById('modalSenderEmailLink');
        emailLink.textContent = email;
        emailLink.href = 'mailto:' + email;
        document.getElementById('modalMessageTime').textContent = time;
        document.getElementById('modalMessageSubject').textContent = subjek;
        document.getElementById('modalMessageBody').textContent = pesan;

        const badgeContainer = document.getElementById('modalMessageStatusBadge');
        if (status === 'Belum Dibaca') {
            badgeContainer.innerHTML = '<span class="badge bg-warning text-dark px-2 py-1 rounded">Belum Dibaca</span>';
        } else if (status === 'Dibaca') {
            badgeContainer.innerHTML = '<span class="badge bg-info text-white px-2 py-1 rounded">Dibaca</span>';
        } else {
            badgeContainer.innerHTML = '<span class="badge bg-success text-white px-2 py-1 rounded">Dibalas</span>';
        }

        // Setup reply mailto
        const mailtoBtn = document.getElementById('modalReplyMailto');
        mailtoBtn.href = `mailto:${email}?subject=Re: ${encodeURIComponent(subjek)}&body=${encodeURIComponent('\n\n--- Pesan Asli Dari ' + nama + ' ---\n' + pesan)}`;

        // Setup mark as replied action url
        const formAction = '{{ url('admin/messages') }}/' + id + '/status';
        document.getElementById('markRepliedForm').action = formAction;

        // Auto trigger AJAX show to mark as read in database
        if (status === 'Belum Dibaca') {
            fetch('{{ url('admin/messages') }}/' + id, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(() => {
                const row = document.getElementById('row-msg-' + id);
                if (row) {
                    row.classList.remove('table-warning', 'bg-opacity-10', 'fw-bold');
                }
            }).catch(() => {});
        }

        const modal = new bootstrap.Modal(document.getElementById('messageDetailModal'));
        modal.show();
    }
</script>
@endsection

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
        <div class="col-md-4 stretch-card grid-margin mb-3 mb-md-0">
            <div class="card bg-gradient-info card-img-holder text-white border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="font-weight-normal mb-2" style="font-size: 15px;">Total Pesan Masuk <i class="mdi mdi-email-outline mdi-24px float-end"></i></h4>
                    <h2 class="mb-1 font-weight-bold">{{ number_format($totalMessages, 0, ',', '.') }}</h2>
                    <small style="opacity: 0.85;">Akumulasi seluruh pesan kontak</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin mb-3 mb-md-0">
            <div class="card bg-gradient-warning card-img-holder text-white border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="font-weight-normal mb-2" style="font-size: 15px;">Belum Dibaca <i class="mdi mdi-email-alert-outline mdi-24px float-end"></i></h4>
                    <h2 class="mb-1 font-weight-bold">{{ number_format($unreadMessages, 0, ',', '.') }}</h2>
                    <small style="opacity: 0.85;">Memerlukan perhatian admin</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card bg-gradient-success card-img-holder text-white border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="font-weight-normal mb-2" style="font-size: 15px;">Sudah Dibalas <i class="mdi mdi-email-check-outline mdi-24px float-end"></i></h4>
                    <h2 class="mb-1 font-weight-bold">{{ number_format($repliedMessages, 0, ',', '.') }}</h2>
                    <small style="opacity: 0.85;">Pesan yang telah ditindaklanjuti</small>
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
                        Sudah Dibaca
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
                    <button type="button" id="btnBulkDelete" class="btn btn-outline-danger btn-sm d-none align-items-center" onclick="confirmBulkDelete()">
                        <i class="mdi mdi-delete-outline me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
                    </button>
                </div>
                <div class="position-relative w-100 w-md-auto" style="min-width: 280px;">
                    <input type="text" name="search" value="{{ request('search') }}"
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
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="bg-light">
                        <tr>
                            <th style="width: 40px;" class="text-center">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input" style="cursor: pointer;">
                            </th>
                            <th>Pengirim</th>
                            <th>Subjek & Pesan</th>
                            <th class="text-center" style="width: 130px;">Status</th>
                            <th style="width: 150px;">Waktu</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $msg)
                            <tr class="{{ $msg->status === 'Belum Dibaca' ? 'table-warning bg-opacity-10 fw-bold' : '' }}" id="row-msg-{{ $msg->id }}">
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
                                        <span class="badge bg-warning text-dark px-3 py-2" style="border-radius: 20px; font-size: 11px;">
                                            <i class="mdi mdi-email-alert-outline me-1"></i> Belum Dibaca
                                        </span>
                                    @elseif($msg->status === 'Dibaca')
                                        <span class="badge bg-info text-white px-3 py-2" style="border-radius: 20px; font-size: 11px;">
                                            <i class="mdi mdi-email-open-outline me-1"></i> Dibaca
                                        </span>
                                    @else
                                        <span class="badge bg-success text-white px-3 py-2" style="border-radius: 20px; font-size: 11px;">
                                            <i class="mdi mdi-check-all me-1"></i> Dibalas
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark" style="font-size: 12.5px;">{{ $msg->created_at->format('d M Y') }}</div>
                                    <small class="text-muted">{{ $msg->created_at->format('H:i') }} WIB</small>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Tombol Baca Modal -->
                                        <button type="button" class="btn btn-sm btn-gradient-primary px-2 py-1"
                                                onclick="openMessageDetail({{ $msg->id }}, '{{ addslashes($msg->nama) }}', '{{ addslashes($msg->email) }}', '{{ addslashes($msg->subjek) }}', '{{ addslashes(preg_replace('/\r|\n/', ' ', $msg->pesan)) }}', '{{ $msg->status }}', '{{ $msg->created_at->format('d M Y, H:i') }} WIB')"
                                                title="Lihat Detail Pesan">
                                            <i class="mdi mdi-eye"></i>
                                        </button>

                                        <!-- Dropdown Ubah Status -->
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-sm btn-outline-secondary px-2 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Ubah Status">
                                                <i class="mdi mdi-list-status"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size: 12.5px; border-radius: 8px;">
                                                <li>
                                                    <form action="{{ route('messages.update-status', $msg->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Belum Dibaca">
                                                        <button type="submit" class="dropdown-item py-2 {{ $msg->status === 'Belum Dibaca' ? 'active' : '' }}">
                                                            <i class="mdi mdi-email-alert-outline text-warning me-2"></i> Belum Dibaca
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('messages.update-status', $msg->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Dibaca">
                                                        <button type="submit" class="dropdown-item py-2 {{ $msg->status === 'Dibaca' ? 'active' : '' }}">
                                                            <i class="mdi mdi-email-open-outline text-info me-2"></i> Dibaca
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('messages.update-status', $msg->id) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="Dibalas">
                                                        <button type="submit" class="dropdown-item py-2 {{ $msg->status === 'Dibalas' ? 'active' : '' }}">
                                                            <i class="mdi mdi-check-all text-success me-2"></i> Dibalas
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Tombol Hapus -->
                                        <form action="{{ route('messages.destroy', $msg->id) }}" method="POST" class="d-inline" id="deleteForm-{{ $msg->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" onclick="confirmDelete({{ $msg->id }})" title="Hapus Pesan">
                                                <i class="mdi mdi-delete-outline"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header bg-gradient-primary text-white py-3">
                <h5 class="modal-title font-weight-bold d-flex align-items-center" id="messageDetailModalLabel">
                    <i class="mdi mdi-email-open-outline me-2"></i> Detail Pesan Kontak
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row mb-3">
                    <div class="col-sm-6 mb-2">
                        <small class="text-muted d-block" style="font-size: 11.5px;">Nama Pengirim</small>
                        <h6 class="font-weight-bold text-dark mb-0" id="modalSenderName">-</h6>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <small class="text-muted d-block" style="font-size: 11.5px;">Email Pengirim</small>
                        <a href="#" id="modalSenderEmailLink" class="text-primary font-weight-bold text-decoration-none">-</a>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-6 mb-2">
                        <small class="text-muted d-block" style="font-size: 11.5px;">Waktu Terkirim</small>
                        <span class="text-dark" id="modalMessageTime">-</span>
                    </div>
                    <div class="col-sm-6 mb-2">
                        <small class="text-muted d-block" style="font-size: 11.5px;">Status Saat Ini</small>
                        <span id="modalMessageStatusBadge">-</span>
                    </div>
                </div>

                <hr class="my-3 text-muted" style="opacity: 0.15;">

                <div class="mb-3">
                    <small class="text-muted d-block mb-1" style="font-size: 11.5px;">Subjek Pesan</small>
                    <h6 class="font-weight-bold text-dark p-2 rounded" style="background: #f8f9fa;" id="modalMessageSubject">-</h6>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-1" style="font-size: 11.5px;">Isi Pesan Lengkap</small>
                    <div class="p-3 rounded border text-dark" style="background: #fafbfc; min-height: 120px; white-space: pre-wrap; font-size: 13.5px; line-height: 1.6;" id="modalMessageBody">
                        -
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-between">
                <div>
                    <!-- Mark as replied form -->
                    <form id="markRepliedForm" action="#" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="Dibalas">
                        <button type="submit" class="btn btn-outline-success btn-sm">
                            <i class="mdi mdi-check-all me-1"></i> Tandai Sudah Dibalas
                        </button>
                    </form>
                </div>
                <div class="d-flex gap-2">
                    <a href="#" id="modalReplyMailto" class="btn btn-gradient-primary btn-sm px-3 d-inline-flex align-items-center">
                        <i class="mdi mdi-reply me-1"></i> Balas via Email
                    </a>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
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

@extends('Layout.layout_pAdmin')

@section('title', 'Kasir - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-sm-start">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-account-multiple"></i>
            </span>Kasir
        </h3>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnBulkDelete" class="btn btn-gradient-danger btn-sm d-none align-items-center justify-content-center px-3" style="height: 38px;">
                <i class="mdi mdi-delete me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
            <button type="button" class="btn btn-gradient-primary btn-sm px-4 ms-2" data-bs-toggle="modal" data-bs-target="#addCassierModal">
                <i class="mdi mdi-plus me-1"></i> Tambah Kasir
            </button>
        </div>
    </div>

    <form action="{{ route('cassiers.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
        @csrf
        <div id="bulkDeleteInputs"></div>
    </form>



    <div class="row mt-4">
        @forelse($cassiers as $cassier)
        <div class="col-md-4 grid-margin stretch-card mb-4">
            <div class="card border-0 shadow-sm cassier-card" style="border-radius: 8px; position: relative; overflow: hidden; background: #fff;">
                <div style="position: absolute; top: 12px; left: 12px; z-index: 10; background: rgba(255,255,255,0.85); padding: 4px; border-radius: 6px;">
                    <input type="checkbox" name="ids[]" value="{{ $cassier->id }}" class="cassier-row-checkbox form-check-input" style="width: 18px; height: 18px; cursor: pointer; margin: 0;">
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <!-- Image Section -->
                    <div class="image-wrapper mx-auto mb-4" style="width: 220px; height: 220px; border-radius: 50%; position: relative; overflow: hidden; background-color: #f3f4f6;">
                        @if($cassier->image)
                            <img src="{{ asset('storage/' . $cassier->image) }}" alt="{{ $cassier->name }}" class="w-100 h-100" style="object-fit: cover;">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted" style="background: #e9ecef;">
                                <i class="mdi mdi-account" style="font-size: 64px;"></i>
                            </div>
                        @endif
                        
                        <!-- Overlay Edit Button on Hover -->
                        <div class="cassier-action-overlay d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(30,39,83,0.55); opacity: 0; transition: opacity 0.3s ease;">
                            <button type="button" class="btn btn-light btn-sm px-4 py-2 text-primary shadow-sm d-flex align-items-center font-weight-bold" data-bs-toggle="modal" data-bs-target="#editCassierModal{{ $cassier->id }}">
                                <i class="mdi mdi-pencil me-1"></i> Edit
                            </button>
                        </div>
                    </div>
                    
                    <!-- Text Section -->
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1" style="font-size: 16px;">{{ $cassier->name }}</h5>
                        <p class="text-muted mb-0" style="font-size: 13px;">{{ $cassier->status == 'Active' ? 'Aktif' : 'Nonaktif' }}</p>
                    </div>

                    <!-- Delete Button (Bottom Right) -->
                    <div style="position: absolute; bottom: 15px; right: 15px;">
                        <form action="{{ route('cassiers.destroy', $cassier->id) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-sm bg-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 4px; border-color: #fca5a5;">
                                <i class="mdi mdi-delete-outline" style="font-size: 16px; margin: 0; color: #ef4444;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Cassier Modal -->
        <div class="modal fade" id="editCassierModal{{ $cassier->id }}" tabindex="-1" aria-labelledby="editCassierModalLabel{{ $cassier->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; margin: 1.75rem auto; width: 95%;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                    <!-- Header Modal -->
                    <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center text-info rounded-circle" style="width: 34px; height: 34px; background-color: #e0f2fe; color: #0284c7 !important;">
                                <i class="mdi mdi-account-edit-outline" style="font-size: 18px;"></i>
                            </span>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editCassierModalLabel{{ $cassier->id }}" style="font-size: 15px;">
                                        Edit Akun Kasir
                                    </h6>
                                    <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">#{{ $cassier->id }}</span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Perbarui data atau status operasional kasir.</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                    </div>
                    <form action="{{ route('cassiers.update', $cassier->id) }}" method="POST" enctype="multipart/form-data" class="edit-form">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                            <!-- Nama Kasir -->
                            <div class="form-group mb-2.5">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Kasir <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-account-outline"></i></span>
                                    <input type="text" class="form-control border-start-0" name="name" value="{{ $cassier->name }}" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                </div>
                            </div>
                            <!-- Email -->
                            <div class="form-group mb-2.5">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Email Login <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-email-outline"></i></span>
                                    <input type="email" class="form-control border-start-0" name="email" value="{{ $cassier->email }}" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                </div>
                            </div>
                            <!-- Password & Status in 2-Column Grid -->
                            <div class="row g-2 mb-2.5">
                                <div class="col-md-7">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Ganti Password <small class="text-muted fw-normal">(Opsional)</small></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-lock-outline"></i></span>
                                            <input type="password" class="form-control border-start-0" name="password" placeholder="Kosongkan bila tetap" style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group mb-0">
                                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Status Kasir <span class="text-danger">*</span></label>
                                        <select class="form-control form-select" name="status" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                            <option value="Active" {{ $cassier->status == 'Active' ? 'selected' : '' }}>Aktif</option>
                                            <option value="Deactive" {{ $cassier->status == 'Deactive' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <!-- Foto Kasir -->
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Foto Kasir <small class="text-muted fw-normal">(Opsional)</small></label>
                                @if($cassier->image)
                                    <div class="mb-2 d-flex align-items-center gap-2 p-2 rounded border bg-light">
                                        <img src="{{ asset('storage/' . $cassier->image) }}" alt="Current Image" class="rounded-circle border" style="height: 38px; width: 38px; object-fit: cover;">
                                        <small class="text-muted" style="font-size: 11px;">Foto profil kasir saat ini.</small>
                                    </div>
                                @endif
                                <input type="file" class="form-control" name="image" accept="image/*" style="font-size: 12px; border-radius: 6px;">
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                            <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                                <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Perbarui Kasir
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @empty
        <div class="col-12">
            <div class="bg-white text-center py-5 shadow-sm border-0" style="border-radius: 8px;">
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <i class="mdi mdi-account-multiple-outline text-muted mb-2" style="font-size: 64px;"></i>
                    <h5 class="text-muted font-weight-bold">Belum Ada Kasir</h5>
                    <p class="text-muted mb-0" style="font-size: 14px;">Tambahkan akun kasir pertama Anda!</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- Add Cassier Modal -->
<div class="modal fade" id="addCassierModal" tabindex="-1" aria-labelledby="addCassierModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px; margin: 1.75rem auto; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
            <!-- Header Modal -->
            <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                <div class="d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center justify-content-center text-success rounded-circle" style="width: 34px; height: 34px; background-color: #dcfce7; color: #16a34a !important;">
                        <i class="mdi mdi-account-plus-outline" style="font-size: 18px;"></i>
                    </span>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-dark" id="addCassierModalLabel" style="font-size: 15px;">
                            Tambah Kasir Baru
                        </h6>
                        <small class="text-muted" style="font-size: 11px;">Daftarkan akun kasir untuk akses transaksi POS offline.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
            </div>
            <form action="{{ route('cassiers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                    <!-- Nama Kasir -->
                    <div class="form-group mb-2.5">
                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Kasir <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-account-outline"></i></span>
                            <input type="text" class="form-control border-start-0" name="name" placeholder="Masukkan nama kasir" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                        </div>
                    </div>
                    <!-- Email -->
                    <div class="form-group mb-2.5">
                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Email Login <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-email-outline"></i></span>
                            <input type="email" class="form-control border-start-0" name="email" placeholder="contoh: kasir@zyhcorner.com" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                        </div>
                    </div>
                    <!-- Password & Status in 2-Column Grid -->
                    <div class="row g-2 mb-2.5">
                        <div class="col-md-7">
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-lock-outline"></i></span>
                                    <input type="password" class="form-control border-start-0" name="password" placeholder="Min. 6 karakter" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group mb-0">
                                <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Status Kasir <span class="text-danger">*</span></label>
                                <select class="form-control form-select" name="status" required style="font-size: 12px; border-radius: 6px; height: 38px;">
                                    <option value="Active" selected>Aktif</option>
                                    <option value="Deactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Foto Kasir -->
                    <div class="form-group mb-0">
                        <label class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Foto Kasir <small class="text-muted fw-normal">(Opsional)</small></label>
                        <input type="file" class="form-control" name="image" accept="image/*" style="font-size: 12px; border-radius: 6px;">
                    </div>
                </div>
                <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                        <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Kasir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .cassier-card:hover .cassier-action-overlay {
        opacity: 1 !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rowCheckboxes = document.querySelectorAll('.cassier-row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.cassier-row-checkbox:checked').length;
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
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkDeleteState);
        });

        if (btnBulkDelete && bulkDeleteForm) {
            btnBulkDelete.addEventListener('click', function(e) {
                e.preventDefault();
                const selectedCheckboxes = document.querySelectorAll('.cassier-row-checkbox:checked');
                const count = selectedCheckboxes.length;
                if (count === 0) return;

                Swal.fire({
                    title: 'Hapus Kasir Terpilih?',
                    text: `Apakah Anda yakin ingin menghapus ${count} akun kasir yang dipilih?`,
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

        @if(isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any() && old('_method') !== 'PUT')
            var myModal = new bootstrap.Modal(document.getElementById('addCassierModal'));
            myModal.show();
        @endif
    });
</script>
@endsection

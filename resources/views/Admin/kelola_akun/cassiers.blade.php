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
                            <button type="submit" class="btn btn-outline-primary btn-sm bg-white d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 4px; border-color: #e4e4e4;">
                                <i class="mdi mdi-delete-outline" style="font-size: 16px; margin: 0; color: #2b5cff;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Cassier Modal -->
        <div class="modal fade" id="editCassierModal{{ $cassier->id }}" tabindex="-1" aria-labelledby="editCassierModalLabel{{ $cassier->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 8px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="editCassierModalLabel{{ $cassier->id }}">Edit Kasir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('cassiers.update', $cassier->id) }}" method="POST" enctype="multipart/form-data" class="edit-form">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label class="font-weight-500 mb-2">Nama Kasir</label>
                                <input type="text" class="form-control" name="name" value="{{ $cassier->name }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-500 mb-2">Email</label>
                                <input type="email" class="form-control" name="email" value="{{ $cassier->email }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-500 mb-2">Password</label>
                                <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak diubah">
                                <small class="form-text text-muted">Minimal 6 karakter. Biarkan kosong jika tidak ingin mengubah password.</small>
                            </div>
                            <div class="form-group mb-3">
                                <label class="font-weight-500 mb-2">Status</label>
                                <select class="form-control form-select" name="status" required>
                                    <option value="Active" {{ $cassier->status == 'Active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Deactive" {{ $cassier->status == 'Deactive' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-weight-500 mb-2">Gambar Kasir (Opsional)</label>
                                @if($cassier->image)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $cassier->image) }}" alt="Current Image" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: cover; border-radius: 50%;">
                                    </div>
                                @endif
                                <input type="file" class="form-control" name="image" accept="image/*">
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-gradient-primary">Perbarui Kasir</button>
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
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 8px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold" id="addCassierModalLabel">Tambah Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('cassiers.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Nama Kasir</label>
                        <input type="text" class="form-control" name="name" placeholder="Masukkan nama kasir" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Email</label>
                        <input type="email" class="form-control" name="email" placeholder="Masukkan email kasir" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                        <small class="form-text text-muted">Minimal 6 karakter.</small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="font-weight-500 mb-2">Status</label>
                        <select class="form-control form-select" name="status" required>
                            <option value="Active">Aktif</option>
                            <option value="Deactive">Nonaktif</option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-500 mb-2">Gambar Kasir</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-gradient-primary">Simpan Kasir</button>
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

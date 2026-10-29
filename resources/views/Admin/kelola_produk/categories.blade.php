@extends('Layout.layout_pAdmin')

@section('title', 'Kategori - Dasbor Admin')

@section('content')
<div class="content-wrapper">
    <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <h3 class="page-title d-inline-flex align-items-center m-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-folder-outline"></i>
            </span>
            <span class="text-nowrap">Kategori Produk</span>
        </h3>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btnBulkDelete" class="btn btn-gradient-danger btn-sm d-none align-items-center justify-content-center px-3" style="height: 38px;">
                <i class="mdi mdi-delete me-1"></i> Hapus Terpilih (<span id="selectedCount">0</span>)
            </button>
            <button type="button" class="btn btn-gradient-primary btn-sm d-flex align-items-center w-10 w-sm-auto justify-content-center" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                <i class="mdi mdi-plus"></i> Tambah Kategori
            </button>
        </div>
    </div>

    <form action="{{ route('categories.bulk-delete') }}" method="POST" id="bulkDeleteForm" style="display: none;">
        @csrf
        <div id="bulkDeleteInputs"></div>
    </form>

    <div class="row mt-4">
        @forelse($kategoris as $kategori)
        <div class="col-md-4 grid-margin stretch-card mb-4">
            <div class="card border-0 shadow-sm category-card" style="border-radius: 8px; overflow: hidden; position: relative;">
                <div style="position: absolute; top: 10px; left: 10px; z-index: 10; background: rgba(255,255,255,0.85); padding: 4px; border-radius: 6px;">
                    <input type="checkbox" name="ids[]" value="{{ $kategori->id_kategori }}" class="category-row-checkbox form-check-input" style="width: 18px; height: 18px; cursor: pointer; margin: 0;">
                </div>
                <div class="card-img-top-wrapper" style="position: relative; height: 220px; overflow: hidden;">
                    <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}" alt="{{ $kategori->nama_kategori }}" class="card-img-top w-100 h-100" style="object-fit: cover;">
                    <div class="category-action-overlay d-flex justify-content-center align-items-center" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(30,39,83,0.55); opacity: 0; transition: opacity 0.3s ease;">
                        <button type="button" class="btn btn-inverse-info btn-sm px-4 py-2 me-2 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $kategori->id_kategori }}">
                            <i class="mdi mdi-pencil me-1"></i> Edit
                        </button>
                        <form action="{{ route('categories.destroy', $kategori->id_kategori) }}" method="POST" class="d-inline delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-inverse-danger btn-sm px-4 py-2 shadow-sm d-flex align-items-center">
                                <i class="mdi mdi-delete me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-3 bg-white">
                    <h5 class="card-title font-weight-bold text-dark mb-1" style="font-size: 16px;">{{ $kategori->nama_kategori }}</h5>
                    <p class="card-text text-muted mb-0" style="font-size: 13px;">{{ $kategori->produks_count ?? 0 }} item</p>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal for {{ $kategori->nama_kategori }} -->
        <div class="modal fade" id="editCategoryModal{{ $kategori->id_kategori }}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{ $kategori->id_kategori }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto; width: 95%;">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                    <!-- Header Modal -->
                    <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center text-info rounded-circle" style="width: 34px; height: 34px; background-color: #e0f2fe; color: #0284c7 !important;">
                                <i class="mdi mdi-tag-edit-outline" style="font-size: 18px;"></i>
                            </span>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="modal-title font-weight-bold mb-0 text-dark" id="editCategoryModalLabel{{ $kategori->id_kategori }}" style="font-size: 15px;">
                                        Edit Kategori
                                    </h6>
                                    <span class="badge bg-light text-primary border" style="font-size: 10px; font-weight: 600; padding: 2px 6px;">#{{ $kategori->id_kategori }}</span>
                                </div>
                                <small class="text-muted" style="font-size: 11px;">Perbarui nama atau gambar sampul kategori.</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                    </div>
                    <form action="{{ route('categories.update', $kategori->id_kategori) }}" method="POST" enctype="multipart/form-data" class="edit-form">
                        @csrf
                        @method('PUT')
                        <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                            <div class="form-group mb-2.5">
                                <label for="nama_kategori_{{ $kategori->id_kategori }}" class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Kategori <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-tag-outline"></i></span>
                                    <input type="text" class="form-control border-start-0" id="nama_kategori_{{ $kategori->id_kategori }}" name="nama_kategori" value="{{ $kategori->nama_kategori }}" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label for="gambar_kategori_{{ $kategori->id_kategori }}" class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Gambar Sampul Kategori <small class="text-muted fw-normal">(Opsional)</small></label>
                                @if($kategori->gambar_kategori)
                                    <div class="mb-2 d-flex align-items-center gap-2 p-2 rounded border bg-light">
                                        <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}" alt="Current Image" class="rounded border" style="height: 48px; width: 64px; object-fit: cover;">
                                        <small class="text-muted" style="font-size: 11px;">Sampul kategori saat ini.</small>
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="gambar_kategori_{{ $kategori->id_kategori }}" name="gambar_kategori" accept="image/*" style="font-size: 12px; border-radius: 6px;">
                                <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                            <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                            <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                                <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Perbarui Kategori
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
                    <i class="mdi mdi-folder-outline text-muted mb-2" style="font-size: 64px;"></i>
                    <h5 class="text-muted font-weight-bold">Kategori Kosong</h5>
                    <p class="text-muted mb-0" style="font-size: 14px;">Belum ada kategori yang ditambahkan. Yuk, buat kategori baru!</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 440px; margin: 1.75rem auto; width: 95%;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
                <!-- Header Modal -->
                <div class="modal-header border-bottom py-2.5 px-3 px-sm-4 bg-white flex-shrink-0">
                    <div class="d-flex align-items-center gap-2">
                        <span class="d-inline-flex align-items-center justify-content-center text-success rounded-circle" style="width: 34px; height: 34px; background-color: #dcfce7; color: #16a34a !important;">
                            <i class="mdi mdi-tag-plus-outline" style="font-size: 18px;"></i>
                        </span>
                        <div>
                            <h6 class="modal-title font-weight-bold mb-0 text-dark" id="addCategoryModalLabel" style="font-size: 15px;">
                                Tambah Kategori Baru
                            </h6>
                            <small class="text-muted" style="font-size: 11px;">Buat kelompok kategori baru untuk katalog produk.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 11px;"></button>
                </div>
                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-3 p-sm-4 bg-white" style="overflow-y: auto;">
                        <div class="form-group mb-2.5">
                            <label for="nama_kategori" class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Nama Kategori <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted" style="border-radius: 6px 0 0 6px;"><i class="mdi mdi-tag-outline"></i></span>
                                <input type="text" class="form-control border-start-0" id="nama_kategori" name="nama_kategori" placeholder="Contoh: Pastry & Croissant" required style="border-radius: 0 6px 6px 0; font-size: 12px;">
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="gambar_kategori" class="form-label font-weight-500 mb-1 text-dark" style="font-size: 12px;">Gambar Sampul Kategori <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="gambar_kategori" name="gambar_kategori" accept="image/*" required style="font-size: 12px; border-radius: 6px;">
                            <small class="text-muted d-block mt-1" style="font-size: 10.5px;">Format: JPG, JPEG, PNG (Disarankan rasio 4:3).</small>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light py-2 px-3 px-sm-4 d-flex justify-content-end gap-2" style="border-radius: 0 0 12px 12px;">
                        <button type="button" class="btn btn-light btn-sm px-3 py-1" data-bs-dismiss="modal" style="border-radius: 6px; font-weight: 500; font-size: 12px;">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary btn-sm px-3 py-1 shadow-sm d-inline-flex align-items-center" style="border-radius: 6px; font-weight: 500; font-size: 12px;">
                            <i class="mdi mdi-check-circle-outline me-1" style="font-size: 14px;"></i> Simpan Kategori
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .category-card:hover .category-action-overlay {
        opacity: 1 !important;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const rowCheckboxes = document.querySelectorAll('.category-row-checkbox');
        const btnBulkDelete = document.getElementById('btnBulkDelete');
        const selectedCountSpan = document.getElementById('selectedCount');
        const bulkDeleteForm = document.getElementById('bulkDeleteForm');

        function updateBulkDeleteState() {
            const checkedCount = document.querySelectorAll('.category-row-checkbox:checked').length;
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
                const selectedCheckboxes = document.querySelectorAll('.category-row-checkbox:checked');
                const count = selectedCheckboxes.length;
                if (count === 0) return;

                Swal.fire({
                    title: 'Hapus Kategori Terpilih?',
                    text: `Apakah Anda yakin ingin menghapus ${count} kategori yang dipilih?`,
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
            var myModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
            myModal.show();
        @endif
    });
</script>
@endsection

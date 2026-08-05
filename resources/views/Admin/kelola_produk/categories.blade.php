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
        <button type="button" class="btn btn-gradient-primary btn-sm d-flex align-items-center w-10 w-sm-auto justify-content-center" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="mdi mdi-plus"></i> Tambah Kategori
        </button>
    </div>



    <div class="row mt-4">
        @forelse($kategoris as $kategori)
        <div class="col-md-4 grid-margin stretch-card mb-4">
            <div class="card border-0 shadow-sm category-card" style="border-radius: 8px; overflow: hidden; position: relative;">
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
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 8px;">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title font-weight-bold" id="editCategoryModalLabel{{ $kategori->id_kategori }}">Edit Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('categories.update', $kategori->id_kategori) }}" method="POST" enctype="multipart/form-data" class="edit-form">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="nama_kategori_{{ $kategori->id_kategori }}" class="font-weight-500 mb-2">Nama Kategori</label>
                                <input type="text" class="form-control" id="nama_kategori_{{ $kategori->id_kategori }}" name="nama_kategori" value="{{ $kategori->nama_kategori }}" required>
                            </div>
                            <div class="form-group mb-0">
                                <label for="gambar_kategori_{{ $kategori->id_kategori }}" class="font-weight-500 mb-2">Gambar Kategori</label>
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $kategori->gambar_kategori) }}" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
                                </div>
                                <input type="file" class="form-control" id="gambar_kategori_{{ $kategori->id_kategori }}" name="gambar_kategori" accept="image/*">
                                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-gradient-primary">Perbarui Kategori</button>
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
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 8px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold" id="addCategoryModalLabel">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="nama_kategori" class="font-weight-500 mb-2">Nama Kategori</label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" placeholder="Masukkan nama kategori" required>
                        </div>
                        <div class="form-group mb-0">
                            <label for="gambar_kategori" class="font-weight-500 mb-2">Gambar Kategori</label>
                            <input type="file" class="form-control" id="gambar_kategori" name="gambar_kategori" accept="image/*" required>
                            <small class="form-text text-muted">Disarankan ukuran 800x600 px (format: jpg, jpeg, png).</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary">Simpan Kategori</button>
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

@if($errors->any())
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // If it's an update, we don't know exactly which modal failed without extra logic, 
        // but we can open add modal if old input is missing ID, or just show the alert.
        // Alert is already shown at the top.
        @if(old('_method') !== 'PUT')
            var myModal = new bootstrap.Modal(document.getElementById('addCategoryModal'));
            myModal.show();
        @endif
    });
</script>
@endif
@endsection

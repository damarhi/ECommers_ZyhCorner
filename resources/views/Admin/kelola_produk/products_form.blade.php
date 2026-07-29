@extends('Layout.layout_pAdmin')

@section('title', $produk->exists ? 'Edit Produk' : 'Tambah Produk')

@section('content')
    <div class="content-wrapper">
@php
    $currentJenis = old('jenis_produk', $produk->jenis_produk ?? request('jenis', 'showcase'));
    $backRoute = ($currentJenis === 'kustom') ? route('products.custom') : route('products.index');
@endphp

        <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h3 class="page-title m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cube-outline"></i>
                </span> {{ $produk->exists ? 'Edit Produk' : 'Tambah Produk' }}
            </h3>
            <nav aria-label="breadcrumb">
                <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary w-100 w-sm-auto">
                    <i class="mdi mdi-arrow-left"></i> Kembali ke Produk
                </a>
            </nav>
        </div>

        <form action="{{ $produk->exists ? route('products.update', $produk->id_produk) : route('products.store') }}"
            method="POST" enctype="multipart/form-data" class="forms-sample {{ $produk->exists ? 'edit-form' : '' }}">
            @csrf
            @if ($produk->exists)
                @method('PUT')
            @endif

            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h4 class="card-title">Informasi</h4>
                            <div class="form-group">
                                <label>Nama Produk</label>
                                <input type="text" name="nama_produk" class="form-control" placeholder="Masukkan Nama"
                                    value="{{ old('nama_produk', $produk->nama_produk) }}" required>
                            </div>
                            <div class="form-group mb-0">
                                <label>Deskripsi Produk</label>
                                <textarea name="deskripsi" class="form-control" placeholder="Deskripsi produk" rows="4">{{ old('deskripsi', $produk->deskripsi) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-2">
                        <div class="card-body">
                            <h4 class="card-title">Kuantitas</h4>
                            <div class="form-group mb-0">
                                <label>Stok Produk</label>
                                <input type="text" name="status_produk" id="status_produk_input" class="form-control" placeholder="Masukkan Stok"
                                    value="{{ old('status_produk', $produk->status_produk) }}" required>
                                <small id="stok_help_text" class="form-text text-muted mt-1" style="display: none; font-size: 12px;"></small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-2">
                        <div class="card-body">
                            <h4 class="card-title">Gambar</h4>
                            <p class="text-muted" style="font-size:12px;"><strong>Catatan:</strong> Format foto SVG, PNG,
                                atau JPG (Ukuran maks 4MB)</p>

                            <div class="row">
                                @for ($i = 1; $i <= 4; $i++)
                                    @php $foto = 'foto_' . $i; @endphp
                                    <div class="col-md-6 mb-3">
                                        <label class="image-upload-box d-flex align-items-center justify-content-center"
                                            style="cursor: pointer; position: relative; overflow: hidden; min-height: 120px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8f9fa;">
                                            <input type="file" name="foto_{{ $i }}" accept="image/*"
                                                style="display: none;"
                                                onchange="previewImage(this, 'preview_{{ $i }}', 'icon_{{ $i }}')">
                                            @if ($produk->$foto)
                                                <img id="preview_{{ $i }}"
                                                    src="{{ asset('storage/' . $produk->$foto) }}"
                                                    style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 1;">
                                                <div id="icon_{{ $i }}"
                                                    style="z-index: 2; background: rgba(255,255,255,0.7); width: 100%; height: 100%; position: absolute; top: 0; left: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.3s;"
                                                    onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0">
                                                    <i class="mdi mdi-image-outline text-primary"
                                                        style="font-size: 24px;"></i>
                                                    <span class="text-primary mt-1">Ganti Photo {{ $i }}</span>
                                                </div>
                                            @else
                                                <img id="preview_{{ $i }}" src=""
                                                    style="width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; z-index: 1; display: none;">
                                                <div id="icon_{{ $i }}"
                                                    style="z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                                    <i class="mdi mdi-image-outline text-muted"
                                                        style="font-size: 32px;"></i>
                                                    <span class="text-muted mt-1">Photo {{ $i }}</span>
                                                </div>
                                            @endif
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h4 class="card-title">Jenis Produk</h4>
                            <div class="form-group mb-0">
                                <label>Pilih Jenis Produk</label>
                                <select name="jenis_produk" class="form-select" required>
                                    <option value="showcase" {{ old('jenis_produk', $produk->jenis_produk ?? request('jenis', 'showcase')) == 'showcase' ? 'selected' : '' }}>Produk Showcase</option>
                                    <option value="kustom" {{ old('jenis_produk', $produk->jenis_produk ?? request('jenis', 'showcase')) == 'kustom' ? 'selected' : '' }}>Produk Kustom</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-2">
                        <div class="card-body">
                            <h4 class="card-title">Kategori</h4>
                            <div class="form-group mb-0">
                                <label>Pilih Kategori</label>
                                <select name="id_kategori" class="form-select">
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach ($kategoris as $k)
                                        <option value="{{ $k->id_kategori }}"
                                            {{ old('id_kategori', $produk->id_kategori) == $k->id_kategori ? 'selected' : '' }}>
                                            {{ $k->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="text-end mt-2">
                                    <a href="#" class="text-primary text-decoration-none"
                                        style="font-size: 13px;">Buat Baru</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-2">
                        <div class="card-body">
                            <h4 class="card-title">Harga</h4>
                            <!-- Single Price (Showcase) -->
                            <div id="single-harga-container" class="form-group mb-0">
                                <label>Harga Produk</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">Rp</span>
                                    </div>
                                    <input type="number" id="single_harga_input" name="harga" class="form-control"
                                        placeholder="Masukkan harga" value="{{ old('harga', $produk->harga) }}"
                                        min="0">
                                </div>
                            </div>

                            <!-- Range Price (Kustom) -->
                            <div id="range-harga-container" class="form-group mb-0" style="display: none;">
                                <label>Range Harga Produk Kustom</label>
                                <div class="row">
                                    <div class="col-6">
                                        <label style="font-size: 12px;" class="text-muted mb-1">Harga Min (Rp)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" style="padding: 0 8px; font-size: 13px;">Rp</span>
                                            </div>
                                            <input type="number" id="range_harga_min_input" name="harga" class="form-control"
                                                placeholder="Min" value="{{ old('harga', $produk->harga) }}" min="0">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label style="font-size: 12px;" class="text-muted mb-1">Harga Max (Rp)</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" style="padding: 0 8px; font-size: 13px;">Rp</span>
                                            </div>
                                            <input type="number" id="range_harga_max_input" name="harga_max" class="form-control"
                                                placeholder="Max" value="{{ old('harga_max', $produk->harga_max) }}" min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <h4 class="card-title">Tanggal Kedaluwarsa</h4>
                            <div class="form-group mb-0">
                                <label>Tanggal Kedaluwarsa (Opsional)</label>
                                <input type="date" name="tanggal_expired" id="tanggal_expired_input" class="form-control"
                                    value="{{ old('tanggal_expired', $produk->tanggal_expired) }}">
                                <small id="expired_help_text" class="form-text text-muted mt-1" style="display: none; font-size: 12px;"></small>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-4">
                        <a href="{{ $backRoute }}" class="btn btn-light me-2">Batal</a>
                        <button type="submit" class="btn btn-gradient-primary">Simpan Produk</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var jenisSelect = document.querySelector('select[name="jenis_produk"]');
            var statusInput = document.getElementById('status_produk_input');
            var stokHelp = document.getElementById('stok_help_text');
            var singleContainer = document.getElementById('single-harga-container');
            var rangeContainer = document.getElementById('range-harga-container');
            var singleInput = document.getElementById('single_harga_input');
            var minInput = document.getElementById('range_harga_min_input');
            var maxInput = document.getElementById('range_harga_max_input');
            var expiredInput = document.getElementById('tanggal_expired_input');
            var expiredHelp = document.getElementById('expired_help_text');

            function updateFormFields() {
                if (!jenisSelect) return;
                var jenis = jenisSelect.value;

                if (jenis === 'kustom') {
                    // Disable & Readonly Kuantitas / Stok
                    if (statusInput) {
                        statusInput.readOnly = true;
                        statusInput.style.backgroundColor = '#e9ecef';
                        statusInput.style.cursor = 'not-allowed';
                        if (!statusInput.value || statusInput.value === 'Tersedia' || !isNaN(statusInput.value)) {
                            statusInput.value = 'Berdasarkan Pesanan';
                        }
                    }
                    if (stokHelp) {
                        stokHelp.innerText = '*Kuantitas produk kustom dibuat berdasarkan pesanan (tidak dapat diisi).';
                        stokHelp.style.display = 'block';
                        stokHelp.style.color = '#6c757d';
                    }

                    // Price Range Setup
                    singleContainer.style.display = 'none';
                    singleInput.disabled = true;

                    rangeContainer.style.display = 'block';
                    minInput.disabled = false;
                    maxInput.disabled = false;

                    // Disable Tanggal Kedaluwarsa
                    if (expiredInput) {
                        expiredInput.readOnly = true;
                        expiredInput.disabled = true;
                        expiredInput.style.backgroundColor = '#e9ecef';
                        expiredInput.style.cursor = 'not-allowed';
                    }
                    if (expiredHelp) {
                        expiredHelp.innerText = '*Tanggal kedaluwarsa tidak berlaku untuk produk kustom.';
                        expiredHelp.style.display = 'block';
                        expiredHelp.style.color = '#6c757d';
                    }
                } else {
                    // Showcase product
                    if (statusInput) {
                        statusInput.readOnly = false;
                        statusInput.style.backgroundColor = '';
                        statusInput.style.cursor = '';
                        if (statusInput.value === 'Berdasarkan Pesanan') {
                            statusInput.value = 'Tersedia';
                        }
                    }
                    if (stokHelp) {
                        stokHelp.style.display = 'none';
                    }

                    // Single Price Setup
                    rangeContainer.style.display = 'none';
                    minInput.disabled = true;
                    maxInput.disabled = true;

                    singleContainer.style.display = 'block';
                    singleInput.disabled = false;

                    // Enable Tanggal Kedaluwarsa
                    if (expiredInput) {
                        expiredInput.readOnly = false;
                        expiredInput.disabled = false;
                        expiredInput.style.backgroundColor = '';
                        expiredInput.style.cursor = '';
                    }
                    if (expiredHelp) {
                        expiredHelp.style.display = 'none';
                    }
                }
            }

            if (jenisSelect) {
                jenisSelect.addEventListener('change', updateFormFields);
                updateFormFields();
            }
        });

        function previewImage(input, previewId, iconId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var img = document.getElementById(previewId);
                    img.src = e.target.result;
                    img.style.display = 'block';

                    var icon = document.getElementById(iconId);
                    icon.style.position = 'absolute';
                    icon.style.top = '0';
                    icon.style.left = '0';
                    icon.style.width = '100%';
                    icon.style.height = '100%';
                    icon.style.background = 'rgba(255,255,255,0.7)';
                    icon.style.opacity = '0';
                    icon.onmouseover = function() {
                        this.style.opacity = 1;
                    };
                    icon.onmouseout = function() {
                        this.style.opacity = 0;
                    };
                    icon.querySelector('span').innerText = 'Ganti Photo';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endsection

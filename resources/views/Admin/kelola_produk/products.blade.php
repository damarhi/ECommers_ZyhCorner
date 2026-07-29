@extends('Layout.layout_pAdmin')

@section('title', 'Produk - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cube-outline"></i>
                </span> Produk
            </h3>
            <nav aria-label="breadcrumb">
                <ul class="breadcrumb mb-0">
                    <li class="breadcrumb-item active" aria-current="page">
                        <button class="btn btn-outline-secondary btn-sm me-2">Ekspor</button>
                        <a href="{{ route('products.create', ['jenis' => 'showcase']) }}" class="btn btn-gradient-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Tambah Produk
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="row">
            <div class="col-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('products.index') }}"
                            class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3 w-100 w-md-auto">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center justify-content-between w-100"
                                        type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false" style="height: 32px; padding: 0 15px; min-width: 120px;">
                                        <span class="d-flex align-items-center"><i class="mdi mdi-filter-variant me-2"></i>
                                            Filter</span>
                                    </button>
                                    <div class="dropdown-menu p-3 shadow" aria-labelledby="filterDropdown"
                                        style="width: 220px; border-radius: 8px; margin-top: 5px;">
                                        <h6 class="dropdown-header px-0 mb-2 text-primary"
                                            style="font-size: 13px; font-weight: 600;">Status Stok</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="status[]" value="Tersedia"
                                                    class="form-check-input"
                                                    {{ is_array(request('status')) && in_array('Tersedia', request('status')) ? 'checked' : '' }}>
                                                Tersedia
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="status[]" value="Habis"
                                                    class="form-check-input"
                                                    {{ is_array(request('status')) && in_array('Habis', request('status')) ? 'checked' : '' }}>
                                                Habis
                                            </label>
                                        </div>

                                        <div class="dropdown-divider mb-3"></div>

                                        <h6 class="dropdown-header px-0 mb-2 text-primary"
                                            style="font-size: 13px; font-weight: 600;">Fitur Tambahan</h6>
                                        <div class="form-check mb-2">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="diskon" value="1"
                                                    class="form-check-input" {{ request('diskon') ? 'checked' : '' }}>
                                                Sedang Diskon
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <label class="form-check-label text-muted" style="cursor: pointer;">
                                                <input type="checkbox" name="terbaru" value="1"
                                                    class="form-check-input" {{ request('terbaru') ? 'checked' : '' }}>
                                                Produk Terbaru
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-gradient-primary w-100">Terapkan
                                            Filter</button>
                                    </div>
                                </div>
                                <div class="input-group w-100 w-sm-auto" style="max-width: 250px; height: 32px;">
                                    <div class="input-group-prepend" style="height: 32px;">
                                        <span class="input-group-text bg-transparent border-right-0"
                                            style="padding: 0 10px; height: 32px;"><i
                                                class="mdi mdi-magnify text-muted"></i></span>
                                    </div>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                        class="form-control border-left-0" placeholder="Cari..."
                                        style="height: 32px; padding-left: 0;">
                                </div>
                            </div>
                            <button type="button"
                                class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center align-self-end align-self-md-center"
                                style="width: 32px; height: 32px; padding: 0;" title="Hapus Terpilih">
                                <i class="mdi mdi-delete-outline" style="margin: 0; font-size: 16px;"></i>
                            </button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="40">
                                            <div class="form-check">
                                                <label class="form-check-label">
                                                    <input type="checkbox" class="form-check-input">
                                                </label>
                                            </div>
                                        </th>
                                        <th>Produk</th>
                                        <th>Stok Produk</th>
                                        <th>Diskon</th>
                                        <th>Harga Asli</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($produks as $p)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <label class="form-check-label">
                                                        <input type="checkbox" class="form-check-input">
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if ($p->foto_1)
                                                        <img src="{{ asset('storage/' . $p->foto_1) }}"
                                                            alt="{{ $p->nama_produk }}" class="me-3"
                                                            style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                    @else
                                                        <div class="me-3 d-flex align-items-center justify-content-center bg-secondary text-white"
                                                            style="width: 50px; height: 50px; border-radius: 8px;">
                                                            <i class="mdi mdi-image text-white"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $p->nama_produk }}</h6>
                                                        <small
                                                            class="text-muted">{{ $p->kategori ? $p->kategori->nama_kategori : 'Tanpa Kategori' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($p->status_produk == 'Tersedia' || $p->status_produk == 'Available')
                                                    <label
                                                        class="badge badge-soft-success">{{ $p->status_produk }}</label>
                                                @else
                                                    <label
                                                        class="badge badge-soft-warning">{{ $p->status_produk }}</label>
                                                @endif
                                            </td>
                                            <td>-</td>
                                            <td>Rp. {{ number_format($p->harga, 0, ',', '.') }}</td>
                                            <td>
                                                <a href="{{ route('products.edit', $p->id_produk) }}"
                                                    class="btn btn-sm btn-inverse-info d-inline-flex align-items-center justify-content-center me-1"
                                                    style="width: 32px; height: 32px; padding: 0;" title="Edit">
                                                    <i class="mdi mdi-pencil" style="margin: 0; font-size: 16px;"></i>
                                                </a>
                                                <form action="{{ route('products.destroy', $p->id_produk) }}"
                                                    method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="btn btn-sm btn-inverse-danger d-inline-flex align-items-center justify-content-center"
                                                        style="width: 32px; height: 32px; padding: 0;" title="Hapus">
                                                        <i class="mdi mdi-delete" style="margin: 0; font-size: 16px;"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center justify-content-center">
                                                    <i class="mdi mdi-cube-outline text-muted mb-2"
                                                        style="font-size: 64px;"></i>
                                                    <h5 class="text-muted font-weight-bold">Belum Ada Produk</h5>
                                                    <p class="text-muted mb-0" style="font-size: 14px;">Tambahkan produk
                                                        pertama Anda sekarang!</p>
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
                                {{ $produks->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                            <div class="text-muted text-center text-md-end w-100 w-md-auto" style="font-size: 14px;">
                                {{ $produks->total() }} Hasil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

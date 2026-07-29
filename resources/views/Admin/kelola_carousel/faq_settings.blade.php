@extends('Layout.layout_pAdmin')

@section('title', 'Kelola FAQ - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h3 class="page-title m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-help-circle-outline"></i>
                </span> Kelola FAQ Pelanggan
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('faq') }}" target="_blank" class="btn btn-gradient-info btn-sm d-flex align-items-center justify-content-center px-3 py-2">
                    <i class="mdi mdi-eye me-1 font-size-16"></i> Lihat Halaman FAQ Pelanggan
                </a>
                <button type="button" class="btn btn-gradient-primary btn-sm d-flex align-items-center justify-content-center px-3 py-2"
                    data-bs-toggle="modal" data-bs-target="#addFaqModal">
                    <i class="mdi mdi-plus me-1 font-size-16"></i> Tambah FAQ Baru
                </button>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title font-weight-bold text-dark m-0">
                                <i class="mdi mdi-format-list-bulleted text-primary me-1"></i> Daftar Pertanyaan & Jawaban FAQ
                            </h4>
                            <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill font-weight-bold">
                                Total: {{ count($faqs) }} FAQ
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th style="min-width: 200px;">Pertanyaan</th>
                                        <th style="min-width: 250px;">Jawaban</th>
                                        <th style="width: 120px;">Kategori</th>
                                        <th style="width: 80px;" class="text-center">Urutan</th>
                                        <th style="width: 100px;" class="text-center">Status</th>
                                        <th style="width: 150px;" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($faqs as $index => $faq)
                                        <tr>
                                            <td class="font-weight-bold text-muted">{{ $index + 1 }}</td>
                                            <td>
                                                <span class="font-weight-bold text-dark d-block">{{ $faq->pertanyaan }}</span>
                                            </td>
                                            <td>
                                                <small class="text-muted text-wrap" style="max-width: 300px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                    {{ $faq->jawaban }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1 font-weight-bold" style="font-size: 11px;">
                                                    {{ $faq->kategori }}
                                                </span>
                                            </td>
                                            <td class="text-center font-weight-bold">{{ $faq->urutan }}</td>
                                            <td class="text-center">
                                                <form action="{{ route('admin.faq.toggle', $faq->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm border-0 p-0" title="Klik untuk mengubah status">
                                                        @if($faq->is_active)
                                                            <span class="badge bg-success text-white px-2 py-1" style="font-size: 11px;">Aktif</span>
                                                        @else
                                                            <span class="badge bg-secondary text-white px-2 py-1" style="font-size: 11px;">Nonaktif</span>
                                                        @endif
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <button type="button" class="btn btn-inverse-warning btn-icon btn-sm" 
                                                            data-bs-toggle="modal" data-bs-target="#editFaqModal{{ $faq->id }}" title="Edit FAQ">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('admin.faq.destroy', $faq->id) }}" method="POST" 
                                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus FAQ ini?');" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-inverse-danger btn-icon btn-sm" title="Hapus FAQ">
                                                            <i class="mdi mdi-delete"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal Edit FAQ -->
                                        <div class="modal fade" id="editFaqModal{{ $faq->id }}" tabindex="-1" aria-labelledby="editFaqModalLabel{{ $faq->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content border-0 shadow">
                                                    <div class="modal-header bg-gradient-primary text-white">
                                                        <h5 class="modal-title font-weight-bold" id="editFaqModalLabel{{ $faq->id }}">
                                                            <i class="mdi mdi-pencil me-1"></i> Edit FAQ #{{ $faq->id }}
                                                        </h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="{{ route('admin.faq.update', $faq->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body p-4">
                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold text-dark mb-1">Pertanyaan <span class="text-danger">*</span></label>
                                                                <textarea name="pertanyaan" class="form-control" rows="2" required placeholder="Tuliskan pertanyaan...">{{ old('pertanyaan', $faq->pertanyaan) }}</textarea>
                                                            </div>

                                                            <div class="form-group mb-3">
                                                                <label class="font-weight-bold text-dark mb-1">Jawaban <span class="text-danger">*</span></label>
                                                                <textarea name="jawaban" class="form-control" rows="4" required placeholder="Tuliskan penjelasan jawaban secara lengkap...">{{ old('jawaban', $faq->jawaban) }}</textarea>
                                                            </div>

                                                            <div class="row">
                                                                <div class="col-md-7">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold text-dark mb-1">Kategori <span class="text-danger">*</span></label>
                                                                        <select name="kategori" class="form-select form-select-sm" required>
                                                                            <option value="Pemesanan" {{ old('kategori', $faq->kategori) == 'Pemesanan' ? 'selected' : '' }}>Pemesanan</option>
                                                                            <option value="Pengiriman" {{ old('kategori', $faq->kategori) == 'Pengiriman' ? 'selected' : '' }}>Pengiriman</option>
                                                                            <option value="Pembayaran" {{ old('kategori', $faq->kategori) == 'Pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                                                                            <option value="Produk" {{ old('kategori', $faq->kategori) == 'Produk' ? 'selected' : '' }}>Produk</option>
                                                                            <option value="Umum" {{ old('kategori', $faq->kategori) == 'Umum' ? 'selected' : '' }}>Umum</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-5">
                                                                    <div class="form-group mb-3">
                                                                        <label class="font-weight-bold text-dark mb-1">Urutan Tampil</label>
                                                                        <input type="number" name="urutan" class="form-control form-control-sm" value="{{ old('urutan', $faq->urutan) }}" min="0">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer bg-light">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-gradient-primary btn-sm font-weight-bold">
                                                                <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="mdi mdi-help-circle-outline font-size-48 d-block mb-2 text-secondary"></i>
                                                Belum ada data FAQ yang ditambahkan. Klik tombol <strong>+ Tambah FAQ Baru</strong> di atas untuk membuat FAQ baru.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah FAQ Baru -->
    <div class="modal fade" id="addFaqModal" tabindex="-1" aria-labelledby="addFaqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title font-weight-bold" id="addFaqModalLabel">
                        <i class="mdi mdi-plus-circle me-1"></i> Tambah FAQ Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.faq.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">Pertanyaan <span class="text-danger">*</span></label>
                            <textarea name="pertanyaan" class="form-control" rows="2" required placeholder="Contoh: Bagaimana cara memesan kue kustom?">{{ old('pertanyaan') }}</textarea>
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">Jawaban <span class="text-danger">*</span></label>
                            <textarea name="jawaban" class="form-control" rows="4" required placeholder="Tuliskan rincian jawaban lengkap bagi pelanggan...">{{ old('jawaban') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-7">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1">Kategori <span class="text-danger">*</span></label>
                                    <select name="kategori" class="form-select form-select-sm" required>
                                        <option value="Pemesanan" {{ old('kategori') == 'Pemesanan' ? 'selected' : '' }}>Pemesanan</option>
                                        <option value="Pengiriman" {{ old('kategori') == 'Pengiriman' ? 'selected' : '' }}>Pengiriman</option>
                                        <option value="Pembayaran" {{ old('kategori') == 'Pembayaran' ? 'selected' : '' }}>Pembayaran</option>
                                        <option value="Produk" {{ old('kategori') == 'Produk' ? 'selected' : '' }}>Produk</option>
                                        <option value="Umum" {{ old('kategori') == 'Umum' ? 'selected' : '' }}>Umum</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-3">
                                    <label class="font-weight-bold text-dark mb-1">Urutan Tampil</label>
                                    <input type="number" name="urutan" class="form-control form-control-sm" value="{{ old('urutan', count($faqs) + 1) }}" min="0">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient-primary btn-sm font-weight-bold">
                            <i class="mdi mdi-check me-1"></i> Simpan FAQ Baru
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

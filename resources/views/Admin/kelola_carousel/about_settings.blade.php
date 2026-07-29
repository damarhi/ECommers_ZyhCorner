@extends('Layout.layout_pAdmin')

@section('title', 'Pengaturan About Us - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h3 class="page-title m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-information-outline"></i>
                </span> Kelola Halaman About Us
            </h3>
            <a href="{{ route('about') }}" target="_blank" class="btn btn-gradient-info btn-sm d-inline-flex align-items-center">
                <i class="mdi mdi-eye me-1"></i> Lihat Halaman Pelanggan
            </a>
        </div>

        <div class="row mt-4">
            <div class="col-12 grid-margin stretch-card">
                <div class="card border-0 shadow-sm" style="border-radius: 14px;">
                    <div class="card-body p-4">
                        
                        <form action="{{ route('admin.about.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <!-- Tab Navigations -->
                            <ul class="nav nav-pills nav-justified bg-light p-2 mb-4 rounded-3 gap-2 flex-wrap" id="aboutTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active font-weight-bold py-2" id="utama-tab" data-bs-toggle="tab" data-bs-target="#utama" type="button" role="tab">
                                        <i class="mdi mdi-storefront me-1"></i> Utama & Intro
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold py-2" id="fitur-tab" data-bs-toggle="tab" data-bs-target="#fitur" type="button" role="tab">
                                        <i class="mdi mdi-target me-1"></i> Tujuan, Visi & Misi
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold py-2" id="delivery-tab" data-bs-toggle="tab" data-bs-target="#delivery" type="button" role="tab">
                                        <i class="mdi mdi-truck-delivery me-1"></i> Pengiriman & Promo
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link font-weight-bold py-2" id="timtesti-tab" data-bs-toggle="tab" data-bs-target="#timtesti" type="button" role="tab">
                                        <i class="mdi mdi-account-group me-1"></i> Tim Kami
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="aboutTabContent">

                                <!-- TAB 1: UTAMA & INTRO (SECTION 1) -->
                                <div class="tab-pane fade show active p-2" id="utama" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Judul Utama Hero (Section 1) <span class="text-danger">*</span></label>
                                                <input type="text" name="judul_utama" class="form-control form-control-sm" value="{{ old('judul_utama', $about->judul_utama) }}" required placeholder="Contoh: 100% Trusted Organic Food Store">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Subjudul / Slogan</label>
                                                <input type="text" name="subjudul_utama" class="form-control form-control-sm" value="{{ old('subjudul_utama', $about->subjudul_utama) }}" placeholder="Contoh: Menghadirkan Cita Rasa Terbaik Sejak 2020">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Cerita Singkat / Deskripsi Hero</label>
                                                <textarea name="cerita_singkat" class="form-control" rows="5" placeholder="Jelaskan sejarah singkat, konsep usaha, atau filosofi toko Anda...">{{ old('cerita_singkat', $about->cerita_singkat) }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Gambar Hero Right (Section 1)</label>
                                                <div class="p-3 border rounded text-center bg-light mb-2">
                                                    @if($about->gambar_tentang && Storage::disk('public')->exists($about->gambar_tentang))
                                                        <img src="{{ asset('storage/' . $about->gambar_tentang) }}" alt="Gambar Utama" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px; object-fit: cover;">
                                                    @else
                                                        <img src="{{ asset('image/about_cake_coffee.png') }}" alt="Gambar Utama Default" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px; object-fit: cover;">
                                                        <p class="mb-0 small text-muted">(Menggunakan Gambar Bawaan: Cake & Coffee)</p>
                                                    @endif
                                                </div>
                                                <input type="file" name="gambar_tentang" class="form-control form-control-sm" accept="image/*">
                                                <small class="text-muted">Format: JPG, PNG, WEBP (Maks: 3MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 2: TUJUAN, VISI & MISI (SECTION 2) -->
                                <div class="tab-pane fade p-2" id="fitur" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Judul Section 2</label>
                                                <input type="text" name="judul_fitur" class="form-control form-control-sm" value="{{ old('judul_fitur', $about->judul_fitur ?? 'Tujuan, Visi & Misi Kami') }}" placeholder="Contoh: Tujuan, Visi & Misi Kami">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Tujuan Usaha</label>
                                                <textarea name="deskripsi_fitur" class="form-control" rows="3" placeholder="Tuliskan tujuan usaha toko Anda...">{{ old('deskripsi_fitur', $about->deskripsi_fitur) }}</textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-primary mb-1"><i class="mdi mdi-eye-outline me-1"></i> Visi Usaha</label>
                                                <textarea name="visi" class="form-control" rows="3" placeholder="Tuliskan visi jangka panjang toko Anda...">{{ old('visi', $about->visi) }}</textarea>
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-success mb-1"><i class="mdi mdi-format-list-bulleted me-1"></i> Misi Usaha</label>
                                                <textarea name="misi" class="form-control" rows="4" placeholder="Tuliskan poin-poin misi toko Anda...">{{ old('misi', $about->misi) }}</textarea>
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Gambar Showcase Left (Section 2)</label>
                                                <div class="p-3 border rounded text-center bg-light mb-2">
                                                    @if($about->foto_galeri_1 && Storage::disk('public')->exists($about->foto_galeri_1))
                                                        <img src="{{ asset('storage/' . $about->foto_galeri_1) }}" alt="Showcase" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px; object-fit: cover;">
                                                    @else
                                                        <img src="{{ asset('image/about_cake_showcase.png') }}" alt="Showcase Default" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px; object-fit: cover;">
                                                        <p class="mb-0 small text-muted">(Menggunakan Gambar Bawaan: Showcase Cake Display)</p>
                                                    @endif
                                                </div>
                                                <input type="file" name="foto_galeri_1" class="form-control form-control-sm" accept="image/*">
                                                <small class="text-muted">Format: JPG, PNG, WEBP (Maks: 3MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 3: PENGIRIMAN & PROMO (SECTION 3) -->
                                <div class="tab-pane fade p-2" id="delivery" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Judul Section Pengiriman (Section 3)</label>
                                                <input type="text" name="judul_delivery" class="form-control form-control-sm" value="{{ old('judul_delivery', $about->judul_delivery ?? 'We Delivered, You Enjoy Your Order.') }}" placeholder="Contoh: We Delivered, You Enjoy Your Order.">
                                            </div>

                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Deskripsi Layanan Pengiriman</label>
                                                <textarea name="deskripsi_delivery" class="form-control" rows="3" placeholder="Tuliskan informasi penanganan pengiriman toko Anda...">{{ old('deskripsi_delivery', $about->deskripsi_delivery) }}</textarea>
                                            </div>

                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold text-dark mb-1">Poin Keunggulan Pengiriman #1</label>
                                                <input type="text" name="poin_delivery_1" class="form-control form-control-sm" value="{{ old('poin_delivery_1', $about->poin_delivery_1 ?? 'Pengiriman cepat & aman dengan kurir khusus makanan') }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="font-weight-bold text-dark mb-1">Poin Keunggulan Pengiriman #2</label>
                                                <input type="text" name="poin_delivery_2" class="form-control form-control-sm" value="{{ old('poin_delivery_2', $about->poin_delivery_2 ?? 'Kemasan kokoh, higienis, dan ramah lingkungan') }}">
                                            </div>
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Poin Keunggulan Pengiriman #3</label>
                                                <input type="text" name="poin_delivery_3" class="form-control form-control-sm" value="{{ old('poin_delivery_3', $about->poin_delivery_3 ?? 'Garansi penggantian penuh jika ada cacat dalam pengiriman') }}">
                                            </div>
                                        </div>

                                        <div class="col-md-5">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold text-dark mb-1">Gambar Storefront Right (Section 3)</label>
                                                <div class="p-3 border rounded text-center bg-light mb-2">
                                                    @if($about->foto_galeri_2 && Storage::disk('public')->exists($about->foto_galeri_2))
                                                        <img src="{{ asset('storage/' . $about->foto_galeri_2) }}" alt="Storefront" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px; object-fit: cover;">
                                                    @else
                                                        <img src="{{ asset('image/about_store_front.png') }}" alt="Storefront Default" class="img-fluid rounded shadow-sm mb-2" style="max-height: 180px; object-fit: cover;">
                                                        <p class="mb-0 small text-muted">(Menggunakan Gambar Bawaan: Zyh Corner Storefront)</p>
                                                    @endif
                                                </div>
                                                <input type="file" name="foto_galeri_2" class="form-control form-control-sm" accept="image/*">
                                                <small class="text-muted">Format: JPG, PNG, WEBP (Maks: 3MB)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- TAB 4: TIM KAMI (SECTION 4) -->
                                <div class="tab-pane fade p-2" id="timtesti" role="tabpanel">
                                    <div class="row mb-4">
                                        <div class="col-md-12">
                                            <div class="card p-3 border-0 bg-light mb-4">
                                                <h5 class="font-weight-bold text-dark mb-3"><i class="mdi mdi-account-group text-primary me-1"></i> Pengaturan Section Tim (Section 4)</h5>
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark mb-1">Judul Section Tim</label>
                                                    <input type="text" name="judul_tim" class="form-control form-control-sm" value="{{ old('judul_tim', $about->judul_tim ?? 'Our Awesome Team') }}" placeholder="Contoh: Our Awesome Team">
                                                </div>
                                                <div class="form-group mb-0">
                                                    <label class="font-weight-bold text-dark mb-1">Deskripsi Subjudul Tim</label>
                                                    <textarea name="deskripsi_tim" class="form-control" rows="3" placeholder="Tuliskan keterangan tim pastry Anda...">{{ old('deskripsi_tim', $about->deskripsi_tim) }}</textarea>
                                                </div>
                                            </div>

                                            <div class="card p-3 border shadow-sm">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h5 class="font-weight-bold text-dark m-0"><i class="mdi mdi-account-multiple-outline text-success me-1"></i> Daftar Anggota Tim</h5>
                                                    <button type="button" class="btn btn-gradient-success btn-sm font-weight-bold" data-bs-toggle="modal" data-bs-target="#modalTambahTeam">
                                                        <i class="mdi mdi-account-plus me-1"></i> Tambah Anggota Tim
                                                    </button>
                                                </div>

                                                <div class="table-responsive">
                                                    <table class="table table-hover align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 70px;">Foto</th>
                                                                <th>Nama</th>
                                                                <th>Jabatan / Peran</th>
                                                                <th style="width: 90px;" class="text-center">Urutan</th>
                                                                <th style="width: 140px;" class="text-center">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($teamMembers as $item)
                                                                <tr>
                                                                    <td>
                                                                        @if($item->foto && Storage::disk('public')->exists($item->foto))
                                                                            <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                                        @else
                                                                            <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=150&auto=format&fit=crop" alt="{{ $item->nama }}" class="rounded-circle shadow-sm" style="width: 44px; height: 44px; object-fit: cover;">
                                                                        @endif
                                                                    </td>
                                                                    <td class="font-weight-bold text-dark">{{ $item->nama }}</td>
                                                                    <td><span class="badge bg-light text-dark border">{{ $item->jabatan }}</span></td>
                                                                    <td class="text-center font-weight-bold">{{ $item->urutan }}</td>
                                                                    <td class="text-center">
                                                                        <button type="button" class="btn btn-gradient-warning btn-xs px-2 py-1 me-1" data-bs-toggle="modal" data-bs-target="#modalEditTeam{{ $item->id }}" title="Edit">
                                                                            <i class="mdi mdi-pencil"></i>
                                                                        </button>
                                                                        <button type="button" class="btn btn-gradient-danger btn-xs px-2 py-1" onclick="if(confirm('Yakin ingin menghapus {{ $item->nama }} dari tim?')) { document.getElementById('form-delete-team-{{ $item->id }}').submit(); }" title="Hapus">
                                                                            <i class="mdi mdi-delete"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="5" class="text-center py-4 text-muted">
                                                                        Belum ada anggota tim khusus. (Menampilkan 4 anggota tim bawaan di halaman pelanggan).
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

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-gradient-primary px-4 py-2 font-weight-bold">
                                    <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan About Us
                                </button>
                            </div>

                        </form>

                        <!-- Hidden Forms for Delete Actions -->
                        @foreach($teamMembers as $item)
                            <form id="form-delete-team-{{ $item->id }}" action="{{ route('admin.about.team.destroy', $item->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH ANGGOTA TIM -->
    <div class="modal fade" id="modalTambahTeam" tabindex="-1" aria-labelledby="modalTambahTeamLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.about.team.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-gradient-success text-white">
                        <h5 class="modal-header-title m-0 text-white font-weight-bold" id="modalTambahTeamLabel">
                            <i class="mdi mdi-account-plus me-1"></i> Tambah Anggota Tim Baru
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">Nama Anggota <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control form-control-sm" required placeholder="Contoh: Jenny Wilson">
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">Jabatan / Peran <span class="text-danger">*</span></label>
                            <input type="text" name="jabatan" class="form-control form-control-sm" required placeholder="Contoh: Head Baker / Pastry Chef">
                        </div>

                        <div class="form-group mb-3">
                            <label class="font-weight-bold text-dark mb-1">Foto Profil Anggota</label>
                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, WEBP (Maks: 3MB)</small>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-dark mb-1">Urutan Tampil</label>
                            <input type="number" name="urutan" class="form-control form-control-sm" value="0" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-gradient-success btn-sm font-weight-bold">
                            <i class="mdi mdi-check me-1"></i> Simpan Anggota Tim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT ANGGOTA TIM -->
    @foreach($teamMembers as $item)
        <div class="modal fade" id="modalEditTeam{{ $item->id }}" tabindex="-1" aria-labelledby="modalEditTeamLabel{{ $item->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('admin.about.team.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-header bg-gradient-warning text-white">
                            <h5 class="modal-header-title m-0 text-white font-weight-bold" id="modalEditTeamLabel{{ $item->id }}">
                                <i class="mdi mdi-pencil me-1"></i> Edit Anggota Tim: {{ $item->nama }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="text-center mb-3">
                                @if($item->foto && Storage::disk('public')->exists($item->foto))
                                    <img src="{{ asset('storage/' . $item->foto) }}" alt="{{ $item->nama }}" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                                @else
                                    <img src="https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?q=80&w=150&auto=format&fit=crop" alt="{{ $item->nama }}" class="rounded-circle shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                                @endif
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1">Nama Anggota <span class="text-danger">*</span></label>
                                <input type="text" name="nama" class="form-control form-control-sm" value="{{ old('nama', $item->nama) }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1">Jabatan / Peran <span class="text-danger">*</span></label>
                                <input type="text" name="jabatan" class="form-control form-control-sm" value="{{ old('jabatan', $item->jabatan) }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-dark mb-1">Ganti Foto Profil (Opsional)</label>
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengganti foto.</small>
                            </div>

                            <div class="form-group mb-0">
                                <label class="font-weight-bold text-dark mb-1">Urutan Tampil</label>
                                <input type="number" name="urutan" class="form-control form-control-sm" value="{{ old('urutan', $item->urutan) }}" min="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-gradient-warning btn-sm font-weight-bold text-white">
                                <i class="mdi mdi-check me-1"></i> Perbarui Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection

@extends('Layout.layout_pAdmin')

@section('title', 'Kelola Halaman Contact Us - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <h3 class="page-title m-0">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-email-outline"></i>
                </span> Kelola Halaman Contact Us
            </h3>
            <a href="{{ route('contact') }}" target="_blank" class="btn btn-gradient-info btn-sm d-flex align-items-center justify-content-center px-3 py-2">
                <i class="mdi mdi-eye me-1 font-size-16"></i> Lihat Halaman Contact Pelanggan
            </a>
        </div>

        <div class="row mt-4">
            <div class="col-12 grid-margin stretch-card">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h4 class="card-title font-weight-bold text-dark mb-4">
                            <i class="mdi mdi-square-edit-outline text-primary me-1"></i> Form Pengaturan Halaman Contact Us
                        </h4>

                        <form action="{{ route('admin.contact.update') }}" method="POST">
                            @csrf

                            <div class="row">
                                <!-- COLUMN LEFT: ALAMAT & KONTAK -->
                                <div class="col-md-6">
                                    <div class="card p-3 border-0 bg-light mb-4">
                                        <h5 class="font-weight-bold text-dark mb-3">
                                            <i class="mdi mdi-card-text-outline text-primary me-1"></i> Detail Informasi Kontak
                                        </h5>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark mb-1">Alamat Toko <span class="text-danger">*</span></label>
                                            <textarea name="alamat_toko" class="form-control" rows="3" required placeholder="Contoh: Jl. Teropong, Sidomulyo Barat, Kec. Tampan, Kota Pekanbaru, Riau 28294">{{ old('alamat_toko', $contact->alamat_toko) }}</textarea>
                                            <small class="text-muted">Alamat fisik toko yang ditampilkan di kartu kontak.</small>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark mb-1">Email Utama <span class="text-danger">*</span></label>
                                                    <input type="email" name="email" class="form-control form-control-sm" value="{{ old('email', $contact->email) }}" required placeholder="zyhcorner@gmail.com">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark mb-1">Email Sekunder (Opsional)</label>
                                                    <input type="email" name="email_2" class="form-control form-control-sm" value="{{ old('email_2', $contact->email_2 ?? 'support@zyhcorner.com') }}" placeholder="support@zyhcorner.com">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark mb-1">Telepon Utama <span class="text-danger">*</span></label>
                                                    <input type="text" name="telepon" class="form-control form-control-sm" value="{{ old('telepon', $contact->telepon) }}" required placeholder="(+62) 812-3456-789">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold text-dark mb-1">Telepon Sekunder (Opsional)</label>
                                                    <input type="text" name="telepon_2" class="form-control form-control-sm" value="{{ old('telepon_2', $contact->telepon_2 ?? '(+62) 811-7654-321') }}" placeholder="(+62) 811-7654-321">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- COLUMN RIGHT: FORM HEADER & MAPS EMBED -->
                                <div class="col-md-6">
                                    <div class="card p-3 border-0 bg-light mb-4">
                                        <h5 class="font-weight-bold text-dark mb-3">
                                            <i class="mdi mdi-message-text-outline text-success me-1"></i> Pengaturan Header Form Pesan
                                        </h5>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark mb-1">Judul Form Pesan</label>
                                            <input type="text" name="judul_form_kontak" class="form-control form-control-sm" value="{{ old('judul_form_kontak', $contact->judul_form_kontak ?? 'Just Say Hello!') }}" placeholder="Contoh: Just Say Hello!">
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="font-weight-bold text-dark mb-1">Deskripsi Subjudul Form</label>
                                            <textarea name="deskripsi_form_kontak" class="form-control" rows="3" placeholder="Tuliskan kata sambutan bagi pelanggan yang ingin mengirim pesan...">{{ old('deskripsi_form_kontak', $contact->deskripsi_form_kontak ?? 'Apakah Anda ingin menyapa kami atau memesan kue khusus untuk hari bahagia Anda? Jangan ragu untuk mengirimkan pesan!') }}</textarea>
                                        </div>
                                    </div>

                                    <div class="card p-3 border-0 bg-light mb-4">
                                        <h5 class="font-weight-bold text-dark mb-3">
                                            <i class="mdi mdi-map-search-outline text-warning me-1"></i> Pengaturan Peta Google Maps
                                        </h5>

                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark mb-1">Link Embed Google Maps (URL / src)</label>
                                            <textarea name="link_maps" class="form-control form-control-sm" rows="3" placeholder="Paste URL embed Google Maps (https://www.google.com/maps/embed?...)">{{ old('link_maps', $contact->link_maps) }}</textarea>
                                            <small class="text-muted">Salin URL dari menu <i>Share > Embed a map</i> pada Google Maps.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PREVIEW PETA GOOGLE MAPS -->
                            <div class="card p-3 border mb-4">
                                <h5 class="font-weight-bold text-dark mb-3"><i class="mdi mdi-map text-info me-1"></i> Preview Peta Lokasi Toko</h5>
                                <div class="rounded overflow-hidden border" style="height: 280px;">
                                    @if($contact->link_maps)
                                        <iframe src="{{ $contact->link_maps }}" width="100%" height="280" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                                    @else
                                        <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">
                                            <span>Link Google Maps Embed belum diatur.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex justify-content-end">
                                <button type="submit" class="btn btn-gradient-primary px-4 py-2 font-weight-bold">
                                    <i class="mdi mdi-content-save me-1"></i> Simpan Perubahan Contact Us
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

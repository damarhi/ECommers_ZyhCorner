@extends('Layout.layout_pAdmin')

@section('title', 'Pengaturan Admin - Dasbor Admin')

@section('content')
    <div class="content-wrapper">
        <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h3 class="page-title m-0 d-flex align-items-center justify-content-center justify-content-md-start">
                <span class="page-title-icon bg-gradient-primary text-white me-2">
                    <i class="mdi mdi-cog-outline"></i>
                </span> Pengaturan Admin & Toko
            </h3>
        </div>

        <div class="row">
            <!-- Left Column: Admin Profile Card Overview -->
            <div class="col-md-4 grid-margin stretch-card">
                <div class="card border-0 shadow-sm text-center p-4" style="border-radius: 8px; background: #ffffff;">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        <div class="position-relative mb-3">
                            @if($admin->foto)
                                <img src="{{ asset('storage/' . $admin->foto) }}" alt="{{ $admin->nama }}"
                                    class="rounded-circle shadow-sm" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #4F46E5;">
                            @else
                                <div class="rounded-circle bg-gradient-primary text-white d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 100px; height: 100px; font-size: 38px; font-weight: 700;">
                                    {{ strtoupper(substr($admin->nama, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <h5 class="font-weight-bold text-dark mb-1" style="font-size: 18px;">{{ $admin->nama }}</h5>
                        <p class="text-muted mb-2" style="font-size: 13px;">{{ $admin->email }}</p>
                        <span class="badge badge-gradient-primary px-3 py-2" style="border-radius: 20px; font-size: 12px;">
                            <i class="mdi mdi-shield-check me-1"></i> Administrator
                        </span>

                        <hr class="w-100 my-4" style="border-color: #f1f5f9;">

                        <div class="w-100 text-start">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted" style="font-size: 13px;"><i class="mdi mdi-phone me-2 text-primary"></i>No. HP</span>
                                <span class="font-weight-500 text-dark" style="font-size: 13px;">{{ $admin->no_hp ?? '-' }}</span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="text-muted" style="font-size: 13px;"><i class="mdi mdi-calendar me-2 text-primary"></i>Bergabung</span>
                                <span class="font-weight-500 text-dark" style="font-size: 13px;">{{ $admin->created_at ? $admin->created_at->format('d M Y') : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Settings Tabs -->
            <div class="col-md-8 grid-margin stretch-card">
                <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #ffffff;">
                    <div class="card-body p-4">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-pills nav-fill gap-2 mb-4" id="settingsTab" role="tablist" style="background: #f8f9fa; padding: 6px; border-radius: 10px;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active font-weight-500 py-2" id="profile-tab-btn" data-bs-toggle="tab" data-bs-target="#profile-tab" type="button" role="tab" aria-controls="profile-tab" aria-selected="true" style="border-radius: 8px; font-size: 13.5px;">
                                    <i class="mdi mdi-account-edit me-1"></i> Profil Saya
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link font-weight-500 py-2" id="password-tab-btn" data-bs-toggle="tab" data-bs-target="#password-tab" type="button" role="tab" aria-controls="password-tab" aria-selected="false" style="border-radius: 8px; font-size: 13.5px;">
                                    <i class="mdi mdi-lock-reset me-1"></i> Keamanan & Password
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link font-weight-500 py-2" id="store-tab-btn" data-bs-toggle="tab" data-bs-target="#store-tab" type="button" role="tab" aria-controls="store-tab" aria-selected="false" style="border-radius: 8px; font-size: 13.5px;">
                                    <i class="mdi mdi-storefront me-1"></i> Info Toko
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="settingsTabContent">
                            <!-- Tab 1: Edit Profile -->
                            <div class="tab-pane fade show active" id="profile-tab" role="tabpanel" aria-labelledby="profile-tab-btn">
                                <form action="{{ route('settings.profile.update') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label class="font-weight-500 mb-1">Nama Lengkap</label>
                                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $admin->nama) }}" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">Email</label>
                                            <input type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">No. HP / WhatsApp</label>
                                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $admin->no_hp) }}" placeholder="Contoh: 08123456789">
                                        </div>
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="font-weight-500 mb-1">Foto Profil (Opsional)</label>
                                        <input type="file" name="foto" class="form-control" accept="image/*">
                                        <small class="form-text text-muted mt-1" style="font-size: 11px;">Format yang didukung: JPG, PNG, JPEG (Maks. 4MB).</small>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-gradient-primary px-4">
                                            <i class="mdi mdi-content-save me-1"></i> Simpan Profil
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab 2: Password Security -->
                            <div class="tab-pane fade" id="password-tab" role="tabpanel" aria-labelledby="password-tab-btn">
                                <form action="{{ route('settings.password.update') }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label class="font-weight-500 mb-1">Password Saat Ini</label>
                                        <input type="password" name="current_password" class="form-control" placeholder="Masukkan password saat ini" required>
                                        @error('current_password')
                                            <span class="text-danger mt-1 d-block" style="font-size: 12px;">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">Password Baru</label>
                                            <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">Konfirmasi Password Baru</label>
                                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="Ulangi password baru" required>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="submit" class="btn btn-gradient-primary px-4">
                                            <i class="mdi mdi-key-change me-1"></i> Perbarui Password
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab 3: Store Information -->
                            <div class="tab-pane fade" id="store-tab" role="tabpanel" aria-labelledby="store-tab-btn">
                                <form action="{{ route('settings.store.update') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">Nama Toko</label>
                                            <input type="text" name="store_name" class="form-control" value="{{ old('store_name', $storeInfo['name']) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">Slogan / Tagline Toko</label>
                                            <input type="text" name="store_tagline" class="form-control" value="{{ old('store_tagline', $storeInfo['tagline'] ?? '') }}" placeholder="Contoh: Pastry & Bakery Berkualitas">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">No. WhatsApp Toko</label>
                                            <input type="text" name="store_phone" class="form-control" value="{{ old('store_phone', $storeInfo['phone']) }}" placeholder="Contoh: 08123456789" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="font-weight-500 mb-1">Email Layanan Pelanggan</label>
                                            <input type="email" name="store_email" class="form-control" value="{{ old('store_email', $storeInfo['email']) }}" placeholder="Contoh: contact@zyhcorner.com" required>
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="font-weight-500 mb-1">Jam Operasional Toko</label>
                                        <input type="text" name="store_hours" class="form-control" value="{{ old('store_hours', $storeInfo['operating_hours']) }}" placeholder="Contoh: Senin - Minggu (08:00 - 21:00)">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="font-weight-500 mb-1">Alamat Lengkap Toko</label>
                                        <textarea name="store_address" class="form-control" rows="3" required>{{ old('store_address', $storeInfo['address']) }}</textarea>
                                    </div>

                                    <!-- Bank Account for Manual Bank Transfer -->
                                    <div class="card mb-4 border-0 shadow-sm" style="background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 10px;">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center mb-3">
                                                <div class="me-2 d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; background: rgba(30, 39, 83, 0.1); color: #1E2753;">
                                                    <i class="mdi mdi-bank" style="font-size: 18px;"></i>
                                                </div>
                                                <div>
                                                    <h6 class="font-weight-bold text-dark mb-0" style="font-size: 14px;">Rekening Bank Toko (Transfer Manual)</h6>
                                                    <small class="text-muted" style="font-size: 11.5px;">Nomor rekening ini akan ditampilkan kepada pelanggan saat memilih metode Transfer Bank.</small>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-500 mb-1" style="font-size: 12.5px;">Nama Bank</label>
                                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $storeInfo['bank_name'] ?? 'Bank Central Asia (BCA)') }}" placeholder="Contoh: Bank Central Asia (BCA)" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-500 mb-1" style="font-size: 12.5px;">Nomor Rekening</label>
                                                    <input type="text" name="bank_account_number" class="form-control font-monospace" value="{{ old('bank_account_number', $storeInfo['bank_account_number'] ?? '8295-0192-38') }}" placeholder="Contoh: 8295019238" pattern="[0-9\-\s]+" title="Hanya angka, strip, dan spasi" required>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="font-weight-500 mb-1" style="font-size: 12.5px;">Atas Nama (Pemilik Rekening)</label>
                                                    <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $storeInfo['bank_account_holder'] ?? 'ZYH CORNER PASTRY') }}" placeholder="Contoh: ZYH CORNER PASTRY" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="font-weight-bold text-dark mt-4 mb-3" style="font-size: 14px;">
                                        <i class="mdi mdi-share-variant me-1 text-primary"></i> Media Sosial Toko (Opsional)
                                    </h6>
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <label class="font-weight-500 mb-1" style="font-size: 12.5px;"><i class="mdi mdi-instagram text-danger me-1"></i>Instagram URL</label>
                                            <input type="url" name="social_instagram" class="form-control" value="{{ old('social_instagram', $storeInfo['social_instagram'] ?? '') }}" placeholder="https://instagram.com/...">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="font-weight-500 mb-1" style="font-size: 12.5px;"><i class="mdi mdi-facebook text-primary me-1"></i>Facebook URL</label>
                                            <input type="url" name="social_facebook" class="form-control" value="{{ old('social_facebook', $storeInfo['social_facebook'] ?? '') }}" placeholder="https://facebook.com/...">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="font-weight-500 mb-1" style="font-size: 12.5px;"><i class="mdi mdi-music-note text-dark me-1"></i>TikTok URL</label>
                                            <input type="url" name="social_tiktok" class="form-control" value="{{ old('social_tiktok', $storeInfo['social_tiktok'] ?? '') }}" placeholder="https://tiktok.com/@...">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-gradient-primary px-4">
                                            <i class="mdi mdi-store-check me-1"></i> Simpan Info Toko
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle URL hash for tab activation
        const hash = window.location.hash;
        if (hash) {
            const triggerEl = document.querySelector(`button[data-bs-target="${hash}"]`);
            if (triggerEl && typeof bootstrap !== 'undefined') {
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }
        }

        // When a tab is shown, update window location hash
        const tabButtons = document.querySelectorAll('#settingsTab button[data-bs-toggle="tab"]');
        tabButtons.forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                const target = e.target.getAttribute('data-bs-target');
                if (target) {
                    history.replaceState(null, null, target);
                }
            });
        });
    });
</script>
@endpush
@endsection

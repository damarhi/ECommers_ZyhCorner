@extends('Layout.layout_pelanggan')

@section('title', 'Pengaturan Akun - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('home') }}">Beranda</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <a href="{{ route('dashboard') }}">Akun Saya</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Pengaturan Akun</span>
@endsection

@section('styles')
    <!-- Load PurpleAdmin CSS and Icons -->
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">

    <!-- Leaflet Map CSS & JS for Address Modal -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @vite(['resources/css/dashboard.css'])
@endsection

@section('content')
    <div class="dashboard-wrapper">
        <div class="row">

            <!-- Left Side: Navigation Sidebar -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="sidebar-navigation">
                    <div class="sidebar-title">Navigation</div>
                    <ul class="nav-menu-list">
                        <li class="nav-menu-item">
                            <a href="{{ route('dashboard') }}">
                                <i class="fa-solid fa-table-cells-large"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-menu-item">
                            <a href="{{ route('order.history') }}">
                                <i class="fa-solid fa-clock-rotate-left"></i> Order History
                            </a>
                        </li>
                        <li class="nav-menu-item">
                            <a href="{{ route('userChart') }}">
                                <i class="fa-solid fa-bag-shopping"></i> Shopping Cart
                            </a>
                        </li>
                        <li class="nav-menu-item active">
                            <a href="{{ route('settings') }}">
                                <i class="fa-solid fa-gear"></i> Settings
                            </a>
                        </li>
                        <li class="nav-menu-item">
                            <a href="#" onclick="event.preventDefault(); confirmLogout();">
                                <i class="fa-solid fa-right-from-bracket"></i> Log-out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Right Side: Settings Panel -->
            <div class="col-lg-9 col-md-8">

                <!-- Segmented Tabs Navigation -->
                <div class="settings-tabs-wrapper mb-4">
                    <button type="button" class="settings-tab-btn active" data-tab="profile" onclick="switchSettingsTab('profile')">
                        <i class="fa-solid fa-user"></i>
                        <span>Profil Akun</span>
                    </button>
                    <button type="button" class="settings-tab-btn" data-tab="address" onclick="switchSettingsTab('address')">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>Alamat Pengiriman</span>
                    </button>
                    <button type="button" class="settings-tab-btn" data-tab="password" onclick="switchSettingsTab('password')">
                        <i class="fa-solid fa-lock"></i>
                        <span>Keamanan & Sandi</span>
                    </button>
                </div>

                <!-- Tab 1: Profil Akun -->
                <div class="settings-tab-content active" id="tabContentProfile">
                    <div class="settings-panel">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">
                                <i class="fa-solid fa-user-gear"></i>
                            </div>
                            <div>
                                <h4 class="settings-panel-title">Pengaturan Profil & Akun</h4>
                                <p class="settings-panel-subtitle">Perbarui data diri, email login, dan nomor kontak yang terhubung dengan akun Anda</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <form id="formAccountSettings" onsubmit="saveAccountSettings(event)">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <div class="form-group-settings">
                                            <label for="inputFullName"><i class="fa-solid fa-user text-muted mr-1"></i> Nama Lengkap</label>
                                            <input type="text" id="inputFullName" class="form-control-settings"
                                                value="{{ $user->nama }}" placeholder="Nama lengkap Anda" required>
                                            <!-- Hidden legacy inputs for test & backward compatibility -->
                                            <input type="hidden" id="inputFirstName" value="{{ $user->nama }}">
                                            <input type="hidden" id="inputLastName" value="">
                                        </div>
                                        <div class="form-group-settings">
                                            <label for="inputAccountEmail"><i class="fa-solid fa-envelope text-muted mr-1"></i> Alamat Email</label>
                                            <input type="email" id="inputAccountEmail" class="form-control-settings"
                                                value="{{ $user->email }}" placeholder="contoh@email.com" required>
                                        </div>
                                        <div class="form-group-settings">
                                            <label for="inputAccountPhone"><i class="fa-solid fa-phone text-muted mr-1"></i> Nomor WhatsApp / HP</label>
                                            <input type="tel" id="inputAccountPhone" class="form-control-settings"
                                                value="{{ $user->no_hp }}" placeholder="08xxxxxxxxxx" required>
                                        </div>
                                        <div style="margin-top: 24px;">
                                            <button type="submit" class="btn-save-settings">
                                                <i class="fa-solid fa-check"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="avatar-wrapper">
                                            <div class="avatar-circle" onclick="document.getElementById('avatarFileInput').click()" style="cursor: pointer;" title="Klik untuk mengganti foto profil">
                                                <img id="avatarImage"
                                                    src="{{ $user->avatar ? asset($user->avatar) : asset('image/default-avatar.svg') }}"
                                                    alt="Profile Picture">
                                                <div class="avatar-hover-overlay">
                                                    <i class="fa-solid fa-camera"></i>
                                                </div>
                                            </div>
                                            <input type="file" id="avatarFileInput" accept="image/*" style="display: none;"
                                                onchange="handleAvatarFile(this)">
                                            <button type="button" class="btn-chose-img"
                                                onclick="document.getElementById('avatarFileInput').click()">
                                                <i class="fa-solid fa-camera mr-1"></i> Ganti Foto Profil
                                            </button>
                                            <span class="avatar-hint">Format JPG, PNG, atau GIF. Maksimal 2MB.</span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Alamat Pengiriman -->
                <!-- Tab 2: Alamat Pengiriman (Display Card Murni dari Dashboard) -->
                <div class="settings-tab-content" id="tabContentAddress" style="display: none;">
                    <div class="settings-panel">
                        <div class="settings-panel-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <div class="settings-panel-header-icon" style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;">
                                    <i class="fa-solid fa-location-dot"></i>
                                </div>
                                <div>
                                    <h4 class="settings-panel-title">Alamat Pengiriman Saya</h4>
                                    <p class="settings-panel-subtitle">Data alamat pengantaran pesanan aktif yang terhubung dengan Buku Alamat Akun Anda</p>
                                </div>
                            </div>
                            <button type="button" class="btn-add-address-header" onclick="openAddAddressModal()">
                                <i class="fa-solid fa-plus-circle mr-1"></i> Tambah Alamat Baru
                            </button>
                        </div>
                        <div class="settings-panel-body">

                            @if($defaultAddress || !empty($user->alamat))
                                @php
                                    $activeStreet = $defaultAddress->street_address ?? $user->alamat;
                                    $activeLabel = $defaultAddress->label ?? 'Alamat Utama';
                                    $activeName = $defaultAddress->recipient_name ?? $user->nama;
                                    $activePhone = $defaultAddress->recipient_phone ?? $user->no_hp;
                                    $activeNotes = $defaultAddress->notes ?? '';
                                    $activeArea = $defaultAddress->destination_area_name ?? '';
                                    $activePostal = $defaultAddress->postal_code ?? '';
                                    $hasGps = !empty($defaultAddress->latitude) && !empty($defaultAddress->longitude);
                                @endphp

                                <!-- Kartu Alamat Utama Aktif -->
                                <div class="address-display-main-card">
                                    <div class="address-card-topbar">
                                        <div class="address-card-badges">
                                            <span class="badge-address-label">
                                                <i class="fa-solid fa-house-chimney"></i> {{ $activeLabel }}
                                            </span>
                                            <span class="badge-address-default">
                                                <i class="fa-solid fa-circle-check"></i> Alamat Utama (Default)
                                            </span>
                                            @if($hasGps)
                                                <span class="badge-address-gps">
                                                    <i class="fa-solid fa-location-crosshairs"></i> Titik GPS Aktif
                                                </span>
                                            @else
                                                <span class="badge-address-nogps">
                                                    <i class="fa-solid fa-circle-info"></i> Belum Dipin GPS
                                                </span>
                                            @endif
                                        </div>
                                        <button type="button" class="btn-card-edit-address" onclick="openEditAddress({{ $defaultAddress ? json_encode($defaultAddress) : 'null' }})">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit Alamat
                                        </button>
                                    </div>

                                    <div class="address-card-recipient">
                                        <span class="recipient-name">{{ $activeName }}</span>
                                        <span class="recipient-divider">•</span>
                                        <span class="recipient-phone"><i class="fa-solid fa-phone"></i> (+62) {{ $activePhone }}</span>
                                    </div>

                                    <div class="address-card-street">
                                        <i class="fa-solid fa-map-location-dot"></i>
                                        <span>{{ $activeStreet }}</span>
                                    </div>

                                    @if($activeArea || $activePostal)
                                        <div class="address-card-area">
                                            <i class="fa-solid fa-city"></i>
                                            <span>{{ $activeArea }}{{ $activePostal ? ', ' . $activePostal : '' }}</span>
                                        </div>
                                    @endif

                                    @if($activeNotes)
                                        <div class="address-card-notes">
                                            <i class="fa-solid fa-note-sticky"></i>
                                            <div class="notes-content">
                                                <strong>Patokan Kurir:</strong> {{ $activeNotes }}
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Alamat Tersimpan Lainnya (Jika Ada) -->
                                @php
                                    $extraAddresses = isset($allAddresses) ? $allAddresses->where('is_default', false) : collect();
                                @endphp
                                @if($extraAddresses->count() > 0)
                                    <div class="address-extra-section mt-4">
                                        <div class="extra-section-header">
                                            <h5 class="extra-section-title">
                                                <i class="fa-solid fa-layer-group text-muted mr-1"></i> Alamat Tersimpan Lainnya ({{ $extraAddresses->count() }})
                                            </h5>
                                            <span class="extra-section-hint">Alamat cadangan untuk pengiriman kantor atau kerabat</span>
                                        </div>

                                        <div class="extra-address-grid">
                                            @foreach($extraAddresses as $extra)
                                                <div class="extra-address-card" id="addressCard-{{ $extra->id }}">
                                                    <div class="extra-card-header">
                                                        <span class="extra-label">{{ $extra->label }}</span>
                                                        <span class="extra-phone"><i class="fa-solid fa-phone mr-1"></i> {{ $extra->recipient_phone }}</span>
                                                    </div>
                                                    <div class="extra-name">{{ $extra->recipient_name }}</div>
                                                    <div class="extra-street">{{ $extra->street_address }}</div>
                                                    @if($extra->destination_area_name)
                                                        <div class="extra-meta"><i class="fa-solid fa-city mr-1 text-muted"></i> {{ $extra->destination_area_name }}{{ $extra->postal_code ? ', ' . $extra->postal_code : '' }}</div>
                                                    @endif
                                                    @if($extra->notes)
                                                        <div class="extra-notes"><i class="fa-solid fa-note-sticky mr-1 text-warning"></i> {{ $extra->notes }}</div>
                                                    @endif
                                                    <div class="extra-card-actions">
                                                        <button type="button" class="btn-action-setdefault" onclick="setDefaultAddress({{ $extra->id }})" title="Jadikan Alamat Utama">
                                                            <i class="fa-solid fa-circle-check"></i> Jadikan Utama
                                                        </button>
                                                        <button type="button" class="btn-action-edit" onclick="openEditAddress({{ json_encode($extra) }})" title="Edit Alamat Ini">
                                                            <i class="fa-solid fa-pen-to-square"></i> Edit
                                                        </button>
                                                        <button type="button" class="btn-action-delete" onclick="deleteAddress({{ $extra->id }})" title="Hapus Alamat Ini">
                                                            <i class="fa-solid fa-trash-can"></i> Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            @else
                                <!-- Empty State -->
                                <div class="address-empty-state">
                                    <div class="empty-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                                    <h5>Belum Ada Alamat Pengiriman</h5>
                                    <p>Anda belum menyimpan alamat pengantaran pesanan. Tambahkan alamat utama Anda sekarang di Dashboard untuk kemudahan proses checkout.</p>
                                    <button type="button" class="btn-manage-address-dashboard" onclick="openAddAddressModal()">
                                        <i class="fa-solid fa-plus-circle"></i> Tambah Alamat Sekarang
                                    </button>
                                </div>
                            @endif


                            <!-- Hidden compatibility inputs for tests & scripts -->
                            <form id="formBillingAddress" onsubmit="saveBillingAddress(event)" style="display: none;">
                                <input type="hidden" id="inputBillingStreet" value="{{ $defaultAddress->street_address ?? $user->alamat }}">
                                <input type="hidden" id="inputBillingNotes" value="{{ $defaultAddress->notes ?? '' }}">
                            </form>

                        </div>
                    </div>
                </div>

                <!-- Tab 3: Keamanan & Sandi -->
                <div class="settings-tab-content" id="tabContentPassword" style="display: none;">
                    <div class="settings-panel">
                        <div class="settings-panel-header">
                            <div class="settings-panel-header-icon" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div>
                                <h4 class="settings-panel-title">Ubah Kata Sandi</h4>
                                <p class="settings-panel-subtitle">Perbarui kata sandi akun secara berkala untuk menjaga keamanan data Anda</p>
                            </div>
                        </div>
                        <div class="settings-panel-body">
                            <form id="formChangePassword" onsubmit="saveNewPassword(event)">
                                <div class="form-group-settings" style="max-width: 520px;">
                                    <label for="inputCurrentPassword"><i class="fa-solid fa-key text-muted mr-1"></i> Kata Sandi Saat Ini</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="inputCurrentPassword" class="form-control-settings"
                                            placeholder="Masukkan kata sandi lama Anda" required>
                                        <span class="password-toggle-icon"
                                            onclick="togglePasswordVisibility('inputCurrentPassword', this)">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="row" style="max-width: 720px;">
                                    <div class="col-md-6">
                                        <div class="form-group-settings">
                                            <label for="inputNewPassword"><i class="fa-solid fa-lock text-muted mr-1"></i> Kata Sandi Baru</label>
                                            <div class="password-input-wrapper">
                                                <input type="password" id="inputNewPassword" class="form-control-settings"
                                                    placeholder="Minimal 6 karakter" required>
                                                <span class="password-toggle-icon"
                                                    onclick="togglePasswordVisibility('inputNewPassword', this)">
                                                    <i class="fa-regular fa-eye-slash"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group-settings">
                                            <label for="inputConfirmPassword"><i class="fa-solid fa-lock-open text-muted mr-1"></i> Ulangi Kata Sandi Baru</label>
                                            <div class="password-input-wrapper">
                                                <input type="password" id="inputConfirmPassword"
                                                    class="form-control-settings" placeholder="Ketik ulang kata sandi baru" required>
                                                <span class="password-toggle-icon"
                                                    onclick="togglePasswordVisibility('inputConfirmPassword', this)">
                                                    <i class="fa-regular fa-eye-slash"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="password-guideline-box">
                                    <i class="fa-solid fa-circle-check" style="color: #00b207; margin-right: 4px;"></i>
                                    <span>Kata sandi baru minimal 6 karakter dan tidak boleh sama dengan kata sandi lama.</span>
                                </div>

                                <div style="margin-top: 24px;">
                                    <button type="submit" class="btn-save-settings">
                                        <i class="fa-solid fa-lock"></i> Perbarui Kata Sandi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Modal: Edit Address (Leaflet GPS & Complete Form) -->
    <div class="edit-modal-overlay" id="modalEditAddress" style="display: none;">
        <div class="edit-modal edit-modal-wide">
            <div class="edit-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="modal-title-icon"><i class="fa-solid fa-map-location-dot"></i></span>
                    <div>
                        <h4 class="edit-modal-title" id="editModalHeaderTitle">Edit Alamat Pengiriman</h4>
                        <p class="edit-modal-subtitle">Perbarui rincian penerima, alamat jalan, dan titik GPS pengantaran</p>
                    </div>
                </div>
                <button type="button" class="edit-modal-close" onclick="closeEditAddress()">&times;</button>
            </div>

            <form id="formEditAddress" onsubmit="saveAddress(event)">
                <input type="hidden" id="editAddrId" value="">
                
                <!-- Mirrored compatibility fields for legacy tests / scripts -->
                <input type="hidden" id="inputBillingAddress" value="{{ $user->alamat ?? '' }}">
                <input type="hidden" id="inputBillingEmail" value="{{ $user->email }}">
                <input type="hidden" id="inputBillingPhone" value="{{ $user->no_hp }}">

                <div class="modal-scrollable-body">
                    <!-- Group 1: Label Alamat & Identitas Penerima -->
                    <div class="modal-form-section">
                        <div class="modal-section-heading">
                            <i class="fa-solid fa-user-tag"></i> Identitas & Label Alamat
                        </div>
                        
                        <div class="form-group-modal">
                            <label for="editAddrLabel">
                                <i class="fa-solid fa-tag"></i> Label Alamat
                            </label>
                            <div class="label-input-with-chips">
                                <input type="text" id="editAddrLabel" class="form-control-modal" placeholder="Contoh: Rumah, Kantor, Toko" value="Alamat Rumah" required>
                                <div class="quick-chips-group">
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Rumah')">Rumah</button>
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Kantor')">Kantor</button>
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Toko')">Toko</button>
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('editAddrLabel', 'Apartemen')">Apartemen</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-modal">
                                <label for="editAddrRecipientName">
                                    <i class="fa-solid fa-user"></i> Nama Penerima
                                </label>
                                <input type="text" id="editAddrRecipientName" class="form-control-modal" placeholder="Nama penerima pesanan" value="{{ $user->nama }}" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="editAddrRecipientPhone">
                                    <i class="fa-solid fa-phone"></i> Nomor WhatsApp / HP
                                </label>
                                <input type="tel" id="editAddrRecipientPhone" class="form-control-modal" placeholder="08xxxxxxxxxx" value="{{ $user->no_hp }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Group 2: Wilayah & Detail Alamat -->
                    <div class="modal-form-section">
                        <div class="modal-section-heading">
                            <i class="fa-solid fa-location-crosshairs"></i> Wilayah & Detail Alamat
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-modal area-search-wrapper">
                                <label for="editAddrAreaSearch">
                                    <i class="fa-solid fa-city"></i> Kota / Kecamatan / Kelurahan
                                </label>
                                <input type="text" id="editAddrAreaSearch" class="form-control-modal" placeholder="Ketik nama kelurahan atau kecamatan..." autocomplete="off">
                                <input type="hidden" id="editAddrDestinationAreaId" value="">
                                <div id="editAddrAreaDropdown" class="area-dropdown-results"></div>
                            </div>
                            <div class="form-group-modal">
                                <label for="editAddrPostalCode">
                                    <i class="fa-solid fa-envelopes-bulk"></i> Kode Pos
                                </label>
                                <input type="text" id="editAddrPostalCode" class="form-control-modal" maxlength="5" placeholder="Contoh: 28282" inputmode="numeric">
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label for="editAddrStreetAddress">
                                <i class="fa-solid fa-location-dot"></i> Detail Alamat Lengkap
                            </label>
                            <textarea id="editAddrStreetAddress" class="form-control-modal" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi" required>{{ $user->alamat ?? '' }}</textarea>
                        </div>

                        <div class="form-group-modal" style="margin-bottom: 0;">
                            <label for="editAddrNotes">
                                <i class="fa-regular fa-clipboard"></i> Catatan Pengiriman / Patokan <span class="label-optional">(Opsional)</span>
                            </label>
                            <textarea id="editAddrNotes" class="form-control-modal" rows="2" placeholder="Contoh: Titipkan di pos satpam / Rumah pagar hitam samping masjid"></textarea>
                        </div>
                    </div>

                    <!-- Group 3: Titik Lokasi Peta GPS Leaflet -->
                    <div class="modal-form-section">
                        <div class="modal-section-heading" style="display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="fa-solid fa-map"></i> Titik Lokasi Pengantaran di Peta</span>
                            <button type="button" class="btn-locate-me" onclick="locateUserPosition('edit')">
                                <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                            </button>
                        </div>

                        <div class="modal-map-wrapper">
                            <div id="modalEditMap" class="modal-map-canvas"></div>
                            <div class="modal-map-bar">
                                <i class="fa-solid fa-location-crosshairs"></i>
                                <span id="modalEditCoordsDisplay">Klik peta atau seret pin marker untuk menandai titik tepat Anda</span>
                            </div>
                        </div>
                        <input type="hidden" id="editAddrLatitude" value="">
                        <input type="hidden" id="editAddrLongitude" value="">
                    </div>

                    <!-- Group 4: Pengaturan Alamat Utama -->
                    <div class="form-group-modal" style="margin-bottom: 8px;">
                        <label class="default-address-toggle">
                            <input type="checkbox" id="editAddrIsDefault" value="1">
                            <span class="toggle-text">
                                <strong>Jadikan sebagai alamat utama</strong>
                                <small>Alamat ini akan otomatis terpilih secara default pada setiap checkout pesanan Anda.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-cancel-modal" onclick="closeEditAddress()">Batal</button>
                    <button type="submit" class="btn-save-modal" id="btnSaveEditAddress">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Add New Address (Complete 1:1 with Dashboard & Checkout) -->
    <div class="edit-modal-overlay" id="modalAddAddress" style="display: none;">
        <div class="edit-modal edit-modal-wide">
            <div class="edit-modal-header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span class="modal-title-icon"><i class="fa-solid fa-plus-circle"></i></span>
                    <div>
                        <h4 class="edit-modal-title">Tambah Alamat Pengiriman Baru</h4>
                        <p class="edit-modal-subtitle">Simpan alamat baru ke buku alamat akun Anda untuk digunakan saat checkout</p>
                    </div>
                </div>
                <button type="button" class="edit-modal-close" onclick="closeAddAddressModal()">&times;</button>
            </div>

            <form id="formAddAddress" onsubmit="submitNewAddress(event)">
                <div class="modal-scrollable-body">
                    <!-- Group 1: Label Alamat & Identitas Penerima -->
                    <div class="modal-form-section">
                        <div class="modal-section-heading">
                            <i class="fa-solid fa-user-tag"></i> Identitas & Label Alamat
                        </div>
                        
                        <div class="form-group-modal">
                            <label for="newAddrLabel">
                                <i class="fa-solid fa-tag"></i> Label Alamat
                            </label>
                            <div class="label-input-with-chips">
                                <input type="text" id="newAddrLabel" class="form-control-modal" placeholder="Contoh: Rumah, Kantor, Toko" value="Alamat Rumah" required>
                                <div class="quick-chips-group">
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Rumah')">Rumah</button>
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Kantor')">Kantor</button>
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Toko')">Toko</button>
                                    <button type="button" class="quick-chip-btn" onclick="setLabelChip('newAddrLabel', 'Apartemen')">Apartemen</button>
                                </div>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-modal">
                                <label for="newAddrRecipientName">
                                    <i class="fa-solid fa-user"></i> Nama Penerima
                                </label>
                                <input type="text" id="newAddrRecipientName" class="form-control-modal" placeholder="Nama penerima pesanan" value="{{ $user->nama }}" required>
                            </div>
                            <div class="form-group-modal">
                                <label for="newAddrRecipientPhone">
                                    <i class="fa-solid fa-phone"></i> Nomor WhatsApp / HP
                                </label>
                                <input type="tel" id="newAddrRecipientPhone" class="form-control-modal" placeholder="08xxxxxxxxxx" value="{{ $user->no_hp }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Group 2: Wilayah & Detail Alamat -->
                    <div class="modal-form-section">
                        <div class="modal-section-heading">
                            <i class="fa-solid fa-location-crosshairs"></i> Wilayah & Detail Alamat
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-modal area-search-wrapper">
                                <label for="newAddrAreaSearch">
                                    <i class="fa-solid fa-city"></i> Kota / Kecamatan / Kelurahan
                                </label>
                                <input type="text" id="newAddrAreaSearch" class="form-control-modal" placeholder="Ketik nama kelurahan atau kecamatan..." autocomplete="off">
                                <input type="hidden" id="newAddrDestinationAreaId" value="">
                                <div id="newAddrAreaDropdown" class="area-dropdown-results"></div>
                            </div>
                            <div class="form-group-modal">
                                <label for="newAddrPostalCode">
                                    <i class="fa-solid fa-envelopes-bulk"></i> Kode Pos
                                </label>
                                <input type="text" id="newAddrPostalCode" class="form-control-modal" maxlength="5" placeholder="Contoh: 28282" inputmode="numeric">
                            </div>
                        </div>

                        <div class="form-group-modal">
                            <label for="newAddrStreetAddress">
                                <i class="fa-solid fa-location-dot"></i> Detail Alamat Lengkap
                            </label>
                            <textarea id="newAddrStreetAddress" class="form-control-modal" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi" required></textarea>
                        </div>

                        <div class="form-group-modal" style="margin-bottom: 0;">
                            <label for="newAddrNotes">
                                <i class="fa-regular fa-clipboard"></i> Catatan Pengiriman / Patokan <span class="label-optional">(Opsional)</span>
                            </label>
                            <textarea id="newAddrNotes" class="form-control-modal" rows="2" placeholder="Contoh: Titipkan di pos satpam / Rumah pagar hitam samping masjid"></textarea>
                        </div>
                    </div>

                    <!-- Group 3: Titik Lokasi Peta GPS Leaflet -->
                    <div class="modal-form-section">
                        <div class="modal-section-heading" style="display: flex; justify-content: space-between; align-items: center;">
                            <span><i class="fa-solid fa-map"></i> Titik Lokasi Pengantaran di Peta</span>
                            <button type="button" class="btn-locate-me" onclick="locateUserPosition('add')">
                                <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                            </button>
                        </div>

                        <div class="modal-map-wrapper">
                            <div id="modalAddMap" class="modal-map-canvas"></div>
                            <div class="modal-map-bar">
                                <i class="fa-solid fa-location-crosshairs"></i>
                                <span id="modalAddCoordsDisplay">Klik peta atau seret pin marker untuk menandai titik tepat Anda</span>
                            </div>
                        </div>
                        <input type="hidden" id="newAddrLatitude" value="">
                        <input type="hidden" id="newAddrLongitude" value="">
                    </div>

                    <!-- Group 4: Pengaturan Alamat Utama -->
                    <div class="form-group-modal" style="margin-bottom: 8px;">
                        <label class="default-address-toggle">
                            <input type="checkbox" id="newAddrIsDefault" value="1">
                            <span class="toggle-text">
                                <strong>Jadikan sebagai alamat utama</strong>
                                <small>Alamat ini akan otomatis terpilih secara default pada setiap checkout pesanan Anda.</small>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer-actions">
                    <button type="button" class="btn-cancel-modal" onclick="closeAddAddressModal()">Batal</button>
                    <button type="submit" class="btn-save-modal" id="btnSaveAddAddress">
                        <i class="fa-solid fa-check"></i> Simpan Alamat Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.settingsConfig = {
            userNama: "{{ $user->nama }}",
            userEmail: "{{ $user->email }}",
            userPhone: "{{ $user->no_hp }}",
            settingsAccountRoute: "{{ route('settings.account') }}",
            settingsPasswordRoute: "{{ route('settings.password') }}",
            settingsAddressRoute: "{{ route('settings.address') }}",
            settingsAvatarRoute: "{{ route('settings.avatar') }}",
            csrfToken: "{{ csrf_token() }}"
        };

        window.dashboardConfig = {
            userNama: "{{ $user->nama }}",
            userEmail: "{{ $user->email }}",
            userPhone: "{{ $user->no_hp }}",
            userAlamat: "{{ $user->alamat ?? '' }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>
    @vite(['resources/js/dashboard.js'])
@endsection

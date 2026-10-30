@extends('Layout.layout_pelanggan')

@section('title', 'Customer Dashboard - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Dashboard</span>
@endsection

@section('styles')
<!-- Load PurpleAdmin CSS and Icons -->
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
<link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">
<!-- Leaflet Map CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@vite(['resources/css/dashboard.css'])
@endsection

@section('content')
    @php
        $isProduction = config('services.midtrans.is_production', false);
        $snapUrl = $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
        $clientKey = config('services.midtrans.client_key');
    @endphp
    <script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>

<div class="dashboard-wrapper">
    <div class="row">
        
        <!-- Left Side: Navigation Sidebar -->
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="sidebar-navigation">
                <div class="sidebar-title">Navigation</div>
                <ul class="nav-menu-list">
                    <li class="nav-menu-item active">
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
                    <li class="nav-menu-item">
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
        
        <!-- Right Side: Content Area -->
        <div class="col-lg-9 col-md-8">
            <div class="row">
                
                <!-- Profile Card -->
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card profile-card">
                        <div class="profile-img-container" style="position: relative; cursor: pointer;" onclick="document.getElementById('dashboardAvatarInput').click()">
                            <img src="{{ $user->avatar ? asset($user->avatar) : asset('image/default-avatar.svg') }}" alt="Profile Picture" id="dashboardAvatar">
                            <div class="avatar-hover-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; opacity: 0; transition: 0.3s; color: white;">
                                <i class="fa-solid fa-camera" style="font-size: 24px;"></i>
                            </div>
                            <input type="file" id="dashboardAvatarInput" accept="image/*" style="display: none;" onchange="uploadDashboardAvatar(this)">
                        </div>
                        <div class="profile-name" id="displayProfileName">{{ $user->nama }}</div>
                        <div class="profile-role">Customer</div>
                        <div style="display: flex; justify-content: center; gap: 15px;">
                            <a onclick="openEditProfile()" class="green-link" style="cursor: pointer;">Edit Name</a>
                            <span style="color: #ccc;">|</span>
                            <a onclick="document.getElementById('dashboardAvatarInput').click()" class="green-link" style="cursor: pointer;">Change Photo</a>
                        </div>
                    </div>
                </div>
                
                <!-- Billing Address & Address Book Card -->
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card billing-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div class="billing-title" style="margin-bottom: 0;">Buku Alamat Pengiriman</div>
                            <button type="button" onclick="openAddAddressModal()" class="green-link" style="background: none; border: none; padding: 0; cursor: pointer; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-plus"></i> Tambah Alamat
                            </button>
                        </div>

                        @php
                            $defaultAddrObj = (isset($addresses) && $addresses->count() > 0) ? ($addresses->firstWhere('is_default', true) ?? $addresses->first()) : null;
                            $hasAddress = $defaultAddrObj || !empty($user->alamat);
                            $extraCount = isset($addresses) ? $addresses->where('is_default', false)->count() : 0;
                        @endphp

                        @if ($hasAddress)
                            <!-- Primary Default Address Display (Populated State) -->
                            <div class="address-item-card is-default mb-2">
                                <div class="saved-address-header">
                                    <span class="saved-address-label">{{ $defaultAddrObj->label ?? 'Alamat Utama' }}</span>
                                    <span class="saved-address-badge-default">Default</span>
                                </div>
                                <div class="billing-name" id="displayBillingName">{{ $defaultAddrObj->recipient_name ?? $user->nama }}</div>
                                <div class="billing-address" id="displayBillingAddress">{{ $defaultAddrObj->street_address ?? $user->alamat }}</div>
                                @if($defaultAddrObj && !empty($defaultAddrObj->destination_area_name))
                                    <div style="font-size: 12.5px; color: #475569; margin-top: 2px;">
                                        {{ $defaultAddrObj->destination_area_name }}{{ !empty($defaultAddrObj->postal_code) ? ', ' . $defaultAddrObj->postal_code : '' }}
                                    </div>
                                @endif
                                <div class="billing-contact" style="margin-top: 6px;">
                                    <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
                                </div>
                                <div class="billing-contact">
                                    <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $defaultAddrObj->recipient_phone ?? $user->no_hp }}</span>
                                </div>
                                <div class="address-item-actions">
                                    <a onclick="openEditAddress({{ $defaultAddrObj ? json_encode($defaultAddrObj) : 'null' }})" class="green-link" style="cursor: pointer;"><i class="fa-solid fa-pen-to-square"></i> Edit Alamat Utama</a>
                                    @if($extraCount > 0)
                                        <button type="button" class="btn-other-addresses" onclick="openSavedAddressesModal()">
                                            <i class="fa-solid fa-layer-group"></i> Lihat Alamat Lainnya ({{ $extraCount }})
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Empty State: Belum Ada Alamat Pengiriman -->
                            <div class="address-empty-card" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 8px; padding: 22px 16px; text-align: center;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: #f1f5f9; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <i class="fa-solid fa-map-location-dot" style="font-size: 20px; color: #94a3b8;"></i>
                                </div>
                                <div style="font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px;">Belum Ada Alamat Pengiriman</div>
                                <div style="font-size: 12.5px; color: #64748b; line-height: 1.5; max-width: 320px; margin: 0 auto 14px auto;">
                                    Tambahkan alamat untuk mempermudah perhitungan ongkir otomatis dan pengiriman pesanan Anda.
                                </div>
                                <button type="button" onclick="openAddAddressModal()" class="btn-add-primary" style="background: #00b207; color: #ffffff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.2s;">
                                    <i class="fa-solid fa-plus"></i> Tambah Alamat Sekarang
                                </button>
                                <!-- Compatibility fallback hooks -->
                                <div style="display: none;">
                                    <span id="displayBillingName">{{ $user->nama }}</span>
                                    <span id="displayBillingAddress"></span>
                                    <span id="displayBillingEmail">{{ $user->email }}</span>
                                    <span id="displayBillingPhone">{{ $user->no_hp }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Recent Order History -->
            <div class="order-history-card">
                <div class="card-header-flex">
                    <h3 class="card-header-title">Recent Order History</h3>
                    <a href="{{ route('order.history') }}" class="green-link">View All</a>
                </div>
                
                <div class="order-rows-list">
                    @forelse($recentOrders as $order)
                        @php
                            // Normalize status for design matching
                            $statusClass = $order->status_class;
                            $statusLabel = $order->status_label;

                            $firstItem = null;
                            $itemCount = 0;
                            $totalQty = 0;
                            $orderItems = $order->items;
                            
                            if (is_array($orderItems) && count($orderItems) > 0) {
                                $firstItem = $orderItems[0];
                                $itemCount = count($orderItems);
                                foreach ($orderItems as $item) {
                                    $totalQty += isset($item['qty']) ? intval($item['qty']) : 1;
                                }
                            }
                            
                            $displayImg = asset('image/logo_circle.png');
                            if ($firstItem && isset($firstItem['img'])) {
                                $displayImg = $firstItem['img'];
                            }

                            $trackingPayload = [
                                'order_id' => $order->id,
                                'order_number' => $order->order_number,
                                'resi_code' => $order->resi_code,
                                'courier_name' => $order->formatted_courier_name,
                                'courier_icon' => $order->courier_icon,
                                'recipient_name' => $order->customer_name ?? $user->nama,
                                'is_completed' => (bool)$order->is_completed,
                                'is_canceled' => (bool)$order->is_canceled,
                                'is_shipping' => (bool)$order->is_shipping,
                                'is_processing' => (bool)$order->is_processing,
                                'is_awaiting_confirmation' => (bool)$order->is_awaiting_confirmation,
                                'is_pending' => (bool)$order->is_pending,
                                'status_step' => (int)$order->status_step,
                                'created_at_formatted' => $order->created_at->format('d M Y, H:i'),
                                'updated_at_formatted' => $order->updated_at ? $order->updated_at->format('d M Y, H:i') : null,
                                'detail_url' => route('order.detail', $order->id),
                                'wa_url' => 'https://wa.me/' . ($storeSettings['wa_number'] ?? \App\Models\StoreSetting::getWaNumber()) . '?text=' . urlencode('Halo ' . ($storeSettings['store_name'] ?? 'Zyh Corner') . ', saya ingin konfirmasi pengiriman pesanan #' . $order->order_number . ' (Resi: ' . $order->resi_code . ')'),
                            ];
                        @endphp
                        <div class="order-row-item">
                            <div class="order-product-info" style="cursor: pointer;" onclick="window.location.href='{{ route('order.detail', $order->id) }}'">
                                <div class="order-img-wrapper">
                                    <img src="{{ $displayImg }}" alt="Product Image">
                                    @if ($itemCount > 1)
                                        <div class="extra-badge">+{{ $itemCount - 1 }}</div>
                                    @endif
                                </div>
                                <div class="order-details-meta">
                                    <div class="order-date">{{ $order->created_at->format('j M, Y') }}</div>
                                    <div class="order-summary">
                                        Total: <strong>Rp. {{ number_format($order->total, 0, ',', '.') }}</strong> 
                                        @if ($totalQty > 1)
                                            ({{ $totalQty }} Products)
                                        @else
                                            (1 Product)
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-actions-flex">
                                @if ($order->is_pending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                                    <button
                                        onclick="payOrderNow('{{ $order->id }}')"
                                        class="btn-pay-now-solid"><i class="fa-solid fa-credit-card"></i> Pay Now</button>
                                @endif
                                @if ($order->can_be_canceled)
                                    <button
                                        onclick="cancelOrder('{{ $order->id }}', '{{ $order->order_number }}')"
                                        class="btn-cancel-outline">Cancel Order</button>
                                @endif
                                @if (Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']))
                                    <a href="{{ route('order.return', $order->id) }}"
                                        class="btn-return-outline"><i class="fa-solid fa-rotate-left"
                                            style="margin-right: 4px;"></i>Return</a>
                                @endif
                                <div class="tracking-action-wrapper">
                                    @if (!($order->is_pending && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid'))
                                    <div class="order-status-badge status-{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </div>
                                    @endif
                                    <a href="{{ route('order.detail', $order->id) }}?track=1"
                                        onclick="event.preventDefault(); openDashboardTrackingModal({{ json_encode($trackingPayload) }});"
                                        data-tracking-order="{{ $order->order_number }}"
                                        data-tracking-payload="{{ json_encode($trackingPayload) }}"
                                        class="btn-tracking-solid" style="text-decoration: none;"
                                        title="Lacak paket dengan Biteship">
                                        <i class="fa-solid fa-location-crosshairs me-1"></i> Tracking Order
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align: center; padding: 40px 20px; color: #777;">
                            <i class="fa-solid fa-receipt" style="font-size: 36px; color: #ccc; margin-bottom: 12px; display: block;"></i>
                            <div style="font-size: 15px; font-weight: 500; color: #444; margin-bottom: 4px;">Belum Ada Riwayat Pesanan</div>
                            <p style="font-size: 13px; color: #888; margin-bottom: 15px;">Anda belum melakukan pemesanan apapun saat ini.</p>
                            <a href="{{ route('shop') }}" class="btn btn-sm" style="background-color: #00b207; color: white; border-radius: 6px; padding: 6px 16px; font-size: 13px; text-decoration: none;">Belanja Sekarang</a>
                        </div>
                    @endforelse
                </div>
            </div>
            
        </div>
        
    </div>
</div>

<!-- Modal: Edit Profile -->
<div class="edit-modal-overlay" id="modalEditProfile">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <h4 class="edit-modal-title">Edit Profile</h4>
            <button class="edit-modal-close" onclick="closeEditProfile()">&times;</button>
        </div>
        <form id="formEditProfile" onsubmit="saveProfile(event)">
            <div class="form-group-modal">
                <label for="inputProfileName">Full Name</label>
                <input type="text" id="inputProfileName" class="form-control-modal" value="{{ $user->nama }}" required>
            </div>
            <button type="submit" class="btn-save-modal">Save Changes</button>
        </form>
    </div>
</div>


<!-- Modal: Daftar Alamat Tersimpan Lainnya -->
<div class="edit-modal-overlay" id="modalSavedAddresses" style="display: none;">
    <div class="edit-modal edit-modal-wide">
        <div class="edit-modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="modal-title-icon" style="background: #ecfdf5; color: #059669; border-color: #a7f3d0;"><i class="fa-solid fa-layer-group"></i></span>
                <div>
                    <h4 class="edit-modal-title">Alamat Tersimpan Lainnya</h4>
                    <p class="edit-modal-subtitle">Kelola alamat cadangan Anda atau jadikan sebagai alamat utama pengiriman</p>
                </div>
            </div>
            <button type="button" class="edit-modal-close" onclick="closeSavedAddressesModal()">&times;</button>
        </div>

        <div class="modal-scrollable-body">
            @if(isset($addresses) && $addresses->where('is_default', false)->count() > 0)
                <div class="modal-saved-list">
                    @foreach($addresses->where('is_default', false) as $extraAddr)
                        <div class="modal-saved-item-card" id="saved-addr-card-{{ $extraAddr->id }}">
                            <div class="saved-item-header">
                                <span class="saved-address-label">{{ $extraAddr->label }}</span>
                                <span class="saved-item-phone"><i class="fa-brands fa-whatsapp"></i> {{ $extraAddr->recipient_phone }}</span>
                            </div>
                            <div class="saved-item-name">{{ $extraAddr->recipient_name }}</div>
                            <div class="saved-item-address">{{ $extraAddr->street_address }}</div>
                            @if($extraAddr->destination_area_name || $extraAddr->postal_code)
                                <div class="saved-item-meta">{{ $extraAddr->destination_area_name }}{{ $extraAddr->postal_code ? ', ' . $extraAddr->postal_code : '' }}</div>
                            @endif
                            @if($extraAddr->notes)
                                <div class="saved-item-notes"><i class="fa-solid fa-note-sticky"></i> Patokan: {{ $extraAddr->notes }}</div>
                            @endif
                            <div class="saved-item-actions">
                                <button type="button" class="btn-item-default" onclick="setDefaultAddress({{ $extraAddr->id }})">
                                    <i class="fa-solid fa-star"></i> Jadikan Utama
                                </button>
                                <button type="button" class="btn-item-edit" onclick="openEditFromSaved({{ json_encode($extraAddr) }})">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                <button type="button" class="btn-item-delete" onclick="deleteAddress({{ $extraAddr->id }})">
                                    <i class="fa-solid fa-trash-can"></i> Hapus
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-extra-addresses">
                    <div class="empty-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                    <h5>Belum Ada Alamat Tersimpan Lainnya</h5>
                    <p>Simpan alamat kantor, rumah kerabat, atau villa untuk memudahkan proses checkout pesanan berikutnya.</p>
                </div>
            @endif
        </div>

        <div class="modal-footer-actions" style="justify-content: space-between;">
            <button type="button" class="btn-add-from-detail" onclick="openAddFromSaved()">
                <i class="fa-solid fa-plus"></i> Tambah Alamat Baru
            </button>
            <button type="button" class="btn-cancel-modal" onclick="closeSavedAddressesModal()">
                Tutup
            </button>
        </div>
    </div>
</div>
<!-- Modal: Edit Address (Complete 1:1 with Checkout) -->
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

                <!-- Group 2: Wilayah Biteship & Kode Pos -->
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
                    <div class="modal-section-heading" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span><i class="fa-solid fa-map"></i> Titik Lokasi Pengantaran di Peta</span>
                        <button type="button" class="btn-locate-me" onclick="locateUserPosition('edit')">
                            <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                        </button>
                    </div>

                    <!-- Map Geocoding Search Bar -->
                    <div class="map-search-container" id="editAddrMapSearchContainer">
                        <div class="map-search-bar">
                            <div class="map-search-input-wrap">
                                <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
                                <input type="text" id="editAddrMapSearchInput" class="map-search-input" 
                                       placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                                       autocomplete="off"
                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('edit'); }"
                                       oninput="toggleClearSearchBtn('edit')">
                                <button type="button" class="btn-clear-map-search" id="editAddrClearSearchBtn" onclick="clearMapSearch('edit')" style="display: none;" title="Hapus pencarian">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-search-map" onclick="searchLocationOnMap('edit')">
                                Cari
                            </button>
                        </div>
                        <div id="editAddrMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
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

<!-- Modal: Add New Address (Complete 1:1 with Checkout) -->
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

                <!-- Group 2: Wilayah Biteship & Detail Alamat -->
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
                    <div class="modal-section-heading" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span><i class="fa-solid fa-map"></i> Titik Lokasi Pengantaran di Peta</span>
                        <button type="button" class="btn-locate-me" onclick="locateUserPosition('add')">
                            <i class="fa-solid fa-crosshairs"></i> Gunakan Lokasi Saya
                        </button>
                    </div>

                    <!-- Map Geocoding Search Bar -->
                    <div class="map-search-container" id="newAddrMapSearchContainer">
                        <div class="map-search-bar">
                            <div class="map-search-input-wrap">
                                <i class="fa-solid fa-magnifying-glass map-search-icon"></i>
                                <input type="text" id="newAddrMapSearchInput" class="map-search-input" 
                                       placeholder="Cari jalan, perumahan, atau gedung di peta..." 
                                       autocomplete="off"
                                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); searchLocationOnMap('add'); }"
                                       oninput="toggleClearSearchBtn('add')">
                                <button type="button" class="btn-clear-map-search" id="newAddrClearSearchBtn" onclick="clearMapSearch('add')" style="display: none;" title="Hapus pencarian">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <button type="button" class="btn-search-map" onclick="searchLocationOnMap('add')">
                                Cari
                            </button>
                        </div>
                        <div id="newAddrMapSearchResults" class="map-search-dropdown" style="display: none;"></div>
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
<!-- Biteship Tracking Modal for Dashboard -->
<div class="biteship-modal-backdrop" id="biteshipTrackingModal" onclick="if(event.target === this) closeBiteshipTrackingModal();">
    <div class="biteship-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTrackingTitle">
        <div class="biteship-modal-header">
            <div class="biteship-modal-title-group">
                <span class="visually-hidden">Powered by Biteship</span>
                <h3 id="modalTrackingTitle" class="biteship-modal-title">Pelacakan Pengiriman</h3>
                <div class="biteship-modal-subtitle">
                    Pesanan <strong id="modalOrderNumberDisplay">-</strong> &bull; Resi: <strong id="modalResiCodeDisplay">-</strong>
                </div>
            </div>
            <button type="button" class="biteship-modal-close" onclick="closeBiteshipTrackingModal()" aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="biteship-modal-body">
            <!-- Order & Courier Info Strip -->
            <div class="tracking-summary-strip">
                <div class="summary-item">
                    <span class="label">Kurir Pengiriman</span>
                    <span class="value">
                        <i class="fa-solid fa-motorcycle text-success me-1" id="modalCourierIcon"></i>
                        <span id="modalCourierName">Kurir Toko / Pengiriman Standar</span>
                    </span>
                </div>
                <div class="summary-item">
                    <span class="label">Nomor Resi</span>
                    <span class="value" style="display: flex; align-items: center; gap: 6px;">
                        <span id="modalResiCodeText" style="font-family: monospace; font-size: 12.5px;">-</span>
                        <button type="button" class="btn-copy-resi" id="modalCopyResiBtn" onclick="copyModalResi()" title="Salin resi">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </span>
                </div>
                <div class="summary-item">
                    <span class="label">Penerima</span>
                    <span class="value" id="modalRecipientName">-</span>
                </div>
                <div class="summary-item">
                    <span class="label">Status Pengiriman</span>
                    <span class="value" id="modalStatusBadgeContainer">-</span>
                </div>
            </div>

            <!-- Checkpoint Timeline -->
            <div class="tracking-timeline-container">
                <h4 class="timeline-heading"><i class="fa-solid fa-route me-2 text-success"></i> Status Perjalanan Paket</h4>
                <div class="tracking-timeline" id="modalTimelineContainer">
                    <!-- Populated dynamically via JS -->
                </div>
            </div>
        </div>

        <div class="biteship-modal-footer">
            <div class="biteship-modal-footer-left">
                <button type="button" class="btn-modal-close" onclick="closeBiteshipTrackingModal()">Tutup</button>
                <a href="#" id="modalDetailLink" class="btn-modal-detail">
                    <i class="fa-solid fa-file-invoice me-1"></i> Rincian Pesanan
                </a>
            </div>
            <div class="biteship-modal-footer-right">
                <a href="#" id="modalWaLink" target="_blank" class="btn-modal-wa">
                    <i class="fa-brands fa-whatsapp me-1"></i> Hubungi CS Toko
                </a>
            </div>
        </div>
    </div>
</div>


<script>
    window.dashboardConfig = {
        userNama: @json($user->nama),
        userEmail: @json($user->email),
        userPhone: @json($user->no_hp),
        userAlamat: @json($defaultAddrObj->street_address ?? ($user->alamat ?? '')),
        userAddress: @json($defaultAddrObj->street_address ?? ($user->alamat ?? '')),
        csrfToken: '{{ csrf_token() }}'
    };
    function uploadDashboardAvatar(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validation
            if (!file.type.match('image.*')) {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'File harus berupa gambar.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
                return;
            }
            
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Ukuran file maksimal adalah 2MB.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
                return;
            }
            
            Swal.fire({
                title: 'Mengunggah...',
                text: 'Mohon tunggu.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            const formData = new FormData();
            formData.append('avatar', file);
            
            fetch("{{ route('settings.avatar') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'Foto profil berhasil diperbarui.',
                        icon: 'success',
                        confirmButtonColor: '#00b207'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Gagal memperbarui foto profil.',
                        icon: 'error',
                        confirmButtonColor: '#00b207'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat mengunggah.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
                console.error(err);
            });
        }
    }
</script>
@vite(['resources/js/dashboard.js'])
@endsection

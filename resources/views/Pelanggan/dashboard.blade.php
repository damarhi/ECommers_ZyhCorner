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
                
                <!-- Billing Address Card -->
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card billing-card">
                        <div class="billing-title">Billing Address</div>
                        <div class="billing-name" id="displayBillingName">{{ $user->nama }}</div>
                        <div class="billing-address" id="displayBillingAddress">pekanbaru, Rumbai, Umban Sari</div>
                        <div class="billing-contact">
                            <span>Email:</span> <span id="displayBillingEmail">{{ $user->email }}</span>
                        </div>
                        <div class="billing-contact">
                            <span>Phone:</span> <span id="displayBillingPhone">(+62) {{ $user->no_hp }}</span>
                        </div>
                        <a onclick="openEditAddress()" class="green-link" style="margin-top: 10px;">Edit Address</a>
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
                            $statusClass = 'pending';
                            $statusLabel = $order->order_status;
                            
                            if (Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered'])) {
                                $statusClass = 'completed';
                                $statusLabel = 'Completed';
                            } elseif (Str::contains(strtolower($order->order_status), ['otw', 'way', 'kirim', 'sedang dikirim'])) {
                                $statusClass = 'on_the_way';
                                $statusLabel = 'On the Way';
                            } elseif (Str::contains(strtolower($order->order_status), ['proses', 'processing', 'ready'])) {
                                $statusClass = 'processing';
                                $statusLabel = 'Processing';
                            } elseif (Str::contains(strtolower($order->order_status), ['batal', 'cancel', 'failed'])) {
                                $statusClass = 'cancelled';
                                $statusLabel = 'Canceled';
                            } elseif (Str::contains(strtolower($order->order_status), ['menunggu'])) {
                                $statusClass = 'pending';
                                $statusLabel = 'Processing';
                            }

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
                            
                            $displayImg = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop';
                            if ($firstItem && isset($firstItem['img'])) {
                                $displayImg = $firstItem['img'];
                            }
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
                                @if (Str::contains(strtolower($order->order_status), ['menunggu', 'pending']))
                                    @if (strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid')
                                        <button
                                            onclick="payOrderNow('{{ $order->id }}')"
                                            class="btn-pay-now-solid"><i class="fa-solid fa-credit-card"></i> Pay Now</button>
                                    @endif
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
                                    @if (!(Str::contains(strtolower($order->order_status), ['menunggu', 'pending']) && strtoupper($order->payment_method) !== 'COD' && $order->payment_status !== 'Paid'))
                                    <div class="order-status-badge status-{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </div>
                                    @endif
                                    <button onclick="trackOrder('{{ $order->order_number }}')"
                                        class="btn-tracking-solid">Tracking Order</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <!-- Mock row matching the screenshot if the database is empty -->
                        <div class="order-row-item" id="dummyOrderRow">
                            <div class="order-product-info" style="cursor: pointer;" onclick="window.location.href='{{ route('order.detail', 1) }}'">
                                <div class="order-img-wrapper">
                                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Cake">
                                    <div class="extra-badge">+4</div>
                                </div>
                                <div class="order-details-meta">
                                    <div class="order-date">8 Sep, 2025</div>
                                    <div class="order-summary">
                                        Total: <strong>Rp. 135.000</strong> (5 Products)
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-actions-flex">
                                <button onclick="cancelDummyOrder()" class="btn-cancel-outline" id="dummyCancelBtn">Cancel Order</button>
                                <div class="tracking-action-wrapper">
                                    <div class="order-status-badge status-processing">
                                        Processing
                                    </div>
                                    <button onclick="trackOrder('#DUMMY')" class="btn-tracking-solid">Tracking Order</button>
                                </div>
                            </div>
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

<!-- Modal: Edit Address -->
<div class="edit-modal-overlay" id="modalEditAddress">
    <div class="edit-modal">
        <div class="edit-modal-header">
            <h4 class="edit-modal-title">Edit Billing Address</h4>
            <button class="edit-modal-close" onclick="closeEditAddress()">&times;</button>
        </div>
        <form id="formEditAddress" onsubmit="saveAddress(event)">
            <div class="form-group-modal">
                <label for="inputBillingAddress">Street Address</label>
                <input type="text" id="inputBillingAddress" class="form-control-modal" placeholder="e.g. pekanbaru, Rumbai, Umban Sari" required>
            </div>
            <div class="form-group-modal">
                <label for="inputBillingEmail">Email Address</label>
                <input type="email" id="inputBillingEmail" class="form-control-modal" value="{{ $user->email }}" required>
            </div>
            <div class="form-group-modal">
                <label for="inputBillingPhone">Phone Number</label>
                <input type="text" id="inputBillingPhone" class="form-control-modal" value="{{ $user->no_hp }}" required>
            </div>
            <button type="submit" class="btn-save-modal">Save Address</button>
        </form>
    </div>
</div>

<script>
    window.dashboardConfig = {
        userNama: @json($user->nama),
        userEmail: @json($user->email),
        userPhone: @json($user->no_hp)
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

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

<style>
    /* Reset clashing Bootstrap styles back to Zyh Corner custom styling */
    .breadcrumb-wrapper {
        background-color: white !important;
        padding: 20px 0 0 0 !important;
        display: block !important;
    }
    
    .breadcrumb-area {
        background: linear-gradient(rgba(45, 78, 55, 0.7), rgba(45, 78, 55, 0.7)), url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=2042&auto=format&fit=crop') center/cover !important;
        height: 120px !important;
        display: flex !important;
        align-items: center !important;
        color: white !important;
        border-radius: 8px !important;
        margin: 0 auto !important;
        max-width: 1200px !important;
        position: relative !important;
        overflow: hidden !important;
        padding: 0 !important;
    }

    .breadcrumb {
        display: flex !important;
        align-items: center !important;
        gap: 10px !important;
        font-size: 14px !important;
        padding-left: 40px !important;
        z-index: 1 !important;
        background: transparent !important;
        margin-bottom: 0 !important;
        border: none !important;
        border-radius: 0 !important;
    }

    .breadcrumb a {
        color: #e0e0e0 !important;
        text-decoration: none !important;
    }
    
    .breadcrumb span {
        color: #00b207 !important;
    }

    .breadcrumb i {
        color: #999 !important;
    }

    body {
        background-color: #ffffff !important;
        font-family: 'Poppins', sans-serif !important;
        color: #1a1a1a !important;
    }
    
    header, nav.navigation, footer.footer, .cart-sidebar {
        font-family: 'Poppins', sans-serif !important;
    }
    
    header a, nav.navigation a, footer.footer a, .cart-sidebar a {
        text-decoration: none !important;
    }
    
    nav.navigation a {
        color: #cccccc !important;
    }
    nav.navigation a:hover, nav.navigation a.active {
        color: white !important;
    }
    
    footer.footer {
        background-color: #1a1a1a !important;
        color: white !important;
        padding-top: 70px !important;
        margin-top: 0 !important;
        height: auto !important;
    }
    
    footer.footer h3 {
        color: white !important;
        font-size: 18px !important;
        font-weight: 500 !important;
    }
    
    footer.footer a {
        color: #999 !important;
    }
    footer.footer a:hover {
        color: white !important;
    }
    
    .search-bar button {
        background-color: #00b207 !important;
        color: white !important;
    }
    
    .search-bar input {
        border: none !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    /* Dashboard layout */
    .dashboard-wrapper {
        max-width: 1200px;
        margin: 50px auto 80px;
        padding: 0 15px;
    }

    /* Custom Navigation Sidebar Card */
    .sidebar-navigation {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 24px 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .sidebar-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        padding: 0 24px 15px;
        margin-bottom: 15px;
        border-bottom: 1px solid #f2f2f2;
    }

    .nav-menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .nav-menu-item a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 24px;
        color: #666;
        font-size: 15px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .nav-menu-item a:hover {
        background-color: #f8f9fa;
        color: #00b207;
    }

    .nav-menu-item.active {
        background-color: #edf2ee;
    }

    .nav-menu-item.active a {
        color: #1a1a1a;
        font-weight: 600;
    }

    .nav-menu-item a i {
        font-size: 18px;
        width: 20px;
        text-align: center;
        color: #555;
    }
    
    .nav-menu-item.active a i {
        color: #00b207;
    }

    /* Profile and Address Cards */
    .dashboard-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 30px;
        height: 100%;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .profile-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        justify-content: center;
    }

    .profile-img-container {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        overflow: hidden;
        margin-bottom: 15px;
        border: 3px solid #f2f8f4;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .profile-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 4px;
    }

    .profile-role {
        font-size: 14px;
        color: #888;
        margin-bottom: 15px;
    }

    .green-link {
        color: #00b207 !important;
        font-weight: 500;
        text-decoration: none !important;
        transition: color 0.3s;
        cursor: pointer;
        font-size: 14px;
        display: inline-block;
    }

    .green-link:hover {
        color: #009906 !important;
    }

    .billing-title {
        font-size: 12px;
        font-weight: 600;
        color: #999;
        letter-spacing: 1px;
        margin-bottom: 18px;
        text-transform: uppercase;
    }

    .billing-name {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin-bottom: 8px;
    }

    .billing-address {
        font-size: 14px;
        color: #666;
        line-height: 1.6;
        margin-bottom: 18px;
        min-height: 45px;
    }

    .billing-contact {
        font-size: 14px;
        color: #333;
        margin-bottom: 8px;
    }
    
    .billing-contact span {
        color: #666;
    }

    /* Recent Order History Table styling */
    .order-history-card {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 30px;
        margin-top: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }

    .card-header-flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f2f2f2;
    }

    .card-header-title {
        font-size: 20px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }

    .order-row-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
        border-bottom: 1px solid #f2f2f2;
    }
    
    .order-row-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .order-product-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .order-img-wrapper {
        width: 65px;
        height: 65px;
        border-radius: 6px;
        overflow: hidden;
        border: 1px solid #e6e6e6;
        background: #f9f9f9;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 4px;
        position: relative;
        flex-shrink: 0;
    }

    .order-img-wrapper img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .extra-badge {
        position: absolute;
        top: -6px;
        right: -6px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #edf2ee;
        color: #00b207;
        font-size: 10px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1.5px solid white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    .extra-badge-inline {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: #f0f0f0;
        color: #1a1a1a;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        border: 1px solid #e6e6e6;
    }

    .order-details-meta {
        display: flex;
        flex-direction: column;
    }

    .order-date {
        font-size: 14px;
        font-weight: 500;
        color: #1a1a1a;
    }

    .order-summary {
        font-size: 13px;
        color: #666;
        margin-top: 4px;
    }

    .order-status-badge {
        font-size: 13px;
        font-weight: 600;
        text-transform: capitalize;
    }
    
    .status-processing {
        color: #00b207;
    }
    
    .status-completed {
        color: #00b207;
    }
    
    .status-pending {
        color: #ff8a00;
    }
    
    .status-cancelled {
        color: #ea4b48;
    }

    .order-actions-flex {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .btn-cancel-outline {
        border: 1px solid #e6e6e6;
        background: white;
        color: #666;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none !important;
    }

    .btn-cancel-outline:hover {
        background: #f8f9fa;
        color: #ea4b48;
        border-color: #ea4b48;
    }

    .btn-tracking-solid {
        background: #00b207;
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: background 0.3s;
        text-decoration: none !important;
    }

    .btn-tracking-solid:hover {
        background: #009906;
        color: white;
    }

    /* Edit Dialog Modal Styles */
    .edit-modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    
    .edit-modal {
        background: white;
        border-radius: 10px;
        width: 480px;
        max-width: 90%;
        padding: 30px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .edit-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid #f2f2f2;
        padding-bottom: 12px;
    }
    
    .edit-modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        margin: 0;
    }
    
    .edit-modal-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #999;
        transition: color 0.3s;
    }
    
    .edit-modal-close:hover {
        color: #ea4b48;
    }
    
    .form-group-modal {
        margin-bottom: 15px;
    }
    
    .form-group-modal label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #4d4d4d;
        margin-bottom: 8px;
    }
    
    .form-control-modal {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
    }
    
    .form-control-modal:focus {
        border-color: #00b207;
    }
    
    .btn-save-modal {
        background-color: #00b207;
        color: white;
        border: none;
        padding: 11px 24px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        transition: background 0.3s;
    }
    
    .btn-save-modal:hover {
        background-color: #009906;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            margin: 20px auto 40px;
        }
        .dashboard-card {
            padding: 20px;
        }
        .order-history-card {
            padding: 20px;
        }
        .order-row-item {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
            padding: 15px 0;
        }
        .order-status-badge {
            align-self: flex-start;
        }
        .order-actions-flex {
            width: 100%;
            justify-content: flex-start;
            gap: 10px;
            flex-wrap: wrap;
        }
        .order-actions-flex button, .order-actions-flex a {
            flex: 1;
            text-align: center;
            padding: 8px 10px;
            font-size: 12px;
            white-space: nowrap;
        }
    }

    @media (max-width: 576px) {
        .edit-modal {
            padding: 20px 15px;
            width: 95%;
        }
    }
</style>
@endsection

@section('content')
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
                        <div class="profile-img-container">
                            <!-- Cute cat photo matching the screenshot -->
                            <img src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?q=80&w=400&auto=format&fit=crop" alt="Profile Picture">
                        </div>
                        <div class="profile-name" id="displayProfileName">{{ $user->nama }}</div>
                        <div class="profile-role">Customer</div>
                        <a onclick="openEditProfile()" class="green-link">Edit Profile</a>
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
                            // Format status
                            $statusClass = 'pending';
                            $statusLabel = $order->order_status;
                            
                            if (Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered'])) {
                                $statusClass = 'completed';
                            } elseif (Str::contains(strtolower($order->order_status), ['proses', 'processing', 'ready'])) {
                                $statusClass = 'processing';
                            } elseif (Str::contains(strtolower($order->order_status), ['batal', 'cancel', 'failed'])) {
                                $statusClass = 'cancelled';
                            }
                        @endphp
                        <div class="order-row-item">
                            <div class="order-product-info" style="cursor: pointer;" onclick="window.location.href='{{ route('order.detail', $order->id) }}'">
                                <div class="order-img-wrapper">
                                    <!-- Fallback image of a beautiful dessert -->
                                    <img src="https://images.unsplash.com/photo-1578985545062-69928b1d9587?q=80&w=200&auto=format&fit=crop" alt="Cake">
                                    <div class="extra-badge">+4</div>
                                </div>
                                <div class="order-details-meta">
                                    <div class="order-date">{{ $order->created_at->format('j M, Y') }}</div>
                                    <div class="order-summary">
                                        Total: <strong>Rp. {{ number_format($order->total, 0, ',', '.') }}</strong> (5 Products)
                                    </div>
                                </div>
                            </div>
                            
                            <div class="order-status-badge status-{{ $statusClass }}">
                                {{ $statusLabel }}
                            </div>
                            
                            <div class="order-actions-flex">
                                @if(Str::contains(strtolower($order->order_status), ['menunggu', 'pending']))
                                    <button onclick="cancelOrder('{{ $order->id }}', '{{ $order->order_number }}')" class="btn-cancel-outline">Cancel Order</button>
                                @endif
                                <button onclick="trackOrder('{{ $order->order_number }}')" class="btn-tracking-solid">Tracking Order</button>
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
                            
                            <div class="order-status-badge status-processing">
                                Processing
                            </div>
                            
                            <div class="order-actions-flex">
                                <button onclick="cancelDummyOrder()" class="btn-cancel-outline" id="dummyCancelBtn">Cancel Order</button>
                                <button onclick="trackOrder('#DUMMY')" class="btn-tracking-solid">Tracking Order</button>
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
    // Load persisted address details from localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const storedName = localStorage.getItem('zyh_cust_name') || "{{ $user->nama }}";
        const storedAddress = localStorage.getItem('zyh_cust_address') || "pekanbaru, Rumbai, Umban Sari";
        const storedEmail = localStorage.getItem('zyh_cust_email') || "{{ $user->email }}";
        const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) {{ $user->no_hp }}";
        
        document.getElementById('displayProfileName').textContent = storedName;
        document.getElementById('displayBillingName').textContent = storedName;
        document.getElementById('displayBillingAddress').textContent = storedAddress;
        document.getElementById('displayBillingEmail').textContent = storedEmail;
        document.getElementById('displayBillingPhone').textContent = storedPhone;
        
        // Sync values to form inputs
        document.getElementById('inputProfileName').value = storedName;
        document.getElementById('inputBillingAddress').value = storedAddress;
        document.getElementById('inputBillingEmail').value = storedEmail;
        document.getElementById('inputBillingPhone').value = storedPhone;
    });

    // Profile modal toggles
    function openEditProfile() {
        document.getElementById('modalEditProfile').style.display = 'flex';
    }
    
    function closeEditProfile() {
        document.getElementById('modalEditProfile').style.display = 'none';
    }
    
    function saveProfile(e) {
        e.preventDefault();
        const newName = document.getElementById('inputProfileName').value;
        localStorage.setItem('zyh_cust_name', newName);
        
        document.getElementById('displayProfileName').textContent = newName;
        document.getElementById('displayBillingName').textContent = newName;
        
        closeEditProfile();
        
        Swal.fire({
            title: 'Berhasil!',
            text: 'Profil berhasil diperbarui.',
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Address modal toggles
    function openEditAddress() {
        document.getElementById('modalEditAddress').style.display = 'flex';
    }
    
    function closeEditAddress() {
        document.getElementById('modalEditAddress').style.display = 'none';
    }
    
    function saveAddress(e) {
        e.preventDefault();
        const address = document.getElementById('inputBillingAddress').value;
        const email = document.getElementById('inputBillingEmail').value;
        const phone = document.getElementById('inputBillingPhone').value;
        
        localStorage.setItem('zyh_cust_address', address);
        localStorage.setItem('zyh_cust_email', email);
        localStorage.setItem('zyh_cust_phone', phone);
        
        document.getElementById('displayBillingAddress').textContent = address;
        document.getElementById('displayBillingEmail').textContent = email;
        document.getElementById('displayBillingPhone').textContent = phone;
        
        closeEditAddress();
        
        Swal.fire({
            title: 'Berhasil!',
            text: 'Alamat tagihan berhasil diperbarui.',
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Tab Alerts
    function showTabAlert(tabName) {
        Swal.fire({
            title: tabName,
            text: `Halaman ${tabName} pelanggan akan segera hadir!`,
            icon: 'info',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Cancel Order Handler
    function cancelOrder(id, orderNumber) {
        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: `Apakah Anda yakin ingin membatalkan pesanan ${orderNumber}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea4b48',
            cancelButtonColor: '#c3cdd6',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                // POST request to cancel order (mocking logic or endpoint if exists, otherwise display success)
                setTimeout(() => {
                    Swal.close();
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: 'Pesanan Anda telah berhasil dibatalkan.',
                        icon: 'success',
                        confirmButtonColor: '#00b207',
                        customClass: { popup: 'rounded-4' }
                    }).then(() => {
                        window.location.reload();
                    });
                }, 1000);
            }
        });
    }

    // Tracking Order Handler
    function trackOrder(orderNumber) {
        Swal.fire({
            title: 'Pelacakan Pesanan',
            text: `Pesanan Anda ${orderNumber} saat ini sedang dalam proses pengiriman.`,
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        });
    }

    // Dummy Row Cancellation
    function cancelDummyOrder() {
        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: 'Apakah Anda yakin ingin membatalkan pesanan #DUMMY ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea4b48',
            cancelButtonColor: '#c3cdd6',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-4' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('dummyOrderRow').style.opacity = '0.5';
                document.getElementById('dummyCancelBtn').style.display = 'none';
                Swal.fire({
                    title: 'Dibatalkan!',
                    text: 'Pesanan dummy berhasil dibatalkan.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                });
            }
        });
    }
</script>
@endsection

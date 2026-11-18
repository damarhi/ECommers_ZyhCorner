<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard')</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/ti-icons/css/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/vendors/font-awesome/css/font-awesome.min.css') }}">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="{{ asset('PurpleAdmin/assets/css/style.css') }}">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="{{ asset('image/logo_circle.png') }}" type="image/png" />
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @yield('styles')
</head>

<body>
    <div class="container-scroller">
        <!-- partial:../../partials/_navbar.html -->
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <a class="navbar-brand brand-logo custom-brand-logo" href="{{ url('admin/dashboard') }}">
                    <img src="{{ asset('image/logo.jpg') }}" alt="logo" class="custom-logo-img" />
                    Zyh Corner
                </a>
                <a class="navbar-brand brand-logo-mini" href="{{ url('admin/dashboard') }}">
                    <img src="{{ asset('image/logo.jpg') }}" alt="logo" class="custom-logo-img" />
                </a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-menu"></span>
                </button>
                <ul class="navbar-nav navbar-nav-right">

                    <li class="nav-item d-none d-lg-block full-screen-link">
                        <a class="nav-link">
                            <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        @php
                            $recentNavbarMessages = ($recentNavbarMessages ?? collect()) instanceof \Illuminate\Support\Collection
                                ? $recentNavbarMessages->filter(fn ($m) => is_object($m))
                                : collect();
                            $navbarUnreadMsgCount = $navbarUnreadMsgCount ?? 0;
                        @endphp
                        <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-email-outline"></i>
                            @if($navbarUnreadMsgCount > 0)
                                <span class="count-symbol bg-warning"></span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list shadow-sm"
                            aria-labelledby="messageDropdown" style="min-width: 290px;">
                            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                <h6 class="mb-0 font-weight-bold">Pesan Kontak</h6>
                                @if($navbarUnreadMsgCount > 0)
                                    <span class="badge bg-warning text-dark">{{ $navbarUnreadMsgCount }} baru</span>
                                @endif
                            </div>
                            @forelse($recentNavbarMessages as $navMsg)
                                <a href="{{ route('messages.index', ['search' => $navMsg->email]) }}" class="dropdown-item preview-item py-2 border-bottom">
                                    <div class="preview-thumbnail">
                                        <div class="avatar-circle bg-light text-primary font-weight-bold d-flex align-items-center justify-content-center"
                                             style="width: 36px; height: 36px; border-radius: 50%; border: 1px solid #e0e0e0; font-size: 13px;">
                                            {{ strtoupper(substr($navMsg->nama, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="preview-item-content d-flex align-items-start flex-column justify-content-center ps-2">
                                        <h6 class="preview-subject ellipsis mb-1 font-weight-normal text-dark" style="max-width: 170px;">
                                            {{ $navMsg->nama }}
                                        </h6>
                                        <p class="text-muted mb-0" style="font-size: 11px;">
                                            {{ $navMsg->created_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </a>
                            @empty
                                <div class="p-3 text-center text-muted" style="font-size: 12px;">
                                    Belum ada pesan kontak
                                </div>
                            @endforelse
                            <div class="p-2 text-center">
                                <a href="{{ route('messages.index') }}" class="text-primary text-decoration-none font-weight-500" style="font-size: 12.5px;">
                                    Lihat Semua Pesan
                                </a>
                            </div>
                        </div>
                    </li>
                    @php
                        $notifUnconfirmed = $notifUnconfirmed ?? 0;
                        $notifRetur = $notifRetur ?? 0;
                        $totalNotif = $totalNotif ?? ($notifUnconfirmed + $notifRetur);
                    @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#"
                            data-bs-toggle="dropdown">
                            <i class="mdi mdi-bell-outline"></i>
                            @if($totalNotif > 0)
                                <span class="count-symbol bg-danger"></span>
                            @endif
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list"
                            aria-labelledby="notificationDropdown">
                            <h6 class="p-3 mb-0">Notifikasi</h6>
                            <div class="dropdown-divider"></div>
                            @if($notifUnconfirmed > 0)
                                <a class="dropdown-item preview-item" href="{{ route('orders.index', ['order_status' => 'menunggu konfirmasi']) }}">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-warning text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;">
                                            <i class="mdi mdi-cart-outline"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                        <h6 class="preview-subject font-weight-normal mb-1">{{ $notifUnconfirmed }} Pesanan Baru</h6>
                                        <p class="text-muted ellipsis mb-0" style="font-size: 11px;">Menunggu konfirmasi pembayaran</p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif

                            @if($notifRetur > 0)
                                <a class="dropdown-item preview-item" href="{{ route('returs.index', ['filter' => 'New']) }}">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-danger text-white d-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px;">
                                            <i class="mdi mdi-keyboard-return"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                        <h6 class="preview-subject font-weight-normal mb-1">{{ $notifRetur }} Retur Baru</h6>
                                        <p class="text-muted ellipsis mb-0" style="font-size: 11px;">Permohonan retur dari pembeli</p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                            @endif

                            @if($totalNotif === 0)
                                <div class="p-3 text-center text-muted" style="font-size: 12.5px;">
                                    <i class="mdi mdi-check-circle-outline text-success me-1"></i> Tidak ada notifikasi baru
                                </div>
                                <div class="dropdown-divider"></div>
                            @endif

                            <div class="p-2 text-center">
                                <a href="{{ route('orders.index') }}" class="text-primary text-decoration-none font-weight-500" style="font-size: 12.5px;">
                                    Lihat Semua Pesanan
                                </a>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item nav-profile">
                        <div class="nav-link d-flex align-items-center">
                            <div class="nav-profile-img d-flex align-items-center justify-content-center rounded-circle text-white me-2"
                                style="width: 34px; height: 34px; min-width: 34px; background: #4F46E5;">
                                <i class="mdi mdi-account" style="font-size: 17px;"></i>
                            </div>
                            <div class="nav-profile-text">
                                <p class="mb-0 text-black font-weight-bold">{{ auth()->user()->nama ?? 'Admin' }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
        <!-- partial -->
        <div class="container-fluid page-body-wrapper">
            <!-- partial:../../partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    @php
                        $isKasirSidebar = auth()->check() && auth()->user()->role === 'kasir';
                        $sidebarUnreadCount = $sidebarUnreadCount ?? 0;
                        $notifUnconfirmed = $notifUnconfirmed ?? 0;
                        $notifRetur = $notifRetur ?? 0;
                    @endphp

                    <li class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/dashboard') }}">
                            <span class="menu-title">{{ $isKasirSidebar ? 'Dashboard Shift' : 'Dashboard' }}</span>
                            <i class="mdi mdi-view-dashboard menu-icon"></i>
                        </a>
                    </li>

                    <!-- Kelola Penjualan -->
                    <li class="nav-category-header">Kelola Penjualan</li>

                    <li class="nav-item {{ Request::is('admin/orders*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/orders') }}">
                            <span class="menu-title d-flex align-items-center justify-content-between w-100 pe-2">
                                <span>Pesanan Online</span>
                                @if($notifUnconfirmed > 0)
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1" style="font-size: 10px;">{{ $notifUnconfirmed }}</span>
                                @endif
                            </span>
                            <i class="mdi mdi-shopping menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('admin/pos*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/pos') }}">
                            <span class="menu-title">Kasir Offline</span>
                            <i class="mdi mdi-cash-register menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('admin/returs*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/returs') }}">
                            <span class="menu-title d-flex align-items-center justify-content-between w-100 pe-2">
                                <span>Return Produk</span>
                                @if($notifRetur > 0)
                                    <span class="badge bg-danger rounded-pill px-2 py-1 text-white" style="font-size: 10px;">{{ $notifRetur }}</span>
                                @endif
                            </span>
                            <i class="mdi mdi-keyboard-backspace menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('admin/all-sales*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/all-sales') }}">
                            <span class="menu-title">{{ $isKasirSidebar ? 'Riwayat Transaksi' : 'Data Seluruh Penjualan' }}</span>
                            <i class="mdi mdi-chart-line menu-icon"></i>
                        </a>
                    </li>

                    <!-- Kelola Data Produk / Operasional Etalase -->
                    <li class="nav-category-header">{{ $isKasirSidebar ? 'Operasional Etalase' : 'Kelola Data Produk' }}</li>

                    <li
                        class="nav-item {{ Request::is('admin/products') || (Request::is('admin/products*') && !Request::is('admin/custom-products*') && !Request::is('admin/discounts*') && !Request::is('admin/expired-products*')) ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/products') }}">
                            <span class="menu-title">Produk Showcase</span>
                            <i class="mdi mdi-cube-outline menu-icon"></i>
                        </a>
                    </li>

                    @if (!$isKasirSidebar)
                        <li class="nav-item {{ Request::is('admin/custom-products*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('products.custom') }}">
                                <span class="menu-title">Produk Kustom</span>
                                <i class="mdi mdi-pencil-box-outline menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::is('admin/categories*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('admin/categories') }}">
                                <span class="menu-title">Kategori</span>
                                <i class="mdi mdi-tag-multiple-outline menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::is('admin/discounts*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('discounts.index') }}">
                                <span class="menu-title">Diskon</span>
                                <i class="mdi mdi-sale menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item {{ Request::is('admin/expired-products*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('products.expired.index') }}">
                            <span class="menu-title">Produk Kadaluarsa</span>
                            <i class="mdi mdi-calendar-remove menu-icon"></i>
                        </a>
                    </li>

                    <!-- Interaksi Pelanggan -->
                    <li class="nav-category-header">Interaksi Pelanggan</li>

                    <li class="nav-item {{ Request::is('admin/messages*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('messages.index') }}">
                            <span class="menu-title d-flex align-items-center justify-content-between w-100 pe-2">
                                <span>Pesan Kontak</span>
                                @if($sidebarUnreadCount > 0)
                                    <span class="badge bg-danger rounded-pill px-2 py-1 text-white" style="font-size: 10px;">{{ $sidebarUnreadCount }}</span>
                                @endif
                            </span>
                            <i class="mdi mdi-email-open-multiple-outline menu-icon"></i>
                        </a>
                    </li>

                    <!-- Kelola Akun -->
                    <li class="nav-category-header">Kelola Akun</li>

                    @if (!$isKasirSidebar)
                        <li class="nav-item {{ Request::is('admin/customers*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('admin/customers') }}">
                                <span class="menu-title">Akun Pelanggan</span>
                                <i class="mdi mdi-account-group-outline menu-icon"></i>
                            </a>
                        </li>
                        <li class="nav-item {{ Request::is('admin/cassiers*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('admin/cassiers') }}">
                                <span class="menu-title">Akun Kasir</span>
                                <i class="mdi mdi-account-tie-outline menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item {{ Request::is('admin/settings*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('settings.index') }}">
                            <span class="menu-title">{{ $isKasirSidebar ? 'Pengaturan Profil' : 'Pengaturan' }}</span>
                            <i class="mdi mdi-cog-outline menu-icon"></i>
                        </a>
                    </li>

                    @if (!$isKasirSidebar)
                        <!-- Kelola Carousel -->
                        <li class="nav-category-header">Kelola Carousel</li>

                        <li class="nav-item {{ Request::is('admin/banners*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('banners.index') }}">
                                <span class="menu-title">Banner</span>
                                <i class="mdi mdi-image-multiple-outline menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    <!-- Tombol Keluar (Logout) -->
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="event.preventDefault(); confirmAdminLogout();">
                            <span class="menu-title text-danger">Keluar</span>
                            <i class="mdi mdi-logout menu-icon text-danger"></i>
                        </a>
                    </li>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                </ul>
            </nav>
            <!-- partial -->
            <div class="main-panel">
                <div class="content-wrapper">
                    @yield('content')
                </div>
                <!-- content-wrapper ends -->
                <!-- partial:../../partials/_footer.html -->
                <footer class="footer bg-white border-top py-3 px-4">
                    <div class="d-sm-flex justify-content-between align-items-center">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © {{ date('Y') }} <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="text-primary font-weight-bold">Zyh Corner</a>. All rights reserved.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center text-muted">Dasbor Kelola Toko Zyh Corner</span>
                    </div>
                </footer>
                <!-- partial -->
            </div>
            <!-- main-panel ends -->
        </div>
        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="{{ asset('PurpleAdmin/assets/vendors/js/vendor.bundle.base.js') }}"></script>
    <!-- endinject -->
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Clear any leftover guest storefront cart state once staff enters Admin/POS panel
            try {
                localStorage.removeItem('zyh_cart');
                localStorage.removeItem('zyh_last_order');
            } catch (e) {}

            // Global Success Alert
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: @json(session('success')),
                    showConfirmButton: false,
                    timer: 2000,
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            @endif

            // Global Error Alert
            @if (isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
                const allErrors = @json(method_exists($errors, 'all') ? array_values(array_filter($errors->all(), 'is_string')) : []);
                if (allErrors.length > 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: allErrors.join('\n'),
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                }
            @endif
        });

        // --- Admin Confirm Logout Alert ---
        window.confirmAdminLogout = function() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari halaman Admin?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4F46E5',
                cancelButtonColor: '#9CA3AF',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4',
                    confirmButton: 'btn btn-gradient-primary',
                    cancelButton: 'btn btn-light'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    try {
                        localStorage.removeItem('zyh_pos_cart');
                        localStorage.removeItem('zyh_cart');
                        localStorage.removeItem('zyh_last_order');
                    } catch (e) {}
                    document.getElementById('logout-form').submit();
                }
            });
        };
    </script>

    <!-- Anti-bfcache Back Button Protection -->
    <script>
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="{{ asset('PurpleAdmin/assets/js/off-canvas.js') }}"></script>
    <script src="{{ asset('PurpleAdmin/assets/js/misc.js') }}"></script>
    <script src="{{ asset('PurpleAdmin/assets/js/settings.js') }}"></script>
    <script src="{{ asset('PurpleAdmin/assets/js/todolist.js') }}"></script>
    <script src="{{ asset('PurpleAdmin/assets/js/jquery.cookie.js') }}"></script>
    <!-- endinject -->
    <!-- Custom js for this page -->
    <!-- End custom js for this page -->
    <script src="{{ asset('js/live-instant-search.js') }}"></script>
    <script src="{{ asset('js/admin-confirmations.js') }}"></script>
    <script src="{{ asset('js/admin-bulk-actions.js') }}"></script>
    @yield('scripts')
    @stack('scripts')
</body>

</html>

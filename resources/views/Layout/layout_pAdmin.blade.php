<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
                <div class="search-field d-none d-md-block">
                    <form class="d-flex align-items-center h-100" action="#">
                        <div class="input-group">
                            <div class="input-group-prepend bg-transparent">
                                <i class="input-group-text border-0 mdi mdi-magnify"></i>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0" placeholder="Cari...">
                        </div>
                    </form>
                </div>
                <ul class="navbar-nav navbar-nav-right">

                    <li class="nav-item d-none d-lg-block full-screen-link">
                        <a class="nav-link">
                            <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="mdi mdi-email-outline"></i>
                            <span class="count-symbol bg-warning"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list"
                            aria-labelledby="messageDropdown">
                            <h6 class="p-3 mb-0">Pesan</h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <img src="{{ asset('PurpleAdmin/assets/images/faces/face4.jpg') }}" alt="image"
                                        class="profile-pic">
                                </div>
                                <div
                                    class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Mark send you a message
                                    </h6>
                                    <p class="text-gray mb-0"> 1 Minutes ago </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <img src="{{ asset('PurpleAdmin/assets/images/faces/face2.jpg') }}" alt="image"
                                        class="profile-pic">
                                </div>
                                <div
                                    class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Cregh send you a
                                        message</h6>
                                    <p class="text-gray mb-0"> 15 Minutes ago </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <img src="{{ asset('PurpleAdmin/assets/images/faces/face3.jpg') }}" alt="image"
                                        class="profile-pic">
                                </div>
                                <div
                                    class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Profile picture updated
                                    </h6>
                                    <p class="text-gray mb-0"> 18 Minutes ago </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="p-3 mb-0 text-center">4 pesan baru</h6>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#"
                            data-bs-toggle="dropdown">
                            <i class="mdi mdi-bell-outline"></i>
                            <span class="count-symbol bg-danger"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list"
                            aria-labelledby="notificationDropdown">
                            <h6 class="p-3 mb-0">Notifikasi</h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-success">
                                        <i class="mdi mdi-calendar"></i>
                                    </div>
                                </div>
                                <div
                                    class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject font-weight-normal mb-1">Event today</h6>
                                    <p class="text-gray ellipsis mb-0"> Just a reminder that you have an event today
                                    </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-warning">
                                        <i class="mdi mdi-cog"></i>
                                    </div>
                                </div>
                                <div
                                    class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject font-weight-normal mb-1">Settings</h6>
                                    <p class="text-gray ellipsis mb-0"> Update dashboard </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-info">
                                        <i class="mdi mdi-link-variant"></i>
                                    </div>
                                </div>
                                <div
                                    class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject font-weight-normal mb-1">Launch Admin</h6>
                                    <p class="text-gray ellipsis mb-0"> New admin wow! </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="p-3 mb-0 text-center">Lihat semua notifikasi</h6>
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
                    @if (auth()->user()->role !== 'kasir')
                        <li class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('admin/dashboard') }}">
                                <span class="menu-title">Dashboard</span>
                                <i class="mdi mdi-view-dashboard menu-icon"></i>
                            </a>
                        </li>
                    @endif

                    <!-- Kelola Penjualan -->
                    <li class="nav-category-header">Kelola Penjualan</li>

                    <li class="nav-item {{ Request::is('admin/orders*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/orders') }}">
                            <span class="menu-title">Pesanan Online</span>
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
                            <span class="menu-title">Return Produk</span>
                            <i class="mdi mdi-keyboard-backspace menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item {{ Request::is('admin/all-sales*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ url('admin/all-sales') }}">
                            <span class="menu-title">Data Seluruh Penjualan</span>
                            <i class="mdi mdi-chart-line menu-icon"></i>
                        </a>
                    </li>

                    @if (auth()->user()->role !== 'kasir')
                        <!-- Kelola Data Produk -->
                        <li class="nav-category-header">Kelola Data Produk</li>

                        <li
                            class="nav-item {{ Request::is('admin/products') || (Request::is('admin/products*') && !Request::is('admin/custom-products*') && !Request::is('admin/discounts*') && !Request::is('admin/expired-products*')) ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('admin/products') }}">
                                <span class="menu-title">Produk Showcase</span>
                                <i class="mdi mdi-cube-outline menu-icon"></i>
                            </a>
                        </li>
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
                        <li class="nav-item {{ Request::is('admin/expired-products*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('products.expired.index') }}">
                                <span class="menu-title">Produk Kadaluarsa</span>
                                <i class="mdi mdi-calendar-remove menu-icon"></i>
                            </a>
                        </li>

                        <!-- Kelola Akun -->
                        <li class="nav-category-header">Kelola Akun</li>

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
                        <li class="nav-item {{ Request::is('admin/settings*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('settings.index') }}">
                                <span class="menu-title">Pengaturan</span>
                                <i class="mdi mdi-cog-outline menu-icon"></i>
                            </a>
                        </li>

                        <!-- Kelola Carousel -->
                        <li class="nav-category-header">Kelola Carousel</li>

                        <li class="nav-item {{ Request::is('admin/banners*') ? 'active' : '' }}">
                            <a class="nav-link" href="{{ route('banners.index') }}">
                                <span class="menu-title">Banner</span>
                                <i class="mdi mdi-image-multiple-outline menu-icon"></i>
                            </a>
                        </li>

                        <!-- Tombol Keluar (Logout) -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="event.preventDefault(); confirmAdminLogout();">
                                <span class="menu-title text-danger">Keluar</span>
                                <i class="mdi mdi-logout menu-icon text-danger"></i>
                            </a>
                        </li>
                    @else
                        <!-- Tombol Keluar untuk Role Kasir -->
                        <li class="nav-item">
                            <a class="nav-link" href="#" onclick="event.preventDefault(); confirmAdminLogout();">
                                <span class="menu-title text-danger">Keluar</span>
                                <i class="mdi mdi-logout menu-icon text-danger"></i>
                            </a>
                        </li>
                    @endif

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
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © {{ date('Y') }} <a href="{{ route('home') }}" target="_blank" class="text-primary font-weight-bold">Zyh Corner</a>. All rights reserved.</span>
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
            // Global Success Alert
            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '{{ session('success') }}',
                    showConfirmButton: false,
                    timer: 2000,
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
            @endif

            // Global Error Alert
            @if (isset($errors) && is_object($errors) && method_exists($errors, 'any') && $errors->any())
                let errorMessages = '';
                @if (method_exists($errors, 'all'))
                    @foreach ($errors->all() as $error)
                        @if(is_string($error))
                            errorMessages += '{{ $error }}\n';
                        @endif
                    @endforeach
                @endif
                if (errorMessages !== '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: errorMessages,
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                }
            @endif

            // Delete Confirmation
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#EF4444',
                        cancelButtonColor: '#9CA3AF',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-4',
                            confirmButton: 'btn btn-gradient-danger',
                            cancelButton: 'btn btn-light'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            // Edit Confirmation
            const editForms = document.querySelectorAll('.edit-form');
            editForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Simpan Perubahan?',
                        text: "Apakah Anda yakin ingin memperbarui data ini?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#4F46E5',
                        cancelButtonColor: '#9CA3AF',
                        confirmButtonText: 'Ya, Simpan!',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'rounded-4',
                            confirmButton: 'btn btn-gradient-primary',
                            cancelButton: 'btn btn-light'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
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
                    document.getElementById('logout-form').submit();
                }
            });
        };
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
    @yield('scripts')
</body>

</html>

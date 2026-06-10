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
    <link rel="shortcut icon" href="{{ asset('PurpleAdmin/assets/images/favicon.png') }}" />
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
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
              <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="mdi mdi-email-outline"></i>
                <span class="count-symbol bg-warning"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                <h6 class="p-3 mb-0">Pesan</h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="{{ asset('PurpleAdmin/assets/images/faces/face4.jpg') }}" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Mark send you a message</h6>
                    <p class="text-gray mb-0"> 1 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="{{ asset('PurpleAdmin/assets/images/faces/face2.jpg') }}" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Cregh send you a message</h6>
                    <p class="text-gray mb-0"> 15 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <img src="{{ asset('PurpleAdmin/assets/images/faces/face3.jpg') }}" alt="image" class="profile-pic">
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject ellipsis mb-1 font-weight-normal">Profile picture updated</h6>
                    <p class="text-gray mb-0"> 18 Minutes ago </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <h6 class="p-3 mb-0 text-center">4 pesan baru</h6>
              </div>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                <i class="mdi mdi-bell-outline"></i>
                <span class="count-symbol bg-danger"></span>
              </a>
              <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                <h6 class="p-3 mb-0">Notifikasi</h6>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-success">
                      <i class="mdi mdi-calendar"></i>
                    </div>
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject font-weight-normal mb-1">Event today</h6>
                    <p class="text-gray ellipsis mb-0"> Just a reminder that you have an event today </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item preview-item">
                  <div class="preview-thumbnail">
                    <div class="preview-icon bg-warning">
                      <i class="mdi mdi-cog"></i>
                    </div>
                  </div>
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
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
                  <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                    <h6 class="preview-subject font-weight-normal mb-1">Launch Admin</h6>
                    <p class="text-gray ellipsis mb-0"> New admin wow! </p>
                  </div>
                </a>
                <div class="dropdown-divider"></div>
                <h6 class="p-3 mb-0 text-center">Lihat semua notifikasi</h6>
              </div>
            </li>
          
           <li class="nav-item nav-profile dropdown">
              <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="nav-profile-img d-flex align-items-center justify-content-center bg-gradient-primary rounded-circle text-white me-2" style="width: 32px; height: 32px; min-width: 32px;">
                  <i class="mdi mdi-account" style="font-size: 18px;"></i>
                </div>
                <div class="nav-profile-text">
                  <p class="mb-1 text-black font-weight-bold">{{ auth()->user()->nama ?? 'Admin' }}</p>
                </div>
              </a>
              <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                <a class="dropdown-item" href="#">
                  <i class="mdi mdi-cached me-2 text-success"></i> Log Aktivitas </a>
                <div class="dropdown-divider"></div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); confirmAdminLogout();">
                  <i class="mdi mdi-logout me-2 text-primary"></i> Keluar </a>
              </div>
            </li>
          </ul>
          <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
            <span class="mdi mdi-menu"></span>
          </button>
        </div>
      </nav>
      <!-- partial -->
      <div class="container-fluid page-body-wrapper">
        <!-- partial:../../partials/_sidebar.html -->
        <nav class="sidebar sidebar-offcanvas" id="sidebar">
          <ul class="nav">
            <li class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
              <a class="nav-link" href="{{ url('admin/dashboard') }}">
                <span class="menu-title">Dashboard</span>
                <i class="mdi mdi-home menu-icon"></i>
              </a>
            </li>

            <li class="nav-item {{ Request::is('admin/orders*') || Request::is('admin/pos*') || Request::is('admin/all-sales*') ? 'active' : '' }}">
              <a class="nav-link" data-bs-toggle="collapse" href="#kelola-penjualan" aria-expanded="{{ Request::is('admin/orders*') || Request::is('admin/pos*') || Request::is('admin/all-sales*') ? 'true' : 'false' }}" aria-controls="kelola-penjualan">
                <span class="menu-title">Kelola Penjualan</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-format-list-bulleted menu-icon"></i>
              </a>
              <div class="collapse {{ Request::is('admin/orders*') || Request::is('admin/pos*') || Request::is('admin/all-sales*') ? 'show' : '' }}" id="kelola-penjualan">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/orders*') ? 'text-primary' : '' }}" href="{{ url('admin/orders') }}">Pesanan Online</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/pos*') ? 'text-primary' : '' }}" href="{{ url('admin/pos') }}">Kasir Offline</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/all-sales*') ? 'text-primary' : '' }}" href="{{ url('admin/all-sales') }}">Data Seluruh Penjualan</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item {{ Request::is('admin/products') || Request::is('admin/products/*') ? 'active' : '' }}">
              <a class="nav-link" href="{{ url('admin/products') }}">
                <span class="menu-title">Produk</span>
                <i class="mdi mdi-cube-outline menu-icon"></i>
              </a>
            </li>
            <li class="nav-item {{ Request::is('admin/categories') || Request::is('admin/categories/*') ? 'active' : '' }}">
              <a class="nav-link" href="{{ url('admin/categories') }}">
                <span class="menu-title">Kategori</span>
                <i class="mdi mdi-folder-outline menu-icon"></i>
              </a>
            </li>
            <li class="nav-item {{ Request::is('admin/customers*') || Request::is('admin/cassiers*') ? 'active' : '' }}">
              <a class="nav-link" data-bs-toggle="collapse" href="#manage-account" aria-expanded="{{ Request::is('admin/customers*') || Request::is('admin/cassiers*') ? 'true' : 'false' }}" aria-controls="manage-account">
                <span class="menu-title">Kelola Akun</span>
                <i class="menu-arrow"></i>
                <i class="mdi mdi-account-multiple-outline menu-icon"></i>
              </a>
              <div class="collapse {{ Request::is('admin/customers*') || Request::is('admin/cassiers*') ? 'show' : '' }}" id="manage-account">
                <ul class="nav flex-column sub-menu">
                  <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/customers*') ? 'text-primary' : '' }}" href="{{ url('admin/customers') }}">Pelanggan</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ Request::is('admin/cassiers*') ? 'text-primary' : '' }}" href="{{ url('admin/cassiers') }}">Kasir</a>
                  </li>
                </ul>
              </div>
            </li>
            <li class="nav-item {{ Request::is('admin/returs') || Request::is('admin/returs/*') ? 'active' : '' }}">
              <a class="nav-link" href="{{ url('admin/returs') }}">
                <span class="menu-title">Retur</span>
                <i class="mdi mdi-keyboard-return menu-icon"></i>
              </a>
            </li>

          </ul>
        </nav>
        <!-- partial -->
        <div class="main-panel">
          <div class="content-wrapper">
            @yield('content')
          </div>
          <!-- content-wrapper ends -->
          <!-- partial:../../partials/_footer.html -->
          
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
            @if(session('success'))
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
            @if($errors->any())
                let errorMessages = '';
                @foreach($errors->all() as $error)
                    errorMessages += '{{ $error }}\n';
                @endforeach
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: errorMessages,
                    customClass: {
                        popup: 'rounded-4'
                    }
                });
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
                        confirmButtonColor: '#fe7c96',
                        cancelButtonColor: '#c3cdd6',
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
                        confirmButtonColor: '#b66dff',
                        cancelButtonColor: '#c3cdd6',
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
                confirmButtonColor: '#b66dff',
                cancelButtonColor: '#c3cdd6',
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
  </body>
</html>

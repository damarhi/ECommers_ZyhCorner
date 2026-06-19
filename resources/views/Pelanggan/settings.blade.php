@extends('Layout.layout_pelanggan')

@section('title', 'Settings - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span style="color: #00b207;">Settings</span>
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

    /* Dashboard layout wrapper */
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

    /* Settings Right Panel Styling */
    .settings-panel {
        background: white;
        border: 1px solid #e6e6e6;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .settings-panel-header {
        font-size: 18px;
        font-weight: 600;
        color: #1a1a1a;
        padding: 20px 24px;
        border-bottom: 1px solid #f2f2f2;
    }

    .settings-panel-body {
        padding: 24px;
    }

    .form-group-settings {
        margin-bottom: 20px;
    }

    .form-group-settings label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        margin-bottom: 8px;
    }

    .form-control-settings {
        width: 100%;
        height: 45px;
        padding: 10px 16px;
        border: 1px solid #e6e6e6;
        border-radius: 6px;
        font-size: 14px;
        color: #1a1a1a;
        outline: none;
        transition: border-color 0.3s;
        background-color: white;
    }

    .form-control-settings:focus {
        border-color: #00b207;
        box-shadow: 0 0 0 3px rgba(0, 178, 7, 0.05);
    }

    /* Avatar section */
    .avatar-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        padding: 10px;
    }

    .avatar-circle {
        width: 160px;
        height: 160px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid #e6e6e6;
        background-color: #f8f9fa;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }

    .avatar-circle img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .btn-chose-img {
        border: 2px solid #00b207;
        background-color: transparent;
        color: #00b207;
        padding: 8px 24px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-chose-img:hover {
        background-color: #00b207;
        color: white;
    }

    .btn-save-settings {
        background-color: #00b207;
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }

    .btn-save-settings:hover {
        background-color: #009906;
    }

    /* Password visibility wrapper */
    .password-input-wrapper {
        position: relative;
    }

    .password-toggle-icon {
        position: absolute;
        right: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
        cursor: pointer;
        font-size: 16px;
        transition: color 0.3s;
    }

    .password-toggle-icon:hover {
        color: #1a1a1a;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            margin: 20px auto 40px;
        }
        .settings-panel-header {
            padding: 15px 20px;
            font-size: 16px;
        }
        .settings-panel-body {
            padding: 20px 15px;
        }
        .avatar-wrapper {
            margin-top: 20px;
            border-top: 1px dashed #e6e6e6;
            padding-top: 20px;
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
            
            @php
                $names = explode(' ', $user->nama, 2);
                $firstName = $names[0] ?? '';
                $lastName = $names[1] ?? '';
            @endphp
            
            <!-- Panel 1: Account Settings -->
            <div class="settings-panel">
                <div class="settings-panel-header">Account Settings</div>
                <div class="settings-panel-body">
                    <form id="formAccountSettings" onsubmit="saveAccountSettings(event)">
                        <div class="row">
                            <!-- Left inputs -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group-settings">
                                            <label for="inputFirstName">First name</label>
                                            <input type="text" id="inputFirstName" class="form-control-settings" value="{{ $firstName }}" required>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group-settings">
                                            <label for="inputLastName">Last Name</label>
                                            <input type="text" id="inputLastName" class="form-control-settings" value="{{ $lastName }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group-settings">
                                    <label for="inputAccountEmail">Email</label>
                                    <input type="email" id="inputAccountEmail" class="form-control-settings" value="{{ $user->email }}" required>
                                </div>
                                <div class="form-group-settings">
                                    <label for="inputAccountPhone">Phone Number</label>
                                    <input type="text" id="inputAccountPhone" class="form-control-settings" value="{{ $user->no_hp }}" required>
                                </div>
                            </div>
                            
                            <!-- Right photo -->
                            <div class="col-md-4">
                                <div class="avatar-wrapper">
                                    <div class="avatar-circle">
                                        <!-- Cute cat profile avatar matching the screenshot -->
                                        <img id="avatarImage" src="https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?q=80&w=400&auto=format&fit=crop" alt="Profile Picture">
                                    </div>
                                    <input type="file" id="avatarFileInput" accept="image/*" style="display: none;" onchange="handleAvatarFile(this)">
                                    <button type="button" class="btn-chose-img" onclick="document.getElementById('avatarFileInput').click()">Chose Image</button>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="submit" class="btn-save-settings">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Panel 2: Billing Address -->
            <div class="settings-panel">
                <div class="settings-panel-header">Billing Address</div>
                <div class="settings-panel-body">
                    <form id="formBillingAddress" onsubmit="saveBillingAddress(event)">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group-settings">
                                    <label for="inputBillingFirstName">First name</label>
                                    <input type="text" id="inputBillingFirstName" class="form-control-settings" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-settings">
                                    <label for="inputBillingLastName">Last name</label>
                                    <input type="text" id="inputBillingLastName" class="form-control-settings">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group-settings">
                                    <label for="inputBillingCompany">Company Name (optional)</label>
                                    <input type="text" id="inputBillingCompany" class="form-control-settings">
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group-settings">
                            <label for="inputBillingStreet">Street Address</label>
                            <input type="text" id="inputBillingStreet" class="form-control-settings" placeholder="e.g. pekanbaru, Rumbai, Umban Sari" required>
                        </div>
                        
                        <div class="form-group-settings">
                            <label for="inputBillingDescribe">Describe Place</label>
                            <input type="text" id="inputBillingDescribe" class="form-control-settings" placeholder="e.g. orange house">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-settings">
                                    <label for="inputBillingEmail">Email</label>
                                    <input type="email" id="inputBillingEmail" class="form-control-settings" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-settings">
                                    <label for="inputBillingPhone">Phone</label>
                                    <input type="text" id="inputBillingPhone" class="form-control-settings" required>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 10px;">
                            <button type="submit" class="btn-save-settings">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Panel 3: Change Password -->
            <div class="settings-panel">
                <div class="settings-panel-header">Change Password</div>
                <div class="settings-panel-body">
                    <form id="formChangePassword" onsubmit="saveNewPassword(event)">
                        <div class="form-group-settings">
                            <label for="inputCurrentPassword">Current Password</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="inputCurrentPassword" class="form-control-settings" placeholder="Password" required>
                                <span class="password-toggle-icon" onclick="togglePasswordVisibility('inputCurrentPassword', this)">
                                    <i class="fa-regular fa-eye-slash"></i>
                                </span>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group-settings">
                                    <label for="inputNewPassword">New Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="inputNewPassword" class="form-control-settings" placeholder="Password" required>
                                        <span class="password-toggle-icon" onclick="togglePasswordVisibility('inputNewPassword', this)">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-settings">
                                    <label for="inputConfirmPassword">Confirm Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" id="inputConfirmPassword" class="form-control-settings" placeholder="Password" required>
                                        <span class="password-toggle-icon" onclick="togglePasswordVisibility('inputConfirmPassword', this)">
                                            <i class="fa-regular fa-eye-slash"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 10px;">
                            <button type="submit" class="btn-save-settings" style="background-color: #00b207;">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
        </div>
        
    </div>
</div>

<script>
    // Load and Sync inputs with localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const storedName = localStorage.getItem('zyh_cust_name') || "{{ $user->nama }}";
        const storedAddress = localStorage.getItem('zyh_cust_address') || "pekanbaru, Rumbai, Umban Sari";
        const storedEmail = localStorage.getItem('zyh_cust_email') || "{{ $user->email }}";
        const storedPhone = localStorage.getItem('zyh_cust_phone') || "(+62) {{ $user->no_hp }}";
        const storedAvatar = localStorage.getItem('zyh_cust_avatar');
        
        // Billing Address split name
        const names = storedName.split(' ');
        const billFirstName = names[0] || '';
        const billLastName = names.slice(1).join(' ') || '';
        
        document.getElementById('inputBillingFirstName').value = billFirstName;
        document.getElementById('inputBillingLastName').value = billLastName;
        document.getElementById('inputBillingStreet').value = storedAddress;
        document.getElementById('inputBillingEmail').value = storedEmail;
        document.getElementById('inputBillingPhone').value = storedPhone;
        
        const storedCompany = localStorage.getItem('zyh_cust_billing_company') || "PCR";
        const storedDescribe = localStorage.getItem('zyh_cust_billing_describe') || "orange house";
        
        document.getElementById('inputBillingCompany').value = storedCompany;
        document.getElementById('inputBillingDescribe').value = storedDescribe;

        if (storedAvatar) {
            document.getElementById('avatarImage').src = storedAvatar;
        }
    });

    // Handle avatar upload and convert to base64
    function handleAvatarFile(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const base64Data = e.target.result;
                document.getElementById('avatarImage').src = base64Data;
                localStorage.setItem('zyh_cust_avatar', base64Data);
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Foto profil berhasil diunggah.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                });
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Save Account Settings Handler
    function saveAccountSettings(e) {
        e.preventDefault();
        
        const firstName = document.getElementById('inputFirstName').value;
        const lastName = document.getElementById('inputLastName').value;
        const email = document.getElementById('inputAccountEmail').value;
        const phone = document.getElementById('inputAccountPhone').value;
        const fullName = (firstName + ' ' + lastName).trim();
        
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        fetch("{{ route('settings.account') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nama: fullName,
                email: email,
                no_hp: phone
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                // Sync with local storage
                localStorage.setItem('zyh_cust_name', fullName);
                localStorage.setItem('zyh_cust_email', email);
                localStorage.setItem('zyh_cust_phone', phone);
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Pengaturan akun berhasil disimpan.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Terjadi kesalahan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan sistem.',
                icon: 'error',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            });
        });
    }

    // Save Billing Address Handler
    function saveBillingAddress(e) {
        e.preventDefault();
        
        const firstName = document.getElementById('inputBillingFirstName').value;
        const lastName = document.getElementById('inputBillingLastName').value;
        const company = document.getElementById('inputBillingCompany').value;
        const street = document.getElementById('inputBillingStreet').value;
        const describe = document.getElementById('inputBillingDescribe').value;
        const email = document.getElementById('inputBillingEmail').value;
        const phone = document.getElementById('inputBillingPhone').value;
        const fullName = (firstName + ' ' + lastName).trim();
        
        // Save to localStorage directly
        localStorage.setItem('zyh_cust_name', fullName);
        localStorage.setItem('zyh_cust_address', street);
        localStorage.setItem('zyh_cust_email', email);
        localStorage.setItem('zyh_cust_phone', phone);
        localStorage.setItem('zyh_cust_billing_company', company);
        localStorage.setItem('zyh_cust_billing_describe', describe);
        
        Swal.fire({
            title: 'Berhasil!',
            text: 'Alamat tagihan berhasil disimpan.',
            icon: 'success',
            confirmButtonColor: '#00b207',
            customClass: { popup: 'rounded-4' }
        }).then(() => {
            window.location.reload();
        });
    }

    // Save Password Handler
    function saveNewPassword(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('inputCurrentPassword').value;
        const newPassword = document.getElementById('inputNewPassword').value;
        const confirmPassword = document.getElementById('inputConfirmPassword').value;
        
        if (newPassword !== confirmPassword) {
            Swal.fire({
                title: 'Password Tidak Cocok!',
                text: 'Konfirmasi password baru tidak cocok.',
                icon: 'warning',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            });
            return;
        }
        
        Swal.fire({
            title: 'Memproses...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => { Swal.showLoading(); }
        });
        
        fetch("{{ route('settings.password') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success) {
                // Clear inputs
                document.getElementById('inputCurrentPassword').value = '';
                document.getElementById('inputNewPassword').value = '';
                document.getElementById('inputConfirmPassword').value = '';
                
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'Password berhasil diperbarui.',
                    icon: 'success',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Terjadi kesalahan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207',
                    customClass: { popup: 'rounded-4' }
                });
            }
        })
        .catch(error => {
            Swal.close();
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Terjadi kesalahan sistem.',
                icon: 'error',
                confirmButtonColor: '#00b207',
                customClass: { popup: 'rounded-4' }
            });
        });
    }

    // Toggle Password Visibility
    function togglePasswordVisibility(inputId, iconEl) {
        const input = document.getElementById(inputId);
        const icon = iconEl.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    }
</script>
@endsection

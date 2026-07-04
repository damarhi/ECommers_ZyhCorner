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
                                                <input type="text" id="inputFirstName" class="form-control-settings"
                                                    value="{{ $firstName }}" required>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group-settings">
                                                <label for="inputLastName">Last Name</label>
                                                <input type="text" id="inputLastName" class="form-control-settings"
                                                    value="{{ $lastName }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group-settings">
                                        <label for="inputAccountEmail">Email</label>
                                        <input type="email" id="inputAccountEmail" class="form-control-settings"
                                            value="{{ $user->email }}" required>
                                    </div>
                                    <div class="form-group-settings">
                                        <label for="inputAccountPhone">Phone Number</label>
                                        <input type="text" id="inputAccountPhone" class="form-control-settings"
                                            value="{{ $user->no_hp }}" required>
                                    </div>
                                </div>

                                <!-- Right photo -->
                                <div class="col-md-4">
                                    <div class="avatar-wrapper">
                                        <div class="avatar-circle">
                                            <img id="avatarImage"
                                                src="{{ $user->avatar ? asset($user->avatar) : asset('image/default-avatar.svg') }}"
                                                alt="Profile Picture">
                                        </div>
                                        <input type="file" id="avatarFileInput" accept="image/*" style="display: none;"
                                            onchange="handleAvatarFile(this)">
                                        <button type="button" class="btn-chose-img"
                                            onclick="document.getElementById('avatarFileInput').click()">Chose
                                            Image</button>
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
                                        <input type="text" id="inputBillingFirstName" class="form-control-settings"
                                            required>
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
                                <input type="text" id="inputBillingStreet" class="form-control-settings"
                                    value="{{ $user->alamat }}" placeholder="e.g. pekanbaru, Rumbai, Umban Sari" required>
                            </div>

                            <div class="form-group-settings">
                                <label for="inputBillingDescribe">Describe Place</label>
                                <input type="text" id="inputBillingDescribe" class="form-control-settings"
                                    placeholder="e.g. orange house">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-settings">
                                        <label for="inputBillingEmail">Email</label>
                                        <input type="email" id="inputBillingEmail" class="form-control-settings"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-settings">
                                        <label for="inputBillingPhone">Phone</label>
                                        <input type="text" id="inputBillingPhone" class="form-control-settings"
                                            required>
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
                                    <input type="password" id="inputCurrentPassword" class="form-control-settings"
                                        placeholder="Password" required>
                                    <span class="password-toggle-icon"
                                        onclick="togglePasswordVisibility('inputCurrentPassword', this)">
                                        <i class="fa-regular fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group-settings">
                                        <label for="inputNewPassword">New Password</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" id="inputNewPassword" class="form-control-settings"
                                                placeholder="Password" required>
                                            <span class="password-toggle-icon"
                                                onclick="togglePasswordVisibility('inputNewPassword', this)">
                                                <i class="fa-regular fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group-settings">
                                        <label for="inputConfirmPassword">Confirm Password</label>
                                        <div class="password-input-wrapper">
                                            <input type="password" id="inputConfirmPassword"
                                                class="form-control-settings" placeholder="Password" required>
                                            <span class="password-toggle-icon"
                                                onclick="togglePasswordVisibility('inputConfirmPassword', this)">
                                                <i class="fa-regular fa-eye-slash"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 10px;">
                                <button type="submit" class="btn-save-settings"
                                    style="background-color: #00b207;">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

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
    </script>
@endsection

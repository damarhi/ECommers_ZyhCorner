@extends('layout.layout_pelanggan')

@section('title', 'Daftar - Zyh Corner')

@section('breadcrumb')
    <a href="#">Account</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span>Register</span>
@endsection

@section('styles')
<style>
    /* Google Button Styling */
    .social-login-separator {
        display: flex;
        align-items: center;
        text-align: center;
        color: #999;
        font-size: 12px;
        margin: 25px 0;
    }
    
    .social-login-separator::before,
    .social-login-separator::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e6e6e6;
    }
    
    .social-login-separator:not(:empty)::before {
        margin-right: 15px;
    }
    
    .social-login-separator:not(:empty)::after {
        margin-left: 15px;
    }
    
    .google-login-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        background-color: white;
        border: 1.5px solid #e6e6e6;
        color: #4d4d4d;
        padding: 12px 15px;
        border-radius: 50px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-decoration: none;
    }
    
    .google-login-btn:hover {
        background-color: #f9f9f9;
        border-color: #cccccc;
        color: #1a1a1a;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    
    .google-login-btn img {
        width: 18px;
        height: 18px;
    }
</style>
@endsection

@section('content')
    <!-- Register Section -->
    <section class="login-section">
        <div class="login-card">
            <h2>Daftar Akun</h2>
            <form action="{{ route('register.post') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger" style="color: #ef4444; margin-bottom: 15px; font-size: 14px; background: #fee2e2; padding: 10px; border-radius: 4px; border: 1px solid #fca5a5;">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="form-group">
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" value="{{ old('nama') }}" required autocomplete="name" autofocus>
                </div>

                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Alamat Email" value="{{ old('email') }}" required autocomplete="email">
                </div>

                <div class="form-group">
                    <input type="tel" name="no_hp" class="form-control" placeholder="Nomor HP / WhatsApp (e.g. 081234567890)" value="{{ old('no_hp') }}" required autocomplete="tel">
                </div>

                <div class="form-group" style="position: relative;">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Kata Sandi (Min. 6 Karakter)" required autocomplete="new-password">
                    <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('password', this)"></i>
                </div>

                <div class="form-group" style="position: relative;">
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Konfirmasi Kata Sandi" required autocomplete="new-password">
                    <i class="fa-regular fa-eye password-toggle" onclick="togglePasswordVisibility('password_confirmation', this)"></i>
                </div>

                <button type="submit" class="login-btn" style="margin-top: 10px;">Daftar</button>
            </form>
            <!-- Divider & Google Sign-In Button -->
            <div class="social-login-separator">atau daftar dengan</div>
            <a href="{{ route('login.google') }}" class="google-login-btn">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo">
                Akun Google
            </a>

            <div class="register-link" style="margin-top: 20px;">
                Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a>
            </div>
        </div>
    </section>

    <script>
        function togglePasswordVisibility(fieldId, icon) {
            const field = document.getElementById(fieldId);
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                field.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
@endsection

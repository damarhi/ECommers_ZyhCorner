@extends('Layout.layout_pelanggan')

@section('title', 'Login - Zyh Corner')

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
    <!-- Login Section -->
    <section class="login-section">
        <div class="login-card">
            <h2 style="text-align: center; margin-bottom: 20px;">Masuk Ke Akun</h2>
            
            @if ($errors->any())
                <div class="alert alert-danger" style="color: #ef4444; margin-bottom: 15px; font-size: 14px; background: #fee2e2; padding: 10px 15px; border-radius: 4px; border: 1px solid #fca5a5;">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" value="{{ old('email') }}" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Kata Sandi" required>
                    <i class="fa-regular fa-eye password-toggle"></i>
                </div>
                <div class="login-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Ingat saya
                    </label>
                    <a href="#" class="forgot-password">Lupa Kata Sandi</a>
                </div>
                <button type="submit" class="login-btn">Masuk</button>
            </form>

            <!-- Divider & Google Sign-In Button -->
            <div class="social-login-separator">atau masuk dengan</div>
            <a href="{{ route('login.google') }}" class="google-login-btn">
                <img src="https://upload.wikimedia.org/wikipedia/commons/c/c1/Google_%22G%22_logo.svg" alt="Google Logo">
                Akun Google
            </a>

            <div class="register-link" style="margin-top: 25px;">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>
        </div>
    </section>

    <script>
        // Toggle Password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.password-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    if (input.type === 'password') {
                        input.type = 'text';
                        this.classList.replace('fa-regular', 'fa-solid');
                    } else {
                        input.type = 'password';
                        this.classList.replace('fa-solid', 'fa-regular');
                    }
                });
            }
        });
    </script>
@endsection

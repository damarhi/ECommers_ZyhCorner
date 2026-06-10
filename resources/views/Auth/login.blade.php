@extends('layout.layout_pelanggan')

@section('title', 'Login - Zyh Corner')

@section('content')
    <!-- Login Section -->
    <section class="login-section">
        <div class="login-card">
            <h2>Masuk</h2>
            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger" style="color: #ef4444; margin-bottom: 15px; font-size: 14px;">
                        {{ $errors->first() }}
                    </div>
                @endif

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
            <div class="register-link">
                Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
            </div>
        </div>
    </section>
@endsection

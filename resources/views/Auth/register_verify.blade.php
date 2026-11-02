@extends('Layout.layout_pelanggan')

@section('title', 'Verifikasi Pendaftaran - Zyh Corner')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/auth-otp.css') }}?v={{ file_exists(public_path('css/auth-otp.css')) ? filemtime(public_path('css/auth-otp.css')) : time() }}">
@endsection

@section('content')
<section class="login-section" style="padding: 60px 0;">
    <div class="otp-verify-card">
        <h2 class="otp-verify-title">Verifikasi Email Anda</h2>
        <p class="otp-verify-subtitle">
            Kami telah mengirimkan 6-digit kode verifikasi ke email:<br>
            <span class="otp-verify-email">{{ $email }}</span>
        </p>

        @if ($errors->any())
            <div class="alert alert-danger" style="color: #ef4444; margin-bottom: 20px; font-size: 14px; background: #fee2e2; padding: 10px 15px; border-radius: 4px; border: 1px solid #fca5a5;">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register.verify.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" name="otp" class="otp-code-input" placeholder="000000" maxlength="6" pattern="\d{6}" required autocomplete="one-time-code" autofocus>
            </div>
            <button type="submit" class="otp-submit-btn">Verifikasi & Daftar</button>
        </form>

        <div class="otp-resend-container">
            Belum menerima kode? 
            <span id="resend-text">
                Kirim ulang dalam <strong id="countdown-sec">60</strong> detik
            </span>
            <a id="resend-link" class="otp-resend-link disabled" style="display: none;" onclick="resendOtpCode()">Kirim Ulang Kode</a>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
    window.authOtpConfig = {
        resendRoute: "{{ route('register.verify.resend') }}",
        csrfToken: "{{ csrf_token() }}"
    };
</script>
<script src="{{ asset('js/auth-otp.js') }}?v={{ file_exists(public_path('js/auth-otp.js')) ? filemtime(public_path('js/auth-otp.js')) : time() }}"></script>
@endsection

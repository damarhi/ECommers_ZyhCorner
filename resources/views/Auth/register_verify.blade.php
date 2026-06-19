@extends('Layout.layout_pelanggan')

@section('title', 'Verifikasi Pendaftaran - Zyh Corner')

@section('styles')
<style>
    .otp-verify-card {
        max-width: 450px;
        margin: 40px auto;
        padding: 40px 30px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        border: 1px solid #f0f0f0;
    }
    
    .otp-verify-title {
        text-align: center;
        margin-bottom: 15px;
        font-size: 24px;
        font-weight: 600;
        color: #1a1a1a;
    }
    
    .otp-verify-subtitle {
        text-align: center;
        color: #666;
        font-size: 14px;
        line-height: 1.5;
        margin-bottom: 25px;
    }
    
    .otp-verify-email {
        font-weight: 600;
        color: #00b207;
    }
    
    .otp-code-input {
        text-align: center;
        font-size: 28px;
        letter-spacing: 12px;
        font-weight: 700;
        padding: 12px;
        border: 2px solid #e6e6e6;
        border-radius: 8px;
        width: 100%;
        margin-bottom: 20px;
        outline: none;
        transition: border-color 0.3s;
    }
    
    .otp-code-input:focus {
        border-color: #00b207;
    }
    
    .otp-submit-btn {
        width: 100%;
        background-color: #00b207;
        color: white;
        border: none;
        padding: 14px;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .otp-submit-btn:hover {
        background-color: #009906;
    }
    
    .otp-resend-container {
        text-align: center;
        margin-top: 25px;
        font-size: 14px;
        color: #666;
    }
    
    .otp-resend-link {
        color: #00b207;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
        transition: color 0.3s;
    }
    
    .otp-resend-link:hover {
        color: #009906;
    }
    
    .otp-resend-link.disabled {
        color: #999;
        cursor: not-allowed;
        pointer-events: none;
    }
</style>
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

<script>
    let secondsLeft = 60;
    let countdownInterval;

    function startTimer() {
        const resendText = document.getElementById('resend-text');
        const resendLink = document.getElementById('resend-link');
        const countdownSec = document.getElementById('countdown-sec');
        
        secondsLeft = 60;
        resendText.style.display = 'inline';
        resendLink.style.display = 'none';
        resendLink.classList.add('disabled');
        countdownSec.textContent = secondsLeft;

        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            secondsLeft--;
            countdownSec.textContent = secondsLeft;

            if (secondsLeft <= 0) {
                clearInterval(countdownInterval);
                resendText.style.display = 'none';
                resendLink.style.display = 'inline';
                resendLink.classList.remove('disabled');
            }
        }, 1000);
    }

    document.addEventListener('DOMContentLoaded', function() {
        startTimer();
    });

    function resendOtpCode() {
        const resendLink = document.getElementById('resend-link');
        if (resendLink.classList.contains('disabled')) return;

        resendLink.classList.add('disabled');
        resendLink.textContent = 'Mengirim...';

        fetch("{{ route('register.verify.resend') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message,
                    icon: 'success',
                    confirmButtonColor: '#00b207'
                });
                resendLink.textContent = 'Kirim Ulang Kode';
                startTimer();
            } else {
                resendLink.classList.remove('disabled');
                resendLink.textContent = 'Kirim Ulang Kode';
                Swal.fire({
                    title: 'Gagal',
                    text: data.message || 'Gagal mengirim kode verifikasi.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(error => {
            resendLink.classList.remove('disabled');
            resendLink.textContent = 'Kirim Ulang Kode';
            Swal.fire({
                title: 'Kesalahan',
                text: 'Terjadi kesalahan sistem. Silakan coba lagi nanti.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
        });
    }
</script>
@endsection

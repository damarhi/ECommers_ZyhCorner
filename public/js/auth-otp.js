/**
 * auth-otp.js
 * Shared handler for OTP countdown timer and AJAX resend requests
 */
(function() {
    let secondsLeft = 60;
    let countdownInterval;

    function startTimer() {
        const resendText = document.getElementById('resend-text');
        const resendLink = document.getElementById('resend-link');
        const countdownSec = document.getElementById('countdown-sec');

        if (!resendText || !resendLink || !countdownSec) return;

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

    function resendOtpCode() {
        const resendLink = document.getElementById('resend-link');
        if (!resendLink || resendLink.classList.contains('disabled')) return;

        const config = window.authOtpConfig || {};
        if (!config.resendRoute) {
            console.error('authOtpConfig.resendRoute is missing');
            return;
        }

        resendLink.classList.add('disabled');
        resendLink.textContent = 'Mengirim...';

        fetch(config.resendRoute, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#00b207'
                    });
                } else {
                    alert(data.message || 'Berhasil mengirim kode!');
                }
                resendLink.textContent = 'Kirim Ulang Kode';
                startTimer();
            } else {
                resendLink.classList.remove('disabled');
                resendLink.textContent = 'Kirim Ulang Kode';
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Gagal',
                        text: data.message || 'Gagal mengirim kode verifikasi.',
                        icon: 'error',
                        confirmButtonColor: '#00b207'
                    });
                } else {
                    alert(data.message || 'Gagal mengirim kode verifikasi.');
                }
            }
        })
        .catch(error => {
            resendLink.classList.remove('disabled');
            resendLink.textContent = 'Kirim Ulang Kode';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan sistem. Silakan coba lagi nanti.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            } else {
                alert('Terjadi kesalahan sistem. Silakan coba lagi nanti.');
            }
        });
    }

    // Expose functions globally
    window.startTimer = startTimer;
    window.resendOtpCode = resendOtpCode;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startTimer);
    } else {
        startTimer();
    }
})();

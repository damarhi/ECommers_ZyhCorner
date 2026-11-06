// =========================================================================
// Zyh Corner - Order Detail Interactive Scripts (public/js/js_order_detail.js)
// =========================================================================

// Biteship Tracking Modal Handlers
function openBiteshipTrackingModal() {
    const modal = document.getElementById('biteshipTrackingModal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeBiteshipTrackingModal() {
    const modal = document.getElementById('biteshipTrackingModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Address Detail Modal Handlers
function openAddressDetailModal() {
    const modal = document.getElementById('orderAddressDetailModal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeAddressDetailModal() {
    const modal = document.getElementById('orderAddressDetailModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Upload Proof Modal Handlers
function openUploadProofModal(isReadOnly = false) {
    const modal = document.getElementById('uploadProofModal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const dropzone = document.getElementById('modalProofDropzone');
        const footerSubmitBtn = document.getElementById('btnSubmitDetailProof');

        if (isReadOnly) {
            if (dropzone) dropzone.style.display = 'none';
            if (footerSubmitBtn) footerSubmitBtn.style.display = 'none';
        } else {
            if (dropzone) dropzone.style.display = 'block';
            if (footerSubmitBtn) footerSubmitBtn.style.display = 'inline-flex';
        }

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeUploadProofModal() {
    const modal = document.getElementById('uploadProofModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

let detailModalSelectedFile = null;

function handleModalProofSelect(event) {
    const file = event.target.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        Swal.fire({
            title: 'Ukuran Terlalu Besar!',
            text: 'Maksimal ukuran berkas bukti transfer adalah 2MB.',
            icon: 'warning',
            confirmButtonColor: '#00b207'
        });
        event.target.value = '';
        return;
    }

    const allowed = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    if (!allowed.includes(file.type)) {
        Swal.fire({
            title: 'Format Tidak Didukung!',
            text: 'Harap pilih berkas gambar berformat JPG, PNG, atau WEBP.',
            icon: 'warning',
            confirmButtonColor: '#00b207'
        });
        event.target.value = '';
        return;
    }

    detailModalSelectedFile = file;

    const reader = new FileReader();
    reader.onload = function(e) {
        const previewImg = document.getElementById('modalPreviewImg');
        const previewFileName = document.getElementById('modalPreviewFileName');
        const previewFileSize = document.getElementById('modalPreviewFileSize');
        const promptEl = document.getElementById('modalDropzonePrompt');
        const previewBox = document.getElementById('modalDropzonePreview');

        if (previewImg) previewImg.src = e.target.result;
        if (previewFileName) previewFileName.textContent = file.name;
        if (previewFileSize) previewFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

        if (promptEl) promptEl.style.display = 'none';
        if (previewBox) previewBox.style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function clearModalProof(event) {
    if (event) event.stopPropagation();
    detailModalSelectedFile = null;
    const input = document.getElementById('modalProofInput');
    if (input) input.value = '';

    const promptEl = document.getElementById('modalDropzonePrompt');
    const previewBox = document.getElementById('modalDropzonePreview');
    if (promptEl) promptEl.style.display = 'block';
    if (previewBox) previewBox.style.display = 'none';
}

function copyDetailModalAcc(acc) {
    if (!acc) return;
    navigator.clipboard.writeText(acc).then(() => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Nomor rekening berhasil disalin!',
            showConfirmButton: false,
            timer: 2000
        });
    });
}

function submitDetailModalProof() {
    if (!detailModalSelectedFile) {
        Swal.fire({
            title: 'Pilih Berkas!',
            text: 'Silakan pilih foto bukti pembayaran terlebih dahulu sebelum menyimpan.',
            icon: 'info',
            confirmButtonColor: '#00b207'
        });
        return;
    }

    const cfg = window.orderDetailConfig || {};
    const csrfToken = cfg.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '');
    const uploadUrl = cfg.uploadProofUrl || '';

    const formData = new FormData();
    formData.append('bukti_pembayaran', detailModalSelectedFile);
    formData.append('_token', csrfToken);

    const btn = document.getElementById('btnSubmitModalProof');
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengunggah...`;
    }

    fetch(uploadUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if (data.success) {
            closeUploadProofModal();
            Swal.fire({
                title: 'Berhasil!',
                text: data.message || 'Bukti pembayaran berhasil diunggah.',
                icon: 'success',
                confirmButtonColor: '#00b207'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Gagal Mengunggah',
                text: data.message || 'Terjadi kesalahan saat mengunggah bukti pembayaran.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        console.error(err);
        Swal.fire({
            title: 'Kesalahan Sistem',
            text: 'Gagal mengunggah berkas. Periksa koneksi internet Anda.',
            icon: 'error',
            confirmButtonColor: '#00b207'
        });
    });
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBiteshipTrackingModal();
        closeAddressDetailModal();
    }
});

// Copy Tracking Waybill / Resi
function copyTrackingResi(code) {
    if (!code) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(() => {
            showResiToast(code);
        }).catch(() => fallbackCopyResi(code));
    } else {
        fallbackCopyResi(code);
    }
}

function fallbackCopyResi(code) {
    const temp = document.createElement('input');
    temp.value = code;
    document.body.appendChild(temp);
    temp.select();
    document.execCommand('copy');
    document.body.removeChild(temp);
    showResiToast(code);
}

function showResiToast(code) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'No. Resi Disalin!',
            text: code,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        });
    }
}

// Profile/address sync: preserve order-specific address
document.addEventListener('DOMContentLoaded', function() {
    const cfg = window.orderDetailConfig || {};
    const storedName = cfg.orderName;
    const storedEmail = cfg.orderEmail;
    const storedPhone = cfg.orderPhone;

    if (storedName) document.querySelectorAll('#displayBillingName').forEach(el => el.textContent = storedName);
    if (storedEmail) document.querySelectorAll('#displayBillingEmail').forEach(el => el.textContent = storedEmail);
    if (storedPhone) document.querySelectorAll('#displayBillingPhone').forEach(el => el.textContent = storedPhone);
});

// Tab Alerts for settings
function showTabAlert(tabName) {
    Swal.fire({
        title: tabName,
        text: `Halaman ${tabName} pelanggan akan segera hadir!`,
        icon: 'info',
        confirmButtonColor: '#00b207',
        customClass: { popup: 'rounded-4' }
    });
}

// Cancel Order Handler with Reason Modal
function cancelOrderDetail(id, orderNumber) {
    const cfg = window.orderDetailConfig || {};
    const csrfToken = cfg.csrfToken || (document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '');

    Swal.fire({
        title: 'ALASAN PEMBATALAN PESANAN',
        html: `
            <div style="text-align: left; margin-top: 10px;">
                <label class="cancel-reason-option selected" onclick="selectCancelReason(this)">
                    <input type="radio" name="cancel_reason" value="Salah memilih alamat pengiriman" checked>
                    <span>Salah memilih alamat pengiriman</span>
                </label>
                <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                    <input type="radio" name="cancel_reason" value="Salah memilih produk atau varian kue">
                    <span>Salah memilih produk atau varian kue</span>
                </label>
                <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                    <input type="radio" name="cancel_reason" value="Ingin mengganti metode pembayaran">
                    <span>Ingin mengganti metode pembayaran</span>
                </label>
                <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                    <input type="radio" name="cancel_reason" value="Ingin mengubah atau menambah pesanan">
                    <span>Ingin mengubah atau menambah pesanan</span>
                </label>
                <label class="cancel-reason-option" onclick="selectCancelReason(this)">
                    <input type="radio" name="cancel_reason" value="Lainnya">
                    <span>Lainnya (Alasan pribadi)</span>
                </label>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Konfirmasi Batal',
        cancelButtonText: 'Kembali',
        confirmButtonColor: '#00b207',
        cancelButtonColor: '#ffffff',
        customClass: {
            popup: 'cancel-reason-popup',
            title: 'cancel-reason-title',
            cancelButton: 'cancel-reason-cancel-btn',
            confirmButton: 'cancel-reason-confirm-btn'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const selectedRadio = document.querySelector('input[name="cancel_reason"]:checked');
            const cancelReason = selectedRadio ? selectedRadio.value : 'Lainnya';

            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const cancelUrl = (cfg.cancelUrlTemplate || `/order/:id/cancel`).replace(':id', id);
            fetch(cancelUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ cancel_reason: cancelReason })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        title: 'Dibatalkan!',
                        text: data.message || 'Pesanan Anda telah berhasil dibatalkan.',
                        icon: 'success',
                        confirmButtonColor: '#00b207',
                        customClass: { popup: 'rounded-4' }
                    }).then(() => {
                        window.location.href = cfg.orderHistoryUrl || '/order-history';
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Gagal membatalkan pesanan.',
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
    });
}

// Select cancel reason helper
function selectCancelReason(el) {
    document.querySelectorAll('.cancel-reason-option').forEach(opt => {
        opt.classList.remove('selected');
    });
    el.classList.add('selected');
    el.querySelector('input[type="radio"]').checked = true;
}

// Pay order now
function payOrderNow(orderId) {
    const cfg = window.orderDetailConfig || {};
    Swal.fire({
        title: 'Mendapatkan Token Pembayaran...',
        text: 'Harap tunggu.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const payUrl = (cfg.payUrlTemplate || `/order/:id/pay`).replace(':id', orderId);
    const checkPaymentUrl = (cfg.checkPaymentUrlTemplate || `/order/:id/check-payment`).replace(':id', orderId);

    fetch(payUrl)
        .then(response => response.json())
        .then(data => {
            Swal.close();
            if (data.success && data.snap_token) {
                if (data.snap_token.startsWith('MOCK-SNAP-')) {
                    Swal.fire({
                        title: 'Simulasi Pembayaran (Mock Mode)',
                        text: 'Sistem berjalan dalam mode simulasi pengujian. Apakah Anda ingin mensimulasikan pembayaran lunas?',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonColor: '#00b207',
                        confirmButtonText: 'Ya, Tandai Lunas',
                        cancelButtonText: 'Batal'
                    }).then((mockRes) => {
                        if (mockRes.isConfirmed) {
                            fetch(checkPaymentUrl).then(() => window.location.reload());
                        }
                    });
                    return;
                }
                if (typeof snap === 'undefined' || !snap || typeof snap.pay !== 'function') {
                    Swal.fire({
                        title: 'Midtrans Belum Siap',
                        text: 'SDK Midtrans Snap belum termuat sempurna. Pastikan variabel MIDTRANS_CLIENT_KEY telah diisi di file .env dan koneksi internet aktif.',
                        icon: 'warning',
                        confirmButtonColor: '#00b207'
                    });
                    return;
                }
                snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        fetch(checkPaymentUrl)
                            .then(() => {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Pembayaran berhasil dikonfirmasi.',
                                    icon: 'success',
                                    confirmButtonColor: '#00b207'
                                }).then(() => {
                                    window.location.reload();
                                });
                            });
                    },
                    onPending: function(result) {
                        fetch(checkPaymentUrl)
                            .then(res => res.json())
                            .then(resData => {
                                if (resData.success && resData.status === 'Paid') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Pembayaran berhasil dikonfirmasi.',
                                        icon: 'success',
                                        confirmButtonColor: '#00b207'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Tertunda',
                                        text: 'Pembayaran sedang diproses atau tertunda.',
                                        icon: 'info',
                                        confirmButtonColor: '#00b207'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            });
                    },
                    onError: function(result) {
                        Swal.fire({
                            title: 'Gagal',
                            text: 'Pembayaran gagal dilakukan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207'
                        });
                    },
                    onClose: function() {
                        fetch(checkPaymentUrl)
                            .then(res => res.json())
                            .then(resData => {
                                if (resData.success && resData.status === 'Paid') {
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: 'Pembayaran berhasil dikonfirmasi.',
                                        icon: 'success',
                                        confirmButtonColor: '#00b207'
                                    }).then(() => {
                                        window.location.reload();
                                    });
                                }
                            });
                    }
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal memulai pembayaran.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Koneksi ke server gagal.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            console.error(err);
        });
}

// Payment Expiry Countdown Timer
document.addEventListener('DOMContentLoaded', function() {
    const cfg = window.orderDetailConfig || {};
    if (!cfg.isPending || !cfg.expiryTimestamp) return;

    const expiryTime = Number(cfg.expiryTimestamp) * 1000;
    const timerElement = document.getElementById('paymentCountdown');
    if (!timerElement) return;

    let timerInterval = null;

    function updateTimer() {
        const now = new Date().getTime();
        const distance = expiryTime - now;

        if (distance <= 0) {
            timerElement.innerHTML = "00:00:00";
            timerElement.style.color = "#ea4b48";
            if (timerInterval) clearInterval(timerInterval);
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            return;
        }

        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        const formattedHours = String(hours).padStart(2, '0');
        const formattedMinutes = String(minutes).padStart(2, '0');
        const formattedSeconds = String(seconds).padStart(2, '0');

        timerElement.innerHTML = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
    }

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
});

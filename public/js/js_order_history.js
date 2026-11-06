/* ============================================================================
 * Order History Page Script (Extracted from Pelanggan/order_history.blade.php)
 * ============================================================================ */

function getOrderHistoryCsrfToken() {
    if (window.orderHistoryConfig && window.orderHistoryConfig.csrfToken) {
        return window.orderHistoryConfig.csrfToken;
    }
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
}

let currentActiveOrderIdForProof = null;
let histModalSelectedFile = null;

function openHistoryProofModal(orderId, orderNumber, totalFormatted, proofUrl, hasProof, uploadedAt, isReadOnly = false, isRejected = false, rejectionReason = '') {
    currentActiveOrderIdForProof = orderId;
    histModalSelectedFile = null;

    document.getElementById('histModalOrderNum').textContent = orderNumber;
    document.getElementById('histModalOrderTotal').textContent = totalFormatted;

    const rejectBox = document.getElementById('histRejectionAlertBox');
    const rejectText = document.getElementById('histRejectionAlertText');
    const currentBox = document.getElementById('histCurrentProofBox');
    const dropzone = document.getElementById('histModalProofDropzone');
    const dropzoneTitle = document.getElementById('histModalDropzoneTitle');
    const submitBtn = document.getElementById('btnSubmitHistProof');

    if (isRejected && rejectBox) {
        rejectBox.style.display = 'block';
        if (rejectText) rejectText.textContent = rejectionReason || 'Bukti transfer tidak valid atau tidak terbaca.';
        if (dropzoneTitle) dropzoneTitle.textContent = 'Klik atau Drag Foto Bukti Transfer Baru ke Sini';
        if (submitBtn) {
            submitBtn.style.background = '#dc2626';
            submitBtn.style.borderColor = '#dc2626';
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Simpan & Kirim Ulang Bukti';
        }
    } else {
        if (rejectBox) rejectBox.style.display = 'none';
        if (submitBtn) {
            submitBtn.style.background = '#00b207';
            submitBtn.style.borderColor = '#00b207';
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up me-1"></i> Simpan & Kirim Bukti';
        }
    }

    if (currentBox) {
        if (hasProof && proofUrl) {
            currentBox.style.display = 'flex';
            const imgEl = document.getElementById('histCurrentProofImg');
            if (imgEl) imgEl.src = proofUrl;
            const linkEl = document.getElementById('histCurrentProofLink');
            if (linkEl) linkEl.href = proofUrl;
            const linkBtnEl = document.getElementById('histCurrentProofLinkBtn');
            if (linkBtnEl) linkBtnEl.href = proofUrl;
            const timeEl = document.getElementById('histCurrentProofTime');
            if (timeEl) timeEl.textContent = uploadedAt ? uploadedAt + ' WIB' : 'Terkirim';
            if (!isRejected && dropzoneTitle) dropzoneTitle.textContent = 'Pilih foto baru jika ingin mengganti bukti';
        } else {
            currentBox.style.display = 'none';
            if (!isRejected && dropzoneTitle) dropzoneTitle.textContent = 'Klik atau Drag Foto Bukti Transfer ke Sini';
        }
    }

    if (isReadOnly) {
        if (dropzone) dropzone.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';
    } else {
        if (dropzone) dropzone.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'inline-flex';
    }
    clearHistoryProof();

    const modal = document.getElementById('historyProofModal');
    if (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('show'), 10);
        document.body.style.overflow = 'hidden';
    }
}

function closeHistoryProofModal() {
    const modal = document.getElementById('historyProofModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 200);
        document.body.style.overflow = '';
    }
    currentActiveOrderIdForProof = null;
    histModalSelectedFile = null;
}

function handleHistoryProofSelect(event) {
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

    histModalSelectedFile = file;

    const reader = new FileReader();
    reader.onload = function(e) {
        const previewImg = document.getElementById('histModalPreviewImg');
        const previewFileName = document.getElementById('histModalPreviewFileName');
        const previewFileSize = document.getElementById('histModalPreviewFileSize');
        const promptEl = document.getElementById('histModalDropzonePrompt');
        const previewBox = document.getElementById('histModalDropzonePreview');

        if (previewImg) previewImg.src = e.target.result;
        if (previewFileName) previewFileName.textContent = file.name;
        if (previewFileSize) previewFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

        if (promptEl) promptEl.style.display = 'none';
        if (previewBox) previewBox.style.display = 'flex';
    };
    reader.readAsDataURL(file);
}

function clearHistoryProof(event) {
    if (event) event.stopPropagation();
    histModalSelectedFile = null;
    const input = document.getElementById('histModalProofInput');
    if (input) input.value = '';

    const promptEl = document.getElementById('histModalDropzonePrompt');
    const previewBox = document.getElementById('histModalDropzonePreview');
    if (promptEl) promptEl.style.display = 'block';
    if (previewBox) previewBox.style.display = 'none';
}

function copyHistoryModalAcc(acc) {
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

function submitHistoryProof() {
    if (!currentActiveOrderIdForProof) return;

    if (!histModalSelectedFile) {
        Swal.fire({
            title: 'Pilih Berkas!',
            text: 'Silakan pilih foto bukti pembayaran terlebih dahulu sebelum menyimpan.',
            icon: 'info',
            confirmButtonColor: '#00b207'
        });
        return;
    }

    const formData = new FormData();
    formData.append('bukti_pembayaran', histModalSelectedFile);
    formData.append('_token', getOrderHistoryCsrfToken());

    const btn = document.getElementById('btnSubmitHistProof');
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengunggah...`;
    }

    fetch(`/order-history/${currentActiveOrderIdForProof}/upload-proof`, {
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
            closeHistoryProofModal();
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

let currentTrackingData = null;

function openOrderHistoryTracking(data) {
    if (!data) return;
    currentTrackingData = data;

    // Header & Meta
    document.getElementById('modalOrderNumberDisplay').textContent = data.order_number;
    document.getElementById('modalResiCodeDisplay').textContent = data.resi_code;
    document.getElementById('modalResiCodeText').textContent = data.resi_code;

    // Courier & Recipient
    const iconEl = document.getElementById('modalCourierIcon');
    iconEl.className = 'fa-solid ' + (data.courier_icon || 'fa-motorcycle') + ' text-success me-1';
    document.getElementById('modalCourierName').textContent = data.courier_name || 'Kurir Toko / Pengiriman Standar';
    document.getElementById('modalRecipientName').textContent = data.recipient_name || '-';

    // Status Badge
    const badgeContainer = document.getElementById('modalStatusBadgeContainer');
    let badgeHtml = '';
    if (data.is_completed) {
        badgeHtml = '<span class="badge-track-pill pill-success"><i class="fa-solid fa-circle-check me-1"></i> Sampai di Tujuan</span>';
    } else if (data.is_canceled) {
        badgeHtml = '<span class="badge-track-pill pill-danger"><i class="fa-solid fa-circle-xmark me-1"></i> Pengiriman Batal</span>';
    } else if (data.is_shipping) {
        badgeHtml = '<span class="badge-track-pill pill-info"><i class="fa-solid fa-truck-fast me-1"></i> Sedang Dikirim</span>';
    } else if (data.is_processing) {
        badgeHtml = '<span class="badge-track-pill pill-warning"><i class="fa-solid fa-box me-1"></i> Sedang Dikemas</span>';
    } else if (data.is_awaiting_confirmation) {
        badgeHtml = '<span class="badge-track-pill pill-amber"><i class="fa-solid fa-clock-rotate-left me-1"></i> Menunggu Konfirmasi</span>';
    } else {
        badgeHtml = '<span class="badge-track-pill pill-secondary"><i class="fa-regular fa-clock me-1"></i> Menunggu Pembayaran</span>';
    }
    badgeContainer.innerHTML = badgeHtml;

    // Links
    document.getElementById('modalDetailLink').href = data.detail_url;
    document.getElementById('modalWaLink').href = data.wa_url;

    // Dynamic Checkpoint Stepper
    const timeline = document.getElementById('modalTimelineContainer');
    let timelineHtml = '';

    if (data.is_canceled) {
        timelineHtml = `
            <div class="timeline-step active">
                <div class="timeline-marker marker-success"><i class="fa-solid fa-check"></i></div>
                <div class="timeline-content">
                    <div class="timeline-title">Pesanan Dikonfirmasi</div>
                    <div class="timeline-time">${data.created_at_formatted} WIB</div>
                    <div class="timeline-desc">Pesanan sempat terdaftar di sistem Zyh Corner.</div>
                </div>
            </div>
            <div class="timeline-step active">
                <div class="timeline-marker marker-danger"><i class="fa-solid fa-ban"></i></div>
                <div class="timeline-content">
                    <div class="timeline-title text-danger">Pengiriman Dibatalkan</div>
                    <div class="timeline-time">${data.updated_at_formatted} WIB</div>
                    <div class="timeline-desc">Proses logistik pengiriman dihentikan karena pesanan telah dibatalkan.</div>
                </div>
            </div>
        `;
    } else {
        const step = parseInt(data.status_step || 1);
        const step1Title = data.is_awaiting_confirmation 
            ? 'Menunggu Konfirmasi Toko'
            : (data.is_pending ? 'Menunggu Pembayaran' : 'Pesanan Dikonfirmasi &amp; Sedang Disiapkan');
        const step1Desc = data.is_awaiting_confirmation
            ? 'Pesanan telah diterima dan sedang menunggu konfirmasi staf kasir/dapur Zyh Corner. Anda masih dapat membatalkan pesanan ini jika diperlukan.'
            : (data.is_pending ? 'Menunggu penyelesaian pembayaran sebelum diteruskan ke dapur.' : 'Dapur Zyh Corner telah mengonfirmasi pesanan dan menyiapkan hidangan dengan higienis.');
        const step1Marker = (data.is_awaiting_confirmation || data.is_pending) ? 'marker-amber' : 'marker-success';
        const step1Icon = (data.is_awaiting_confirmation || data.is_pending) ? 'fa-clock-rotate-left' : 'fa-check';

        timelineHtml = `
            <div class="timeline-step active">
                <div class="timeline-marker ${step1Marker}"><i class="fa-solid ${step1Icon}"></i></div>
                <div class="timeline-content">
                    <div class="timeline-title">${step1Title}</div>
                    <div class="timeline-time">${data.created_at_formatted} WIB</div>
                    <div class="timeline-desc">${step1Desc}</div>
                </div>
            </div>
            <div class="timeline-step ${step >= 2 ? 'active' : ''}">
                <div class="timeline-marker ${step >= 2 ? (step === 2 ? 'marker-active' : 'marker-success') : 'marker-muted'}">
                    <i class="fa-solid ${step > 2 ? 'fa-check' : 'fa-box'}"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Kurir Dialokasikan (Driver Assigned)</div>
                    <div class="timeline-time">${step >= 2 ? 'Kurir telah menerima pesanan' : 'Menunggu kurir siap'}</div>
                    <div class="timeline-desc">Sistem logistik Biteship menghubungkan pesanan dengan armada pengiriman terdekat.</div>
                </div>
            </div>
            <div class="timeline-step ${step >= 3 ? 'active' : ''}">
                <div class="timeline-marker ${step >= 3 ? (step === 3 ? 'marker-active' : 'marker-success') : 'marker-muted'}">
                    <i class="fa-solid ${step > 3 ? 'fa-check' : 'fa-truck-fast'}"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Pesanan Dalam Perjalanan (In Transit)</div>
                    <div class="timeline-time">${step >= 3 ? 'Kurir sedang menuju alamat tujuan' : 'Menunggu penjemputan'}</div>
                    <div class="timeline-desc">Paket telah di-pickup dan sedang dalam rute pengantaran ke alamat pembeli.</div>
                </div>
            </div>
            <div class="timeline-step ${step >= 4 ? 'active' : ''}">
                <div class="timeline-marker ${step >= 4 ? 'marker-success' : 'marker-muted'}">
                    <i class="fa-solid fa-flag-checkered"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-title">Pesanan Telah Tiba di Tujuan (Delivered)</div>
                    <div class="timeline-time">${step >= 4 ? data.updated_at_formatted + ' WIB' : 'Estimasi tiba hari ini'}</div>
                    <div class="timeline-desc">Paket telah diterima dengan baik di alamat tujuan. Terima kasih telah berbelanja di Zyh Corner!</div>
                </div>
            </div>
        `;
    }
    timeline.innerHTML = timelineHtml;

    // Display Modal
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

function copyModalResi() {
    if (!currentTrackingData || !currentTrackingData.resi_code) return;
    const code = currentTrackingData.resi_code;
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

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBiteshipTrackingModal();
    }
});

// Global trackOrder connector
window.trackOrder = function(orderNumber) {
    const btn = document.querySelector(`[data-tracking-order="${orderNumber}"]`);
    if (btn) {
        btn.click();
    }
};

// Cancel Reason and Confirmation
function selectCancelReason(el) {
    document.querySelectorAll('.cancel-reason-option').forEach(opt => opt.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio) radio.checked = true;
}

function cancelOrder(id, orderNumber) {
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
                text: 'Harap tunggu.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            const csrfToken = getOrderHistoryCsrfToken();
            fetch(`/order-history/${id}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ cancel_reason: cancelReason })
            })
            .then(res => res.json())
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
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: data.message || 'Gagal membatalkan pesanan.',
                        icon: 'error',
                        confirmButtonColor: '#00b207'
                    });
                }
            })
            .catch(err => {
                Swal.close();
                Swal.fire({
                    title: 'Error!',
                    text: 'Koneksi ke server gagal saat membatalkan pesanan.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
                console.error(err);
            });
        }
    });
}

function payOrderNow(orderId) {
    Swal.fire({
        title: 'Mendapatkan Token Pembayaran...',
        text: 'Harap tunggu.',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/order-history/${orderId}/pay`)
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
                            fetch(`/order-history/${orderId}/check-payment`).then(() => window.location.reload());
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
                        fetch(`/order-history/${orderId}/check-payment`)
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
                        fetch(`/order-history/${orderId}/check-payment`)
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
                        fetch(`/order-history/${orderId}/check-payment`)
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

@extends('Layout.layout_pelanggan')

@section('title', 'Order Success - Zyh Corner')

@section('breadcrumb')
    <a href="{{ route('userChart') }}">Shopping Cart</a>
    <i class="fa-solid fa-chevron-right" style="font-size: 10px; color: #999;"></i>
    <span>Order Placed</span>
@endsection

@section('content')
<!-- Midtrans Snap JS (Required for Pay Now) -->
@if($order->payment_status === 'Pending' && strtoupper($order->payment_method) !== 'COD')
    @php
        $isProduction = config('services.midtrans.is_production', false);
        $snapUrl = $isProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
        $clientKey = config('services.midtrans.client_key');
    @endphp
    <script type="text/javascript" src="{{ $snapUrl }}" data-client-key="{{ $clientKey }}"></script>
@endif

<div class="order-success-wrapper">
    <div class="order-success-card">
        
        <!-- Header Section -->
        <div class="success-header-section">
            
            <h1 class="success-page-title">Order Placed Successfully!</h1>
            <p class="success-page-subtitle">Thank you for shopping at Zyh Corner. Your order details are given below.</p>
        </div>

        <!-- Main Grid Layout -->
        <div class="success-grid">
            
            <!-- Left Side: Order Meta & Summary -->
            <div class="success-meta-col">
                <div class="order-info-card">
                    <h3 class="card-section-title"><i class="fa-solid fa-circle-info text-green"></i> Order Details</h3>
                    <div class="info-row">
                        <span class="info-label">Order Number:</span>
                        <span class="info-value text-bold text-green">{{ $order->order_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Order Date:</span>
                        <span class="info-value">{{ $order->created_at->isoFormat('D MMMM YYYY, HH:mm') }} WIB</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value text-bold">{{ $order->payment_method }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Shipping Method:</span>
                        <span class="info-value">{{ $order->shipping_method }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Address:</span>
                        <span class="info-value text-muted">{{ $order->alamat_pengiriman ?: 'Alamat belum diatur' }}</span>
                    </div>
                    @if($order->catatan_pesanan)
                    <div class="info-row">
                        <span class="info-label">Notes:</span>
                        <span class="info-value text-italic text-muted">"{{ $order->catatan_pesanan }}"</span>
                    </div>
                    @endif
                    <div class="info-row total-row">
                        <span class="info-label">Total Payment:</span>
                        <span class="info-value total-price" id="displayTotal">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Dynamically Populated Order Items -->
                <div class="order-items-card">
                    <h3 class="card-section-title"><i class="fa-solid fa-basket-shopping text-green"></i> Items Ordered</h3>
                    <div id="successOrderItemsList" class="items-list-container">
                        <!-- Loaded dynamically from localStorage.getItem('zyh_last_order') -->
                        <div class="items-loading-placeholder">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading items...
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Payment Deadline & Actions -->
            <div class="success-payment-col">
                
                @if(strtoupper($order->payment_method) === 'COD')
                    <!-- COD Info Card -->
                    <div class="payment-status-card cod-theme">
                        <div class="payment-icon-wrapper">
                            <i class="fa-solid fa-truck-fast"></i>
                        </div>
                        <h3 class="payment-card-status">Cash on Delivery</h3>
                        <p class="payment-card-desc">No upfront payment is required. Please prepare cash to pay the driver upon arrival.</p>
                        <div class="cod-instruction-box">
                            <div class="instruction-step">
                                <span class="step-num">1</span>
                                <span>We will verify and pack your items.</span>
                            </div>
                            <div class="instruction-step">
                                <span class="step-num">2</span>
                                <span>Courier ({{ $order->shipping_method }}) will deliver the order to your address.</span>
                            </div>
                            <div class="instruction-step text-bold">
                                <span class="step-num">3</span>
                                <span>Pay <strong>Rp. {{ number_format($order->total, 0, ',', '.') }}</strong> directly to the courier.</span>
                            </div>
                        </div>
                    </div>
                @elseif($order->is_bank_transfer)
                    <!-- Manual Bank Transfer Card with Rekening Info & Proof Upload -->
                    <div class="payment-status-card bank-theme" id="bankPaymentCard" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; background: #ffffff; box-shadow: 0 4px 15px rgba(0,0,0,0.03);">
                        @php
                            $isBankOrderConfirmed = ($order->payment_status === 'Paid') || in_array(strtolower($order->order_status ?? ''), ['dikemas', 'dikirim', 'selesai']);
                        @endphp
                        @if($isBankOrderConfirmed)
                            <div class="payment-icon-wrapper success" style="width: 56px; height: 56px; border-radius: 50%; background: #e8f8ee; color: #00b207; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 12px;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h3 class="payment-card-status text-green" style="font-size: 18px; font-weight: 700; color: #00b207; text-align: center; margin-bottom: 6px;">Pembayaran Terverifikasi Lunas</h3>
                            <p class="payment-card-desc" style="font-size: 13px; color: #64748b; text-align: center; margin-bottom: 15px;">Bukti transfer Anda telah diverifikasi oleh kasir. Pesanan Anda kini berstatus <strong>{{ ucfirst($order->order_status) }}</strong>.</p>
                            @if($order->has_payment_proof)
                                <div class="text-center mb-3">
                                    <a href="{{ $order->payment_proof_url }}" target="_blank" class="btn btn-sm btn-outline-success" style="font-size: 12px; border-radius: 6px; padding: 5px 14px;">
                                        <i class="fa-solid fa-file-invoice-dollar me-1"></i> Lihat Bukti Transfer
                                    </a>
                                </div>
                            @endif
                        @elseif($order->order_status === 'batal' || $order->payment_status === 'Cancelled')
                            <div class="payment-icon-wrapper failed" style="width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 12px;">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                            <h3 class="payment-card-status text-red" style="font-size: 18px; font-weight: 700; color: #ef4444; text-align: center; margin-bottom: 6px;">Pesanan Dibatalkan</h3>
                            <p class="payment-card-desc" style="font-size: 13px; color: #64748b; text-align: center; margin-bottom: 15px;">Pesanan ini telah dibatalkan.</p>
                        @else
                            @php
                                $isRejected = $order->is_payment_proof_rejected;
                            @endphp
                            <div class="payment-icon-wrapper pending" id="bankIconWrapper" style="width: 56px; height: 56px; border-radius: 50%; background: {{ $isRejected ? '#fef2f2' : ($order->has_payment_proof ? '#ecfdf5' : '#fffbeb') }}; color: {{ $isRejected ? '#dc2626' : ($order->has_payment_proof ? '#059669' : '#f59e0b') }}; display: flex; align-items: center; justify-content: center; font-size: 26px; margin: 0 auto 12px;">
                                @if($isRejected)
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                @elseif($order->has_payment_proof)
                                    <i class="fa-solid fa-file-circle-check"></i>
                                @else
                                    <i class="fa-solid fa-building-columns"></i>
                                @endif
                            </div>
                            
                            <h3 class="payment-card-status" id="bankStatusTitle" style="font-size: 18px; font-weight: 700; color: {{ $isRejected ? '#dc2626' : '#1e293b' }}; text-align: center; margin-bottom: 6px;">
                                @if($isRejected)
                                    Bukti Pembayaran Ditolak Kasir
                                @elseif($order->has_payment_proof)
                                    Bukti Pembayaran Terkirim
                                @else
                                    Instruksi Transfer Bank
                                @endif
                            </h3>
                            
                            <p class="payment-card-desc" id="bankStatusDesc" style="font-size: 13px; color: #64748b; text-align: center; margin-bottom: 16px;">
                                @if($isRejected)
                                    Bukti transfer sebelumnya tidak dapat diterima. Silakan periksa alasan di bawah dan unggah bukti baru.
                                @elseif($order->has_payment_proof)
                                    Bukti transfer Anda sedang dalam antrean verifikasi kasir. Anda dapat mengunggah bukti baru jika ada perubahan.
                                @else
                                    Silakan lakukan transfer sesuai nominal tagihan ke nomor rekening resmi toko Zyh Corner:
                                @endif
                            </p>

                            @if($isRejected)
                                <!-- Alert Banner Alasan Penolakan -->
                                <div class="rejection-alert-box p-3 mb-3 rounded-3" style="background: #fef2f2; border: 1px solid #fecaca; text-align: left;">
                                    <div class="d-flex align-items-start gap-2.5">
                                        <i class="fa-solid fa-circle-exclamation text-danger mt-0.5" style="font-size: 16px;"></i>
                                        <div>
                                            <div style="font-size: 13px; font-weight: 700; color: #991b1b; margin-bottom: 2px;">
                                                Alasan Penolakan:
                                            </div>
                                            <div style="font-size: 12.5px; color: #b91c1c;">
                                                {{ $order->payment_proof_rejection_reason ?? 'Bukti pembayaran sebelumnya tidak valid. Mohon unggah bukti transfer yang sah.' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <!-- Card Rekening Resmi Toko Zyh Corner -->
                            <div class="bank-rekening-box" style="background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 11px; font-weight: 700; text-transform: uppercase; background: #1E2753; color: #ffffff; padding: 3px 8px; border-radius: 4px;">{{ $storeBank['name'] ?? 'BCA' }}</span>
                                        <span style="font-size: 12px; color: #475569; font-weight: 600;">a/n {{ $storeBank['account_holder'] ?? 'ZYH CORNER PASTRY' }}</span>
                                    </div>
                                    <button type="button" class="btn-copy-acc" onclick="copyAccountNumber('{{ $storeBank['account_number'] ?? '8295-0192-38' }}')" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 10px; font-size: 12px; color: #1E2753; font-weight: 600; cursor: pointer; transition: all 0.2s;" title="Salin Nomor Rekening">
                                        <i class="fa-regular fa-copy me-1"></i> <span id="copyBtnText">Salin</span>
                                    </button>
                                </div>
                                <div style="font-family: monospace; font-size: 20px; font-weight: 800; color: #0f172a; letter-spacing: 1px; margin-bottom: 8px;" id="displayAccNum">
                                    {{ $storeBank['account_number'] ?? '8295-0192-38' }}
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 8px; font-size: 12.5px;">
                                    <span style="color: #64748b;">Jumlah Tagihan:</span>
                                    <strong style="color: #00b207; font-size: 16px;">Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>
                            </div>

                            <!-- Upload Bukti Pembayaran Dropzone (Tepat di Atas Tombol Aksi) -->
                            <div class="proof-upload-section" style="margin-bottom: 16px;">
                                <label style="display: block; font-size: 12.5px; font-weight: 600; color: #334155; margin-bottom: 6px;">
                                    <i class="fa-solid fa-receipt me-1 text-primary"></i> Upload Bukti Pembayaran:
                                </label>

                                @if($order->has_payment_proof)
                                    <div id="currentProofContainer" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <a href="{{ $order->payment_proof_url }}" target="_blank" title="Buka Bukti Transfer">
                                                <img src="{{ $order->payment_proof_url }}" alt="Bukti Transfer" style="width: 44px; height: 44px; object-fit: cover; border-radius: 6px; border: 1px solid #86efac;">
                                            </a>
                                            <div>
                                                <div style="font-size: 12px; font-weight: 700; color: #166534;"><i class="fa-solid fa-circle-check me-1"></i> Bukti Terkirim</div>
                                                <div style="font-size: 11px; color: #15803d;">{{ $order->bukti_pembayaran_uploaded_at ? $order->bukti_pembayaran_uploaded_at->isoFormat('D MMMM YYYY, HH:mm') : '' }} WIB</div>
                                            </div>
                                        </div>
                                        <a href="{{ $order->payment_proof_url }}" target="_blank" class="btn btn-sm" style="font-size: 11.5px; color: #15803d; text-decoration: underline; font-weight: 600;">Lihat Foto</a>
                                    </div>
                                @endif

                                <div class="proof-dropzone" id="proofDropzone" onclick="document.getElementById('proofFileInput').click()" style="border: 2px dashed #cbd5e1; border-radius: 10px; padding: 16px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.2s;">
                                    <input type="file" id="proofFileInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;" onchange="handleProofFileSelect(event)">
                                    <div id="dropzonePrompt">
                                        <i class="fa-solid fa-cloud-arrow-up" style="font-size: 26px; color: #94a3b8; margin-bottom: 6px;"></i>
                                        <div style="font-size: 12.5px; font-weight: 600; color: #334155;">
                                            @if($isRejected)
                                                Klik atau Drag Foto Bukti Transfer Baru ke Sini
                                            @elseif($order->has_payment_proof)
                                                Pilih foto baru jika ingin mengganti bukti
                                            @else
                                                Klik atau Drag Foto Bukti Transfer ke Sini
                                            @endif
                                        </div>
                                    </div>
                                    <div id="dropzonePreview" style="display: none; align-items: center; gap: 12px; text-align: left;">
                                        <img id="previewImage" src="" alt="Pratinjau" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                                        <div style="flex-grow: 1; overflow: hidden;">
                                            <div id="previewFileName" style="font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap; text-overflow: ellipsis; overflow: hidden;">nama_file.jpg</div>
                                            <div id="previewFileSize" style="font-size: 11px; color: #64748b;">120 KB</div>
                                        </div>
                                        <button type="button" onclick="clearSelectedProof(event)" style="background: none; border: none; color: #ef4444; font-size: 16px; cursor: pointer; padding: 4px;" title="Hapus Berkas">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Pay Now / Action Button -->
                            <div class="payment-action-box" id="payButtonWrapper">
                                <button type="button" class="btn-pay-now-primary" id="btnSubmitBankProof" onclick="submitBankTransferProof()" style="{{ $isRejected ? 'background: #dc2626; border-color: #dc2626;' : ($order->has_payment_proof ? 'background: #0284c7; border-color: #0284c7;' : '') }}">
                                    @if($isRejected)
                                        <span>Upload Ulang Bukti Pembayaran</span> <i class="fa-solid fa-cloud-arrow-up"></i>
                                    @elseif($order->has_payment_proof)
                                        <span>Kirim Ulang Bukti Pembayaran</span> <i class="fa-solid fa-arrow-right"></i>
                                    @else
                                        <span>Kirim Bukti Pembayaran</span> <i class="fa-solid fa-cloud-arrow-up"></i>
                                    @endif
                                </button>
                                <button type="button" class="btn-cancel-order-outline" onclick="cancelOrderSuccessPage()">
                                    Batalkan Pesanan <i class="fa-solid fa-circle-xmark"></i>
                                </button>
                                <div class="pay-note" style="font-size: 11px; color: #94a3b8; text-align: center; margin-top: 8px;">
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i> Data dan bukti pembayaran Anda terverifikasi secara aman
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- Online Payment (Midtrans Snap: QRIS, VA, E-Wallet) Status Card -->
                    <div class="payment-status-card online-theme" id="onlinePaymentCard">
                        @if($order->payment_status === 'Paid')
                            <div class="payment-icon-wrapper success">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <h3 class="payment-card-status text-green">Payment Successful</h3>
                            <p class="payment-card-desc">We have successfully verified your payment. Your order is now being processed and packed.</p>
                        @elseif($order->order_status === 'batal' || $order->payment_status === 'Cancelled')
                            <div class="payment-icon-wrapper failed">
                                <i class="fa-solid fa-circle-xmark"></i>
                            </div>
                            <h3 class="payment-card-status text-red">Order Cancelled</h3>
                            <p class="payment-card-desc">Pesanan ini telah dibatalkan.</p>
                        @else
                            <div class="payment-icon-wrapper pending" id="paymentIconWrapper">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                            <h3 class="payment-card-status" id="paymentStatusTitle">Awaiting Payment</h3>
                            <p class="payment-card-desc" id="paymentStatusDesc">Please complete your payment before the deadline below to avoid automatic cancellation.</p>
                            
                            <!-- Live Countdown Clock -->
                            <div class="countdown-section-wrapper" id="countdownSection">
                                <div class="countdown-deadline-text">Payment Deadline:</div>
                                <div class="countdown-deadline-val">{{ \Carbon\Carbon::parse($deadline)->isoFormat('dddd, D MMMM YYYY, HH:mm') }} WIB</div>
                                <div class="countdown-clock-container">
                                    <div class="clock-segment">
                                        <span class="clock-num" id="hoursBox">23</span>
                                        <span class="clock-lbl">Hours</span>
                                    </div>
                                    <div class="clock-divider">:</div>
                                    <div class="clock-segment">
                                        <span class="clock-num" id="minutesBox">59</span>
                                        <span class="clock-lbl">Minutes</span>
                                    </div>
                                    <div class="clock-divider">:</div>
                                    <div class="clock-segment">
                                        <span class="clock-num" id="secondsBox">59</span>
                                        <span class="clock-lbl">Seconds</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Pay Now & Cancel Action Buttons -->
                            <div class="payment-action-box" id="payButtonWrapper">
                                <button type="button" class="btn-pay-now-primary" onclick="payNowSuccessPage()">
                                    Pay Now <i class="fa-solid fa-credit-card"></i>
                                </button>
                                <button type="button" class="btn-cancel-order-outline" onclick="cancelOrderSuccessPage()">
                                    Cancel Order <i class="fa-solid fa-circle-xmark"></i>
                                </button>
                                <div class="pay-note">Payments are processed securely via Midtrans</div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Navigation Card -->
                <div class="navigation-actions-card">
                    <a href="{{ route('order.history') }}" class="btn-secondary-custom">
                        <i class="fa-solid fa-clipboard-list"></i> View Order History
                    </a>
                    <a href="{{ route('home') }}" class="btn-primary-custom">
                        <i class="fa-solid fa-house"></i> Back to Home
                    </a>
                </div>

            </div>

        </div>

    </div>
</div>

<script>
    // Variables populated from backend
    const remainingSeconds = parseInt("{{ $remainingSeconds }}") || 0;
    const snapToken = "{{ $order->snap_token }}";
    const orderNumber = "{{ $order->order_number }}";

    document.addEventListener('DOMContentLoaded', function() {
        // Load items from localStorage
        const itemsList = document.getElementById('successOrderItemsList');
        if (itemsList) {
            const lastOrder = JSON.parse(localStorage.getItem('zyh_last_order')) || [];
            if (lastOrder.length > 0) {
                itemsList.innerHTML = lastOrder.map(item => `
                    <div class="success-order-item">
                        <div class="success-item-img-wrapper">
                            <img src="${item.img}" alt="${item.name}">
                        </div>
                        <div class="success-item-info">
                            <h4 class="success-item-name">${item.name}</h4>
                            <div class="success-item-meta">
                                <span>Quantity: ${item.qty}</span>
                                <span>Price: Rp. ${new Intl.NumberFormat('id-ID').format(item.price)}</span>
                            </div>
                        </div>
                        <div class="success-item-subtotal">
                            Rp. ${new Intl.NumberFormat('id-ID').format(item.price * item.qty)}
                        </div>
                    </div>
                `).join('');
            } else {
                itemsList.innerHTML = `
                    <div class="empty-items-placeholder">
                        <i class="fa-solid fa-basket-shopping"></i> No items details cached.
                    </div>
                `;
            }
        }

        // Live Countdown Clock Initialization
        if (remainingSeconds > 0 && document.getElementById('countdownSection')) {
            startCountdownTimer(remainingSeconds);
        }
    });

    // Countdown Clock Logic
    let timerInterval = null;
    function startCountdownTimer(seconds) {
        let timeLeft = seconds;
        const hoursBox = document.getElementById('hoursBox');
        const minutesBox = document.getElementById('minutesBox');
        const secondsBox = document.getElementById('secondsBox');

        function updateClock() {
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                if (hoursBox) hoursBox.textContent = '00';
                if (minutesBox) minutesBox.textContent = '00';
                if (secondsBox) secondsBox.textContent = '00';
                handlePaymentExpired();
                return;
            }

            const hrs = Math.floor(timeLeft / 3600);
            const mins = Math.floor((timeLeft % 3600) / 60);
            const secs = timeLeft % 60;

            if (hoursBox) hoursBox.textContent = String(hrs).padStart(2, '0');
            if (minutesBox) minutesBox.textContent = String(mins).padStart(2, '0');
            if (secondsBox) secondsBox.textContent = String(secs).padStart(2, '0');

            timeLeft--;
        }

        updateClock();
        timerInterval = setInterval(updateClock, 1000);
    }

    function handlePaymentExpired() {
        const desc = document.getElementById('paymentStatusDesc');
        const clock = document.getElementById('countdownSection');
        const payBtn = document.getElementById('payButtonWrapper');
        const statusTitle = document.getElementById('paymentStatusTitle');
        const iconWrapper = document.getElementById('paymentIconWrapper');

        if (desc) desc.textContent = "Waktu pembayaran pesanan ini telah habis.";
        if (clock) clock.style.display = 'none';
        if (payBtn) payBtn.style.display = 'none';
        if (statusTitle) statusTitle.textContent = "Pembayaran Kedaluwarsa";
        if (statusTitle) statusTitle.className = "payment-card-status text-red";
        if (iconWrapper) {
            iconWrapper.className = "payment-icon-wrapper failed";
            iconWrapper.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
        }
    }

    // Pay Now function utilizing Midtrans Snap
    let currentSnapToken = "{{ $order->snap_token }}";

    function payNowSuccessPage() {
        if (typeof snap === 'undefined') {
            Swal.fire({
                title: 'Error!',
                text: 'Library pembayaran gagal dimuat. Harap segarkan halaman.',
                icon: 'error',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        if (currentSnapToken && currentSnapToken.trim() !== '') {
            triggerSnapPayment(currentSnapToken);
        } else {
            Swal.fire({
                title: 'Mendapatkan Token Pembayaran...',
                text: 'Harap tunggu.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });

            fetch(`/order-history/{{ $order->id }}/pay`)
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if (data.success && data.snap_token) {
                        currentSnapToken = data.snap_token;
                        triggerSnapPayment(currentSnapToken);
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Token pembayaran tidak ditemukan. Harap hubungi dukungan pelanggan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207'
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat memproses token pembayaran.',
                        icon: 'error',
                        confirmButtonColor: '#00b207'
                    });
                });
        }
    }

    function triggerSnapPayment(token) {
        if (!token) return;
        if (token.startsWith('MOCK-SNAP-')) {
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
                    fetch(`/order-history/{{ $order->id }}/check-payment`)
                        .then(() => updateUIPaymentSuccess());
                }
            });
            return;
        }
        if (typeof snap === 'undefined' || !snap || typeof snap.pay !== 'function') {
            Swal.fire({
                title: 'Midtrans Belum Siap',
                text: 'SDK Midtrans Snap belum termuat sempurna. Pastikan variabel MIDTRANS_CLIENT_KEY telah diisi di file .env.',
                icon: 'warning',
                confirmButtonColor: '#00b207'
            });
            return;
        }
        snap.pay(token, {
            onSuccess: function(result) {
                if (typeof timerInterval !== 'undefined' && timerInterval) clearInterval(timerInterval);
                fetch(`/order-history/{{ $order->id }}/check-payment`)
                    .then(res => res.json())
                    .then(() => {
                        Swal.fire({
                            title: 'Pembayaran Berhasil!',
                            text: 'Terima kasih, pembayaran Anda telah berhasil kami terima.',
                            icon: 'success',
                            confirmButtonColor: '#00b207'
                        }).then(() => {
                            updateUIPaymentSuccess();
                        });
                    })
                    .catch(() => {
                        updateUIPaymentSuccess();
                    });
            },
            onPending: function(result) {
                fetch(`/order-history/{{ $order->id }}/check-payment`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.status === 'Paid') {
                            if (typeof timerInterval !== 'undefined' && timerInterval) clearInterval(timerInterval);
                            Swal.fire({
                                title: 'Pembayaran Berhasil!',
                                text: 'Terima kasih, pembayaran Anda telah berhasil kami terima.',
                                icon: 'success',
                                confirmButtonColor: '#00b207'
                            }).then(() => {
                                updateUIPaymentSuccess();
                            });
                        } else {
                            Swal.fire({
                                title: 'Pembayaran Tertunda',
                                text: 'Harap selesaikan pembayaran sesuai instruksi yang diberikan.',
                                icon: 'info',
                                confirmButtonColor: '#00b207'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            title: 'Pembayaran Tertunda',
                            text: 'Harap selesaikan pembayaran sesuai instruksi yang diberikan.',
                            icon: 'info',
                            confirmButtonColor: '#00b207'
                        });
                    });
            },
            onError: function(result) {
                Swal.fire({
                    title: 'Pembayaran Gagal',
                    text: 'Gagal memproses pembayaran. Silakan coba beberapa saat lagi.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            },
            onClose: function() {
                fetch(`/order-history/{{ $order->id }}/check-payment`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.status === 'Paid') {
                            if (typeof timerInterval !== 'undefined' && timerInterval) clearInterval(timerInterval);
                            Swal.fire({
                                title: 'Pembayaran Berhasil!',
                                text: 'Terima kasih, pembayaran Anda telah berhasil kami terima.',
                                icon: 'success',
                                confirmButtonColor: '#00b207'
                            }).then(() => {
                                updateUIPaymentSuccess();
                            });
                        }
                    });
            }
        });
    }

    function updateUIPaymentSuccess() {
        const card = document.getElementById('onlinePaymentCard');
        if (card) {
            card.innerHTML = `
                <div class="payment-icon-wrapper success">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <h3 class="payment-card-status text-green">Payment Successful</h3>
                <p class="payment-card-desc">We have successfully verified your payment. Your order is now being processed and packed.</p>
            `;
        }
    }

    function cancelOrderSuccessPage() {
        Swal.fire({
            title: 'Batalkan Pesanan?',
            text: 'Apakah Anda yakin ingin membatalkan pesanan ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea4b48',
            cancelButtonColor: '#c3cdd6',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-4'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                fetch("{{ route('order.cancel', $order->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    if (data.success) {
                        clearInterval(timerInterval);
                        Swal.fire({
                            title: 'Dibatalkan!',
                            text: data.message || 'Pesanan Anda telah berhasil dibatalkan.',
                            icon: 'success',
                            confirmButtonColor: '#00b207',
                            customClass: {
                                popup: 'rounded-4'
                            }
                        }).then(() => {
                            updateUIPaymentCancelled();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Gagal membatalkan pesanan.',
                            icon: 'error',
                            confirmButtonColor: '#00b207',
                            customClass: {
                                popup: 'rounded-4'
                            }
                        });
                    }
                })
                .catch(err => {
                    Swal.close();
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan sistem.',
                        icon: 'error',
                        confirmButtonColor: '#00b207',
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                    console.error(err);
                });
            }
        });
    }

    function updateUIPaymentCancelled() {
        const card = document.getElementById('onlinePaymentCard');
        if (card) {
            card.innerHTML = `
                <div class="payment-icon-wrapper failed">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
                <h3 class="payment-card-status text-red">Order Cancelled</h3>
                <p class="payment-card-desc">Pesanan ini telah dibatalkan.</p>
            `;
        }
    }

    // Bank Account Number Copy with Toast Feedback
    function copyAccountNumber(accNumber) {
        if (!accNumber) return;
        navigator.clipboard.writeText(accNumber).then(() => {
            const copyBtnText = document.getElementById('copyBtnText');
            if (copyBtnText) {
                copyBtnText.textContent = 'Tersalin!';
                setTimeout(() => {
                    copyBtnText.textContent = 'Salin';
                }, 2000);
            }
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Nomor rekening berhasil disalin!',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        }).catch(() => {
            const el = document.createElement('textarea');
            el.value = accNumber;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
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

    // Selected proof file management
    let selectedProofFile = null;

    function handleProofFileSelect(event) {
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

        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            Swal.fire({
                title: 'Format Tidak Didukung!',
                text: 'Harap pilih berkas gambar berformat JPG, PNG, atau WEBP.',
                icon: 'warning',
                confirmButtonColor: '#00b207'
            });
            event.target.value = '';
            return;
        }

        selectedProofFile = file;

        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('previewImage');
            const previewFileName = document.getElementById('previewFileName');
            const previewFileSize = document.getElementById('previewFileSize');
            const dropzonePrompt = document.getElementById('dropzonePrompt');
            const dropzonePreview = document.getElementById('dropzonePreview');

            if (previewImg) previewImg.src = e.target.result;
            if (previewFileName) previewFileName.textContent = file.name;
            if (previewFileSize) previewFileSize.textContent = (file.size / 1024).toFixed(1) + ' KB';

            if (dropzonePrompt) dropzonePrompt.style.display = 'none';
            if (dropzonePreview) dropzonePreview.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }

    function clearSelectedProof(event) {
        if (event) event.stopPropagation();
        selectedProofFile = null;
        const fileInput = document.getElementById('proofFileInput');
        if (fileInput) fileInput.value = '';

        const dropzonePrompt = document.getElementById('dropzonePrompt');
        const dropzonePreview = document.getElementById('dropzonePreview');
        if (dropzonePrompt) dropzonePrompt.style.display = 'block';
        if (dropzonePreview) dropzonePreview.style.display = 'none';
    }

    function submitBankTransferProof() {
        if (!selectedProofFile) {
            Swal.fire({
                title: 'Pilih Berkas!',
                text: 'Silakan pilih foto bukti pembayaran terlebih dahulu sebelum mengirim.',
                icon: 'info',
                confirmButtonColor: '#00b207'
            });
            return;
        }

        const formData = new FormData();
        formData.append('bukti_pembayaran', selectedProofFile);
        formData.append('_token', "{{ csrf_token() }}");

        const btn = document.getElementById('btnSubmitBankProof');
        const originalBtnHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Mengunggah...`;
        }

        fetch("{{ route('order.upload.proof', $order->id) }}", {
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
                btn.innerHTML = originalBtnHtml;
            }

            if (data.success) {
                Swal.fire({
                    title: 'Bukti Berhasil Dikirim!',
                    text: data.message || 'Kasir kami akan segera memverifikasi mutasi pembayaran Anda.',
                    icon: 'success',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Gagal Mengunggah',
                    text: data.message || 'Terjadi kendala saat mengunggah bukti pembayaran.',
                    icon: 'error',
                    confirmButtonColor: '#00b207'
                });
            }
        })
        .catch(err => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
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
</script>
@endsection

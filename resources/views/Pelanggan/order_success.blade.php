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
                @else
                    <!-- Online Payment (Transfer / E-Wallet) Status Card -->
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
        snap.pay(token, {
            onSuccess: function(result) {
                if (typeof timerInterval !== 'undefined' && timerInterval) clearInterval(timerInterval);
                Swal.fire({
                    title: 'Pembayaran Berhasil!',
                    text: 'Terima kasih, pembayaran Anda telah berhasil kami terima.',
                    icon: 'success',
                    confirmButtonColor: '#00b207'
                }).then(() => {
                    updateUIPaymentSuccess();
                });
            },
            onPending: function(result) {
                Swal.fire({
                    title: 'Pembayaran Tertunda',
                    text: 'Harap selesaikan pembayaran sesuai instruksi yang diberikan.',
                    icon: 'info',
                    confirmButtonColor: '#00b207'
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
                Swal.fire({
                    title: 'Pembayaran Dibatalkan',
                    text: 'Anda menutup popup pembayaran sebelum menyelesaikannya.',
                    icon: 'warning',
                    confirmButtonColor: '#00b207'
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
</script>
@endsection

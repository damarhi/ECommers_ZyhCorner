<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Invoice - {{ $order->order_number }}</title>
    <!-- Modern legible font for thermal receipt printing -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/invoice.css') }}">
</head>
<body>

    <!-- Screen Control Toolbar (Hidden when printed) -->
    <div class="no-print-toolbar no-print">
        <div class="toolbar-info">
            <span>Struk Thermal 80mm</span>
        </div>
        <div class="toolbar-actions">
            <button type="button" onclick="window.close(); if(!window.closed){ history.back(); }" class="btn btn-light">
                Kembali
            </button>
            <button type="button" onclick="window.print();" class="btn btn-primary">
                Cetak Struk
            </button>
        </div>
    </div>

    <!-- Thermal 80mm Receipt Container -->
    <div class="receipt-container invoice-container">
        
        <!-- Store Header -->
        <div class="store-header">
            <h1 class="store-logo">{{ \App\Models\StoreSetting::get('store_name', 'Zyh Corner') }}</h1>
            <div class="store-tagline">{{ \App\Models\StoreSetting::get('store_tagline', 'Pastry & Bakery Specialist') }}</div>
            <div class="store-address">{{ \App\Models\StoreSetting::get('store_address', 'Jl. Pemda, Pangkalan Kerinci Kota, Kec. Pangkalan Kerinci, Kabupaten Pelalawan, Riau 28654') }}</div>
            <div class="store-contacts">
                WA: {{ \App\Models\StoreSetting::get('store_phone', '0812-3456-789') }} | {{ \App\Models\StoreSetting::get('store_email', 'contact@zyhcorner.com') }}
            </div>
        </div>

        <hr class="divider-dashed">

        <!-- Transaction Meta -->
        <div class="receipt-meta">
            <div class="meta-row">
                <span class="meta-label">No. Order</span>
                <span class="meta-value meta-order-number">
                    {{ $order->order_number }}
                    <span class="channel-tag">{{ $order->jenis_penjualan }}</span>
                </span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Tanggal</span>
                <span class="meta-value">{{ $order->created_at->format('d/m/Y H:i') }} WIB</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Metode Bayar</span>
                <span class="meta-value">{{ $order->payment_method ?? 'Tunai' }}</span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Status Bayar</span>
                <span class="meta-value status-pill">
                    @if(strtoupper($order->payment_status) == 'PAID' || $order->payment_status == 'Dibayar')
                        [LUNAS]
                    @else
                        [PENDING]
                    @endif
                </span>
            </div>
        </div>

        <hr class="divider-dashed">

        <!-- Customer & Delivery Info -->
        <div class="receipt-customer">
            <div class="section-title">Pelanggan & Pengiriman</div>
            <div class="customer-detail-row">
                <span class="meta-label">Nama</span>
                <span class="meta-value font-weight-bold">{{ $order->customer_name }}</span>
            </div>
            @if(!empty($customerPhone))
            @php
                $displayPhone = (string) $customerPhone;
                if (!str_starts_with($displayPhone, '0') && !str_starts_with($displayPhone, '+') && !str_starts_with($displayPhone, '62')) {
                    $displayPhone = '0' . $displayPhone;
                }
            @endphp
            <div class="customer-detail-row">
                <span class="meta-label">No. HP</span>
                <span class="meta-value">{{ $displayPhone }}</span>
            </div>
            @endif
            @if(!empty($order->email_pembeli))
            <div class="customer-detail-row">
                <span class="meta-label">Email</span>
                <span class="meta-value">{{ $order->email_pembeli }}</span>
            </div>
            @endif
            @if(!empty($order->shipping_method))
            <div class="customer-detail-row">
                <span class="meta-label">Kurir</span>
                <span class="meta-value">{{ $order->shipping_method }}</span>
            </div>
            @endif
            @if(!empty($customerAddress['street']) || !empty($order->alamat_pengiriman))
            <div class="address-card">
                <div class="address-header-row">
                    <span class="meta-label">Alamat Pengiriman</span>
                    @if(!empty($customerAddress['label']))
                        <span class="address-label-badge">{{ $customerAddress['label'] }}</span>
                    @endif
                </div>
                <div class="address-street">
                    {{ $customerAddress['street'] ?? $order->alamat_pengiriman }}
                </div>
                @if(!empty($customerAddress['area']) || !empty($customerAddress['postal_code']))
                <div class="address-sub-area">
                    {{ $customerAddress['area'] ?? '' }}
                    @if(!empty($customerAddress['postal_code']) && !str_contains($customerAddress['street'] ?? '', $customerAddress['postal_code']))
                        ({{ $customerAddress['postal_code'] }})
                    @endif
                </div>
                @endif
                @if(!empty($customerAddress['detected_place']) && !str_contains($customerAddress['street'] ?? '', $customerAddress['detected_place']))
                <div class="address-geo-place">
                    <strong>Landmark/Tempat:</strong> {{ $customerAddress['detected_place'] }}
                </div>
                @endif
                @if(!empty($customerAddress['notes']))
                <div class="address-landmark">
                    <strong>Patokan:</strong> {{ $customerAddress['notes'] }}
                </div>
                @endif
                @if(!empty($customerAddress['gps']))
                <div class="address-gps">
                    &#128205; GPS: {{ $customerAddress['gps'] }}
                    @if(!empty($customerAddress['detected_road']) && !str_contains($customerAddress['street'] ?? '', $customerAddress['detected_road']))
                        ({{ $customerAddress['detected_road'] }})
                    @endif
                </div>
                @endif
            </div>
            @elseif(!empty($customerAddress['is_pickup']))
            <div class="customer-detail-row" style="margin-top: 4px;">
                <span class="meta-label">Metode</span>
                <span class="address-pickup-badge">Ambil di Toko / Kasir Langsung</span>
            </div>
            @endif

            @php
                $displayNotes = isset($cleanNotes) ? $cleanNotes : trim(preg_replace('/\[Titik GPS:[^\]]+\]/i', '', preg_replace('/\[Penerima:[^\]]+\]/i', '', $order->catatan_pesanan ?? '')));
            @endphp
            @if(!empty($displayNotes))
            <div class="customer-notes-block">
                <strong>Catatan:</strong> {{ $displayNotes }}
            </div>
            @endif
        </div>

        <hr class="divider-dashed">

        <!-- Items Section -->
        <div class="section-title" style="margin-bottom: 4px;">Item Belanja</div>
        <div class="items-list">
            @forelse($items as $index => $item)
            <div class="item-entry">
                <div class="item-title-row">
                    <span class="item-name">{{ $index + 1 }}. {{ $item['name'] }}</span>
                </div>
                @if(!empty($item['custom_text']))
                <div class="item-custom-text">
                    &bull; Tulisan: "{{ $item['custom_text'] }}"
                </div>
                @endif
                <div class="item-calc-row">
                    <span class="item-qty-price">{{ $item['qty'] }} &times; Rp. {{ number_format($item['price'], 0, ',', '.') }}</span>
                    <span class="item-subtotal">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                </div>
            </div>
            @empty
            <div style="text-align: center; color: #666666; padding: 12px 0;">
                Tidak ada item pesanan.
            </div>
            @endforelse
        </div>

        <hr class="divider-dashed">

        <!-- Billing Summary -->
        <div class="receipt-summary">
            <div class="summary-row">
                <span class="lbl">Subtotal Produk</span>
                <span class="val">Rp. {{ number_format($order->items_subtotal, 0, ',', '.') }}</span>
            </div>
            @if($order->shipping_cost > 0)
            <div class="summary-row">
                <span class="lbl">Ongkos Kirim</span>
                <span class="val">Rp. {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="summary-row total">
                <span class="lbl">TOTAL BAYAR</span>
                <span class="val">Rp. {{ number_format($order->total, 0, ',', '.') }}</span>
            </div>
        </div>

        <hr class="divider-dashed">

        <!-- Footer -->
        <div class="receipt-footer">
            <div class="footer-thanks">Terima Kasih Atas Kunjungan Anda!</div>
            <div class="footer-note">Simpan struk ini sebagai bukti transaksi resmi.</div>
            <div class="footer-timestamp">
                Dicetak: {{ now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB
            </div>
        </div>

    </div>

    <!-- Automatically trigger printer dialog on page load -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

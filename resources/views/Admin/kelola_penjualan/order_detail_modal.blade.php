<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1" aria-labelledby="orderDetailModalLabel{{ $order->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 760px; margin: 1.75rem auto; width: 95%;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden; max-height: calc(100vh - 3.5rem); display: flex; flex-direction: column;">
            <!-- Header Modal -->
            <div class="modal-header border-bottom bg-white flex-shrink-0" style="padding: 18px 24px;">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="d-inline-flex align-items-center justify-content-center text-primary rounded-circle" style="width: 38px; height: 38px; background-color: #ede9fe; color: #4f46e5 !important;">
                        <i class="mdi mdi-receipt-text-outline" style="font-size: 20px;"></i>
                    </span>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h6 class="modal-title font-weight-bold mb-0 text-dark" id="orderDetailModalLabel{{ $order->id }}" style="font-size: 16px;">
                                Detail {{ $order->order_number }}
                            </h6>
                            <span class="badge {{ $order->jenis_penjualan == 'Online' ? 'badge-soft-info' : 'badge-soft-dark' }}" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">
                                {{ $order->jenis_penjualan }}
                            </span>
                        </div>
                        <small class="d-inline-flex align-items-center gap-1.5 mt-0.5" style="font-size: 12px; color: #475569;">
                            <i class="mdi mdi-clock-outline text-primary" style="font-size: 14px; line-height: 1;"></i>
                            <span class="font-weight-600">{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="font-size: 12px;"></button>
            </div>
            <!-- Body Modal -->
            <div class="modal-body bg-white" style="padding: 24px; overflow-y: auto;">
                <!-- Grid Informasi: 2 Kolom Bersih & Simetris -->
                <div class="row g-3 mb-4">
                    <!-- Kolom Kiri: Pelanggan & Pengiriman -->
                    @php
                        $resolvedAddr = $order->resolved_address;
                        $customerPhone = $resolvedAddr['recipient_phone'] ?? $order->customer_phone;
                        $cleanNotes = $resolvedAddr['clean_notes'] ?? $order->clean_notes;
                    @endphp
                    <div class="col-md-6">
                        <div class="modal-info-panel h-100 d-flex flex-column">
                            <div class="modal-panel-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-account-circle-outline text-primary" style="font-size: 18px; line-height: 1;"></i>
                                    <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Informasi Pelanggan</span>
                                </div>
                                @if(!empty($resolvedAddr['label']))
                                    <span class="badge bg-white text-dark border" style="font-size: 10.5px; font-weight: 500; padding: 2px 8px; border-color: #cbd5e1 !important;">
                                        {{ $resolvedAddr['label'] }}
                                    </span>
                                @endif
                            </div>
                            
                            <div class="modal-data-row">
                                <span class="modal-data-label">Nama Pelanggan</span>
                                <span class="modal-data-value">{{ $order->customer_name }}</span>
                            </div>

                            @if(!empty($customerPhone))
                            @php
                                $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
                                if (str_starts_with($cleanPhone, '0')) {
                                    $waPhone = '62' . substr($cleanPhone, 1);
                                } elseif (str_starts_with($cleanPhone, '62')) {
                                    $waPhone = $cleanPhone;
                                } else {
                                    $waPhone = '62' . $cleanPhone;
                                }
                            @endphp
                            <div class="modal-data-row">
                                <span class="modal-data-label">No. WhatsApp / HP</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="modal-data-value">{{ $customerPhone }}</span>
                                    <a href="https://wa.me/{{ $waPhone }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-success d-inline-flex align-items-center gap-1" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;" title="Hubungi via WhatsApp">
                                        <i class="mdi mdi-whatsapp"></i> Chat
                                    </a>
                                </div>
                            </div>
                            @endif

                            @if($order->email_pembeli)
                            <div class="modal-data-row">
                                <span class="modal-data-label">Email</span>
                                <span class="modal-data-value text-truncate ms-2" style="max-width: 200px;">{{ $order->email_pembeli }}</span>
                            </div>
                            @endif

                            @if(!empty($order->shipping_method))
                            <div class="modal-data-row">
                                <span class="modal-data-label">Metode Kurir</span>
                                @if(str_contains(strtolower($order->shipping_method ?? ''), 'ambil') || str_contains(strtolower($order->shipping_method ?? ''), 'pickup'))
                                    <span class="badge-courier-pickup">
                                        <i class="mdi mdi-store-check"></i> {{ $order->shipping_method }}
                                    </span>
                                @else
                                    <span class="badge-courier-delivery">
                                        <i class="mdi mdi-truck-fast"></i> {{ $order->shipping_method }}
                                    </span>
                                @endif
                            </div>
                            @endif

                            @if(!empty($resolvedAddr['street']) || !empty($order->alamat_pengiriman))
                            <div class="py-2.5 border-bottom" style="border-color: #f1f5f9 !important;">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="modal-data-label">Alamat Pengiriman</span>
                                    @if(!empty($resolvedAddr['gps']))
                                        <span class="badge badge-soft-success d-inline-flex align-items-center gap-1" style="font-size: 10.5px; font-weight: 500; padding: 2px 7px;">
                                            <i class="mdi mdi-crosshairs-gps"></i> Terverifikasi GPS
                                        </span>
                                    @endif
                                </div>
                                <div class="modal-address-box">
                                    <div class="modal-address-street">
                                        {{ $resolvedAddr['street'] ?? $order->alamat_pengiriman }}
                                    </div>

                                    @if(!empty($resolvedAddr['detected_place']) && !str_contains($resolvedAddr['street'] ?? '', $resolvedAddr['detected_place']))
                                    <div class="modal-address-landmark">
                                        <i class="mdi mdi-store-marker-outline text-primary"></i> <span>Landmark: {{ $resolvedAddr['detected_place'] }}</span>
                                    </div>
                                    @endif

                                    @if(!empty($resolvedAddr['area']) || !empty($resolvedAddr['postal_code']))
                                    <div class="modal-address-subarea">
                                        {{ $resolvedAddr['area'] ?? '' }}
                                        @if(!empty($resolvedAddr['postal_code']) && !str_contains($resolvedAddr['street'] ?? '', $resolvedAddr['postal_code']))
                                            <span class="modal-address-postcode">{{ $resolvedAddr['postal_code'] }}</span>
                                        @endif
                                    </div>
                                    @endif

                                    @if(!empty($resolvedAddr['notes']))
                                    <div class="modal-address-subarea mt-1.5" style="color: #64748b; font-style: italic;">
                                        <span class="not-italic text-secondary">Patokan:</span> {{ $resolvedAddr['notes'] }}
                                    </div>
                                    @endif

                                    @if(!empty($resolvedAddr['gps']))
                                    <div class="mt-2.5 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="border-color: #e2e8f0 !important;">
                                        <span class="modal-address-gps">
                                            <i class="mdi mdi-map-marker text-danger"></i> {{ $resolvedAddr['gps'] }}
                                        </span>
                                        <a href="{{ $resolvedAddr['maps_url'] ?? 'https://maps.google.com/?q=' . urlencode($resolvedAddr['gps']) }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-primary d-inline-flex align-items-center gap-1" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                            <i class="mdi mdi-open-in-new"></i> Buka Google Maps
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @elseif(str_contains(strtolower($order->shipping_method ?? ''), 'ambil') || str_contains(strtolower($order->shipping_method ?? ''), 'pickup') || $order->jenis_penjualan === 'Offline')
                            <div class="py-2.5 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="modal-data-label d-block mb-1.5">Metode Pengambilan</span>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge-courier-pickup" style="font-size: 11.5px; padding: 4px 10px;">
                                        <i class="mdi mdi-store-check"></i> Ambil Langsung di Outlet
                                    </span>
                                </div>
                            </div>
                            @endif

                            @if(!empty($cleanNotes))
                            <div class="mt-auto pt-3">
                                <span class="modal-data-label d-block mb-1.5">Catatan Pelanggan</span>
                                <div class="modal-notes-container">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="mdi mdi-note-text-outline text-muted flex-shrink-0" style="font-size: 16px; margin-top: 1px; line-height: 1;"></i>
                                        <span class="modal-notes-text">“{{ $cleanNotes }}”</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kolom Kanan: Status & Pembayaran -->
                    <div class="col-md-6">
                        <div class="modal-info-panel h-100 d-flex flex-column">
                            <div class="modal-panel-header">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="mdi mdi-credit-card-outline text-primary" style="font-size: 18px; line-height: 1;"></i>
                                    <span style="font-size: 12px; font-weight: 600; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">Status & Pembayaran</span>
                                </div>
                            </div>

                            <div class="modal-data-row">
                                <span class="modal-data-label">Metode Bayar</span>
                                <span class="modal-data-value">
                                    {{ $order->payment_method ?? 'Transfer' }}
                                </span>
                            </div>

                            <div class="modal-data-row">
                                <span class="modal-data-label">Status Bayar</span>
                                <div>
                                    @if(strtoupper($order->payment_status) == 'PAID' || $order->payment_status == 'Dibayar')
                                        <span class="badge badge-soft-success font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Dibayar</span>
                                    @else
                                        <span class="badge badge-soft-warning font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Tertunda</span>
                                    @endif
                                </div>
                            </div>

                            @if($order->has_payment_proof)
                            <div class="modal-data-row">
                                <span class="modal-data-label">Bukti Transfer</span>
                                <div>
                                    <button type="button" class="btn btn-xs btn-outline-warning d-inline-flex align-items-center gap-1 font-weight-500" data-bs-toggle="modal" data-bs-target="#verifyProofModal{{ $order->id }}" style="font-size: 11px; padding: 2px 8px; border-radius: 4px;">
                                        <i class="mdi mdi-file-document-check-outline"></i> Verifikasi Bukti
                                    </button>
                                </div>
                            </div>
                            @endif
                            <div class="modal-data-row">
                                <span class="modal-data-label">Status Pesanan</span>
                                <div id="modal-detail-status-badge-{{ $order->id }}">
                                    @if($order->order_status == 'menunggu pembayaran')
                                        <span class="badge badge-soft-warning font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Menunggu Pembayaran</span>
                                    @elseif($order->order_status == 'menunggu konfirmasi')
                                        <span class="badge badge-soft-warning badge-pulse-amber font-weight-600" style="font-size: 11.5px; padding: 4px 10px;">Menunggu Konfirmasi</span>
                                    @elseif($order->order_status == 'dikemas')
                                        <span class="badge badge-soft-info font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Dikemas</span>
                                    @elseif($order->order_status == 'dikirim')
                                        <span class="badge badge-soft-primary font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Dikirim</span>
                                    @elseif($order->order_status == 'selesai')
                                        <span class="badge badge-soft-success font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Selesai</span>
                                    @elseif($order->order_status == 'batal')
                                        <span class="badge badge-soft-danger font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">Dibatalkan</span>
                                    @else
                                        <span class="badge badge-soft-dark font-weight-600" style="font-size: 11.5px; padding: 4px 10px; border-radius: 4px;">{{ $order->order_status }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($order->shipping_method)
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top" style="border-color: #f1f5f9 !important;">
                                <span class="modal-data-label">Kurir Pengiriman</span>
                                @if(str_contains(strtolower($order->shipping_method ?? ''), 'ambil') || str_contains(strtolower($order->shipping_method ?? ''), 'pickup'))
                                    <span class="badge-courier-pickup">
                                        <i class="mdi mdi-store-check"></i> {{ $order->shipping_method }}
                                    </span>
                                @else
                                    <span class="badge-courier-delivery">
                                        <i class="mdi mdi-truck-delivery-outline"></i> {{ $order->shipping_method }}
                                    </span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Items Section: Daftar Produk & Rincian Tagihan Terpadu -->
                <div class="text-uppercase mb-2 d-flex align-items-center gap-2" style="font-size: 12px; font-weight: 600; letter-spacing: 0.5px; color: #0f172a;">
                    <i class="mdi mdi-cube-outline text-primary" style="font-size: 18px; line-height: 1;"></i>
                    <span>Daftar Produk Dipesan</span>
                </div>

                <div class="rounded-3 border overflow-hidden" style="border-color: #cbd5e1 !important; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);">
                    <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f1f5f9; position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="padding: 11px 18px; font-size: 11.5px; font-weight: 600; color: #334155; letter-spacing: 0.5px; border-bottom: 1.5px solid #cbd5e1;">PRODUK</th>
                                    <th class="text-center" style="width: 75px; padding: 11px 18px; font-size: 11.5px; font-weight: 600; color: #334155; letter-spacing: 0.5px; border-bottom: 1.5px solid #cbd5e1;">QTY</th>
                                    <th class="text-end" style="width: 150px; padding: 11px 18px; font-size: 11.5px; font-weight: 600; color: #334155; letter-spacing: 0.5px; border-bottom: 1.5px solid #cbd5e1;">SUBTOTAL</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $modalItems = [];
                                    $orderItems = $order->items;
                                    if (is_array($orderItems) && count($orderItems) > 0) {
                                        foreach ($orderItems as $item) {
                                            $product = \App\Models\Produk::find($item['id'] ?? null);
                                            if (!$product) {
                                                $product = new \App\Models\Produk();
                                                $product->id_produk = $item['id'] ?? 0;
                                                $product->nama_produk = $item['name'] ?? 'Product Removed';
                                                $product->harga = $item['price'] ?? 0;
                                            }
                                            
                                            $qty = isset($item['qty']) ? intval($item['qty']) : (isset($item['quantity']) ? intval($item['quantity']) : 1);
                                            $price = isset($item['price']) ? floatval($item['price']) : floatval($product->harga);
                                            
                                            $modalItems[] = [
                                                'name' => $item['name'] ?? $product->nama_produk,
                                                'qty' => $qty,
                                                'price' => $price,
                                                'subtotal' => $price * $qty,
                                                'custom_text' => $item['custom_text'] ?? null,
                                            ];
                                        }
                                    }
                                @endphp

                                @forelse($modalItems as $item)
                                <tr>
                                    <td style="padding: 12px 18px; white-space: normal; word-wrap: break-word;">
                                        <div class="font-weight-500" style="font-size: 13px; color: #0f172a;">{{ $item['name'] }}</div>
                                        @if(!empty($item['custom_text']))
                                            <div class="badge badge-soft-info mt-1.5 d-inline-flex align-items-center gap-1.5" style="font-size: 11px; font-weight: 500; white-space: normal; text-align: left; max-width: 100%; padding: 3px 8px; border-radius: 4px;">
                                                <i class="mdi mdi-lead-pencil flex-shrink-0" style="font-size: 12px; line-height: 1;"></i>
                                                <span>Tulisan: "{{ $item['custom_text'] }}"</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-500" style="padding: 12px 18px; font-size: 13px; color: #0f172a;">{{ $item['qty'] }}</td>
                                    <td class="text-end font-weight-500" style="padding: 12px 18px; font-size: 13px; color: #0f172a;">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3.5 text-muted" style="font-size: 12px;">
                                        Tidak ada item belanja dalam pesanan ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Breakdown Tagihan Terintegrasi (Tanpa celah, sejajar dengan kolom subtotal) -->
                    <div class="border-top" style="padding: 16px 20px; background: #f8fafc; border-color: #cbd5e1 !important;">
                        <div class="row justify-content-end">
                            <div class="col-sm-7 col-md-6 col-lg-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="modal-data-label" style="font-size: 12.5px;">Subtotal Produk</span>
                                    <span class="modal-data-value" style="font-size: 13px;">Rp. {{ number_format($order->items_subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($order->shipping_cost > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="modal-data-label" style="font-size: 12.5px;">Ongkos Kirim</span>
                                    <span class="modal-data-value" style="font-size: 13px;">Rp. {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between pt-2.5 mt-2 border-top" style="border-color: #cbd5e1 !important;">
                                    <span style="font-size: 13px; font-weight: 600; color: #0f172a;">Total Tagihan</span>
                                    <strong style="font-size: 16.5px; font-weight: 700; color: #1E2753;">Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Modal -->
            <div class="modal-footer border-top bg-light d-flex justify-content-between align-items-center flex-shrink-0" style="padding: 14px 24px;">
                <span class="d-inline-flex align-items-center gap-1.5" style="font-size: 12.5px; color: #334155;">
                    <span class="font-weight-600">ID Pesanan:</span>
                    <strong class="font-weight-700" style="color: #0f172a;">#{{ $order->id }}</strong>
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal" style="padding: 6px 18px; border-radius: 6px; font-weight: 500; font-size: 12.5px;">Tutup</button>
                    <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-gradient-primary btn-sm d-inline-flex align-items-center gap-1.5 shadow-sm" style="padding: 6px 18px; border-radius: 6px; font-weight: 500; font-size: 12.5px;">
                        <i class="mdi mdi-printer" style="font-size: 15px; line-height: 1;"></i>
                        <span>Cetak Invoice</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

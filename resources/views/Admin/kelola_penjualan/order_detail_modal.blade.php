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
                        <small class="text-muted d-inline-flex align-items-center gap-1.5 mt-0.5" style="font-size: 12px;">
                            <i class="mdi mdi-clock-outline text-muted" style="font-size: 14px; line-height: 1;"></i>
                            <span>{{ $order->created_at->format('d M Y, H:i') }} WIB</span>
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
                    <div class="col-md-6">
                        <div class="h-100 d-flex flex-column" style="padding: 18px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                            <div class="text-uppercase text-muted font-weight-bold mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="font-size: 11px; letter-spacing: 0.6px;">
                                <i class="mdi mdi-account-circle-outline text-primary" style="font-size: 16px; line-height: 1;"></i>
                                <span>Informasi Pelanggan</span>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="text-muted font-weight-500" style="font-size: 12px;">Nama Pelanggan</span>
                                <strong class="text-dark text-end" style="font-size: 13px;">{{ $order->customer_name }}</strong>
                            </div>

                            @if($order->email_pembeli)
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="text-muted font-weight-500" style="font-size: 12px;">Email</span>
                                <span class="text-dark font-weight-600 text-end text-truncate ms-2" style="font-size: 13px; max-width: 200px;">{{ $order->email_pembeli }}</span>
                            </div>
                            @endif

                            @if($order->alamat_pengiriman)
                            <div class="mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="text-muted font-weight-500 d-block mb-1.5" style="font-size: 12px;">Alamat Pengiriman</span>
                                <div class="d-flex align-items-start gap-2">
                                    <i class="mdi mdi-map-marker-outline text-primary flex-shrink-0" style="font-size: 16px; margin-top: 1px; line-height: 1;"></i>
                                    <span class="text-dark font-weight-500" style="font-size: 12.5px; line-height: 1.5;">{{ $order->alamat_pengiriman }}</span>
                                </div>
                            </div>
                            @endif

                            @if($order->catatan_pesanan)
                            <div class="mt-auto pt-3">
                                <span class="text-muted font-weight-500 d-block mb-1.5" style="font-size: 11.5px;">Catatan Pesanan</span>
                                <div style="padding: 10px 14px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px;">
                                    <div class="d-flex align-items-start gap-2">
                                        <i class="mdi mdi-note-text-outline text-muted flex-shrink-0" style="font-size: 15px; margin-top: 1px; line-height: 1;"></i>
                                        <span class="text-secondary fst-italic" style="font-size: 12px; line-height: 1.5;">“{{ $order->catatan_pesanan }}”</span>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Kolom Kanan: Status & Pembayaran -->
                    <div class="col-md-6">
                        <div class="h-100 d-flex flex-column" style="padding: 18px 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;">
                            <div class="text-uppercase text-muted font-weight-bold mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="font-size: 11px; letter-spacing: 0.6px;">
                                <i class="mdi mdi-credit-card-outline text-primary" style="font-size: 16px; line-height: 1;"></i>
                                <span>Status & Pembayaran</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="text-muted font-weight-500" style="font-size: 12px;">Metode Bayar</span>
                                <span class="font-weight-600 text-dark" style="font-size: 13px;">
                                    {{ $order->payment_method ?? 'Transfer' }}
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="text-muted font-weight-500" style="font-size: 12px;">Status Bayar</span>
                                <div>
                                    @if(strtoupper($order->payment_status) == 'PAID' || $order->payment_status == 'Dibayar')
                                        <span class="badge badge-soft-success" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dibayar</span>
                                    @else
                                        <span class="badge badge-soft-warning" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Tertunda</span>
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom" style="border-color: #f1f5f9 !important;">
                                <span class="text-muted font-weight-500" style="font-size: 12px;">Status Pesanan</span>
                                <div id="modal-detail-status-badge-{{ $order->id }}">
                                    @if($order->order_status == 'menunggu pembayaran')
                                        <span class="badge badge-soft-warning" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Menunggu Pembayaran</span>
                                    @elseif($order->order_status == 'menunggu konfirmasi')
                                        <span class="badge badge-soft-warning badge-pulse-amber" style="font-size: 11px; font-weight: 600; padding: 3px 9px;">Menunggu Konfirmasi</span>
                                    @elseif($order->order_status == 'dikemas')
                                        <span class="badge badge-soft-info" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dikemas</span>
                                    @elseif($order->order_status == 'dikirim')
                                        <span class="badge badge-soft-primary" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dikirim</span>
                                    @elseif($order->order_status == 'selesai')
                                        <span class="badge badge-soft-success" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Selesai</span>
                                    @elseif($order->order_status == 'batal')
                                        <span class="badge badge-soft-danger" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">Dibatalkan</span>
                                    @else
                                        <span class="badge badge-soft-dark" style="font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 4px;">{{ $order->order_status }}</span>
                                    @endif
                                </div>
                            </div>

                            @if($order->shipping_method)
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-3">
                                <span class="text-muted font-weight-500" style="font-size: 12px;">Kurir Pengiriman</span>
                                <span class="font-weight-600 text-dark d-inline-flex align-items-center gap-1.5" style="font-size: 13px;">
                                    <i class="mdi mdi-truck-delivery-outline text-primary" style="font-size: 16px; line-height: 1;"></i>
                                    <span>{{ $order->shipping_method }}</span>
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Items Section: Daftar Produk & Rincian Tagihan Terpadu -->
                <div class="text-uppercase text-muted font-weight-bold mb-2 d-flex align-items-center gap-2" style="font-size: 11px; letter-spacing: 0.6px;">
                    <i class="mdi mdi-cube-outline text-primary" style="font-size: 16px; line-height: 1;"></i>
                    <span>Daftar Produk Dipesan</span>
                </div>

                <div class="rounded-3 border overflow-hidden" style="border-color: #e2e8f0 !important;">
                    <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8fafc; position: sticky; top: 0; z-index: 1;">
                                <tr>
                                    <th style="padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0;">PRODUK</th>
                                    <th class="text-center" style="width: 75px; padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0;">QTY</th>
                                    <th class="text-end" style="width: 150px; padding: 12px 18px; font-size: 11px; font-weight: 700; color: #475569; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0;">SUBTOTAL</th>
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
                                        <div class="font-weight-600 text-dark" style="font-size: 13px;">{{ $item['name'] }}</div>
                                        @if(!empty($item['custom_text']))
                                            <div class="badge badge-soft-info mt-1.5 d-inline-flex align-items-center gap-1.5" style="font-size: 11px; font-weight: 500; white-space: normal; text-align: left; max-width: 100%; padding: 3px 8px; border-radius: 4px;">
                                                <i class="mdi mdi-lead-pencil flex-shrink-0" style="font-size: 12px; line-height: 1;"></i>
                                                <span>Tulisan: "{{ $item['custom_text'] }}"</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center text-dark font-weight-600" style="padding: 12px 18px; font-size: 13px;">{{ $item['qty'] }}</td>
                                    <td class="text-end font-weight-600 text-dark" style="padding: 12px 18px; font-size: 13px;">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
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
                    <div class="border-top" style="padding: 16px 20px; background: #f8fafc; border-color: #e2e8f0 !important;">
                        <div class="row justify-content-end">
                            <div class="col-sm-7 col-md-6 col-lg-5">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-weight-500" style="font-size: 12.5px;">Subtotal Produk</span>
                                    <span class="text-dark font-weight-600" style="font-size: 12.5px;">Rp. {{ number_format($order->items_subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($order->shipping_cost > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted font-weight-500" style="font-size: 12.5px;">Ongkos Kirim</span>
                                    <span class="text-dark font-weight-600" style="font-size: 12.5px;">Rp. {{ number_format($order->shipping_cost, 0, ',', '.') }}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between pt-2 mt-1.5 border-top" style="border-color: #e2e8f0 !important;">
                                    <span class="text-dark font-weight-bold" style="font-size: 13px;">Total Tagihan</span>
                                    <strong class="font-weight-bold" style="font-size: 16px; color: #1E2753;">Rp. {{ number_format($order->total, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer Modal -->
            <div class="modal-footer border-top bg-light d-flex justify-content-between align-items-center flex-shrink-0" style="padding: 14px 24px;">
                <span class="text-muted d-inline-flex align-items-center gap-1.5" style="font-size: 12px;">
                    <span>ID Pesanan:</span>
                    <strong class="text-dark font-weight-700">#{{ $order->id }}</strong>
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

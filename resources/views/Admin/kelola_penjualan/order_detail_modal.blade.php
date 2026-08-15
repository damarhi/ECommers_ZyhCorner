<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal{{ $order->id }}" tabindex="-1" aria-labelledby="orderDetailModalLabel{{ $order->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 8px;">
            <!-- Header using purpleAdmin gradient -->
            <div class="modal-header bg-gradient-primary text-white py-3">
                <h5 class="modal-title font-weight-bold text-white d-flex align-items-center" id="orderDetailModalLabel{{ $order->id }}">
                    <i class="mdi mdi-receipt me-2"></i> Detail {{ $order->order_number }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1); opacity: 0.8;"></button>
            </div>
            
            <!-- Body -->
            <div class="modal-body p-3">
                <!-- Info Pelanggan & Transaksi (Compact List) -->
                <div class="card border-0 mb-3 bg-light">
                    <div class="card-body p-3">
                        <table class="table table-borderless table-sm m-0" style="font-size: 13px; line-height: 1.5;">
                            <tr>
                                <td class="text-muted p-1" style="width: 110px; font-weight: 600;">Pelanggan</td>
                                <td class="p-1">: {{ $order->customer_name }}</td>
                            </tr>
                            @if($order->email_pembeli)
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600;">Email</td>
                                <td class="p-1">: {{ $order->email_pembeli }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600;">Tanggal</td>
                                <td class="p-1">: {{ $order->created_at->format('d M Y, H:i') }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600;">Transaksi</td>
                                <td class="p-1">: 
                                    <span class="badge {{ $order->jenis_penjualan == 'Online' ? 'badge-soft-info' : 'badge-soft-dark' }}">
                                        {{ $order->jenis_penjualan }}
                                    </span>
                                </td>
                            </tr>
                            @if($order->payment_method)
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600;">Metode Bayar</td>
                                <td class="p-1">: {{ $order->payment_method }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600; vertical-align: middle;">Status Bayar</td>
                                <td class="p-1">: 
                                    @if($order->payment_status == 'Paid')
                                        <span class="badge badge-soft-success">Dibayar</span>
                                    @else
                                        <span class="badge badge-soft-warning">Tertunda</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600; vertical-align: middle;">Status Order</td>
                                <td class="p-1">: 
                                    @if($order->order_status == 'menunggu pembayaran')
                                        <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                                    @elseif($order->order_status == 'dikemas')
                                        <span class="badge badge-soft-info">Dikemas</span>
                                    @elseif($order->order_status == 'dikirim')
                                        <span class="badge badge-soft-primary">Dikirim</span>
                                    @elseif($order->order_status == 'selesai')
                                        <span class="badge badge-soft-success">Selesai</span>
                                    @else
                                        <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @if($order->alamat_pengiriman)
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600; vertical-align: top;">Alamat Kirim</td>
                                <td class="p-1" style="white-space: pre-line;">: {{ $order->alamat_pengiriman }}</td>
                            </tr>
                            @endif
                            @if($order->catatan_pesanan)
                            <tr>
                                <td class="text-muted p-1" style="font-weight: 600; vertical-align: top;">Catatan</td>
                                <td class="p-1" style="white-space: pre-line;">: {{ $order->catatan_pesanan }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
                
                <!-- Items Table -->
                <div class="table-responsive rounded-2 border mb-3">
                    <table class="table table-striped table-sm m-0" style="font-size: 13px;">
                        <thead>
                            <tr class="bg-light">
                                <th style="font-weight: 600; padding: 8px;">Produk</th>
                                <th style="font-weight: 600; padding: 8px; text-align: center; width: 60px;">Qty</th>
                                <th style="font-weight: 600; padding: 8px; text-align: right; width: 110px;">Subtotal</th>
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
                                <td style="padding: 8px; white-space: normal; word-wrap: break-word;">
                                    <div class="font-weight-500">{{ $item['name'] }}</div>
                                    @if(!empty($item['custom_text']))
                                        <div class="badge bg-soft-info text-info mt-1" style="font-size: 11px; white-space: normal; text-align: left; max-width: 100%;">
                                            <i class="mdi mdi-lead-pencil me-1"></i> Tulisan Kue: "{{ $item['custom_text'] }}"
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 8px; text-align: center;">{{ $item['qty'] }}</td>
                                <td style="padding: 8px; text-align: right; font-weight: 600;">Rp. {{ number_format($item['subtotal'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">
                                    Tidak ada item.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="2" style="padding: 10px 8px; font-weight: bold; text-align: right;">Total</td>
                                <td style="padding: 10px 8px; font-weight: bold; text-align: right; color: #1E2753;">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="modal-footer border-0 bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-gradient-primary btn-sm d-inline-flex align-items-center justify-content-center">
                    <i class="mdi mdi-printer me-2" style="font-size: 14px;"></i> Cetak Invoice
                </a>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Pesanan</th>
                <th>Tanggal</th>
                <th>Jenis</th>
                <th>Pelanggan</th>
                <th>Status Pembayaran</th>
                <th>Metode Bayar</th>
                <th>Status Pesanan</th>
                <th>Total</th>
                <th class="text-center" style="width: 95px;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($sales as $order)
            @php
                $productNames = collect(is_array($order->items) ? $order->items : [])
                    ->map(fn($item) => $item['nama_produk'] ?? $item['name'] ?? '')
                    ->filter()
                    ->implode(' ');
            @endphp
            <tr data-search="{{ strtolower($order->order_number . ' ' . $order->customer_name . ' ' . $productNames . ' ' . $order->jenis_penjualan . ' ' . $order->metode_pembayaran . ' ' . $order->status_pembayaran . ' ' . $order->status_pesanan) }}">
                <td class="font-weight-600 text-dark">{{ $order->order_number }}</td>
                <td class="text-muted">{{ $order->created_at->format('M j, g:i A') }}</td>
                <td>
                    @if($order->jenis_penjualan == 'Online')
                        <span class="badge badge-soft-info">Online</span>
                    @else
                        <span class="badge badge-soft-dark">Offline</span>
                    @endif
                </td>
                <td class="font-weight-500 text-dark">{{ $order->customer_name }}</td>
                <td>
                    @if($order->payment_status == 'Paid')
                        <span class="badge badge-soft-success">Dibayar</span>
                    @elseif($order->has_payment_proof)
                        <span class="badge badge-soft-warning" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#verifyProofModal{{ $order->id }}" title="Klik untuk verifikasi bukti transfer">
                            <i class="mdi mdi-file-document-check-outline me-1"></i>Bukti Transfer
                        </span>
                    @else
                        <span class="badge badge-soft-warning">Tertunda</span>
                    @endif
                </td>
                <td>
                    @php
                        $method = $order->payment_method ?: ($order->jenis_penjualan === 'Offline' ? 'Cash' : 'Transfer');
                        $mLower = strtolower($method);
                    @endphp
                    <span class="d-inline-flex align-items-center text-dark font-weight-500" style="font-size: 12.5px; gap: 5px;">
                        @if(str_contains($mLower, 'cash') || str_contains($mLower, 'tunai'))
                            <i class="mdi mdi-cash text-success" style="font-size: 16px; line-height: 1;"></i>
                        @elseif(str_contains($mLower, 'transfer') || str_contains($mLower, 'bank'))
                            <i class="mdi mdi-bank-transfer text-primary" style="font-size: 16px; line-height: 1;"></i>
                        @elseif(str_contains($mLower, 'wallet') || str_contains($mLower, 'qris') || str_contains($mLower, 'midtrans'))
                            <i class="mdi mdi-wallet-outline text-info" style="font-size: 16px; line-height: 1;"></i>
                        @elseif(str_contains($mLower, 'cod'))
                            <i class="mdi mdi-truck-delivery-outline text-warning" style="font-size: 16px; line-height: 1;"></i>
                        @else
                            <i class="mdi mdi-credit-card-outline text-secondary" style="font-size: 16px; line-height: 1;"></i>
                        @endif
                        <span>{{ $method }}</span>
                    </span>
                </td>
                <td>
                    @if($order->order_status == 'menunggu pembayaran')
                        <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                    @elseif($order->order_status == 'menunggu konfirmasi')
                        <span class="badge badge-pulse-amber">Menunggu Konfirmasi</span>
                    @elseif($order->order_status == 'dikemas')
                        <span class="badge badge-soft-info">Dikemas</span>
                    @elseif($order->order_status == 'dikirim')
                        <span class="badge badge-soft-primary">Dikirim</span>
                    @elseif($order->order_status == 'selesai')
                        <span class="badge badge-soft-success">Selesai</span>
                    @elseif($order->order_status == 'batal')
                        <span class="badge badge-soft-danger">Dibatalkan</span>
                    @else
                        <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                    @endif
                </td>
                <td class="font-weight-600 text-dark">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center">
                        @if($order->has_payment_proof)
                            <button type="button" class="btn btn-sm btn-inverse-warning d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#verifyProofModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0; border-radius: 6px; background: #fffbeb; color: #d97706; border: 1px solid #fde68a;" title="Verifikasi Bukti Transfer">
                                <i class="mdi mdi-file-document-check-outline" style="font-size: 16px;"></i>
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-inverse-success d-inline-flex align-items-center justify-content-center me-1" data-bs-toggle="modal" data-bs-target="#orderDetailModal{{ $order->id }}" style="width: 32px; height: 32px; padding: 0;" title="Detail Pesanan">
                            <i class="mdi mdi-eye" style="font-size: 16px; margin: 0;"></i>
                        </button>
                        <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-inverse-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; padding: 0; border-radius: 6px;" title="Cetak Invoice" aria-label="Cetak Invoice">
                            <i class="mdi mdi-printer" style="font-size: 16px; margin: 0;"></i>
                        </a>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                        @if(request('search'))
                            <i class="mdi mdi-file-search-outline text-muted mb-2" style="font-size: 54px; opacity: 0.6;"></i>
                            <h5 class="text-dark font-weight-bold mb-1">Tidak ada data yang cocok</h5>
                            <p class="text-muted mb-3" style="font-size: 13.5px;">Tidak ditemukan data penjualan dengan kata kunci "<strong>{{ request('search') }}</strong>".</p>
                            <button type="button" class="btn btn-sm btn-outline-primary reset-instant-search-btn" style="border-radius: 6px;">
                                <i class="mdi mdi-refresh me-1"></i> Reset Pencarian
                            </button>
                        @else
                            <i class="mdi mdi-cart-outline text-muted mb-2" style="font-size: 64px;"></i>
                            <h5 class="text-muted font-weight-bold">Belum Ada Data Penjualan</h5>
                            <p class="text-muted mb-0" style="font-size: 14px;">Oops, belum ada data penjualan yang dapat ditampilkan.</p>
                        @endif
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Modals (placed outside the table to prevent HTML rendering issues) -->
@foreach($sales as $order)
    @include('Admin.kelola_penjualan.order_detail_modal', ['order' => $order])
    @include('Admin.kelola_penjualan.verify_proof_modal', ['order' => $order])
@endforeach

<!-- Pagination -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top gap-3">
    <div class="d-flex align-items-center overflow-auto w-100 w-md-auto">
        {{ $sales->appends(request()->query())->links('pagination::bootstrap-4') }}
    </div>
    <div class="text-muted text-center text-md-end w-100 w-md-auto font-weight-500" style="font-size: 13.5px;">
        Menampilkan {{ $sales->firstItem() ?? 0 }} - {{ $sales->lastItem() ?? 0 }} dari {{ $sales->total() }} hasil
    </div>
</div>

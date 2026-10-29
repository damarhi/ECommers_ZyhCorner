@extends('Layout.layout_pAdmin')

@section('title', 'Dashboard - Admin')

@section('content')
    <div class="page-header d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
        <h3 class="page-title d-inline-flex align-items-center m-0">
            <span class="page-title-icon bg-gradient-primary text-white me-2">
                <i class="mdi mdi-home"></i>
            </span>
            <span class="text-nowrap">Dashboard</span>
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb mb-0">
                <li class="breadcrumb-item active" aria-current="page">
                    <span></span>Ringkasan Toko
                </li>
            </ul>
        </nav>
    </div>

    <!-- Summary KPI Cards -->
    <div class="row">
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-coral border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-chart-line"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Penjualan Mingguan</p>
                        <h3 class="stat-value">Rp {{ number_format($penjualanMingguan ?? 0, 0, ',', '.') }}</h3>
                        <p class="stat-change mb-0">Pembayaran lunas minggu ini</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-indigo border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-cart-outline"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Pesanan Mingguan</p>
                        <h3 class="stat-value">{{ number_format($pesananMingguan ?? 0) }}</h3>
                        <p class="stat-change mb-0">Pesanan masuk minggu ini</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 stretch-card grid-margin">
            <div class="card stat-card stat-card-teal border-0 text-white">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon">
                        <i class="mdi mdi-account-group"></i>
                    </div>
                    <div>
                        <p class="stat-label mb-0">Pelanggan Terdaftar</p>
                        <h3 class="stat-value">{{ number_format($totalPelanggan ?? 0) }}</h3>
                        <p class="stat-change mb-0">Total akun pelanggan terdaftar</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dynamic Charts Row -->
    <div class="row">
        <div class="col-md-7 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="card-title mb-1 font-weight-bold">Tren Penjualan 7 Hari Terakhir</h4>
                            <p class="text-muted mb-0" style="font-size: 12.5px;">Akumulasi nominal transaksi lunas harian</p>
                        </div>
                    </div>
                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5 grid-margin stretch-card">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <h4 class="card-title mb-1 font-weight-bold">Saluran Penjualan</h4>
                    <p class="text-muted mb-3" style="font-size: 12.5px;">Perbandingan pesanan Online vs Offline (Kasir)</p>
                    <div style="position: relative; height: 200px; width: 100%;" class="d-flex justify-content-center">
                        <canvas id="channelChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-around mt-3 pt-2 border-top">
                        <div class="text-center">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #844fc1;"></span>
                            <span class="text-muted" style="font-size: 12px;">Online:</span>
                            <strong class="text-dark d-block" style="font-size: 15px;">{{ $onlineCount ?? 0 }}</strong>
                        </div>
                        <div class="text-center">
                            <span class="d-inline-block rounded-circle me-1" style="width: 10px; height: 10px; background-color: #00d284;"></span>
                            <span class="text-muted" style="font-size: 12px;">Kasir Offline:</span>
                            <strong class="text-dark d-block" style="font-size: 15px;">{{ $offlineCount ?? 0 }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Latest Orders Table (Real Dynamic Data) -->
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card border-0 shadow-sm" style="border-radius: 8px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="card-title mb-1 font-weight-bold">Pesanan Terbaru</h4>
                            <p class="text-muted mb-0" style="font-size: 13px;">Daftar 5 transaksi pesanan terakhir di toko</p>
                        </div>
                        <a href="{{ route('orders.index') }}" class="btn btn-sm btn-gradient-primary">
                            Lihat Semua Pesanan <i class="mdi mdi-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Pelanggan</th>
                                    <th>No. Pesanan</th>
                                    <th>Metode & Saluran</th>
                                    <th>Status Pembayaran</th>
                                    <th>Status Pesanan</th>
                                    <th>Total Tagihan</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light text-primary font-weight-bold d-flex align-items-center justify-content-center me-2"
                                                    style="width: 32px; height: 32px; font-size: 12px; border: 1px solid #e0e0e0;">
                                                    {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                                                </div>
                                                <span class="font-weight-500 text-dark">{{ $order->customer_name }}</span>
                                            </div>
                                        </td>
                                        <td class="font-weight-500 text-muted">{{ $order->order_number }}</td>
                                        <td>
                                            <span class="badge {{ $order->jenis_penjualan === 'Online' ? 'badge-soft-info' : 'badge-soft-dark' }}">
                                                {{ $order->jenis_penjualan }}
                                            </span>
                                            <small class="text-muted d-block mt-1" style="font-size: 11px;">{{ $order->payment_method ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if($order->payment_status === 'Paid')
                                                <span class="badge badge-soft-success">Dibayar</span>
                                            @else
                                                <span class="badge badge-soft-warning">Tertunda</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($order->order_status === 'menunggu pembayaran')
                                                <span class="badge badge-soft-warning">Menunggu Pembayaran</span>
                                            @elseif(in_array(strtolower($order->order_status ?? ''), ['menunggu konfirmasi', 'unconfirmed']))
                                                <span class="badge badge-soft-warning badge-pulse-amber">Menunggu Konfirmasi</span>
                                            @elseif($order->order_status === 'dikemas')
                                                <span class="badge badge-soft-info">Dikemas</span>
                                            @elseif($order->order_status === 'dikirim')
                                                <span class="badge badge-soft-primary">Dikirim</span>
                                            @elseif($order->order_status === 'selesai')
                                                <span class="badge badge-soft-success">Selesai</span>
                                            @elseif($order->order_status === 'batal')
                                                <span class="badge badge-soft-danger">Dibatalkan</span>
                                            @else
                                                <span class="badge badge-soft-dark">{{ $order->order_status }}</span>
                                            @endif
                                        </td>
                                        <td class="font-weight-600 text-dark">Rp. {{ number_format($order->total, 0, ',', '.') }}</td>
                                        <td class="text-muted" style="font-size: 12.5px;">{{ $order->created_at->format('d M Y, H:i') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('orders.index') }}?search={{ urlencode($order->order_number) }}" class="btn btn-inverse-primary btn-sm px-2 py-1" title="Lihat di Pesanan">
                                                <i class="mdi mdi-eye-outline" style="font-size: 14px;"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <i class="mdi mdi-receipt text-muted mb-2" style="font-size: 32px; display: block;"></i>
                                            Belum ada pesanan terbaru saat ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('PurpleAdmin/assets/vendors/chart.js/chart.umd.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Sales Trend Line Chart (7 Days)
    const salesCtx = document.getElementById('salesTrendChart');
    if (salesCtx) {
        const labels = @json($chartLabels ?? []);
        const data = @json($chartData ?? []);

        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Penjualan (Rp)',
                    data: data,
                    borderColor: '#9a55ff',
                    backgroundColor: 'rgba(154, 85, 255, 0.12)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#9a55ff',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp. ' + new Intl.NumberFormat('id-ID').format(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 11 },
                            callback: function(value) {
                                if (value >= 1000000) {
                                    return (value / 1000000) + ' Jt';
                                } else if (value >= 1000) {
                                    return (value / 1000) + ' Rb';
                                }
                                return value;
                            }
                        },
                        grid: {
                            color: '#f0f2f5'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 11 }
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // 2. Channel Distribution Doughnut Chart (Online vs Offline)
    const channelCtx = document.getElementById('channelChart');
    if (channelCtx) {
        const onlineCount = {{ $onlineCount ?? 0 }};
        const offlineCount = {{ $offlineCount ?? 0 }};
        const hasData = (onlineCount + offlineCount) > 0;

        new Chart(channelCtx, {
            type: 'doughnut',
            data: {
                labels: ['Online', 'Kasir Offline'],
                datasets: [{
                    data: hasData ? [onlineCount, offlineCount] : [1],
                    backgroundColor: hasData ? ['#844fc1', '#00d284'] : ['#e9ecef'],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        enabled: hasData,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.raw + ' pesanan';
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan, Kadaluarsa & Retur - Zyh Corner</title>
    <link rel="shortcut icon" href="{{ asset('image/logo_circle.png') }}" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/css_sales_report.css') }}?v={{ file_exists(public_path('css/css_sales_report.css')) ? filemtime(public_path('css/css_sales_report.css')) : time() }}">
</head>
<body>

    <!-- Top Action Toolbar -->
    <div class="action-toolbar no-print">
        <div>
            <a href="{{ route('all-sales.index') }}" class="btn-action btn-back">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Penjualan
            </a>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" onclick="exportToGoogleSpreadsheet()" class="btn-action btn-gsheet">
                <i class="fa-solid fa-table"></i> Ekspor ke Google Spreadsheet
            </button>
            <button onclick="window.print()" class="btn-action btn-print">
                <i class="fa-solid fa-print"></i> Cetak Laporan Dokumen
            </button>
        </div>
    </div>

    <!-- Interactive Multi-Filter Panel -->
    <div class="filter-panel no-print">
        <div class="filter-header">
            <h3 class="filter-title"><i class="fa-solid fa-sliders" style="color: #00b207;"></i> Filter Laporan Penjualan</h3>
            <div class="preset-buttons">
                <a href="{{ route('all-sales.report', array_merge(request()->except(['start_date', 'end_date']), ['preset' => 'today'])) }}" class="btn-preset {{ $preset === 'today' ? 'active' : '' }}">Hari Ini</a>
                <a href="{{ route('all-sales.report', array_merge(request()->except(['start_date', 'end_date']), ['preset' => 'last_7_days'])) }}" class="btn-preset {{ $preset === 'last_7_days' ? 'active' : '' }}">7 Hari Terakhir</a>
                <a href="{{ route('all-sales.report', array_merge(request()->except(['start_date', 'end_date']), ['preset' => 'this_month'])) }}" class="btn-preset {{ $preset === 'this_month' ? 'active' : '' }}">Bulan Ini</a>
                <a href="{{ route('all-sales.report', array_merge(request()->except(['start_date', 'end_date']), ['preset' => 'this_year'])) }}" class="btn-preset {{ $preset === 'this_year' ? 'active' : '' }}">Tahun Ini</a>
                <a href="{{ route('all-sales.report') }}" class="btn-preset {{ empty($preset) && empty($startDate) && empty($endDate) ? 'active' : '' }}">Semua Waktu</a>
            </div>
        </div>

        <form method="GET" action="{{ route('all-sales.report') }}">
            <div class="filter-grid">
                <div class="filter-group">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate ?? '' }}">
                </div>
                <div class="filter-group">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ $endDate ?? '' }}">
                </div>
                <div class="filter-group">
                    <label>Jenis Penjualan</label>
                    <select name="jenis_penjualan">
                        <option value="All" {{ request('jenis_penjualan') == 'All' || empty(request('jenis_penjualan')) ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="Online" {{ request('jenis_penjualan') == 'Online' ? 'selected' : '' }}>Online</option>
                        <option value="Offline" {{ request('jenis_penjualan') == 'Offline' ? 'selected' : '' }}>Offline (Kasir)</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Status Pembayaran</label>
                    <select name="payment_status">
                        <option value="All" {{ request('payment_status') == 'All' || empty(request('payment_status')) ? 'selected' : '' }}>Semua Status</option>
                        <option value="Paid" {{ request('payment_status') == 'Paid' ? 'selected' : '' }}>Paid (Lunas)</option>
                        <option value="Pending" {{ request('payment_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Filter Diskon / Promo</label>
                    <select name="has_discount">
                        <option value="All" {{ request('has_discount') == 'All' || empty(request('has_discount')) ? 'selected' : '' }}>Semua Pesanan</option>
                        <option value="Yes" {{ request('has_discount') == 'Yes' ? 'selected' : '' }}>Dengan Diskon/Promo</option>
                        <option value="No" {{ request('has_discount') == 'No' ? 'selected' : '' }}>Tanpa Diskon</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Cari Transaksi</label>
                    <input type="text" name="search" placeholder="No. Order / Pelanggan..." value="{{ request('search') }}">
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn-submit-filter"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
                    <a href="{{ route('all-sales.report') }}" class="btn-reset-filter"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Printable Report Container -->
    <div class="report-container">
        <!-- Header -->
        <div class="header-section">
            <div class="brand-logo">
                <img src="{{ asset('image/logo.jpg') }}" alt="Zyh Corner Logo">
                <div>
                    <h1 class="brand-title">{{ strtoupper(\App\Models\StoreSetting::get('store_name', 'ZYH CORNER')) }}</h1>
                    <p class="brand-subtitle">{{ \App\Models\StoreSetting::get('store_tagline', 'Pastry & Bakery Specialist') }} | Telp: {{ \App\Models\StoreSetting::get('store_phone', '(+62) 812-3456-789') }}</p>
                </div>
            </div>
            <div class="report-meta">
                <h2 class="report-title">Laporan Penjualan</h2>
                <p class="report-date">Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</p>
            </div>
        </div>

        <!-- Filter Info Badge -->
        <div class="filter-info-badge">
            <div>
                <strong>Periode:</strong> 
                @if(!empty($startDate) && !empty($endDate))
                    {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @elseif(!empty($startDate))
                    Mulai {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                @elseif(!empty($endDate))
                    Sampai {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
                @else
                    Semua Waktu
                @endif
            </div>
            <div>
                <strong>Jenis Penjualan:</strong> {{ request('jenis_penjualan') && request('jenis_penjualan') !== 'All' ? request('jenis_penjualan') : 'Semua Channel' }}
                | <strong>Filter Diskon:</strong> {{ request('has_discount') == 'Yes' ? 'Dengan Diskon' : (request('has_discount') == 'No' ? 'Tanpa Diskon' : 'Semua') }}
            </div>
        </div>

        <!-- KPI Cards Group 1: Pemasukan Penjualan -->
        <h4 class="section-heading"><i class="fa-solid fa-chart-line" style="color: #00b207;"></i> Rangkuman Pemasukan Penjualan</h4>
        <div class="kpi-grid">
            <div class="kpi-card gross">
                <div class="kpi-label">Total Pemasukkan Asli (Bruto)</div>
                <div class="kpi-value">Rp. {{ number_format($totalPemasukkanAsli ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card discount">
                <div class="kpi-label">Total Potongan Diskon</div>
                <div class="kpi-value">Rp. {{ number_format($totalPotonganDiskon ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card net">
                <div class="kpi-label">Total Pemasukkan Setelah Diskon</div>
                <div class="kpi-value">Rp. {{ number_format($totalPemasukkanSetelahDiskon ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Total Transaksi Selesai</div>
                <div class="kpi-value">{{ number_format($totalTransaksi ?? 0, 0, ',', '.') }} ({{ $totalOnlineCount }} Online / {{ $totalOfflineCount }} Offline)</div>
            </div>
        </div>

        <!-- KPI Cards Group 2: Produk Kadaluarsa & Retur Selesai -->
        <h4 class="section-heading" style="border-left-color: #dc2626;"><i class="fa-solid fa-boxes-packing" style="color: #dc2626;"></i> Rangkuman Produk Kadaluarsa & Retur Selesai</h4>
        <div class="kpi-grid">
            <div class="kpi-card expired-loss">
                <div class="kpi-label">Jumlah Produk Kadaluarsa</div>
                <div class="kpi-value" style="color: #ea580c;">{{ number_format($jumlahProdukKadaluarsa ?? 0, 0, ',', '.') }} Unit / Pcs</div>
            </div>
            <div class="kpi-card expired-loss">
                <div class="kpi-label">Total Harga Produk Kadaluarsa</div>
                <div class="kpi-value">Rp. {{ number_format($totalHargaKadaluarsa ?? 0, 0, ',', '.') }}</div>
            </div>
            <div class="kpi-card retur-loss">
                <div class="kpi-label">Jumlah Retur Selesai</div>
                <div class="kpi-value" style="color: #d97706;">{{ number_format($jumlahReturSelesai ?? 0, 0, ',', '.') }} Item / Transaksi</div>
            </div>
            <div class="kpi-card retur-loss">
                <div class="kpi-label">Total Harga Retur Selesai</div>
                <div class="kpi-value">Rp. {{ number_format($totalHargaReturSelesai ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Tabel Detail Produk Kadaluarsa (Jika Ada) -->
        @if(isset($expiredProductsList) && $expiredProductsList->count() > 0)
            <h4 class="section-heading" style="border-left-color: #ea580c; font-size: 13px; margin-top: 24px;"><i class="fa-solid fa-clock-rotate-left" style="color: #ea580c;"></i> Rincian Produk Kadaluarsa (Stok Expired)</h4>
            <table class="table-report" style="margin-bottom: 24px;">
                <thead>
                    <tr style="background: #fff7ed;">
                        <th style="width: 35px;">No</th>
                        <th>Nama Produk Kadaluarsa</th>
                        <th>Tanggal Kadaluarsa</th>
                        <th style="text-align: center;">Jumlah Stok</th>
                        <th style="text-align: right;">Harga Satuan</th>
                        <th style="text-align: right;">Total Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expiredProductsList as $idx => $exp)
                        @php
                            $hargaSatuan = $exp->produk ? floatval($exp->produk->harga) : 0;
                            $subtotalLoss = $hargaSatuan * intval($exp->jumlah_stok);
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $exp->produk->nama_produk ?? 'Produk Dihapus' }}</strong></td>
                            <td>{{ $exp->tanggal_expired ? \Carbon\Carbon::parse($exp->tanggal_expired)->format('d/m/Y') : '-' }}</td>
                            <td style="text-align: center; font-weight: 600; color: #ea580c;">{{ number_format($exp->jumlah_stok, 0, ',', '.') }} pcs</td>
                            <td style="text-align: right;">Rp. {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                            <td style="text-align: right; font-weight: 600; color: #dc2626;">Rp. {{ number_format($subtotalLoss, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="tfoot-total" style="background: #fff7ed;">
                        <td colspan="3" style="text-align: right;">TOTAL HARGA PRODUK KADALUARSA:</td>
                        <td style="text-align: center; color: #ea580c;">{{ number_format($jumlahProdukKadaluarsa, 0, ',', '.') }} pcs</td>
                        <td></td>
                        <td style="text-align: right; color: #dc2626;">Rp. {{ number_format($totalHargaKadaluarsa, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <!-- Tabel Detail Produk Retur Selesai (Jika Ada) -->
        @if(isset($returnedProductsList) && $returnedProductsList->count() > 0)
            <h4 class="section-heading" style="border-left-color: #d97706; font-size: 13px; margin-top: 24px;"><i class="fa-solid fa-arrow-rotate-left" style="color: #d97706;"></i> Rincian Produk Retur Selesai</h4>
            <table class="table-report" style="margin-bottom: 24px;">
                <thead>
                    <tr style="background: #fffbebf;">
                        <th style="width: 35px;">No</th>
                        <th>No. Order / Pelanggan</th>
                        <th>Nama Produk Retur</th>
                        <th>Alasan Retur</th>
                        <th>Tanggal Retur</th>
                        <th style="text-align: center;">Status Retur</th>
                        <th style="text-align: right;">Estimasi Harga</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sumReturPrice = 0; @endphp
                    @foreach($returnedProductsList as $idx => $retur)
                        @php
                            $pPrice = 0;
                            $productMapByName = \App\Models\Produk::all()->keyBy('nama_produk');
                            $orderMapByNumber = \App\Models\Pesanan::all()->keyBy('order_number');

                            if ($retur->product_name && $productMapByName->has($retur->product_name)) {
                                $pPrice = floatval($productMapByName->get($retur->product_name)->harga);
                            } elseif ($retur->order_id && $orderMapByNumber->has($retur->order_id)) {
                                $ord = $orderMapByNumber->get($retur->order_id);
                                $items = is_array($ord->items) ? $ord->items : [];
                                foreach ($items as $it) {
                                    if (strcasecmp($it['name'] ?? '', $retur->product_name ?? '') === 0) {
                                        $pPrice = floatval($it['price'] ?? 0);
                                        break;
                                    }
                                }
                                if ($pPrice <= 0 && count($items) > 0) {
                                    $pPrice = floatval($items[0]['price'] ?? 0);
                                }
                            }
                            $sumReturPrice += $pPrice;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $retur->order_id ?? '-' }}</strong><br>
                                <span style="font-size: 11px; color: #718096;">{{ $retur->customer_name ?? '-' }}</span>
                            </td>
                            <td><strong>{{ $retur->product_name ?? '-' }}</strong></td>
                            <td style="color: #4b5563;">{{ $retur->reason ?? '-' }}</td>
                            <td>{{ $retur->return_date ? \Carbon\Carbon::parse($retur->return_date)->format('d/m/Y') : '-' }}</td>
                            <td style="text-align: center;">
                                <span class="badge-type badge-done"><i class="fa-solid fa-circle-check"></i> {{ ucfirst($retur->status) }}</span>
                            </td>
                            <td style="text-align: right; font-weight: 600; color: #d97706;">Rp. {{ number_format($pPrice, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="tfoot-total" style="background: #fffbebf;">
                        <td colspan="6" style="text-align: right;">TOTAL HARGA PRODUK RETUR SELESAI:</td>
                        <td style="text-align: right; color: #d97706;">Rp. {{ number_format($sumReturPrice, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <!-- Table Report Penjualan -->
        <h4 class="section-heading" style="margin-top: 24px;"><i class="fa-solid fa-list-check" style="color: #00b207;"></i> Rincian Transaksi Penjualan Toko</h4>
        <table class="table-report">
            <thead>
                <tr>
                    <th style="width: 35px;">No</th>
                    <th>No. Pesanan</th>
                    <th>Tanggal Transaksi</th>
                    <th>Jenis Penjualan</th>
                    <th>Pelanggan</th>
                    <th>Status & Tag</th>
                    <th style="text-align: right;">Total Omset (Setelah Diskon)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $index => $order)
                    @php
                        $items = is_array($order->items) ? $order->items : [];
                        $itemIds = array_column($items, 'id');
                        $hasDisc = false;
                        foreach($itemIds as $pId) {
                            if (in_array($pId, $discountedProductIds ?? [])) $hasDisc = true;
                        }
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td><strong>{{ $order->order_number }}</strong></td>
                        <td>{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            @if(strtolower($order->jenis_penjualan ?? '') === 'offline')
                                <span class="badge-type badge-offline">Offline</span>
                            @else
                                <span class="badge-type badge-online">Online</span>
                            @endif
                        </td>
                        <td>{{ $order->customer_name ?? '-' }}</td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px; align-items: flex-start;">
                                <span style="color: {{ strtolower($order->payment_status ?? '') === 'paid' ? '#00b207' : '#d97706' }}; font-weight: 600; font-size: 11.5px;">
                                    {{ $order->payment_status ?? 'Pending' }}
                                </span>
                                @if($hasDisc)
                                    <span class="badge-type badge-discount"><i class="fa-solid fa-tag"></i> Promo Diskon</span>
                                @endif
                            </div>
                        </td>
                        <td style="text-align: right; font-weight: 600;">Rp. {{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #a0aec0; padding: 24px;">Tidak ada data penjualan yang ditemukan untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="tfoot-total">
                    <td colspan="6" style="text-align: right;">TOTAL PEMASUKKAN SETELAH DISKON:</td>
                    <td style="text-align: right; color: #00b207;">Rp. {{ number_format($totalPemasukkanSetelahDiskon ?? 0, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-date">Pangkalan Kerinci, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div style="font-size: 12px; color: #4a5568;">Mengetahui,</div>
                <div class="signature-space"></div>
                <div class="signature-name">Manager Toko Zyh Corner</div>
                <div class="signature-role">Manajer Operasional</div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 & Google Spreadsheet Export Script -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    window.salesReportConfig = {
        exportUrl: "{{ route('export.all-sales') }}"
    };
</script>
<script src="{{ asset('js/js_sales_report.js') }}?v={{ file_exists(public_path('js/js_sales_report.js')) ? filemtime(public_path('js/js_sales_report.js')) : time() }}"></script>

</body>
</html>

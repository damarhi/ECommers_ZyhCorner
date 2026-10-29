<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan, Kadaluarsa & Retur - Zyh Corner</title>
    <link rel="shortcut icon" href="{{ asset('image/logo_circle.png') }}" type="image/png" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            margin: 0;
            padding: 0;
            background-color: #f4f6f9;
            color: #2d3748;
            font-size: 13px;
        }

        .action-toolbar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-print { background-color: #00b207; color: #ffffff; }
        .btn-print:hover { background-color: #009606; }
        .btn-csv { background-color: #0284c7; color: #ffffff; }
        .btn-csv:hover { background-color: #0369a1; }
        .btn-gsheet { background-color: #0f9d58; color: #ffffff; }
        .btn-gsheet:hover { background-color: #0b8043; }
        .btn-back { background-color: #e2e8f0; color: #4a5568; }
        .btn-back:hover { background-color: #cbd5e0; }

        /* Filter Panel Card */
        .filter-panel {
            max-width: 1000px;
            margin: 20px auto 0;
            background: #ffffff;
            padding: 20px 24px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }

        .filter-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid #edf2f7;
        }

        .filter-title {
            font-size: 15px;
            font-weight: 600;
            color: #1a202c;
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }

        .preset-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-preset {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
        }

        .btn-preset:hover, .btn-preset.active {
            background: #00b207;
            color: #ffffff;
            border-color: #00b207;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
            gap: 14px;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .filter-group label {
            font-size: 11px;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .filter-group input, .filter-group select {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 12.5px;
            color: #1e293b;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s;
        }

        .filter-group input:focus, .filter-group select:focus {
            border-color: #00b207;
        }

        .filter-actions {
            display: flex;
            gap: 8px;
        }

        .btn-submit-filter {
            background: #00b207;
            color: white;
            border: none;
            padding: 9px 16px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
        }

        .btn-submit-filter:hover { background: #009606; }

        .btn-reset-filter {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
            padding: 9px 14px;
            border-radius: 6px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
        }

        .btn-reset-filter:hover { background: #e2e8f0; }

        /* Printable Document Container */
        .report-container {
            max-width: 1000px;
            margin: 20px auto 40px;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2.5px solid #00b207;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-logo img {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            object-fit: cover;
        }

        .brand-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
            line-height: 1.2;
        }

        .brand-subtitle {
            font-size: 12px;
            color: #718096;
            margin: 2px 0 0 0;
        }

        .report-meta { text-align: right; }

        .report-title {
            font-size: 18px;
            font-weight: 700;
            color: #00b207;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .report-date { font-size: 12px; color: #718096; margin: 0; }

        .filter-info-badge {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #475569;
            flex-wrap: wrap;
            gap: 8px;
        }

        .section-heading {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin: 24px 0 12px 0;
            display: flex;
            align-items: center;
            gap: 8px;
            border-left: 4px solid #00b207;
            padding-left: 10px;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-bottom: 24px;
        }

        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 16px;
        }

        .kpi-label {
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .kpi-value {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .kpi-card.gross { background: #f0f9ff; border-color: #bae6fd; }
        .kpi-card.gross .kpi-value { color: #0284c7; }

        .kpi-card.discount { background: #fdf2f8; border-color: #fbcfe8; }
        .kpi-card.discount .kpi-value { color: #db2777; }

        .kpi-card.net { background: #f0fdf4; border-color: #bbf7d0; }
        .kpi-card.net .kpi-value { color: #00b207; }

        .kpi-card.expired-loss { background: #fef2f2; border-color: #fecaca; }
        .kpi-card.expired-loss .kpi-value { color: #dc2626; }

        .kpi-card.retur-loss { background: #fffbebf; border-color: #fef3c7; }
        .kpi-card.retur-loss .kpi-value { color: #d97706; }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .table-report th {
            background-color: #edf2f7;
            color: #2d3748;
            font-weight: 600;
            text-align: left;
            padding: 10px 12px;
            font-size: 12px;
            border-top: 1px solid #cbd5e0;
            border-bottom: 2px solid #cbd5e0;
        }

        .table-report td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 12.5px;
        }

        .table-report tbody tr:nth-child(even) { background-color: #f8fafc; }

        .badge-type {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 10.5px;
            font-weight: 600;
        }

        .badge-online { background-color: #e0f2fe; color: #0284c7; }
        .badge-offline { background-color: #fef3c7; color: #d97706; }
        .badge-discount { background-color: #fce7f3; color: #db2777; }
        .badge-done { background-color: #dcfce7; color: #166534; }

        .tfoot-total td {
            font-weight: 700;
            font-size: 13.5px;
            background-color: #f1f5f9;
            border-top: 2px solid #cbd5e0;
            border-bottom: 2px solid #cbd5e0;
        }

        .signature-section {
            display: flex;
            justify-content: flex-end;
            margin-top: 40px;
        }

        .signature-box { text-align: center; width: 220px; }
        .signature-date { font-size: 12px; color: #4a5568; margin-bottom: 8px; }
        .signature-space { height: 60px; }
        .signature-name { font-weight: 700; text-decoration: underline; color: #1a202c; }
        .signature-role { font-size: 11.5px; color: #718096; }

        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff; }
            .report-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
                max-width: 100%;
                border: none;
            }
        }
    </style>
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
        function exportToGoogleSpreadsheet() {
            const gsheetUrl = "https://docs.google.com/spreadsheets/d/1RzbBH2PJvgOCiCJ_HgpaI88q87uJQKDlAUwJn3XH4_E/edit?usp=sharing";
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.set('format', 'raw');

            const exportUrl = "{{ route('export.all-sales') }}?" + currentParams.toString();

            Swal.fire({
                title: 'Menyiapkan Data Laporan Terfilter...',
                text: 'Mengolah data transaksi, kadaluarsa, dan retur untuk Google Spreadsheet',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(exportUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(resData => {
                if (resData && resData.data) {
                    navigator.clipboard.writeText(resData.data).then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Laporan Terfilter Berhasil Disalin!',
                            html: 'Seluruh data laporan terfilter telah otomatis disalin ke clipboard.<br><br><b>Langkah Selanjutnya:</b><br>1. Klik tombol <b>"Buka Google Spreadsheet"</b> di bawah.<br>2. Klik sel <b>A1</b> di Google Spreadsheet lalu tekan <b>Ctrl + V</b> (Paste).',
                            confirmButtonText: '<i class="fa-solid fa-arrow-up-right-from-square"></i> Buka Google Spreadsheet',
                            confirmButtonColor: '#0f9d58',
                            showCancelButton: true,
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open(gsheetUrl, '_blank');
                            }
                        });
                    }).catch(() => {
                        window.open(gsheetUrl, '_blank');
                    });
                } else {
                    window.open(gsheetUrl, '_blank');
                }
            })
            .catch(() => {
                window.open(gsheetUrl, '_blank');
            });
        }
    </script>

</body>
</html>

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diskon;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukKadaluarsa;
use App\Models\Retur;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EksporController extends Controller
{
    /**
     * Ekspor seluruh data penjualan terfilter (CSV / Spreadsheet / TSV).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAllSales(Request $request)
    {
        if ($request->has('gsheet') || $request->input('target') === 'google') {
            return redirect('https://docs.google.com/spreadsheets/d/1RzbBH2PJvgOCiCJ_HgpaI88q87uJQKDlAUwJn3XH4_E/edit?usp=sharing');
        }

        [$sales, $startDate, $endDate, $discountedProductIds] = $this->filterSalesQuery($request);
        $metrics = $this->calculateFinancialMetrics($sales, $startDate, $endDate);

        if ($request->wantsJson() || $request->ajax() || $request->input('format') === 'raw') {
            $tsv = $this->buildTsvData($sales, $metrics, $discountedProductIds, $startDate, $endDate);
            return response()->json(['success' => true, 'data' => $tsv]);
        }

        $filePath = $this->generateXmlSpreadsheet($sales, $metrics, $discountedProductIds, $startDate, $endDate);

        return response()->download($filePath, 'Laporan_Penjualan_Master.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Memfilter data transaksi penjualan berdasarkan jenis penjualan, status bayar, rentang tanggal, search, dan promo diskon.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array{0: \Illuminate\Support\Collection, 1: ?string, 2: ?string, 3: array}
     */
    private function filterSalesQuery(Request $request): array
    {
        $query = Pesanan::query();

        // 1. Jenis Penjualan Filter
        $jenis = $request->input('jenis_penjualan');
        if (!empty($jenis) && $jenis !== 'All') {
            if (is_array($jenis)) {
                $query->where(function($q) use ($jenis) {
                    if (in_array('Offline', $jenis)) {
                        $q->where('jenis_penjualan', 'Offline');
                    }
                    if (in_array('Online', $jenis)) {
                        if (in_array('Offline', $jenis)) {
                            $q->orWhere(function($subQ) {
                                $subQ->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
                            });
                        } else {
                            $q->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
                        }
                    }
                });
            } elseif ($jenis === 'Online') {
                $query->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
            } elseif ($jenis === 'Offline') {
                $query->where('jenis_penjualan', 'Offline');
            }
        } else {
            $query->where(function($q) {
                $q->where('jenis_penjualan', 'Offline')
                  ->orWhere(function($subQ) {
                      $subQ->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
                  });
            });
        }

        // 2. Status Pembayaran Filter
        if ($request->filled('payment_status') && $request->payment_status !== 'All') {
            if (is_array($request->payment_status)) {
                $query->whereIn('payment_status', $request->payment_status);
            } else {
                $query->where('payment_status', $request->payment_status);
            }
        }

        // 3. Date Range & Preset Filters
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $preset = $request->input('preset');

        if ($preset === 'today') {
            $startDate = Carbon::today()->toDateString();
            $endDate = Carbon::today()->toDateString();
        } elseif ($preset === 'last_7_days') {
            $startDate = Carbon::today()->subDays(6)->toDateString();
            $endDate = Carbon::today()->toDateString();
        } elseif ($preset === 'this_month') {
            $startDate = Carbon::now()->startOfMonth()->toDateString();
            $endDate = Carbon::now()->endOfMonth()->toDateString();
        } elseif ($preset === 'this_year') {
            $startDate = Carbon::now()->startOfYear()->toDateString();
            $endDate = Carbon::now()->endOfYear()->toDateString();
        }

        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // 4. Search Keyword Filter
        if ($request->filled('search')) {
            $searchTerm = '%' . trim($request->search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('email_pembeli', 'like', $searchTerm)
                  ->orWhere('items', 'like', $searchTerm);
            });
        }

        // 5. Filter Diskon (has_discount)
        $hasDiscountFilter = $request->input('has_discount');
        $discountedProductIds = Diskon::pluck('id_produk')->filter()->toArray();

        $allSales = $query->latest()->get();

        if (!empty($hasDiscountFilter) && $hasDiscountFilter !== 'All') {
            $sales = $allSales->filter(function($order) use ($hasDiscountFilter, $discountedProductIds) {
                $items = is_array($order->items) ? $order->items : [];
                $itemIds = array_column($items, 'id');
                $hasDiscount = false;
                foreach ($itemIds as $pId) {
                    if (in_array($pId, $discountedProductIds)) {
                        $hasDiscount = true;
                        break;
                    }
                }
                return $hasDiscountFilter === 'Yes' ? $hasDiscount : !$hasDiscount;
            })->values();
        } else {
            $sales = $allSales;
        }

        return [$sales, $startDate, $endDate, $discountedProductIds];
    }

    /**
     * Menghitung metrik keuangan omset asli, potongan diskon, kerugian expired, dan retur selesai.
     *
     * @param  \Illuminate\Support\Collection  $sales
     * @param  ?string  $startDate
     * @param  ?string  $endDate
     * @return array
     */
    private function calculateFinancialMetrics($sales, ?string $startDate, ?string $endDate): array
    {
        // Calculate Revenue Metrics
        $totalPemasukkanSetelahDiskon = $sales->sum('total');
        $allProducts = Produk::all()->keyBy('id_produk');
        $totalPemasukkanAsli = 0;
        foreach ($sales as $order) {
            $items = is_array($order->items) ? $order->items : [];
            $orderGross = 0;
            foreach ($items as $item) {
                $qty = intval($item['qty'] ?? $item['quantity'] ?? 1);
                $pId = $item['id'] ?? null;
                $product = $pId ? $allProducts->get($pId) : null;
                $originalPrice = $product ? floatval($product->harga) : floatval($item['price'] ?? 0);
                if ($originalPrice < floatval($item['price'] ?? 0)) {
                    $originalPrice = floatval($item['price'] ?? 0);
                }
                $orderGross += ($originalPrice * $qty);
            }
            if ($orderGross <= 0) {
                $orderGross = floatval($order->total);
            }
            $totalPemasukkanAsli += $orderGross;
        }
        $totalPotonganDiskon = max(0, $totalPemasukkanAsli - $totalPemasukkanSetelahDiskon);

        // Expired Products Calculation
        $expiredQuery = ProdukKadaluarsa::with('produk');
        if (!empty($startDate)) {
            $expiredQuery->whereDate('tanggal_expired', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $expiredQuery->whereDate('tanggal_expired', '<=', $endDate);
        }
        $expiredProductsList = $expiredQuery->get();
        $jumlahProdukKadaluarsa = $expiredProductsList->sum('jumlah_stok');
        $totalHargaKadaluarsa = $expiredProductsList->sum(function($exp) {
            $harga = $exp->produk ? floatval($exp->produk->harga) : 0;
            return intval($exp->jumlah_stok) * $harga;
        });

        // Returned Products Calculation
        $returQuery = Retur::query();
        if (!empty($startDate)) {
            $returQuery->whereDate('return_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $returQuery->whereDate('return_date', '<=', $endDate);
        }
        $allRetursList = $returQuery->latest()->get();
        $returnedProductsList = $allRetursList->filter(function($r) {
            $status = strtolower($r->status ?? '');
            return in_array($status, ['done', 'accepted', 'completed', 'selesai', 'disetujui']);
        })->values();
        $jumlahReturSelesai = $returnedProductsList->count();
        $productMapByName = Produk::all()->keyBy('nama_produk');
        $orderMapByNumber = Pesanan::all()->keyBy('order_number');
        $totalHargaReturSelesai = $returnedProductsList->sum(function($retur) use ($productMapByName, $orderMapByNumber) {
            $price = 0;
            if ($retur->product_name && $productMapByName->has($retur->product_name)) {
                $price = floatval($productMapByName->get($retur->product_name)->harga);
            } elseif ($retur->order_id && $orderMapByNumber->has($retur->order_id)) {
                $ord = $orderMapByNumber->get($retur->order_id);
                $items = is_array($ord->items) ? $ord->items : [];
                foreach ($items as $it) {
                    if (strcasecmp($it['name'] ?? '', $retur->product_name ?? '') === 0) {
                        $price = floatval($it['price'] ?? 0);
                        break;
                    }
                }
                if ($price <= 0 && count($items) > 0) {
                    $price = floatval($items[0]['price'] ?? 0);
                }
            }
            return $price;
        });

        return [
            'totalPemasukkanAsli' => $totalPemasukkanAsli,
            'totalPemasukkanSetelahDiskon' => $totalPemasukkanSetelahDiskon,
            'totalPotonganDiskon' => $totalPotonganDiskon,
            'expiredProductsList' => $expiredProductsList,
            'jumlahProdukKadaluarsa' => $jumlahProdukKadaluarsa,
            'totalHargaKadaluarsa' => $totalHargaKadaluarsa,
            'returnedProductsList' => $returnedProductsList,
            'jumlahReturSelesai' => $jumlahReturSelesai,
            'totalHargaReturSelesai' => $totalHargaReturSelesai,
            'productMapByName' => $productMapByName,
            'orderMapByNumber' => $orderMapByNumber,
        ];
    }

    /**
     * Menyusun data teks berformat TSV dengan BOM UTF-8 untuk clipboard Google Sheets / AJAX.
     *
     * @param  \Illuminate\Support\Collection  $sales
     * @param  array  $metrics
     * @param  array  $discountedProductIds
     * @param  ?string  $startDate
     * @param  ?string  $endDate
     * @return string
     */
    private function buildTsvData($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string
    {
        $tsv = "\xEF\xBB\xBF";
        $tsv .= "LAPORAN PENJUALAN ZYH CORNER (TERFILTER)\n";
        $tsv .= "Periode Filter\t" . ($startDate ?: 'Semua') . " s.d. " . ($endDate ?: 'Semua') . "\n";
        $tsv .= "Total Pemasukkan Asli (Bruto)\tRp " . number_format($metrics['totalPemasukkanAsli'], 0, ',', '.') . "\n";
        $tsv .= "Total Potongan Diskon\tRp " . number_format($metrics['totalPotonganDiskon'], 0, ',', '.') . "\n";
        $tsv .= "Total Pemasukkan Setelah Diskon (Net)\tRp " . number_format($metrics['totalPemasukkanSetelahDiskon'], 0, ',', '.') . "\n";
        $tsv .= "Jumlah Produk Kadaluarsa\t" . number_format($metrics['jumlahProdukKadaluarsa'], 0, ',', '.') . " Pcs\n";
        $tsv .= "Total Harga Produk Kadaluarsa\tRp " . number_format($metrics['totalHargaKadaluarsa'], 0, ',', '.') . "\n";
        $tsv .= "Jumlah Retur Selesai\t" . number_format($metrics['jumlahReturSelesai'], 0, ',', '.') . " Item\n";
        $tsv .= "Total Harga Retur Selesai\tRp " . number_format($metrics['totalHargaReturSelesai'], 0, ',', '.') . "\n\n";

        // Table 1: Transaksi Penjualan Toko
        $tsv .= "--- RINCIAN TRANSAKSI PENJUALAN TOKO ---\n";
        $tsv .= "No. Pesanan\tTanggal Transaksi\tJenis Penjualan\tPelanggan\tEmail/Telepon\tStatus Pembayaran\tStatus Pesanan\tStatus Promo Diskon\tTotal Omset Net\n";
        foreach ($sales as $order) {
            $items = is_array($order->items) ? $order->items : [];
            $itemIds = array_column($items, 'id');
            $hasDisc = false;
            foreach ($itemIds as $pId) {
                if (in_array($pId, $discountedProductIds)) $hasDisc = true;
            }
            $tsv .= implode("\t", [
                $order->order_number,
                $order->created_at ? $order->created_at->format('d-m-Y H:i') : '-',
                $order->jenis_penjualan ?? 'Online',
                $order->customer_name ?? '-',
                $order->email_pembeli ?? '-',
                $order->payment_status ?? 'Pending',
                $order->order_status ?? 'Diproses',
                $hasDisc ? 'Ya (Promo Diskon)' : 'Tidak',
                'Rp ' . number_format($order->total ?? 0, 0, ',', '.')
            ]) . "\n";
        }

        // Table 2: Rincian Produk Kadaluarsa
        if ($metrics['expiredProductsList']->count() > 0) {
            $tsv .= "\n--- RINCIAN PRODUK KADALUARSA (STOK EXPIRED) ---\n";
            $tsv .= "Nama Produk Kadaluarsa\tTanggal Kadaluarsa\tJumlah Stok\tHarga Satuan\tTotal Nominal Kerugian\n";
            foreach ($metrics['expiredProductsList'] as $exp) {
                $hargaSatuan = $exp->produk ? floatval($exp->produk->harga) : 0;
                $subtotalLoss = $hargaSatuan * intval($exp->jumlah_stok);
                $tsv .= implode("\t", [
                    $exp->produk->nama_produk ?? 'Produk Dihapus',
                    $exp->tanggal_expired ? date('d-m-Y', strtotime($exp->tanggal_expired)) : '-',
                    $exp->jumlah_stok . ' Pcs',
                    'Rp ' . number_format($hargaSatuan, 0, ',', '.'),
                    'Rp ' . number_format($subtotalLoss, 0, ',', '.')
                ]) . "\n";
            }
        }

        // Table 3: Rincian Produk Retur Selesai
        if ($metrics['returnedProductsList']->count() > 0) {
            $tsv .= "\n--- RINCIAN PRODUK RETUR SELESAI ---\n";
            $tsv .= "No. Order\tNama Pelanggan\tNama Produk Retur\tAlasan Retur\tTanggal Retur\tStatus Retur\tEstimasi Nominal Retur\n";
            foreach ($metrics['returnedProductsList'] as $retur) {
                $pPrice = 0;
                if ($retur->product_name && $metrics['productMapByName']->has($retur->product_name)) {
                    $pPrice = floatval($metrics['productMapByName']->get($retur->product_name)->harga);
                } elseif ($retur->order_id && $metrics['orderMapByNumber']->has($retur->order_id)) {
                    $ord = $metrics['orderMapByNumber']->get($retur->order_id);
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
                $tsv .= implode("\t", [
                    $retur->order_id ?? '-',
                    $retur->customer_name ?? '-',
                    $retur->product_name ?? '-',
                    $retur->reason ?? '-',
                    $retur->return_date ? date('d-m-Y', strtotime($retur->return_date)) : '-',
                    $retur->status ?? 'Done',
                    'Rp ' . number_format($pPrice, 0, ',', '.')
                ]) . "\n";
            }
        }

        return $tsv;
    }

    /**
     * Membuat berkas XML Excel Spreadsheet 2003 multi-tabel dan menyimpannya ke storage.
     *
     * @param  \Illuminate\Support\Collection  $sales
     * @param  array  $metrics
     * @param  array  $discountedProductIds
     * @param  ?string  $startDate
     * @param  ?string  $endDate
     * @return string Path absolut ke berkas spreadsheet master
     */
    private function generateXmlSpreadsheet($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string
    {
        $reportsDir = storage_path('app/reports');
        if (!file_exists($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }

        $masterFilePath = $reportsDir . '/Laporan_Penjualan_Master.xls';
        $sheetName = 'Laporan ' . date('d-m H.i.s');

        $wsXml = ' <Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">' . "\n";
        $wsXml .= '  <Table>' . "\n";

        // Summary Rows
        $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">LAPORAN PENJUALAN ZYH CORNER (TERFILTER)</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Periode Filter</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars(($startDate ?: 'Semua') . ' s.d. ' . ($endDate ?: 'Semua')) . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Pemasukkan Asli (Bruto)</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($metrics['totalPemasukkanAsli'], 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Potongan Diskon</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($metrics['totalPotonganDiskon'], 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Pemasukkan Setelah Diskon (Net)</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($metrics['totalPemasukkanSetelahDiskon'], 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Jumlah Produk Kadaluarsa</Data></Cell><Cell><Data ss:Type="String">' . number_format($metrics['jumlahProdukKadaluarsa'], 0, ',', '.') . ' Pcs</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Harga Produk Kadaluarsa</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($metrics['totalHargaKadaluarsa'], 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Jumlah Retur Selesai</Data></Cell><Cell><Data ss:Type="String">' . number_format($metrics['jumlahReturSelesai'], 0, ',', '.') . ' Item</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Harga Retur Selesai</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($metrics['totalHargaReturSelesai'], 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row></Row>' . "\n";

        // Table 1: Transaksi Penjualan Toko
        $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">RINCIAN TRANSAKSI PENJUALAN TOKO</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row>';
        $headers = ['No. Pesanan', 'Tanggal Transaksi', 'Jenis Penjualan', 'Pelanggan', 'Email/Telepon', 'Status Pembayaran', 'Status Pesanan', 'Status Promo Diskon', 'Total Omset Net'];
        foreach ($headers as $h) {
            $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        $wsXml .= '</Row>' . "\n";

        foreach ($sales as $order) {
            $items = is_array($order->items) ? $order->items : [];
            $itemIds = array_column($items, 'id');
            $hasDisc = false;
            foreach ($itemIds as $pId) {
                if (in_array($pId, $discountedProductIds)) $hasDisc = true;
            }

            $wsXml .= '   <Row>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->order_number) . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->created_at ? $order->created_at->format('d-m-Y H:i') : '-') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->jenis_penjualan ?? 'Online') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->customer_name ?? '-') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->email_pembeli ?? '-') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->payment_status ?? 'Pending') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($order->order_status ?? 'Diproses') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($hasDisc ? 'Ya (Promo Diskon)' : 'Tidak') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">Rp ' . number_format($order->total ?? 0, 0, ',', '.') . '</Data></Cell>';
            $wsXml .= '</Row>' . "\n";
        }

        // Table 2: Rincian Produk Kadaluarsa
        if ($metrics['expiredProductsList']->count() > 0) {
            $wsXml .= '   <Row></Row>' . "\n";
            $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">RINCIAN PRODUK KADALUARSA (STOK EXPIRED)</Data></Cell></Row>' . "\n";
            $wsXml .= '   <Row>';
            $expHeaders = ['Nama Produk Kadaluarsa', 'Tanggal Kadaluarsa', 'Jumlah Stok', 'Harga Satuan', 'Total Nominal Kerugian'];
            foreach ($expHeaders as $h) {
                $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
            }
            $wsXml .= '</Row>' . "\n";

            foreach ($metrics['expiredProductsList'] as $exp) {
                $hargaSatuan = $exp->produk ? floatval($exp->produk->harga) : 0;
                $subtotalLoss = $hargaSatuan * intval($exp->jumlah_stok);
                $wsXml .= '   <Row>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($exp->produk->nama_produk ?? 'Produk Dihapus') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($exp->tanggal_expired ? date('d-m-Y', strtotime($exp->tanggal_expired)) : '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($exp->jumlah_stok . ' Pcs') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">Rp ' . number_format($hargaSatuan, 0, ',', '.') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">Rp ' . number_format($subtotalLoss, 0, ',', '.') . '</Data></Cell>';
                $wsXml .= '</Row>' . "\n";
            }
        }

        // Table 3: Rincian Produk Retur Selesai
        if ($metrics['returnedProductsList']->count() > 0) {
            $wsXml .= '   <Row></Row>' . "\n";
            $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">RINCIAN PRODUK RETUR SELESAI</Data></Cell></Row>' . "\n";
            $wsXml .= '   <Row>';
            $returHeaders = ['No. Order', 'Nama Pelanggan', 'Nama Produk Retur', 'Alasan Retur', 'Tanggal Retur', 'Status Retur', 'Estimasi Nominal Retur'];
            foreach ($returHeaders as $h) {
                $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
            }
            $wsXml .= '</Row>' . "\n";

            foreach ($metrics['returnedProductsList'] as $retur) {
                $pPrice = 0;
                if ($retur->product_name && $metrics['productMapByName']->has($retur->product_name)) {
                    $pPrice = floatval($metrics['productMapByName']->get($retur->product_name)->harga);
                } elseif ($retur->order_id && $metrics['orderMapByNumber']->has($retur->order_id)) {
                    $ord = $metrics['orderMapByNumber']->get($retur->order_id);
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

                $wsXml .= '   <Row>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($retur->order_id ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($retur->customer_name ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($retur->product_name ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($retur->reason ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($retur->return_date ? date('d-m-Y', strtotime($retur->return_date)) : '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($retur->status ?? 'Done') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">Rp ' . number_format($pPrice, 0, ',', '.') . '</Data></Cell>';
                $wsXml .= '</Row>' . "\n";
            }
        }

        $wsXml .= '  </Table>' . "\n";
        $wsXml .= ' </Worksheet>' . "\n";

        if (file_exists($masterFilePath)) {
            $masterContent = file_get_contents($masterFilePath);
            $pos = strrpos($masterContent, '</Workbook>');
            if ($pos !== false) {
                $updatedContent = substr($masterContent, 0, $pos) . $wsXml . '</Workbook>';
            } else {
                $updatedContent = $masterContent . $wsXml;
            }
        } else {
            $stylesXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
                '<?mso-application progid="Excel.Sheet"?>' . "\n" .
                '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n" .
                ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n" .
                ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n" .
                ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n" .
                ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n" .
                ' <Styles>' . "\n" .
                '  <Style ss:ID="Default" ss:Name="Normal"><Alignment ss:Vertical="Bottom"/><Font ss:FontName="Arial" ss:Size="10"/></Style>' . "\n" .
                '  <Style ss:ID="Header"><Font ss:FontName="Arial" ss:Size="11" ss:Color="#FFFFFF" ss:Bold="1"/><Interior ss:Color="#00B207" ss:Pattern="Solid"/></Style>' . "\n" .
                '  <Style ss:ID="SubHeader"><Font ss:FontName="Arial" ss:Size="10" ss:Color="#FFFFFF" ss:Bold="1"/><Interior ss:Color="#0284C7" ss:Pattern="Solid"/></Style>' . "\n" .
                ' </Styles>' . "\n";
            $updatedContent = $stylesXml . $wsXml . '</Workbook>';
        }

        file_put_contents($masterFilePath, $updatedContent);

        return $masterFilePath;
    }

    /**
     * Ekspor CSV data pesanan aktif.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportOrders()
    {
        $fileName = 'data-pesanan-online-' . date('Y-m-d-His') . '.csv';
        $orders = Pesanan::where('jenis_penjualan', 'Online')->orWhereNull('jenis_penjualan')->latest()->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputs($file, "sep=;\n");
            fputcsv($file, ['No. Pesanan', 'Tanggal', 'Pelanggan', 'Email', 'Status Pembayaran', 'Status Pesanan', 'Total Harga'], ';');

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->created_at ? $order->created_at->format('d-m-Y H:i') : '-',
                    $order->customer_name ?? '-',
                    $order->email_pembeli ?? '-',
                    $order->payment_status ?? 'Pending',
                    $order->order_status ?? 'Diproses',
                    'Rp ' . number_format($order->total ?? 0, 0, ',', '.')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

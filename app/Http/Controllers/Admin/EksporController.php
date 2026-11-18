<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\SalesReportService;
use Illuminate\Http\Request;

class EksporController extends Controller
{
    /**
     * Ekspor seluruh data penjualan terfilter (Spreadsheet XML per-request / TSV AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function exportAllSales(Request $request)
    {
        if ($request->has('gsheet') || $request->input('target') === 'google') {
            return redirect('https://docs.google.com/spreadsheets/d/1RzbBH2PJvgOCiCJ_HgpaI88q87uJQKDlAUwJn3XH4_E/edit?usp=sharing');
        }

        [$sales, $startDate, $endDate, , , $discountedProductIds] = SalesReportService::filterSales($request);
        $metrics = SalesReportService::calculateMetrics($sales, $startDate, $endDate);

        if ($request->wantsJson() || $request->ajax() || $request->input('format') === 'raw') {
            $tsv = $this->buildTsvData($sales, $metrics, $discountedProductIds, $startDate, $endDate);
            return response()->json(['success' => true, 'data' => $tsv]);
        }

        $xmlContent = $this->generateXmlSpreadsheetContent($sales, $metrics, $discountedProductIds, $startDate, $endDate);

        return response($xmlContent, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="Laporan_Penjualan_Master.xls"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Menyusun data teks berformat TSV dengan BOM UTF-8 (disanitasi dari injeksi formula) untuk clipboard Google Sheets / AJAX.
     */
    private function buildTsvData($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string
    {
        $clean = fn ($val) => str_replace(["\t", "\r", "\n"], ' ', SalesReportService::sanitizeSpreadsheetCell($val));

        $tsv = "\xEF\xBB\xBF";
        $tsv .= "LAPORAN PENJUALAN ZYH CORNER (TERFILTER)\n";
        $tsv .= "Periode Filter\t" . $clean(($startDate ?: 'Semua') . ' s.d. ' . ($endDate ?: 'Semua')) . "\n";
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
            $hasDisc = SalesReportService::orderHasDiscount($order, $discountedProductIds);
            $tsv .= implode("\t", [
                $clean($order->order_number),
                $order->created_at ? $order->created_at->format('d-m-Y H:i') : '-',
                $clean($order->jenis_penjualan ?? 'Online'),
                $clean($order->customer_name ?? '-'),
                $clean($order->email_pembeli ?? '-'),
                $clean($order->payment_status ?? 'Pending'),
                $clean($order->order_status ?? 'Diproses'),
                $hasDisc ? 'Ya (Promo Diskon)' : 'Tidak',
                'Rp ' . number_format($order->total ?? 0, 0, ',', '.')
            ]) . "\n";
        }

        // Table 2: Rincian Produk Kadaluarsa
        if ($metrics['expiredProductsList']->count() > 0) {
            $tsv .= "\n--- RINCIAN PRODUK KADALUARSA (STOK EXPIRED) ---\n";
            $tsv .= "Nama Produk Kadaluarsa\tTanggal Kadaluarsa\tJumlah Stok\tHarga Satuan\tTotal Nominal Kerugian\n";
            foreach ($metrics['expiredProductsList'] as $exp) {
                $stokNum = SalesReportService::parseStockQuantity($exp->jumlah_stok);
                $hargaSatuan = $exp->produk ? (float) $exp->produk->harga : 0.0;
                $subtotalLoss = $hargaSatuan * $stokNum;
                $tsv .= implode("\t", [
                    $clean($exp->produk->nama_produk ?? 'Produk Dihapus'),
                    $exp->tanggal_expired ? date('d-m-Y', strtotime($exp->tanggal_expired)) : '-',
                    $stokNum . ' Pcs',
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
                $pPrice = $metrics['returResolvedPrices'][$retur->id]
                    ?? SalesReportService::resolveReturnNominal($retur, $metrics['productMapByName'], $metrics['orderMapByNumber']);
                $tsv .= implode("\t", [
                    $clean($retur->order_id ?? '-'),
                    $clean($retur->customer_name ?? '-'),
                    $clean($retur->product_name ?? '-'),
                    $clean($retur->reason ?? '-'),
                    $retur->return_date ? date('d-m-Y', strtotime($retur->return_date)) : '-',
                    $clean($retur->status ?? 'Done'),
                    'Rp ' . number_format($pPrice, 0, ',', '.')
                ]) . "\n";
            }
        }

        return $tsv;
    }

    /**
     * Membuat konten XML Excel Spreadsheet 2003 per-request secara langsung di memori (tanpa menumpuk ke file bersama di storage).
     */
    private function generateXmlSpreadsheetContent($sales, array $metrics, array $discountedProductIds, ?string $startDate, ?string $endDate): string
    {
        $xmlCell = fn ($val) => htmlspecialchars(SalesReportService::sanitizeSpreadsheetCell($val), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $sheetName = 'Laporan ' . date('d-m H.i.s');

        $wsXml = ' <Worksheet ss:Name="' . htmlspecialchars($sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '">' . "\n";
        $wsXml .= '  <Table>' . "\n";

        // Summary Rows
        $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">LAPORAN PENJUALAN ZYH CORNER (TERFILTER)</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Periode Filter</Data></Cell><Cell><Data ss:Type="String">' . $xmlCell(($startDate ?: 'Semua') . ' s.d. ' . ($endDate ?: 'Semua')) . '</Data></Cell></Row>' . "\n";
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
            $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
        }
        $wsXml .= '</Row>' . "\n";

        foreach ($sales as $order) {
            $hasDisc = SalesReportService::orderHasDiscount($order, $discountedProductIds);

            $wsXml .= '   <Row>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->order_number) . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->created_at ? $order->created_at->format('d-m-Y H:i') : '-') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->jenis_penjualan ?? 'Online') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->customer_name ?? '-') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->email_pembeli ?? '-') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->payment_status ?? 'Pending') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($order->order_status ?? 'Diproses') . '</Data></Cell>';
            $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($hasDisc ? 'Ya (Promo Diskon)' : 'Tidak') . '</Data></Cell>';
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
                $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
            }
            $wsXml .= '</Row>' . "\n";

            foreach ($metrics['expiredProductsList'] as $exp) {
                $stokNum = SalesReportService::parseStockQuantity($exp->jumlah_stok);
                $hargaSatuan = $exp->produk ? (float) $exp->produk->harga : 0.0;
                $subtotalLoss = $hargaSatuan * $stokNum;
                $wsXml .= '   <Row>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($exp->produk->nama_produk ?? 'Produk Dihapus') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($exp->tanggal_expired ? date('d-m-Y', strtotime($exp->tanggal_expired)) : '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($stokNum . ' Pcs') . '</Data></Cell>';
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
                $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
            }
            $wsXml .= '</Row>' . "\n";

            foreach ($metrics['returnedProductsList'] as $retur) {
                $pPrice = $metrics['returResolvedPrices'][$retur->id]
                    ?? SalesReportService::resolveReturnNominal($retur, $metrics['productMapByName'], $metrics['orderMapByNumber']);

                $wsXml .= '   <Row>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($retur->order_id ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($retur->customer_name ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($retur->product_name ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($retur->reason ?? '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($retur->return_date ? date('d-m-Y', strtotime($retur->return_date)) : '-') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">' . $xmlCell($retur->status ?? 'Done') . '</Data></Cell>';
                $wsXml .= '<Cell><Data ss:Type="String">Rp ' . number_format($pPrice, 0, ',', '.') . '</Data></Cell>';
                $wsXml .= '</Row>' . "\n";
            }
        }

        $wsXml .= '  </Table>' . "\n";
        $wsXml .= ' </Worksheet>' . "\n";

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

        return $stylesXml . $wsXml . '</Workbook>';
    }

    /**
     * Ekspor CSV data pesanan aktif (disanitasi dari injeksi formula).
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
                    SalesReportService::sanitizeSpreadsheetCell($order->order_number),
                    $order->created_at ? $order->created_at->format('d-m-Y H:i') : '-',
                    SalesReportService::sanitizeSpreadsheetCell($order->customer_name ?? '-'),
                    SalesReportService::sanitizeSpreadsheetCell($order->email_pembeli ?? '-'),
                    SalesReportService::sanitizeSpreadsheetCell($order->payment_status ?? 'Pending'),
                    SalesReportService::sanitizeSpreadsheetCell($order->order_status ?? 'Diproses'),
                    'Rp ' . number_format($order->total ?? 0, 0, ',', '.')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

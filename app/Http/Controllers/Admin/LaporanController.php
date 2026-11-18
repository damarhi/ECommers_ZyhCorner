<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Services\SalesReportService;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Halaman master seluruh penjualan toko dan endpoint live AJAX search.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function allSales(Request $request)
    {
        $query = Pesanan::query();

        if ($request->has('jenis_penjualan') && is_array($request->jenis_penjualan)) {
            $types = $request->jenis_penjualan;
            $query->where(function($q) use ($types) {
                if (in_array('Offline', $types)) {
                    $q->where('jenis_penjualan', 'Offline');
                }
                if (in_array('Online', $types)) {
                    // Force online orders to be 'selesai'
                    if (in_array('Offline', $types)) {
                        $q->orWhere(function($subQ) {
                            $subQ->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
                        });
                    } else {
                        $q->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
                    }
                }
            });
        } else {
            $query->where(function($q) {
                $q->where('jenis_penjualan', 'Offline')
                  ->orWhere(function($subQ) {
                      $subQ->where('jenis_penjualan', 'Online')->where('order_status', 'selesai');
                  });
            });
        }

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('email_pembeli', 'like', $searchTerm)
                  ->orWhere('items', 'like', $searchTerm);
            });
        }

        $isAdmin = auth()->check() && auth()->user()->role === 'admin';
        $totalPendapatan = $isAdmin ? (clone $query)->sum('total') : null;
        $totalOnlineCount = (clone $query)->where('jenis_penjualan', 'Online')->count();
        $totalOfflineCount = (clone $query)->where('jenis_penjualan', 'Offline')->count();

        $sales = $query->latest()->paginate(10)->withQueryString();
        $preloadedProducts = Pesanan::preloadModalContext($sales->items());

        if ($request->ajax() || $request->has('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $tableHtml = view('Admin.kelola_penjualan.partials.all_sales_table', compact('sales', 'preloadedProducts'))->render();
            if ($request->wantsJson()) {
                return response()->json([
                    'html' => $tableHtml,
                    'total' => $sales->total(),
                    'firstItem' => $sales->firstItem() ?? 0,
                    'lastItem' => $sales->lastItem() ?? 0,
                    'totalPendapatan' => $isAdmin ? ('Rp. ' . number_format((float) $totalPendapatan, 0, ',', '.')) : null,
                    'totalOnlineCount' => $totalOnlineCount,
                    'totalOfflineCount' => $totalOfflineCount,
                ]);
            }
            return response($tableHtml);
        }

        return view('Admin.kelola_penjualan.all_sales', compact('sales', 'preloadedProducts', 'totalPendapatan', 'totalOnlineCount', 'totalOfflineCount'));
    }

    /**
     * Halaman laporan penjualan komprehensif, filtering, dan metrik KPI keuangan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function salesReport(Request $request)
    {
        [$sales, $startDate, $endDate, $preset, $hasDiscountFilter, $discountedProductIds] = SalesReportService::filterSales($request);
        $metrics = SalesReportService::calculateMetrics($sales, $startDate, $endDate);

        $totalPemasukkanAsli = $metrics['totalPemasukkanAsli'];
        $totalPemasukkanSetelahDiskon = $metrics['totalPemasukkanSetelahDiskon'];
        $totalPotonganDiskon = $metrics['totalPotonganDiskon'];

        $totalTransaksi = $sales->count();
        $totalOnlineCount = $sales->where('jenis_penjualan', 'Online')->count();
        $totalOfflineCount = $sales->where('jenis_penjualan', 'Offline')->count();

        $jumlahProdukKadaluarsa = $metrics['jumlahProdukKadaluarsa'];
        $totalHargaKadaluarsa = $metrics['totalHargaKadaluarsa'];
        $expiredProductsList = $metrics['expiredProductsList'];

        $jumlahReturSelesai = $metrics['jumlahReturSelesai'];
        $totalHargaReturSelesai = $metrics['totalHargaReturSelesai'];
        $returnedProductsList = $metrics['returnedProductsList'];
        $returResolvedPrices = $metrics['returResolvedPrices'];

        return view('Admin.kelola_penjualan.sales_report', compact(
            'sales',
            'totalPemasukkanAsli',
            'totalPemasukkanSetelahDiskon',
            'totalPotonganDiskon',
            'totalTransaksi',
            'totalOnlineCount',
            'totalOfflineCount',
            'jumlahProdukKadaluarsa',
            'totalHargaKadaluarsa',
            'expiredProductsList',
            'jumlahReturSelesai',
            'totalHargaReturSelesai',
            'returnedProductsList',
            'returResolvedPrices',
            'startDate',
            'endDate',
            'preset',
            'hasDiscountFilter',
            'discountedProductIds'
        ));
    }
}

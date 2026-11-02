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

        $totalPendapatan = (clone $query)->sum('total');
        $totalOnlineCount = (clone $query)->where('jenis_penjualan', 'Online')->count();
        $totalOfflineCount = (clone $query)->where('jenis_penjualan', 'Offline')->count();

        $sales = $query->latest()->paginate(10)->withQueryString();

        if ($request->ajax() || $request->has('ajax') || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            $tableHtml = view('Admin.kelola_penjualan.partials.all_sales_table', compact('sales'))->render();
            if ($request->wantsJson()) {
                return response()->json([
                    'html' => $tableHtml,
                    'total' => $sales->total(),
                    'firstItem' => $sales->firstItem() ?? 0,
                    'lastItem' => $sales->lastItem() ?? 0,
                    'totalPendapatan' => 'Rp. ' . number_format($totalPendapatan, 0, ',', '.'),
                    'totalOnlineCount' => $totalOnlineCount,
                    'totalOfflineCount' => $totalOfflineCount,
                ]);
            }
            return response($tableHtml);
        }

        return view('Admin.kelola_penjualan.all_sales', compact('sales', 'totalPendapatan', 'totalOnlineCount', 'totalOfflineCount'));
    }

    /**
     * Halaman laporan penjualan komprehensif, filtering, dan metrik KPI keuangan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function salesReport(Request $request)
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
                $itemProductIds = array_column($items, 'id');
                $hasDiscount = false;
                foreach ($itemProductIds as $pId) {
                    if (in_array($pId, $discountedProductIds)) {
                        $hasDiscount = true;
                        break;
                    }
                }
                if ($hasDiscountFilter === 'Yes' && !$hasDiscount) return false;
                if ($hasDiscountFilter === 'No' && $hasDiscount) return false;
                return true;
            })->values();
        } else {
            $sales = $allSales;
        }

        // Load all products map for accurate price lookup
        $allProducts = Produk::all()->keyBy('id_produk');

        // Calculate Revenue Metrics:
        // Total Pemasukkan Setelah Diskon (Final Omset)
        $totalPemasukkanSetelahDiskon = $sales->sum('total');

        // Total Pemasukkan Asli (Bruto sebelum diskon)
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

        // Total Potongan Diskon
        $totalPotonganDiskon = max(0, $totalPemasukkanAsli - $totalPemasukkanSetelahDiskon);

        $totalTransaksi = $sales->count();
        $totalOnlineCount = $sales->where('jenis_penjualan', 'Online')->count();
        $totalOfflineCount = $sales->where('jenis_penjualan', 'Offline')->count();

        // Expired Products Calculation (Separated from Sales Filter)
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

        // Returned Products Calculation (Status completed/accepted)
        $returQuery = Retur::query();
        if (!empty($startDate)) {
            $returQuery->whereDate('return_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $returQuery->whereDate('return_date', '<=', $endDate);
        }
        $allRetursList = $returQuery->latest()->get();

        // Filter for completed/accepted returns (Done, Accepted, Completed, Selesai, Disetujui)
        $returnedProductsList = $allRetursList->filter(function($r) {
            $status = strtolower($r->status ?? '');
            return in_array($status, ['done', 'accepted', 'completed', 'selesai', 'disetujui']);
        })->values();

        $jumlahReturSelesai = $returnedProductsList->count();

        // Lookup product name and order for returned item pricing
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
            'startDate',
            'endDate',
            'preset',
            'hasDiscountFilter',
            'discountedProductIds'
        ));
    }
}

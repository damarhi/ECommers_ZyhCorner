<?php

namespace App\Services;

use App\Models\Diskon;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukKadaluarsa;
use App\Models\Retur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SalesReportService
{
    /**
     * Neutralize spreadsheet formula injection characters (=, +, -, @, TAB, CR) for CSV/TSV cells.
     */
    public static function sanitizeSpreadsheetCell($value): string
    {
        $str = (string) ($value ?? '');
        if ($str === '') {
            return '';
        }

        $trimmed = ltrim($str);
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'" . $str;
        }
        if (in_array($str[0], ["\t", "\r"], true)) {
            return "'" . $str;
        }

        return $str;
    }

    /**
     * Safely parse numeric stock count from string/int (e.g. '10', '5 Pcs', 'Tersedia').
     */
    public static function parseStockQuantity($raw): int
    {
        if (is_int($raw)) {
            return max(0, $raw);
        }
        if (is_numeric($raw)) {
            return max(0, (int) $raw);
        }

        $str = trim((string) $raw);
        if ($str === '') {
            return 0;
        }
        if (strtolower($str) === 'tersedia') {
            return 99;
        }
        if (preg_match('/(\d+)/', $str, $m)) {
            return (int) $m[1];
        }

        return 0;
    }

    /**
     * Apply standardized sales report filters and return [salesCollection, startDate, endDate, preset, hasDiscountFilter, discountedProductIds].
     *
     * @return array{0: Collection, 1: ?string, 2: ?string, 3: ?string, 4: ?string, 5: array}
     */
    public static function filterSales(Request $request): array
    {
        $query = Pesanan::query();

        // 1. Jenis Penjualan Filter
        $jenis = $request->input('jenis_penjualan');
        if (!empty($jenis) && $jenis !== 'All') {
            if (is_array($jenis)) {
                $query->where(function ($q) use ($jenis) {
                    if (in_array('Offline', $jenis, true)) {
                        $q->where('jenis_penjualan', 'Offline');
                    }
                    if (in_array('Online', $jenis, true)) {
                        if (in_array('Offline', $jenis, true)) {
                            $q->orWhere(function ($subQ) {
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
            $query->where(function ($q) {
                $q->where('jenis_penjualan', 'Offline')
                  ->orWhere(function ($subQ) {
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
            $searchTerm = '%' . trim((string) $request->search) . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm)
                  ->orWhere('email_pembeli', 'like', $searchTerm)
                  ->orWhere('items', 'like', $searchTerm);
            });
        }

        // 5. Filter Diskon (has_discount)
        $hasDiscountFilter = $request->input('has_discount');
        $discountedProductIds = Diskon::pluck('id_produk')->filter()->map(fn ($id) => (int) $id)->all();

        $allSales = $query->latest()->get();

        if (!empty($hasDiscountFilter) && $hasDiscountFilter !== 'All') {
            $sales = $allSales->filter(function (Pesanan $order) use ($hasDiscountFilter, $discountedProductIds) {
                $hasDiscount = self::orderHasDiscount($order, $discountedProductIds);
                if ($hasDiscountFilter === 'Yes') {
                    return $hasDiscount;
                }
                if ($hasDiscountFilter === 'No') {
                    return !$hasDiscount;
                }
                return true;
            })->values();
        } else {
            $sales = $allSales;
        }

        return [$sales, $startDate, $endDate, $preset, $hasDiscountFilter, $discountedProductIds];
    }

    /**
     * Determine whether an order received a discount (either via historical original_price > price or discounted product list).
     */
    public static function orderHasDiscount(Pesanan $order, array $discountedProductIds): bool
    {
        $items = is_array($order->items) ? $order->items : [];
        foreach ($items as $item) {
            if (isset($item['original_price'], $item['price']) && (float) $item['original_price'] > (float) $item['price']) {
                return true;
            }
            $pId = isset($item['id']) ? (int) $item['id'] : (isset($item['id_produk']) ? (int) $item['id_produk'] : 0);
            if ($pId > 0 && in_array($pId, $discountedProductIds, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Compute comprehensive financial, expired-stock, and completed-return metrics without shipping distortion or PHP 8.3 TypeError.
     */
    public static function calculateMetrics(Collection $sales, ?string $startDate, ?string $endDate): array
    {
        $totalPemasukkanSetelahDiskon = (float) $sales->sum('total');
        $allProducts = Produk::all()->keyBy('id_produk');

        $totalPotonganDiskon = 0.0;
        foreach ($sales as $order) {
            $items = is_array($order->items) ? $order->items : [];
            $orderDiscount = 0.0;

            foreach ($items as $item) {
                $qty = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
                $paidUnitPrice = (float) ($item['price'] ?? 0);
                $pId = $item['id'] ?? $item['id_produk'] ?? null;
                $product = $pId ? $allProducts->get($pId) : null;

                if (isset($item['original_price']) && (float) $item['original_price'] > 0) {
                    $originalUnitPrice = (float) $item['original_price'];
                } elseif ($product) {
                    $originalUnitPrice = (float) $product->harga;
                } else {
                    $originalUnitPrice = $paidUnitPrice;
                }

                if ($originalUnitPrice > $paidUnitPrice) {
                    $orderDiscount += ($originalUnitPrice - $paidUnitPrice) * $qty;
                }
            }

            $totalPotonganDiskon += $orderDiscount;
        }

        // Gross revenue before discount includes shipping so Bruto - Diskon == Net holds even when shipping > discount
        $totalPemasukkanAsli = $totalPemasukkanSetelahDiskon + $totalPotonganDiskon;

        // Expired Products Calculation (type-safe on PHP 8.3 even when jumlah_stok is '5 Pcs' or 'Tersedia')
        $expiredQuery = ProdukKadaluarsa::with('produk');
        if (!empty($startDate)) {
            $expiredQuery->whereDate('tanggal_expired', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $expiredQuery->whereDate('tanggal_expired', '<=', $endDate);
        }
        $expiredProductsList = $expiredQuery->get();

        $jumlahProdukKadaluarsa = $expiredProductsList->sum(fn ($exp) => self::parseStockQuantity($exp->jumlah_stok));
        $totalHargaKadaluarsa = $expiredProductsList->sum(function ($exp) {
            $harga = $exp->produk ? (float) $exp->produk->harga : 0.0;
            return self::parseStockQuantity($exp->jumlah_stok) * $harga;
        });

        // Returned Products Calculation (Completed/Accepted)
        $returQuery = Retur::query();
        if (!empty($startDate)) {
            $returQuery->whereDate('return_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $returQuery->whereDate('return_date', '<=', $endDate);
        }
        $allRetursList = $returQuery->latest()->get();

        $returnedProductsList = $allRetursList->filter(function ($r) {
            $status = strtolower((string) ($r->status ?? ''));
            return in_array($status, ['done', 'accepted', 'completed', 'selesai', 'disetujui'], true);
        })->values();

        $jumlahReturSelesai = $returnedProductsList->count();
        $productMapByName = $allProducts->keyBy('nama_produk');

        $returOrderIds = $returnedProductsList->pluck('order_id')->filter()->unique()->values()->all();
        $orderMapByNumber = !empty($returOrderIds)
            ? Pesanan::whereIn('order_number', $returOrderIds)->get()->keyBy('order_number')
            : collect();

        $returResolvedPrices = [];
        $totalHargaReturSelesai = 0.0;

        foreach ($returnedProductsList as $retur) {
            $price = self::resolveReturnNominal($retur, $productMapByName, $orderMapByNumber);
            $returResolvedPrices[$retur->id] = $price;
            $totalHargaReturSelesai += $price;
        }

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
            'returResolvedPrices' => $returResolvedPrices,
        ];
    }

    public static function resolveReturnNominal(Retur $retur, Collection $productMapByName, Collection $orderMapByNumber): float
    {
        $price = 0.0;
        if ($retur->product_name && $productMapByName->has($retur->product_name)) {
            $price = (float) $productMapByName->get($retur->product_name)->harga;
        } elseif ($retur->order_id && $orderMapByNumber->has($retur->order_id)) {
            $ord = $orderMapByNumber->get($retur->order_id);
            $items = is_array($ord->items) ? $ord->items : [];
            foreach ($items as $it) {
                $itemName = $it['name'] ?? $it['nama_produk'] ?? '';
                if (strcasecmp((string) $itemName, (string) ($retur->product_name ?? '')) === 0) {
                    $price = (float) ($it['price'] ?? 0);
                    break;
                }
            }
            if ($price <= 0 && count($items) > 0) {
                $price = (float) ($items[0]['price'] ?? 0);
            }
        }

        return $price;
    }
}

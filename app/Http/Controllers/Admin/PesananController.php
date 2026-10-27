<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::query()->where('jenis_penjualan', 'Online');
        
        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm);
            });
        }

        if ($request->has('payment_status') && is_array($request->payment_status)) {
            $query->whereIn('payment_status', $request->payment_status);
        }

        $orders = $query->latest()->paginate(10);
        return view('Admin.kelola_penjualan.orders', compact('orders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'payment_status' => 'required|string|in:Pending,Paid',
            'order_status' => 'required|string|in:menunggu pembayaran,menunggu konfirmasi,dikemas,dikirim,selesai,batal',
            'total' => 'required|numeric|min:0',
        ]);

        Pesanan::create([
            'order_number' => '#' . strtoupper(Str::random(6)),
            'customer_name' => $request->customer_name,
            'payment_status' => $request->payment_status,
            'order_status' => $request->order_status,
            'total' => $request->total,
        ]);

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'payment_status' => 'required|string|in:Pending,Paid',
            'order_status' => 'required|string|in:menunggu pembayaran,menunggu konfirmasi,dikemas,dikirim,selesai,batal',
            'total' => 'required|numeric|min:0',
        ]);

        $pesanan->update($request->only('customer_name', 'payment_status', 'order_status', 'total'));

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }

    /**
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Quick confirm an unconfirmed order and change status to dikemas (kitchen prep).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function quickConfirm(Request $request, $id)
    {
        $order = Pesanan::findOrFail($id);

        if (!in_array(strtolower($order->order_status ?? ''), ['menunggu konfirmasi', 'unconfirmed'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ini sudah dikonfirmasi atau statusnya bukan Menunggu Konfirmasi.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Status pesanan bukan Menunggu Konfirmasi.');
        }

        $order->update([
            'order_status' => 'dikemas',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan ' . $order->order_number . ' berhasil dikonfirmasi dan masuk antrean dapur!',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'order_status' => 'dikemas',
                'status_label' => 'Sedang Dikemas',
            ]);
        }

        return redirect()->back()->with('success', 'Pesanan ' . $order->order_number . ' berhasil dikonfirmasi dan masuk antrean dapur!');
    }
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
                  ->orWhere('email_pembeli', 'like', $searchTerm);
            });
        }

        $totalPendapatan = (clone $query)->sum('total');
        $totalOnlineCount = (clone $query)->where('jenis_penjualan', 'Online')->count();
        $totalOfflineCount = (clone $query)->where('jenis_penjualan', 'Offline')->count();

        $sales = $query->latest()->paginate(10)->withQueryString();
        return view('Admin.kelola_penjualan.all_sales', compact('sales', 'totalPendapatan', 'totalOnlineCount', 'totalOfflineCount'));
    }

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
            $startDate = \Carbon\Carbon::today()->toDateString();
            $endDate = \Carbon\Carbon::today()->toDateString();
        } elseif ($preset === 'last_7_days') {
            $startDate = \Carbon\Carbon::today()->subDays(6)->toDateString();
            $endDate = \Carbon\Carbon::today()->toDateString();
        } elseif ($preset === 'this_month') {
            $startDate = \Carbon\Carbon::now()->startOfMonth()->toDateString();
            $endDate = \Carbon\Carbon::now()->endOfMonth()->toDateString();
        } elseif ($preset === 'this_year') {
            $startDate = \Carbon\Carbon::now()->startOfYear()->toDateString();
            $endDate = \Carbon\Carbon::now()->endOfYear()->toDateString();
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
                  ->orWhere('email_pembeli', 'like', $searchTerm);
            });
        }

        // 5. Filter Diskon (has_discount)
        $hasDiscountFilter = $request->input('has_discount');
        $discountedProductIds = \App\Models\Diskon::pluck('id_produk')->filter()->toArray();

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
        $allProducts = \App\Models\Produk::all()->keyBy('id_produk');

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
        $expiredQuery = \App\Models\ProdukKadaluarsa::with('produk');
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
        $returQuery = \App\Models\Retur::query();
        if (!empty($startDate)) {
            $returQuery->whereDate('return_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $returQuery->whereDate('return_date', '<=', $endDate);
        }
        $allRetursList = $returQuery->latest()->get();

        // Filter for completed/accepted returns (Done, Accepted, Processed, Selesai, Disetujui)
        $returnedProductsList = $allRetursList->filter(function($r) {
            $status = strtolower($r->status ?? '');
            return in_array($status, ['done', 'accepted', 'processed', 'selesai', 'disetujui']);
        })->values();

        $jumlahReturSelesai = $returnedProductsList->count();

        // Lookup product name and order for returned item pricing
        $productMapByName = \App\Models\Produk::all()->keyBy('nama_produk');
        $orderMapByNumber = \App\Models\Pesanan::all()->keyBy('order_number');

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

    /**
     * @param  int|string  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function printInvoice($id)
    {
        $order = Pesanan::findOrFail($id);
        
        $items = [];
        $orderItems = $order->items;

        if (is_array($orderItems) && count($orderItems) > 0) {
            foreach ($orderItems as $item) {
                $product = \App\Models\Produk::find($item['id'] ?? null);
                if (!$product) {
                    $product = new \App\Models\Produk();
                    $product->id_produk = $item['id'] ?? 0;
                    $product->nama_produk = $item['name'] ?? 'Product Removed';
                    $product->harga = $item['price'] ?? 0;
                    $product->foto_1 = null;
                }
                
                $qty = isset($item['qty']) ? intval($item['qty']) : (isset($item['quantity']) ? intval($item['quantity']) : 1);
                $price = isset($item['price']) ? floatval($item['price']) : floatval($product->harga);
                
                $items[] = [
                    'name' => $item['name'] ?? $product->nama_produk,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                ];
            }
        }
        
        return view('Admin.kelola_penjualan.invoice', compact('order', 'items'));
    }

    public function exportAllSales(Request $request)
    {
        if ($request->has('gsheet') || $request->input('target') === 'google') {
            return redirect('https://docs.google.com/spreadsheets/d/1RzbBH2PJvgOCiCJ_HgpaI88q87uJQKDlAUwJn3XH4_E/edit?usp=sharing');
        }

        $fileName = 'laporan-penjualan-terfilter-' . date('Y-m-d-His') . '.csv';

        // Apply exact filtering logic from salesReport
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
            $startDate = \Carbon\Carbon::today()->toDateString();
            $endDate = \Carbon\Carbon::today()->toDateString();
        } elseif ($preset === 'last_7_days') {
            $startDate = \Carbon\Carbon::today()->subDays(6)->toDateString();
            $endDate = \Carbon\Carbon::today()->toDateString();
        } elseif ($preset === 'this_month') {
            $startDate = \Carbon\Carbon::now()->startOfMonth()->toDateString();
            $endDate = \Carbon\Carbon::now()->endOfMonth()->toDateString();
        } elseif ($preset === 'this_year') {
            $startDate = \Carbon\Carbon::now()->startOfYear()->toDateString();
            $endDate = \Carbon\Carbon::now()->endOfYear()->toDateString();
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
                  ->orWhere('email_pembeli', 'like', $searchTerm);
            });
        }

        // 5. Filter Diskon (has_discount)
        $hasDiscountFilter = $request->input('has_discount');
        $discountedProductIds = \App\Models\Diskon::pluck('id_produk')->filter()->toArray();

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

        // Calculate Revenue Metrics
        $totalPemasukkanSetelahDiskon = $sales->sum('total');
        $allProducts = \App\Models\Produk::all()->keyBy('id_produk');
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
        $expiredQuery = \App\Models\ProdukKadaluarsa::with('produk');
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
        $returQuery = \App\Models\Retur::query();
        if (!empty($startDate)) {
            $returQuery->whereDate('return_date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $returQuery->whereDate('return_date', '<=', $endDate);
        }
        $allRetursList = $returQuery->latest()->get();
        $returnedProductsList = $allRetursList->filter(function($r) {
            $status = strtolower($r->status ?? '');
            return in_array($status, ['done', 'accepted', 'processed', 'selesai', 'disetujui']);
        })->values();
        $jumlahReturSelesai = $returnedProductsList->count();
        $productMapByName = \App\Models\Produk::all()->keyBy('nama_produk');
        $orderMapByNumber = \App\Models\Pesanan::all()->keyBy('order_number');
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

        // Prepare TSV formatted text data for Google Spreadsheet clipboard paste
        $tsv = "\xEF\xBB\xBF";
        $tsv .= "LAPORAN PENJUALAN ZYH CORNER (TERFILTER)\n";
        $tsv .= "Periode Filter\t" . ($startDate ?: 'Semua') . " s.d. " . ($endDate ?: 'Semua') . "\n";
        $tsv .= "Total Pemasukkan Asli (Bruto)\tRp " . number_format($totalPemasukkanAsli, 0, ',', '.') . "\n";
        $tsv .= "Total Potongan Diskon\tRp " . number_format($totalPotonganDiskon, 0, ',', '.') . "\n";
        $tsv .= "Total Pemasukkan Setelah Diskon (Net)\tRp " . number_format($totalPemasukkanSetelahDiskon, 0, ',', '.') . "\n";
        $tsv .= "Jumlah Produk Kadaluarsa\t" . number_format($jumlahProdukKadaluarsa, 0, ',', '.') . " Pcs\n";
        $tsv .= "Total Harga Produk Kadaluarsa\tRp " . number_format($totalHargaKadaluarsa, 0, ',', '.') . "\n";
        $tsv .= "Jumlah Retur Selesai\t" . number_format($jumlahReturSelesai, 0, ',', '.') . " Item\n";
        $tsv .= "Total Harga Retur Selesai\tRp " . number_format($totalHargaReturSelesai, 0, ',', '.') . "\n\n";

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
        if ($expiredProductsList->count() > 0) {
            $tsv .= "\n--- RINCIAN PRODUK KADALUARSA (STOK EXPIRED) ---\n";
            $tsv .= "Nama Produk Kadaluarsa\tTanggal Kadaluarsa\tJumlah Stok\tHarga Satuan\tTotal Nominal Kerugian\n";
            foreach ($expiredProductsList as $exp) {
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
        if ($returnedProductsList->count() > 0) {
            $tsv .= "\n--- RINCIAN PRODUK RETUR SELESAI ---\n";
            $tsv .= "No. Order\tNama Pelanggan\tNama Produk Retur\tAlasan Retur\tTanggal Retur\tStatus Retur\tEstimasi Nominal Retur\n";
            foreach ($returnedProductsList as $retur) {
                $pPrice = 0;
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

        if ($request->wantsJson() || $request->ajax() || $request->input('format') === 'raw') {
            return response()->json(['success' => true, 'data' => $tsv]);
        }

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
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Pemasukkan Asli (Bruto)</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($totalPemasukkanAsli, 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Potongan Diskon</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($totalPotonganDiskon, 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Pemasukkan Setelah Diskon (Net)</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($totalPemasukkanSetelahDiskon, 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Jumlah Produk Kadaluarsa</Data></Cell><Cell><Data ss:Type="String">' . number_format($jumlahProdukKadaluarsa, 0, ',', '.') . ' Pcs</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Harga Produk Kadaluarsa</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($totalHargaKadaluarsa, 0, ',', '.') . '</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Jumlah Retur Selesai</Data></Cell><Cell><Data ss:Type="String">' . number_format($jumlahReturSelesai, 0, ',', '.') . ' Item</Data></Cell></Row>' . "\n";
        $wsXml .= '   <Row><Cell><Data ss:Type="String">Total Harga Retur Selesai</Data></Cell><Cell><Data ss:Type="String">Rp ' . number_format($totalHargaReturSelesai, 0, ',', '.') . '</Data></Cell></Row>' . "\n";
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
        if ($expiredProductsList->count() > 0) {
            $wsXml .= '   <Row></Row>' . "\n";
            $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">RINCIAN PRODUK KADALUARSA (STOK EXPIRED)</Data></Cell></Row>' . "\n";
            $wsXml .= '   <Row>';
            $expHeaders = ['Nama Produk Kadaluarsa', 'Tanggal Kadaluarsa', 'Jumlah Stok', 'Harga Satuan', 'Total Nominal Kerugian'];
            foreach ($expHeaders as $h) {
                $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
            }
            $wsXml .= '</Row>' . "\n";

            foreach ($expiredProductsList as $exp) {
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
        if ($returnedProductsList->count() > 0) {
            $wsXml .= '   <Row></Row>' . "\n";
            $wsXml .= '   <Row><Cell ss:StyleID="Header"><Data ss:Type="String">RINCIAN PRODUK RETUR SELESAI</Data></Cell></Row>' . "\n";
            $wsXml .= '   <Row>';
            $returHeaders = ['No. Order', 'Nama Pelanggan', 'Nama Produk Retur', 'Alasan Retur', 'Tanggal Retur', 'Status Retur', 'Estimasi Nominal Retur'];
            foreach ($returHeaders as $h) {
                $wsXml .= '<Cell ss:StyleID="SubHeader"><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
            }
            $wsXml .= '</Row>' . "\n";

            foreach ($returnedProductsList as $retur) {
                $pPrice = 0;
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

        return response()->download($masterFilePath, 'Laporan_Penjualan_Master.xls', [
            'Content-Type' => 'application/vnd.ms-excel',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

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

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:pesanans,id',
        ]);

        $count = count($validated['ids']);
        Pesanan::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('orders.index')->with('success', "{$count} data pesanan berhasil dihapus.");
    }
}

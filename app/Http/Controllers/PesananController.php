<?php

namespace App\Http\Controllers;

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
            'order_status' => 'required|string|in:menunggu pembayaran,dikemas,dikirim,selesai',
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

    public function update(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'payment_status' => 'required|string|in:Pending,Paid',
            'order_status' => 'required|string|in:menunggu pembayaran,dikemas,dikirim,selesai',
            'total' => 'required|numeric|min:0',
        ]);

        $pesanan->update($request->only('customer_name', 'payment_status', 'order_status', 'total'));

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
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
}

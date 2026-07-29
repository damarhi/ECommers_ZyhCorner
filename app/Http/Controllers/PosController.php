<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Order; // Need to check what model handles orders
use App\Models\Customer;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_produk', 'like', $searchTerm)
                  ->orWhere('deskripsi', 'like', $searchTerm);
            });
        }

        if ($request->has('kategori') && is_array($request->kategori)) {
            $query->whereIn('id_kategori', $request->kategori);
        }

        $products = $query->latest()->paginate(20);
        // Since categories are needed for the filter
        $kategoris = \App\Models\Kategori::all();
        return view('Admin.kelola_penjualan.pos', compact('products', 'kategoris'));
    }

    public function checkout(Request $request)
    {
        $items = $request->input('items');
        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja kosong!'
            ], 400);
        }

        $total = 0;
        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $customerName = $request->input('customer_name') ?: 'Walk-in Customer';

        // Generate a unique order number
        do {
            $orderNumber = '#' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (\App\Models\Pesanan::where('order_number', $orderNumber)->exists());

        try {
            $pesanan = \App\Models\Pesanan::create([
                'order_number' => $orderNumber,
                'jenis_penjualan' => 'Offline',
                'customer_name' => $customerName,
                'email_pembeli' => null,
                'payment_status' => 'Paid',
                'order_status' => 'selesai',
                'total' => $total,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout berhasil dengan nomor order ' . $orderNumber,
                'order' => $pesanan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses checkout: ' . $e->getMessage()
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;

class PelangganController extends Controller
{
    public function home()
    {
        $featuredProducts = Produk::with('kategori')->inRandomOrder()->limit(4)->get();
        $hotDeals = Produk::with('kategori')->inRandomOrder()->limit(3)->get();
        $bestSellers = Produk::with('kategori')->inRandomOrder()->limit(3)->get();
        $topRated = Produk::with('kategori')->inRandomOrder()->limit(3)->get();
        $categories = Kategori::withCount('produks')->get();
        $newestProducts = Produk::with('kategori')->orderBy('created_at', 'desc')->limit(4)->get();

        return view('Pelanggan.home', compact(
            'featuredProducts', 'hotDeals', 'bestSellers', 'topRated', 'categories', 'newestProducts'
        ));
    }

    public function shop(Request $request)
    {
        $products = Produk::with('kategori')->paginate(16);
        $categories = Kategori::all();

        return view('Pelanggan.shop', compact('products', 'categories'));
    }

    public function detail($id)
    {
        $product = Produk::with('kategori')->findOrFail($id);
        $relatedProducts = Produk::with('kategori')
            ->where('id_kategori', $product->id_kategori)
            ->where('id_produk', '!=', $product->id_produk)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('Pelanggan.detail', compact('product', 'relatedProducts'));
    }

    public function userChart()
    {
        return view('Pelanggan.userChart');
    }

    public function checkout()
    {
        return view('Pelanggan.checkout');
    }

    public function processCheckout(Request $request)
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
            $total += $item['price'] * $item['qty'];
        }

        if (auth()->check()) {
            $customerName = auth()->user()->nama;
            $email = auth()->user()->email;
        } else {
            $customerName = 'Guest Customer';
            $email = null;
        }

        do {
            $orderNumber = '#' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (\App\Models\Pesanan::where('order_number', $orderNumber)->exists());

        try {
            $pesanan = \App\Models\Pesanan::create([
                'order_number' => $orderNumber,
                'jenis_penjualan' => 'Online',
                'customer_name' => $customerName,
                'email_pembeli' => $email,
                'payment_status' => 'Pending',
                'order_status' => 'menunggu pembayaran', // Default state from table
                'total' => $total,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat dengan nomor order ' . $orderNumber,
                'order' => $pesanan
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orderSuccess()
    {
        return view('Pelanggan.order_success');
    }
}

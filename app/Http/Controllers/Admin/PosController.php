<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        // Auto sync expired products
        Produk::checkAndEmptyExpiredProducts();

        // Only display showcase products (ready to sell in offline store)
        $query = Produk::with(['kategori', 'diskonAktif'])
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            });

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

        // Prioritaskan produk yang memiliki stok (> 0) berada di urutan teratas
        $query->orderByRaw("CASE 
            WHEN LOWER(status_produk) = 'tersedia' THEN 1 
            WHEN status_produk IS NOT NULL AND status_produk != '' AND status_produk != '0' AND LOWER(status_produk) != 'habis' THEN 1 
            ELSE 0 
        END DESC")->latest();

        $products = $query->paginate(20);
        $kategoris = Kategori::all();
        return view('Admin.kelola_penjualan.pos', compact('products', 'kategoris'));
    }

    public function checkoutPage()
    {
        return view('Admin.kelola_penjualan.pos_checkout');
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

        $normalizedItems = [];
        $total = 0;

        foreach ($items as $key => $item) {
            $productId = $item['id'] ?? $item['id_produk'] ?? $key;
            $quantity = (int) ($item['quantity'] ?? $item['qty'] ?? 1);

            if ($quantity <= 0) {
                continue;
            }

            $product = Produk::with('diskonAktif')->find($productId);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan di database!'
                ], 404);
            }

            if ($product->stok <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk "' . $product->nama_produk . '" sudah habis!'
                ], 400);
            }

            if ($product->stok < $quantity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok produk "' . $product->nama_produk . '" tidak mencukupi (tersisa ' . $product->stok . ' item).'
                ], 400);
            }

            $unitPrice = $product->harga_diskon ?? $product->harga;
            $subtotal = $unitPrice * $quantity;
            $total += $subtotal;

            $normalizedItems[] = [
                'id' => $product->id_produk,
                'id_produk' => $product->id_produk,
                'name' => $product->nama_produk,
                'price' => $unitPrice,
                'quantity' => $quantity,
                'qty' => $quantity,
                'subtotal' => $subtotal,
                'image' => $product->foto_1 ? asset('storage/' . $product->foto_1) : ($item['image'] ?? null)
            ];
        }

        if (empty($normalizedItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada produk valid dalam keranjang!'
            ], 400);
        }

        $customerName = $request->input('customer_name') ?: 'Walk-in Customer';
        $paymentMethod = $request->input('payment_method', 'Cash');

        // Generate a unique order number
        do {
            $orderNumber = '#' . strtoupper(Str::random(6));
        } while (Pesanan::where('order_number', $orderNumber)->exists());

        try {
            $pesanan = DB::transaction(function () use ($orderNumber, $customerName, $paymentMethod, $total, $normalizedItems) {
                $pesanan = Pesanan::create([
                    'order_number' => $orderNumber,
                    'jenis_penjualan' => 'Offline',
                    'customer_name' => $customerName,
                    'payment_method' => $paymentMethod,
                    'email_pembeli' => null,
                    'payment_status' => 'Paid',
                    'order_status' => 'selesai',
                    'total' => $total,
                    'items' => $normalizedItems,
                ]);

                Produk::reduceStockForOrder($pesanan);

                return $pesanan;
            });

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

    public function addStock(Request $request, $id)
    {
        $request->validate([
            'additional_stock' => 'required|integer|min:1|max:99999',
            'tanggal_expired' => 'nullable|date',
        ]);

        $product = Produk::findOrFail($id);

        $currentStock = $product->stok;
        $additional = (int) $request->input('additional_stock');
        $newStock = $currentStock + $additional;

        $product->status_produk = (string) $newStock;

        if ($request->filled('tanggal_expired')) {
            $product->tanggal_expired = $request->input('tanggal_expired');
        }

        $product->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Stok produk '{$product->nama_produk}' berhasil ditambah {$additional} item (Total stok: {$newStock}).",
                'new_stock' => $newStock,
                'product_id' => $product->id_produk,
            ]);
        }

        return redirect()->back()->with('success', "Stok produk '{$product->nama_produk}' berhasil diperbarui.");
    }
}

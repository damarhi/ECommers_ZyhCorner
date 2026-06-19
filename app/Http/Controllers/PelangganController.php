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

    public function dashboard()
    {
        $user = auth()->user();
        
        $recentOrders = \App\Models\Pesanan::where('email_pembeli', $user->email)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('Pelanggan.dashboard', compact('user', 'recentOrders'));
    }

    public function orderHistory(Request $request)
    {
        $user = auth()->user();
        $statusFilter = $request->query('status');

        $query = \App\Models\Pesanan::where('email_pembeli', $user->email);

        if ($statusFilter) {
            if ($statusFilter === 'processing') {
                $query->whereIn('order_status', ['menunggu pembayaran', 'processing', 'proses', 'ready']);
            } elseif ($statusFilter === 'on_the_way') {
                $query->whereIn('order_status', ['on the way', 'otw', 'kirim', 'sedang dikirim']);
            } elseif ($statusFilter === 'completed') {
                $query->whereIn('order_status', ['selesai', 'completed', 'delivered']);
            } elseif ($statusFilter === 'canceled') {
                $query->whereIn('order_status', ['batal', 'cancel', 'failed']);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(5);

        // Fetch returns for the customer
        $returs = collect();
        if ($statusFilter === 'return' || is_null($statusFilter)) {
            $returs = \App\Models\Retur::where('customer_name', $user->nama)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('Pelanggan.order_history', compact('user', 'orders', 'statusFilter', 'returs'));
    }

    public function orderDetail($id)
    {
        $user = auth()->user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        $products = \App\Models\Produk::inRandomOrder()->limit(min(3, \App\Models\Produk::count() ?: 1))->get();
        
        $items = [];
        $total = floatval($order->total);
        $count = count($products);

        if ($count > 0) {
            $remaining = $total;
            for ($i = 0; $i < $count; $i++) {
                $product = $products[$i];
                $qty = ($i === 0 && $count > 1) ? 2 : 1;
                
                if ($i === $count - 1) {
                    $itemPrice = $remaining / $qty;
                } else {
                    $itemPrice = round(($total / ($count + 1)) / 1000) * 1000;
                    if ($itemPrice * $qty >= $remaining) {
                        $itemPrice = round(($remaining / 2) / 1000) * 1000;
                    }
                }
                
                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $itemPrice,
                    'subtotal' => $itemPrice * $qty
                ];
                
                $remaining -= $itemPrice * $qty;
            }
        }

        $estimatedArrival = $order->created_at->addDays(1)->format('l') . ', 2 PM - 4 PM';

        return view('Pelanggan.order_detail', compact('user', 'order', 'items', 'estimatedArrival'));
    }

    public function cancelOrder($id)
    {
        $user = auth()->user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if (\Illuminate\Support\Str::contains(strtolower($order->order_status), ['menunggu', 'pending'])) {
            $order->update([
                'order_status' => 'batal',
                'payment_status' => 'Cancelled'
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak dapat dibatalkan.'
        ], 400);
    }

    public function returnOrder($id)
    {
        $user = auth()->user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        // Only allow returns for completed/delivered orders
        $isDelivered = \Illuminate\Support\Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']);
        if (!$isDelivered) {
            return redirect()->route('order.detail', $order->id)->with('error', 'Pengembalian hanya tersedia untuk pesanan yang sudah dikirim.');
        }

        return view('Pelanggan.return_order', compact('user', 'order'));
    }

    public function submitReturn(Request $request, $id)
    {
        $user = auth()->user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        // Only allow returns for completed/delivered orders
        $isDelivered = \Illuminate\Support\Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']);
        if (!$isDelivered) {
            return response()->json([
                'success' => false,
                'message' => 'Pengembalian hanya tersedia untuk pesanan yang sudah dikirim.'
            ], 400);
        }

        try {
            \App\Models\Retur::create([
                'customer_name' => $user->nama,
                'order_id' => $order->order_number,
                'reason' => $request->reason . ($request->description ? ' - ' . $request->description : ''),
                'return_date' => now()->toDateString(),
                'product_name' => 'Order ' . $order->order_number,
                'status' => 'New'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian berhasil diajukan! Tim kami akan segera memprosesnya.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengajukan pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }

    public function returnDetail($id)
    {
        $user = auth()->user();
        $retur = \App\Models\Retur::where('customer_name', $user->nama)->findOrFail($id);

        // Find the associated order by order_number
        $order = \App\Models\Pesanan::where('order_number', $retur->order_id)
            ->where('email_pembeli', $user->email)
            ->first();

        // Generate order items (same logic as orderDetail)
        $items = [];
        if ($order) {
            $products = \App\Models\Produk::inRandomOrder()->limit(min(3, \App\Models\Produk::count() ?: 1))->get();
            $total = floatval($order->total);
            $count = count($products);

            if ($count > 0) {
                $remaining = $total;
                for ($i = 0; $i < $count; $i++) {
                    $product = $products[$i];
                    $qty = ($i === 0 && $count > 1) ? 2 : 1;

                    if ($i === $count - 1) {
                        $itemPrice = $remaining / $qty;
                    } else {
                        $itemPrice = round(($total / ($count + 1)) / 1000) * 1000;
                        if ($itemPrice * $qty >= $remaining) {
                            $itemPrice = round(($remaining / 2) / 1000) * 1000;
                        }
                    }

                    $items[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price' => $itemPrice,
                        'subtotal' => $itemPrice * $qty
                    ];

                    $remaining -= $itemPrice * $qty;
                }
            }
        }

        return view('Pelanggan.return_detail', compact('user', 'retur', 'order', 'items'));
    }

    public function settings()
    {
        $user = auth()->user();
        return view('Pelanggan.settings', compact('user'));
    }

    public function updateAccount(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'no_hp' => 'required|numeric|unique:users,no_hp,' . $user->user_id . ',user_id',
        ]);

        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui!',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|different:current_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.'
            ], 400);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui!'
        ]);
    }
}

<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Customer;
use App\Models\User;
use App\Models\Produk;
use App\Models\Retur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CustomerOrderController extends Controller
{
    /**
     * Cancel expired orders older than 24 hours.
     */
    private function cancelExpiredOrders()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $expiredOrders = Pesanan::where('email_pembeli', $user->email)
                ->whereIn('order_status', ['menunggu pembayaran', 'menunggu', 'pending'])
                ->where('payment_status', '!=', 'Paid')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
                
            foreach ($expiredOrders as $order) {
                $order->update([
                    'order_status' => 'batal',
                    'payment_status' => 'Cancelled'
                ]);
            }
        }
    }

    public function dashboard()
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();

        // Fallback to customer table location if user's alamat is null
        if (!$user->alamat) {
            $customer = Customer::where(function($q) use ($user) {
                if ($user->user_id) {
                    $q->where('user_id', $user->user_id);
                }
                if ($user->nama) {
                    $q->orWhere('name', $user->nama);
                }
                if ($user->no_hp) {
                    $rawPhone = ltrim((string)$user->no_hp, '0');
                    $q->orWhere('contact', 'like', '%' . $rawPhone . '%');
                }
            })->first();

            if ($customer && $customer->location) {
                User::where('user_id', $user->user_id)->update(['alamat' => $customer->location]);
                $user->alamat = $customer->location;
                if (!$customer->user_id) {
                    $customer->user_id = $user->user_id;
                    $customer->save();
                }
            }
        }

        $recentOrders = Pesanan::where('email_pembeli', $user->email)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('Pelanggan.dashboard', compact('user', 'recentOrders'));
    }

    public function orderHistory(Request $request)
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();
        $statusFilter = $request->query('status');

        $query = Pesanan::where('email_pembeli', $user->email);

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
            $returs = Retur::where('customer_name', $user->nama)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('Pelanggan.order_history', compact('user', 'orders', 'statusFilter', 'returs'));
    }

    /**
     * @param  int|string  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function orderDetail($id)
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        $items = [];
        $orderItems = $order->items;

        if (is_array($orderItems) && count($orderItems) > 0) {
            foreach ($orderItems as $item) {
                $product = Produk::find($item['id']);
                if (!$product) {
                    $product = new Produk();
                    $product->id_produk = $item['id'];
                    $product->nama_produk = $item['name'] ?? 'Product Removed';
                    $product->harga = $item['price'] ?? 0;
                    $product->foto_1 = null;
                }
                
                $qty = isset($item['qty']) ? intval($item['qty']) : 1;
                $price = isset($item['price']) ? floatval($item['price']) : floatval($product->harga);
                
                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                    'img' => $item['img'] ?? null
                ];
            }
        } else {
            $products = Produk::inRandomOrder()->limit(min(3, Produk::count() ?: 1))->get();
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

        $estimatedArrival = $order->created_at->addDays(1)->format('l') . ', 2 PM - 4 PM';
        $expiryTimestamp = $order->created_at->addHours(24)->timestamp;

        return view('Pelanggan.order_detail', compact('user', 'order', 'items', 'estimatedArrival', 'expiryTimestamp'));
    }

    /**
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder($id)
    {
        $user = Auth::user();
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if (Str::contains(strtolower($order->order_status), ['menunggu', 'pending'])) {
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
}

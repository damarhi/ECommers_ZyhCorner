<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Customer;
use App\Models\User;
use App\Models\Produk;
use App\Models\Retur;
use App\Models\UserAddress;
use App\Models\StoreSetting;
use Carbon\Carbon;
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
                Produk::restoreStockForOrder($order);
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

        // Auto-seed user_addresses if user has alamat but no addresses
        if ($user->alamat && !UserAddress::where('user_id', $user->user_id)->exists()) {
            UserAddress::create([
                'user_id' => $user->user_id,
                'label' => 'Alamat Utama',
                'recipient_name' => $user->nama,
                'recipient_phone' => $user->no_hp ?? '',
                'street_address' => $user->alamat,
                'is_default' => true,
            ]);
        }

        $addresses = $user->addresses()->get();

        $recentOrders = Pesanan::where('email_pembeli', $user->email)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('Pelanggan.dashboard', compact('user', 'recentOrders', 'addresses'));
    }

    public function orderHistory(Request $request)
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();
        $statusFilter = $request->query('status');

        $query = Pesanan::where('email_pembeli', $user->email);

        if ($statusFilter) {
            if ($statusFilter === 'processing') {
                $query->whereIn('order_status', ['menunggu pembayaran', 'menunggu konfirmasi', 'dikemas', 'processing', 'proses', 'ready']);
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

        $storePhone = StoreSetting::getWaNumber();
        $storeName = StoreSetting::get('store_name', 'Zyh Corner');

        return view('Pelanggan.order_history', compact('user', 'orders', 'statusFilter', 'returs', 'storePhone', 'storeName'));
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
                $product = Produk::find($item['id'] ?? null);
                if (!$product) {
                    $product = new Produk();
                    $product->id_produk = $item['id'] ?? null;
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
                    'img' => $item['img'] ?? null,
                    'custom_text' => $item['custom_text'] ?? null,
                ];
            }
        }

        $itemsSubtotal = array_sum(array_column($items, 'subtotal'));
        $shippingCost = max(0, floatval($order->total) - $itemsSubtotal);

        Carbon::setLocale('id');
        $estimatedArrival = $order->created_at->addDays(1)->translatedFormat('l') . ', 14:00 - 16:00 WIB';
        $expiryTimestamp = $order->created_at->addHours(24)->timestamp;

        $storePhone = StoreSetting::getWaNumber();
        $storeName = StoreSetting::get('store_name', 'Zyh Corner');

        return view('Pelanggan.order_detail', compact(
            'user',
            'order',
            'items',
            'estimatedArrival',
            'expiryTimestamp',
            'shippingCost',
            'itemsSubtotal',
            'storePhone',
            'storeName'
        ));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelOrder(Request $request, $id)
    {
        $user = Auth::user();
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);
        if ($order->can_be_canceled) {
            $cancelReason = $request->input('cancel_reason');

            $updateData = [
                'order_status' => 'batal',
                'payment_status' => 'Cancelled'
            ];
            if (!empty($cancelReason)) {
                $updateData['cancel_reason'] = $cancelReason;
            }

            $order->update($updateData);
            Produk::restoreStockForOrder($order);
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

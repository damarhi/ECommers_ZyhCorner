<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Customer;
use App\Models\Pesanan;
use App\Services\NotificationService;
use App\Mail\PesananBaruKasirMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CartCheckoutController extends Controller
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

        // Validate stock for all items before placing order
        $total = 0;
        foreach ($items as $item) {
            $product = Produk::find($item['id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan!'
                ], 404);
            }
            if ($product->stok <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk "' . $product->nama_produk . '" sudah habis!'
                ], 400);
            }
            if ($product->stok < $item['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok produk "' . $product->nama_produk . '" tidak mencukupi (tersisa ' . $product->stok . ').'
                ], 400);
            }
            $total += $product->harga * $item['qty'];
        }

        $address = $request->input('address');
        $notes = $request->input('notes');
        $paymentMethod = $request->input('payment_method', 'COD');
        $shippingMethod = $request->input('shipping_method', 'Maxim');

        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $customerName = $user->nama;
            $email = $user->email;

            // Save address to user if not set
            if ($address) {
                if (!$user->alamat) {
                    $user->update(['alamat' => $address]);
                }
                // Sync to customer profile
                $customer = Customer::where('user_id', $user->user_id)->first();
                if ($customer) {
                    if (!$customer->location) {
                        $customer->update(['location' => $address]);
                    }
                } else {
                    Customer::create([
                        'user_id' => $user->user_id,
                        'name' => $user->nama,
                        'email' => $user->email,
                        'contact' => $user->no_hp,
                        'location' => $address
                    ]);
                }
            }
        } else {
            $customerName = 'Guest Customer';
            $email = null;
        }

        do {
            $orderNumber = '#' . strtoupper(Str::random(6));
        } while (Pesanan::where('order_number', $orderNumber)->exists());

        try {
            $orderStatus = (strtoupper($paymentMethod) === 'COD') ? 'dikemas' : 'menunggu pembayaran';

            $pesanan = Pesanan::create([
                'order_number' => $orderNumber,
                'jenis_penjualan' => 'Online',
                'payment_method' => $paymentMethod,
                'shipping_method' => $shippingMethod,
                'customer_name' => $customerName,
                'email_pembeli' => $email,
                'alamat_pengiriman' => $address,
                'catatan_pesanan' => $notes,
                'payment_status' => 'Pending',
                'order_status' => $orderStatus,
                'total' => $total,
                'items' => $items,
            ]);

            // Generate Midtrans Snap token if not COD
            $snapToken = null;
            if (strtoupper($paymentMethod) !== 'COD') {
                try {
                    $snapToken = app(\App\Http\Controllers\Pelanggan\PembayaranController::class)->getMidtransSnapToken($pesanan);
                } catch (\Exception $me) {
                    // Log the error and proceed without Snap Token. The user can retry payment from order history.
                    Log::error('Midtrans Snap Token generation failed: ' . $me->getMessage());
                }
            }

            // Reduce stock and send email notification to cashier if COD or Paid
            if (strtoupper($paymentMethod) === 'COD' || $pesanan->payment_status === 'Paid') {
                Produk::reduceStockForOrder($pesanan);
                NotificationService::sendToKasir(new PesananBaruKasirMail($pesanan));
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat dengan nomor order ' . $orderNumber,
                'order' => $pesanan,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orderSuccess(Request $request)
    {
        $this->cancelExpiredOrders();
        $orderId = $request->query('order_id');
        $order = null;
        if ($orderId) {
            $order = Pesanan::where('id', $orderId)
                ->where('email_pembeli', Auth::user()->email)
                ->first();
        }

        if (!$order) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        $deadline = $order->created_at->addHours(24);
        $remainingSeconds = max(0, now()->diffInSeconds($deadline, false));

        return view('Pelanggan.order_success', compact('order', 'deadline', 'remainingSeconds'));
    }
}

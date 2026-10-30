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
                Produk::restoreStockForOrder($order);
            }
        }
    }

    public function userChart()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir'])) {
            abort(403, 'Akses Ditolak: Halaman keranjang belanja hanya diperuntukkan bagi pelanggan toko.');
        }

        return view('Pelanggan.userChart');
    }

    public function checkout()
    {
        $customTextProductIds = Produk::where('bisa_custom_teks', true)->pluck('id_produk')->map(fn($id) => (string)$id)->toArray();
        $user = Auth::user();
        $userAddresses = $user ? $user->addresses()->get() : collect();
        return view('Pelanggan.checkout', compact('customTextProductIds', 'userAddresses'));
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
        $recipientName = $request->input('recipient_name');
        $recipientPhone = $request->input('recipient_phone');
        $postalCode = $request->input('postal_code');
        $shippingCost = (int) $request->input('shipping_cost', 0);

        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $customerName = !empty($recipientName) ? $recipientName : $user->nama;
            $email = $user->email;

            // Save address to user if not set
            if ($address) {
                if (!$user->alamat) {
                    $user->update(['alamat' => $address]);
                }
                if ($recipientPhone && !$user->no_hp) {
                    $user->update(['no_hp' => $recipientPhone]);
                }
                // Sync to customer profile
                $customer = Customer::where('user_id', $user->user_id)->first();
                if ($customer) {
                    if (!$customer->location) {
                        $customer->update(['location' => $address]);
                    }
                    if ($recipientPhone && !$customer->contact) {
                        $customer->update(['contact' => $recipientPhone]);
                    }
                } else {
                    Customer::create([
                        'user_id' => $user->user_id,
                        'name' => $customerName,
                        'email' => $user->email,
                        'contact' => $recipientPhone ?: $user->no_hp,
                        'location' => $address
                    ]);
                }
                // Auto-save to address book if requested or if user has no addresses saved yet
                if ($address && ($request->boolean('save_address') || !\App\Models\UserAddress::where('user_id', $user->user_id)->exists())) {
                    $hasAddress = \App\Models\UserAddress::where('user_id', $user->user_id)->exists();
                    \App\Models\UserAddress::create([
                        'user_id' => $user->user_id,
                        'label' => $request->input('address_label') ?: 'Alamat Utama',
                        'recipient_name' => $customerName,
                        'recipient_phone' => $recipientPhone ?: ($user->no_hp ?? ''),
                        'destination_area_id' => $request->input('destination_area_id'),
                        'destination_area_name' => $request->input('destination_area_name'),
                        'postal_code' => $postalCode,
                        'street_address' => $address,
                        'notes' => $notes ?: null,
                        'latitude' => $request->input('latitude'),
                        'longitude' => $request->input('longitude'),
                        'is_default' => !$hasAddress,
                    ]);
                }
            }
        } else {
            $customerName = !empty($recipientName) ? $recipientName : 'Guest Customer';
            $email = null;
        }

        $fullAddress = $address;
        if ($postalCode && !str_contains($fullAddress, $postalCode)) {
            $fullAddress .= ' (Kode Pos: ' . $postalCode . ')';
        }

        $finalNotes = $notes;
        if ($recipientPhone) {
            $contactNote = '[Penerima: ' . $customerName . ' (' . $recipientPhone . ')]';
            $finalNotes = $finalNotes ? ($finalNotes . ' ' . $contactNote) : $contactNote;
        }
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $mapLink = "https://maps.google.com/?q={$lat},{$lng}";
            $gpsNote = "[Titik GPS: {$lat}, {$lng} | {$mapLink}]";
            $finalNotes = $finalNotes ? ($finalNotes . ' ' . $gpsNote) : $gpsNote;
        }
        do {
            $orderNumber = '#' . strtoupper(Str::random(6));
        } while (Pesanan::where('order_number', $orderNumber)->exists());

        try {
            $orderStatus = (strtoupper($paymentMethod) === 'COD') ? 'menunggu konfirmasi' : 'menunggu pembayaran';

            $pesanan = Pesanan::create([
                'order_number' => $orderNumber,
                'jenis_penjualan' => 'Online',
                'payment_method' => $paymentMethod,
                'shipping_method' => $shippingMethod,
                'customer_name' => $customerName,
                'email_pembeli' => $email,
                'alamat_pengiriman' => $fullAddress,
                'catatan_pesanan' => $finalNotes,
                'payment_status' => 'Pending',
                'order_status' => $orderStatus,
                'total' => $total + $shippingCost,
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

            // Potong stok langsung untuk seluruh pesanan (baik COD maupun Online/Transfer)
            Produk::reduceStockForOrder($pesanan);

            // Kirim notifikasi email ke kasir untuk pesanan COD atau Paid
            if (strtoupper($paymentMethod) === 'COD' || $pesanan->payment_status === 'Paid') {
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
            $userEmail = Auth::user()->email;
            $cleanId = preg_replace('/[^a-zA-Z0-9\-\_\~\.]/', '', $orderId);

            $order = Pesanan::where('email_pembeli', $userEmail)
                ->where(function ($q) use ($orderId, $cleanId) {
                    $q->where('id', $orderId)
                      ->orWhere('order_number', $orderId)
                      ->orWhere('order_number', '#' . $orderId)
                      ->orWhere('order_number', $cleanId)
                      ->orWhere('order_number', '#' . $cleanId);
                })
                ->first();
        }

        if (!$order) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        if ($order->payment_status !== 'Paid' && strtoupper((string)$order->payment_method) !== 'COD' && !$order->is_bank_transfer) {
            app(\App\Http\Controllers\Pelanggan\PembayaranController::class)->verifyAndSyncOrderPayment($order);
            $order->refresh();
        }

        $deadline = $order->created_at->addHours(24);
        $remainingSeconds = max(0, now()->diffInSeconds($deadline, false));

        $storeBank = [
            'name' => \App\Models\StoreSetting::get('bank_name', 'Bank Central Asia (BCA)'),
            'account_number' => \App\Models\StoreSetting::get('bank_account_number', '8295-0192-38'),
            'account_holder' => \App\Models\StoreSetting::get('bank_account_holder', 'ZYH CORNER PASTRY'),
        ];

        return view('Pelanggan.order_success', compact('order', 'deadline', 'remainingSeconds', 'storeBank'));
    }

    /**
     * Search areas using BiteshipService (API with local fallback).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function biteshipAreas(Request $request)
    {
        $query = (string) $request->query('search', $request->query('q', ''));
        $areas = \App\Services\BiteshipService::searchAreas($query);
        return response()->json($areas);
    }

    /**
     * Get calculated courier rates for checkout.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function biteshipRates(Request $request)
    {
        $destinationAreaId = (string) $request->input('destination_area_id', '');
        $destinationAreaName = (string) $request->input('destination_area_name', '');
        $items = $request->input('items', []);

        // Resolve area ID if empty but name provided
        if (empty($destinationAreaId) && !empty($destinationAreaName)) {
            $matchedAreas = \App\Services\BiteshipService::searchAreas($destinationAreaName);
            if (!empty($matchedAreas)) {
                $destinationAreaId = $matchedAreas[0]['id'] ?? '';
            }
        }

        if (empty($destinationAreaId)) {
            $destinationAreaId = config('services.biteship.origin_area_id', 'ID_PLW_01');
        }

        $ratesResult = \App\Services\BiteshipService::getRates($destinationAreaId, $items);
        $couriers = \App\Services\BiteshipService::formatCheckoutRates($ratesResult, $destinationAreaId, $destinationAreaName);

        return response()->json([
            'success' => true,
            'source' => $ratesResult['source'] ?? 'standard_tiers',
            'destination_area_id' => $destinationAreaId,
            'destination_area_name' => $destinationAreaName,
            'couriers' => $couriers,
            'rates' => $ratesResult['rates'] ?? []
        ]);
    }
}

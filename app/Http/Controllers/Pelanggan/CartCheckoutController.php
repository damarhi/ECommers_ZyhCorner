<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Customer;
use App\Models\Pesanan;
use App\Services\BiteshipService;
use App\Services\NotificationService;
use App\Mail\PesananBaruKasirMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
            $expiredOrders = Pesanan::ownedBy($user)
                ->whereIn('order_status', ['menunggu pembayaran', 'menunggu', 'pending'])
                ->where('payment_status', '!=', 'Paid')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
                
            foreach ($expiredOrders as $order) {
                if ($order->is_payment_proof_rejected && $order->updated_at && $order->updated_at->gt(now()->subHours(24))) {
                    continue;
                }
                $order->update([
                    'order_status' => 'batal',
                    'payment_status' => 'Cancelled'
                ]);
                Produk::restoreStockForOrder($order);
                Customer::syncMetricsForOrder($order);
            }
        }
    }

    public function userChart()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir'], true)) {
            abort(403, 'Akses Ditolak: Halaman keranjang belanja hanya diperuntukkan bagi pelanggan toko.');
        }

        return view('Pelanggan.userChart');
    }

    public function checkout()
    {
        if (!Auth::check() || in_array(Auth::user()->role, ['admin', 'kasir'], true)) {
            abort(403, 'Akses Ditolak: Halaman checkout hanya diperuntukkan bagi pelanggan toko.');
        }

        $customTextProductIds = Produk::where('bisa_custom_teks', true)->pluck('id_produk')->map(fn($id) => (string)$id)->toArray();
        $user = Auth::user();
        $userAddresses = $user ? $user->addresses()->get() : collect();
        return view('Pelanggan.checkout', compact('customTextProductIds', 'userAddresses'));
    }

    public function processCheckout(Request $request)
    {
        if (!Auth::check() || in_array(Auth::user()->role, ['admin', 'kasir'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Fitur checkout online hanya diperuntukkan bagi pelanggan toko.'
            ], 403);
        }

        $rawItems = $request->input('items');
        if (empty($rawItems) || !is_array($rawItems)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja kosong!'
            ], 400);
        }

        $metaValidator = Validator::make($request->all(), [
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'shipping_method' => ['nullable', 'string', 'max:100'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'destination_area_id' => ['nullable', 'string', 'max:100'],
            'destination_area_name' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'address_label' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ], [
            'shipping_cost.min' => 'Ongkos kirim tidak boleh bernilai negatif.',
        ]);

        if ($metaValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $metaValidator->errors()->first(),
            ], 422);
        }

        $itemsList = array_values($rawItems);
        $aggregatedQty = [];
        $parsedItems = [];

        foreach ($itemsList as $item) {
            if (!is_array($item)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format item pesanan tidak valid.'
                ], 422);
            }

            $rawId = $item['id'] ?? $item['id_produk'] ?? null;
            $rawQty = $item['qty'] ?? $item['quantity'] ?? null;

            if ($rawId === null || $rawId === '' || !is_numeric($rawQty) || (int) $rawQty != $rawQty || (int) $rawQty <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah produk pada keranjang tidak valid (minimal 1).'
                ], 422);
            }

            $pid = (int) $rawId;
            $qty = (int) $rawQty;
            $aggregatedQty[$pid] = ($aggregatedQty[$pid] ?? 0) + $qty;
            $parsedItems[] = [
                'id' => $pid,
                'qty' => $qty,
                'custom_text' => $item['custom_text'] ?? null,
            ];
        }

        $productsById = Produk::with('diskonAktif')
            ->whereIn('id_produk', array_keys($aggregatedQty))
            ->get()
            ->keyBy('id_produk');

        // Validate existence, product type, expiry, and aggregated stock; compute authoritative price with active discount
        $total = 0;
        $serverItems = [];
        $today = \Carbon\Carbon::today();

        foreach ($parsedItems as $parsed) {
            $product = $productsById->get($parsed['id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan!'
                ], 404);
            }

            if (strtolower((string) $product->jenis_produk) === 'kustom') {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk "' . $product->nama_produk . '" adalah produk kustom yang dipesan melalui konsultasi langsung.'
                ], 400);
            }

            if (!empty($product->tanggal_expired) && \Carbon\Carbon::parse($product->tanggal_expired)->lte($today)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk "' . $product->nama_produk . '" sudah melewati masa layak jual!'
                ], 400);
            }

            if ($product->stok <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk "' . $product->nama_produk . '" sudah habis!'
                ], 400);
            }

            $totalRequestedForProduct = $aggregatedQty[$product->id_produk];
            if ($product->stok < $totalRequestedForProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok produk "' . $product->nama_produk . '" tidak mencukupi (tersisa ' . $product->stok . ').'
                ], 400);
            }

            $unitPrice = (float) $product->harga_diskon;
            $originalPrice = (float) $product->harga;
            $total += $unitPrice * $parsed['qty'];

            $normalizedItem = [
                'id' => $product->id_produk,
                'name' => $product->nama_produk,
                'nama_produk' => $product->nama_produk,
                'price' => $unitPrice,
                'original_price' => $originalPrice,
                'qty' => $parsed['qty'],
                'image' => $product->foto_1 ? asset('storage/' . $product->foto_1) : asset('image/logo_circle.png'),
            ];

            if ($product->bisa_custom_teks) {
                $normalizedItem['bisa_custom_teks'] = true;
                $cleanCustomText = $parsed['custom_text'] !== null
                    ? trim(strip_tags((string) $parsed['custom_text']))
                    : '';
                if ($cleanCustomText !== '') {
                    $normalizedItem['custom_text'] = mb_substr($cleanCustomText, 0, 200);
                }
            }

            $serverItems[] = $normalizedItem;
        }

        $address = $request->input('address');
        $notes = $request->input('notes');
        $paymentMethod = trim((string) $request->input('payment_method', 'COD'));
        $shippingMethod = trim((string) $request->input('shipping_method', 'Maxim'));
        $recipientName = $request->input('recipient_name');
        $recipientPhone = $request->input('recipient_phone');
        $postalCode = $request->input('postal_code');
        $shippingCost = max(0, (int) round((float) $request->input('shipping_cost', 0)));

        // Enforce authoritative server-side shipping rates for Pick Up and BiteShip-managed couriers
        $shippingLower = strtolower($shippingMethod);
        if (str_contains($shippingLower, 'pick up') || str_contains($shippingLower, 'pickup') || str_contains($shippingLower, 'ambil')) {
            $shippingCost = 0;
        } elseif (in_array($shippingMethod, ['Gojek', 'Grab', 'Maxim'], true)) {
            $destinationAreaId = trim((string) $request->input('destination_area_id', ''));
            $destinationAreaName = trim((string) $request->input('destination_area_name', ''));

            if ($destinationAreaId === '' && $destinationAreaName !== '') {
                $matchedAreas = BiteshipService::searchAreas($destinationAreaName);
                if (!empty($matchedAreas)) {
                    $destinationAreaId = (string) ($matchedAreas[0]['id'] ?? '');
                }
            }

            if ($destinationAreaId === '') {
                $destinationAreaId = (string) config('services.biteship.origin_area_id', 'ID_PLW_01');
            }

            $ratesPayloadItems = array_map(fn (array $si) => [
                'name' => $si['name'],
                'value' => (int) $si['price'],
                'weight' => 500,
                'quantity' => (int) $si['qty'],
            ], $serverItems);

            $ratesResult = BiteshipService::getRates($destinationAreaId, $ratesPayloadItems);
            $authoritativeCouriers = BiteshipService::formatCheckoutRates($ratesResult, $destinationAreaId, $destinationAreaName);
            if (isset($authoritativeCouriers[$shippingMethod])) {
                $shippingCost = (int) $authoritativeCouriers[$shippingMethod];
            }
        }

        $authenticatedUserId = null;
        if (Auth::check()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();
            $authenticatedUserId = $user->user_id;
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

            $pesanan = DB::transaction(function () use (
                $authenticatedUserId,
                $orderNumber,
                $paymentMethod,
                $shippingMethod,
                $customerName,
                $email,
                $fullAddress,
                $finalNotes,
                $orderStatus,
                $total,
                $shippingCost,
                $serverItems
            ) {
                $created = Pesanan::create([
                    'user_id' => $authenticatedUserId,
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
                    'shipping_cost' => $shippingCost,
                    'items' => $serverItems,
                ]);

                // Potong stok secara atomik dengan lockForUpdate() di dalam transaksi yang sama
                Produk::reduceStockForOrder($created);

                return $created;
            });

            Customer::syncMetricsForOrder($pesanan);

            // Generate Midtrans Snap token only for online gateway methods (not COD and not manual Bank Transfer)
            $snapToken = null;
            if (strtoupper($paymentMethod) !== 'COD' && !$pesanan->is_bank_transfer) {
                try {
                    $snapToken = app(\App\Http\Controllers\Pelanggan\PembayaranController::class)->getMidtransSnapToken($pesanan);
                } catch (\Exception $me) {
                    Log::error('Midtrans Snap Token generation failed: ' . $me->getMessage());
                }
            }

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
        } catch (\DomainException $de) {
            return response()->json([
                'success' => false,
                'message' => $de->getMessage(),
            ], 400);
        } catch (\Exception $e) {
            Log::error('Checkout process failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pesanan. Silakan coba beberapa saat lagi.'
            ], 500);
        }
    }

    public function orderSuccess(Request $request)
    {
        if (!Auth::check() || in_array(Auth::user()->role, ['admin', 'kasir'], true)) {
            abort(403, 'Akses Ditolak: Halaman pesanan pelanggan hanya diperuntukkan bagi pelanggan toko.');
        }

        $this->cancelExpiredOrders();
        $orderId = $request->query('order_id');
        $order = null;
        if ($orderId) {
            $user = Auth::user();
            $cleanId = preg_replace('/[^a-zA-Z0-9\-\_\~\.]/', '', $orderId);

            $order = Pesanan::ownedBy($user)
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

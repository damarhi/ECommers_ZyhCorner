<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Services\NotificationService;
use App\Mail\PesananBaruKasirMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PembayaranController extends Controller
{
    /**
     * Generate or retrieve Midtrans Snap Token for a given order.
     *
     * @param  \App\Models\Pesanan  $pesanan
     * @return string
     * @throws \Exception
     */
    public function getMidtransSnapToken($pesanan)
    {
        if ($pesanan->snap_token) {
            return $pesanan->snap_token;
        }

        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey) {
            throw new \Exception('Midtrans Server Key belum dikonfigurasi di file .env (MIDTRANS_SERVER_KEY). Silakan masukkan Server Key dari dashboard Midtrans.');
        }

        // Support local sandbox demo/mock mode if specified
        if (in_array(strtolower($serverKey), ['mock', 'sandbox-demo', 'demo'])) {
            $mockToken = 'MOCK-SNAP-' . strtoupper(bin2hex(random_bytes(8)));
            $cleanMockOrderId = preg_replace('/[^a-zA-Z0-9\-\_\~\.]/', '', $pesanan->order_number);
            $pesanan->update([
                'snap_token' => $mockToken,
                'midtrans_order_id' => $cleanMockOrderId,
            ]);
            return $mockToken;
        }

        $isProduction = filter_var(config('services.midtrans.is_production', false), FILTER_VALIDATE_BOOLEAN);
        if (str_starts_with($serverKey, 'SB-')) {
            $isProduction = false;
        }

        $url = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $cleanOrderId = preg_replace('/[^a-zA-Z0-9\-\_\~\.]/', '', $pesanan->order_number);

        $payload = [
            'transaction_details' => [
                'order_id' => $cleanOrderId,
                'gross_amount' => (int) round((float) $pesanan->total),
            ],
            'customer_details' => [
                'first_name' => $pesanan->customer_name ?? 'Pelanggan',
                'email' => $pesanan->email_pembeli ?? 'customer@zyhcorner.com',
                'phone' => Auth::check() ? (string) Auth::user()->no_hp : '08123456789',
            ],
            'callbacks' => [
                'finish' => route('order.success'),
            ],
        ];
        $response = Http::timeout(10)->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withBasicAuth($serverKey, '')
          ->post($url, $payload);

        if ($response->failed()) {
            $errBody = $response->json();
            $errMsg = isset($errBody['error_messages'][0]) ? $errBody['error_messages'][0] : $response->body();

            if (str_contains(strtolower($errMsg), 'order_id') || str_contains(strtolower($errMsg), 'already been taken')) {
                $retryOrderId = $cleanOrderId . '-' . time();
                $payload['transaction_details']['order_id'] = $retryOrderId;
                $retryRes = Http::timeout(10)->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->withBasicAuth($serverKey, '')
                  ->post($url, $payload);

                if ($retryRes->successful()) {
                    $snapToken = $retryRes->json('token');
                    if ($snapToken) {
                        $pesanan->update([
                            'snap_token' => $snapToken,
                            'midtrans_order_id' => $retryOrderId,
                        ]);
                        return $snapToken;
                    }
                }
            }

            throw new \Exception('Midtrans API Error: ' . $errMsg);
        }

        $result = $response->json();
        $snapToken = $result['token'] ?? null;

        if ($snapToken) {
            $pesanan->update([
                'snap_token' => $snapToken,
                'midtrans_order_id' => $cleanOrderId,
            ]);
        }

        return $snapToken;
    }

    /**
     * Handle Midtrans Webhook Callback.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function midtransCallback(Request $request)
    {
        $serverKey = (string) config('services.midtrans.server_key', '');
        $incomingSignature = (string) $request->input('signature_key', '');

        if ($serverKey === '' || in_array(strtolower($serverKey), ['mock', 'sandbox-demo', 'demo'], true) || $incomingSignature === '') {
            Log::warning('Midtrans Webhook rejected: missing or mock server key / empty signature.');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $hashed = hash('sha512', (string) $request->order_id . (string) $request->status_code . (string) $request->gross_amount . $serverKey);

        if (!hash_equals($hashed, $incomingSignature)) {
            Log::warning('Midtrans Webhook: Invalid signature key.');
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $rawOrderId = (string) $request->order_id;
        $baseOrderId = str_contains($rawOrderId, '-') ? explode('-', $rawOrderId)[0] : $rawOrderId;

        $order = Pesanan::where('midtrans_order_id', $rawOrderId)->first()
            ?? Pesanan::where('order_number', $rawOrderId)
                ->orWhere('order_number', '#' . $rawOrderId)
                ->orWhere('order_number', $baseOrderId)
                ->orWhere('order_number', '#' . $baseOrderId)
                ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Verify gross_amount matches order total (within Rp 1 rounding tolerance)
        if ($request->filled('gross_amount') && abs((float) $request->gross_amount - (float) $order->total) >= 1.0) {
            Log::warning("Midtrans Webhook: Gross amount mismatch for {$order->order_number} ({$request->gross_amount} vs {$order->total})");
            return response()->json(['message' => 'Gross amount mismatch'], 400);
        }

        // Protect Paid orders from late/duplicate pending/expire/cancel callbacks
        if ($order->payment_status === 'Paid') {
            return response()->json(['message' => 'Callback processed successfully']);
        }

        $transactionStatus = (string) $request->transaction_status;
        $type = (string) $request->payment_type;
        $fraudStatus = strtolower((string) ($request->fraud_status ?? 'accept'));

        Log::info("Midtrans Webhook: Processing order {$order->order_number} with status {$transactionStatus}");

        if ($transactionStatus === 'capture') {
            if ($type === 'credit_card') {
                if ($fraudStatus === 'challenge') {
                    $order->update([
                        'payment_status' => 'Pending',
                        'order_status' => 'menunggu pembayaran'
                    ]);
                } elseif ($fraudStatus === 'accept') {
                    $order->update([
                        'payment_status' => 'Paid',
                        'order_status' => 'menunggu konfirmasi'
                    ]);
                    Customer::syncMetricsForOrder($order->fresh());
                    NotificationService::sendToKasir(new PesananBaruKasirMail($order));
                }
            }
        } elseif ($transactionStatus === 'settlement') {
            if ($fraudStatus === 'accept' || $fraudStatus === '') {
                $order->update([
                    'payment_status' => 'Paid',
                    'order_status' => 'menunggu konfirmasi'
                ]);
                Customer::syncMetricsForOrder($order->fresh());
                NotificationService::sendToKasir(new PesananBaruKasirMail($order));
            }
        } elseif ($transactionStatus === 'pending') {
            $order->update([
                'payment_status' => 'Pending',
                'order_status' => 'menunggu pembayaran'
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
            $order->update([
                'payment_status' => 'Failed',
                'order_status' => 'batal'
            ]);
            Produk::restoreStockForOrder($order);
            Customer::syncMetricsForOrder($order->fresh());
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }

    /**
     * Get Midtrans Snap Token for an unpaid order from customer order history.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSnapTokenForOrder($id)
    {
        $user = Auth::user();
        $order = Pesanan::ownedBy($user)->findOrFail($id);

        if ($order->payment_status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibayar.'
            ], 400);
        }

        if ($order->is_canceled) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini telah dibatalkan.'
            ], 400);
        }

        try {
            $snapToken = $this->getMidtransSnapToken($order);
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            Log::error('getSnapTokenForOrder failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan token pembayaran. Silakan coba lagi beberapa saat.'
            ], 500);
        }
    }

    /**
     * Verify order payment status directly with Midtrans API and update database.
     *
     * @param  \App\Models\Pesanan  $order
     * @return bool Returns true if order is Paid
     */
    public function verifyAndSyncOrderPayment($order)
    {
        if ($order->payment_status === 'Paid') {
            return true;
        }

        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey || in_array(strtolower($serverKey), ['mock', 'sandbox-demo', 'demo'], true)) {
            return false;
        }

        $isProduction = filter_var(config('services.midtrans.is_production', false), FILTER_VALIDATE_BOOLEAN);
        if (str_starts_with($serverKey, 'SB-')) {
            $isProduction = false;
        }

        $lookupOrderId = $order->midtrans_order_id ?: preg_replace('/[^a-zA-Z0-9\-\_\~\.]/', '', $order->order_number);
        $url = $isProduction
            ? "https://api.midtrans.com/v2/{$lookupOrderId}/status"
            : "https://api.sandbox.midtrans.com/v2/{$lookupOrderId}/status";

        try {
            $response = Http::timeout(10)->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->withBasicAuth($serverKey, '')
              ->get($url);

            if ($response->successful()) {
                $result = $response->json();
                $transactionStatus = $result['transaction_status'] ?? '';
                $fraudStatus = strtolower((string) ($result['fraud_status'] ?? 'accept'));

                if (isset($result['gross_amount']) && abs((float) $result['gross_amount'] - (float) $order->total) >= 1.0) {
                    Log::warning("verifyAndSyncOrderPayment gross_amount mismatch for {$order->order_number}");
                    return false;
                }

                if (in_array($transactionStatus, ['capture', 'settlement'], true) && ($fraudStatus === 'accept' || $fraudStatus === '')) {
                    $order->update([
                        'payment_status' => 'Paid',
                        'order_status' => 'menunggu konfirmasi'
                    ]);
                    Customer::syncMetricsForOrder($order->fresh());
                    NotificationService::sendToKasir(new PesananBaruKasirMail($order));
                    return true;
                } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'], true)) {
                    $order->update([
                        'payment_status' => 'Failed',
                        'order_status' => 'batal'
                    ]);
                    Produk::restoreStockForOrder($order);
                    Customer::syncMetricsForOrder($order->fresh());
                    return false;
                }
            }
        } catch (\Exception $e) {
            Log::error('Verify and Sync Order Payment failed: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Check Midtrans Payment Status for an order manually.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPaymentStatus($id)
    {
        $user = Auth::user();
        $order = Pesanan::ownedBy($user)->findOrFail($id);

        $isPaid = $this->verifyAndSyncOrderPayment($order);

        if ($isPaid || $order->fresh()->payment_status === 'Paid') {
            return response()->json([
                'success' => true,
                'status' => 'Paid',
                'message' => 'Pembayaran Anda telah lunas!'
            ]);
        }

        if ($order->fresh()->payment_status === 'Failed' || $order->fresh()->order_status === 'batal') {
            return response()->json([
                'success' => false,
                'status' => 'Failed',
                'message' => 'Pembayaran gagal atau kedaluwarsa.'
            ]);
        }

        return response()->json([
            'success' => false,
            'status' => $order->payment_status,
            'message' => 'Pembayaran masih pending atau belum dilakukan.'
        ]);
    }
}

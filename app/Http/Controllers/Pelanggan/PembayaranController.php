<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
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
            throw new \Exception('Midtrans Server Key is not configured in .env / config.');
        }

        $isProduction = config('services.midtrans.is_production', false);
        if (str_starts_with($serverKey, 'SB-')) {
            $isProduction = false;
        } elseif (str_starts_with($serverKey, 'Mid-server-')) {
            $isProduction = true;
        }

        $url = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $pesanan->order_number,
                'gross_amount' => (int) $pesanan->total,
            ],
            'customer_details' => [
                'first_name' => $pesanan->customer_name ?? 'Pelanggan',
                'email' => $pesanan->email_pembeli ?? 'customer@zyhcorner.com',
                'phone' => Auth::check() ? (string) Auth::user()->no_hp : '08123456789',
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withBasicAuth($serverKey, '')
          ->post($url, $payload);

        if ($response->failed()) {
            $errBody = $response->json();
            $errMsg = isset($errBody['error_messages'][0]) ? $errBody['error_messages'][0] : $response->body();

            if (str_contains(strtolower($errMsg), 'order_id') || str_contains(strtolower($errMsg), 'already been taken')) {
                $payload['transaction_details']['order_id'] = $pesanan->order_number . '-' . time();
                $retryRes = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->withBasicAuth($serverKey, '')
                  ->post($url, $payload);

                if ($retryRes->successful()) {
                    $snapToken = $retryRes->json('token');
                    if ($snapToken) {
                        $pesanan->update(['snap_token' => $snapToken]);
                        return $snapToken;
                    }
                }
            }

            throw new \Exception('Midtrans API Error: ' . $errMsg);
        }

        $result = $response->json();
        $snapToken = $result['token'] ?? null;

        if ($snapToken) {
            $pesanan->update(['snap_token' => $snapToken]);
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
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            Log::warning('Midtrans Webhook: Invalid signature key: ' . $request->signature_key);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Pesanan::where('order_number', $request->order_id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $type = $request->payment_type;

        Log::info("Midtrans Webhook: Processing order {$order->order_number} with status {$transactionStatus}");

        if ($transactionStatus == 'capture') {
            if ($type == 'credit_card') {
                if ($request->fraud_status == 'challenge') {
                    $order->update([
                        'payment_status' => 'Pending',
                        'order_status' => 'menunggu pembayaran'
                    ]);
                } else {
                    $order->update([
                        'payment_status' => 'Paid',
                        'order_status' => 'dikemas'
                    ]);
                    Produk::reduceStockForOrder($order);
                    NotificationService::sendToKasir(new PesananBaruKasirMail($order));
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->update([
                'payment_status' => 'Paid',
                'order_status' => 'dikemas'
            ]);
            Produk::reduceStockForOrder($order);
            NotificationService::sendToKasir(new PesananBaruKasirMail($order));
        } elseif (in_array($transactionStatus, ['pending'])) {
            $order->update([
                'payment_status' => 'Pending',
                'order_status' => 'menunggu pembayaran'
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->update([
                'payment_status' => 'Failed',
                'order_status' => 'batal'
            ]);
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
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if ($order->payment_status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibayar.'
            ], 400);
        }

        try {
            $snapToken = $this->getMidtransSnapToken($order);
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan token pembayaran: ' . $e->getMessage()
            ], 500);
        }
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
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if ($order->payment_status === 'Paid') {
            return response()->json([
                'success' => true,
                'status' => 'Paid',
                'message' => 'Pembayaran Anda telah lunas!'
            ]);
        }

        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production', false);
        $url = $isProduction
            ? "https://api.midtrans.com/v2/{$order->order_number}/status"
            : "https://api.sandbox.midtrans.com/v2/{$order->order_number}/status";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->withBasicAuth($serverKey, '')
              ->get($url);

            if ($response->successful()) {
                $result = $response->json();
                $transactionStatus = $result['transaction_status'] ?? '';
                
                if (in_array($transactionStatus, ['capture', 'settlement'])) {
                    $order->update([
                        'payment_status' => 'Paid',
                        'order_status' => 'dikemas'
                    ]);
                    Produk::reduceStockForOrder($order);
                    NotificationService::sendToKasir(new PesananBaruKasirMail($order));
                    return response()->json([
                        'success' => true,
                        'status' => 'Paid',
                        'message' => 'Pembayaran berhasil dikonfirmasi!'
                    ]);
                } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $order->update([
                        'payment_status' => 'Failed',
                        'order_status' => 'batal'
                    ]);
                    return response()->json([
                        'success' => false,
                        'status' => 'Failed',
                        'message' => 'Pembayaran gagal atau kedaluwarsa.'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Check Payment Status failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'status' => $order->payment_status,
            'message' => 'Pembayaran masih pending atau belum dilakukan.'
        ]);
    }
}

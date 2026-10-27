<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Kategori;

class PembayaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_midtrans_callback_with_invalid_signature_returns_403(): void
    {
        $response = $this->postJson('/midtrans-callback', [
            'order_id' => '#ORDER123',
            'status_code' => '200',
            'gross_amount' => '50000.00',
            'signature_key' => 'invalid-signature-key-hash',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Invalid signature']);
    }

    public function test_midtrans_callback_with_valid_signature_updates_order(): void
    {
        $serverKey = 'SB-Mid-server-mock-key';
        config(['services.midtrans.server_key' => $serverKey]);

        $orderNumber = '#ORDER999';
        $statusCode = '200';
        $grossAmount = '50000.00';

        $order = Pesanan::create([
            'order_number' => $orderNumber,
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Budi',
            'email_pembeli' => 'budi@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 50000,
        ]);

        $validSignature = hash("sha512", $orderNumber . $statusCode . $grossAmount . $serverKey);

        $response = $this->postJson('/midtrans-callback', [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $validSignature,
            'transaction_status' => 'settlement',
            'payment_type' => 'bank_transfer',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Callback processed successfully']);

        $this->assertEquals('Paid', $order->fresh()->payment_status);
        $this->assertEquals('menunggu konfirmasi', $order->fresh()->order_status);
    }
}

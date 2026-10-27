<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Kategori $kategori;
    private Produk $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::create([
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'no_hp' => '081234567890',
            'role' => 'pelanggan',
            'password' => bcrypt('secret123'),
        ]);

        $this->kategori = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        $this->product = Produk::create([
            'id_kategori' => $this->kategori->id_kategori,
            'nama_produk' => 'Brownies Fudgy',
            'harga' => 65000,
            'status_produk' => '5',
            'jenis_produk' => 'showcase',
        ]);
    }

    public function test_checkout_fails_if_quantity_exceeds_available_stock(): void
    {
        $response = $this->actingAs($this->customer)->postJson('/checkout', [
            'items' => [
                ['id' => $this->product->id_produk, 'qty' => 6] // stock is 5
            ],
            'payment_method' => 'COD',
            'address' => 'Jl. Diponegoro No 10',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);

        $this->product->refresh();
        $this->assertEquals(5, $this->product->stok);
    }

    public function test_online_transfer_checkout_reduces_stock_immediately(): void
    {
        $response = $this->actingAs($this->customer)->postJson('/checkout', [
            'items' => [
                ['id' => $this->product->id_produk, 'qty' => 2]
            ],
            'payment_method' => 'transfer',
            'address' => 'Jl. Diponegoro No 10',
        ]);

        $response->assertStatus(200);
        $this->product->refresh();
        $this->assertEquals(3, $this->product->stok);

        $order = Pesanan::first();
        $this->assertNotNull($order);
        $this->assertEquals('Pending', $order->payment_status);
    }

    public function test_cancelling_order_restores_product_stock(): void
    {
        // 1. Checkout 2 units
        $this->actingAs($this->customer)->postJson('/checkout', [
            'items' => [
                ['id' => $this->product->id_produk, 'qty' => 2]
            ],
            'payment_method' => 'transfer',
            'address' => 'Jl. Diponegoro No 10',
        ]);

        $this->product->refresh();
        $this->assertEquals(3, $this->product->stok);

        $order = Pesanan::first();
        $this->assertNotNull($order);

        // 2. Cancel order
        $cancelResponse = $this->actingAs($this->customer)->postJson("/order-history/{$order->id}/cancel");
        $cancelResponse->assertStatus(200);
        // 3. Verify stock restored
        $this->product->refresh();
        $this->assertEquals(5, $this->product->stok);

        $order->refresh();
        $this->assertEquals('batal', $order->order_status);
        $this->assertEquals('Cancelled', $order->payment_status);
    }

    public function test_expired_orders_restore_stock_automatically(): void
    {
        // 1. Create order 2 days ago
        $order = Pesanan::create([
            'order_number' => 'ORD-TEST-999',
            'customer_name' => $this->customer->nama,
            'email_pembeli' => $this->customer->email,
            'items' => [
                ['id' => $this->product->id_produk, 'qty' => 3, 'price' => 65000, 'name' => 'Brownies Fudgy']
            ],
            'total' => 195000,
            'payment_method' => 'transfer',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'alamat_pengiriman' => 'Jl. Diponegoro No 10',
            'created_at' => now()->subHours(26),
            'updated_at' => now()->subHours(26),
        ]);
        $order->created_at = now()->subHours(26);
        $order->save();
        // Manually deduce stock as if checkout happened
        $this->product->reduceStock(3);
        $this->product->refresh();
        $this->assertEquals(2, $this->product->stok);

        // 2. Trigger order history which checks cancelExpiredOrders()
        $this->actingAs($this->customer)->get('/order-history');

        // 3. Verify stock restored
        $this->product->refresh();
        $this->assertEquals(5, $this->product->stok);

        $order->refresh();
        $this->assertEquals('batal', $order->order_status);
        $this->assertEquals('Cancelled', $order->payment_status);
    }

    public function test_midtrans_payment_success_does_not_double_deduct_stock(): void
    {
        config(['services.midtrans.server_key' => 'dummy_server_key']);
        config(['midtrans.server_key' => 'dummy_server_key']);
        // 1. Create order and deduct stock at checkout
        $order = Pesanan::create([
            'order_number' => 'ORD-SETTLE-123',
            'customer_name' => $this->customer->nama,
            'email_pembeli' => $this->customer->email,
            'items' => [
                ['id' => $this->product->id_produk, 'qty' => 2, 'price' => 65000, 'name' => 'Brownies Fudgy']
            ],
            'total' => 130000,
            'payment_method' => 'transfer',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'alamat_pengiriman' => 'Jl. Diponegoro No 10',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->product->reduceStock(2);
        $this->product->refresh();
        $this->assertEquals(3, $this->product->stok);

        // 2. Midtrans settlement callback
        $orderId = 'ORD-SETTLE-123';
        $statusCode = '200';
        $grossAmount = '130000.00';
        $serverKey = config('services.midtrans.server_key');
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $callbackResponse = $this->postJson('/midtrans-callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);
        $callbackResponse->assertStatus(200);

        // 3. Stock should remain 3 (NOT 1!)
        $this->product->refresh();
        $this->assertEquals(3, $this->product->stok);

        $order->refresh();
        $this->assertEquals('Paid', $order->payment_status);
    }

    public function test_midtrans_cancellation_or_expiry_restores_stock(): void
    {
        config(['services.midtrans.server_key' => 'dummy_server_key']);

        // 1. Create order and deduct stock at checkout
        $order = Pesanan::create([
            'order_number' => 'ORD-CANCEL-123',
            'customer_name' => $this->customer->nama,
            'email_pembeli' => $this->customer->email,
            'items' => [
                ['id' => $this->product->id_produk, 'qty' => 2, 'price' => 65000, 'name' => 'Brownies Fudgy']
            ],
            'total' => 130000,
            'payment_method' => 'transfer',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'alamat_pengiriman' => 'Jl. Diponegoro No 10',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->product->reduceStock(2);
        $this->product->refresh();
        $this->assertEquals(3, $this->product->stok);

        // 2. Midtrans expire callback
        $orderId = 'ORD-CANCEL-123';
        $statusCode = '202';
        $grossAmount = '130000.00';
        $serverKey = config('services.midtrans.server_key');
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $callbackResponse = $this->postJson('/midtrans-callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'expire',
        ]);

        $callbackResponse->assertStatus(200);

        // 3. Stock should be restored to 5
        $this->product->refresh();
        $this->assertEquals(5, $this->product->stok);

        $order->refresh();
        $this->assertEquals('Failed', $order->payment_status);
        $this->assertEquals('batal', $order->order_status);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Kategori;

class OrderConfirmationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomerUser(): User
    {
        return User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'customer@example.com',
            'nama' => 'Pelanggan Setia',
        ]);
    }

    private function createKasirUser(): User
    {
        return User::factory()->create([
            'role' => 'kasir',
            'email' => 'kasir@example.com',
            'nama' => 'Kasir Toko',
        ]);
    }

    private function createProductWithStock(int $initialStock = 10): Produk
    {
        $kategori = Kategori::create([
            'nama_kategori' => 'Kue Basah',
            'gambar_kategori' => 'dummy.png',
        ]);

        return Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Bolu Gulung Mawar',
            'jenis_produk' => 'showcase',
            'harga' => 35000,
            'status_produk' => (string) $initialStock,
        ]);
    }

    public function test_cod_order_starts_with_menunggu_konfirmasi_status(): void
    {
        $user = $this->createCustomerUser();
        $product = $this->createProductWithStock(10);

        $response = $this->actingAs($user)->postJson('/checkout', [
            'payment_method' => 'COD',
            'shipping_method' => 'Kurir Toko',
            'shipping_cost' => 10000,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '08123456789',
            'address' => 'Jl. Mawar No. 12, Coblong, Bandung, Jawa Barat 40132',
            'postal_code' => '40132',
            'items' => [
                [
                    'id' => $product->id_produk,
                    'qty' => 2,
                    'price' => 35000,
                    'name' => $product->nama_produk,
                    'tipe' => 'showcase'
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $order = Pesanan::first();
        $this->assertNotNull($order);
        $this->assertEquals('menunggu konfirmasi', $order->order_status);
        $this->assertTrue($order->is_awaiting_confirmation);
        $this->assertTrue($order->can_be_canceled);
        $this->assertEquals('Menunggu Konfirmasi', $order->status_label);
        $this->assertEquals(8, (int) $product->fresh()->status_produk);
    }

    public function test_paid_midtrans_order_transitions_to_menunggu_konfirmasi(): void
    {
        $serverKey = 'SB-Mid-server-mock-key';
        config(['services.midtrans.server_key' => $serverKey]);

        $orderNumber = '#ORD-MID-123';
        $order = Pesanan::create([
            'order_number' => $orderNumber,
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Siti',
            'email_pembeli' => 'siti@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 70000,
        ]);

        $statusCode = '200';
        $grossAmount = '70000.00';
        $signature = hash("sha512", $orderNumber . $statusCode . $grossAmount . $serverKey);

        $response = $this->postJson('/midtrans-callback', [
            'order_id' => $orderNumber,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $this->assertEquals('Paid', $order->payment_status);
        $this->assertEquals('menunggu konfirmasi', $order->order_status);
        $this->assertTrue($order->is_awaiting_confirmation);
        $this->assertTrue($order->can_be_canceled);
    }

    public function test_customer_can_cancel_order_during_menunggu_konfirmasi_and_stock_is_restored(): void
    {
        $user = $this->createCustomerUser();
        $product = $this->createProductWithStock(5);

        $order = Pesanan::create([
            'order_number' => '#ORD-CANCEL-1',
            'jenis_penjualan' => 'Online',
            'customer_name' => $user->nama,
            'email_pembeli' => $user->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 70000,
            'items' => [
                [
                    'id' => $product->id_produk,
                    'qty' => 2,
                    'price' => 35000,
                    'name' => $product->nama_produk,
                    'tipe' => 'showcase'
                ]
            ]
        ]);

        // Stock was reduced at checkout from 5 to 3
        $product->update(['status_produk' => '3']);

        $response = $this->actingAs($user)->postJson("/order-history/{$order->id}/cancel", [
            'cancel_reason' => 'Salah memilih produk atau varian kue'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan!'
        ]);

        $order->refresh();
        $this->assertEquals('batal', $order->order_status);
        $this->assertEquals('Cancelled', $order->payment_status);
        $this->assertEquals('Salah memilih produk atau varian kue', $order->cancel_reason);

        // Verify inventory restoration
        $this->assertEquals(5, (int) $product->fresh()->status_produk);
    }

    public function test_customer_cannot_cancel_order_after_dikemas_status(): void
    {
        $user = $this->createCustomerUser();

        $order = Pesanan::create([
            'order_number' => '#ORD-DIKEMAS-1',
            'jenis_penjualan' => 'Online',
            'customer_name' => $user->nama,
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'total' => 50000,
        ]);

        $response = $this->actingAs($user)->postJson("/order-history/{$order->id}/cancel", [
            'cancel_reason' => 'Ingin ganti alamat'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Pesanan tidak dapat dibatalkan.'
        ]);

        $order->refresh();
        $this->assertEquals('dikemas', $order->order_status);
    }

    public function test_kasir_can_quick_confirm_order_with_single_click(): void
    {
        $kasir = $this->createKasirUser();

        $order = Pesanan::create([
            'order_number' => '#ORD-CONFIRM-9',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Andi',
            'email_pembeli' => 'andi@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'menunggu konfirmasi',
            'total' => 120000,
        ]);

        $response = $this->actingAs($kasir)->postJson("/admin/orders/{$order->id}/quick-confirm");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'order_status' => 'dikemas',
            'status_label' => 'Sedang Dikemas'
        ]);

        $order->refresh();
        $this->assertEquals('dikemas', $order->order_status);
    }

    public function test_quick_confirm_rejects_non_unconfirmed_order(): void
    {
        $kasir = $this->createKasirUser();

        $order = Pesanan::create([
            'order_number' => '#ORD-SHIPPED-1',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Bambang',
            'email_pembeli' => 'bambang@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'dikirim',
            'total' => 80000,
        ]);

        $response = $this->actingAs($kasir)->postJson("/admin/orders/{$order->id}/quick-confirm");

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Pesanan ini sudah dikonfirmasi atau statusnya bukan Menunggu Konfirmasi.'
        ]);

        $this->assertEquals('dikirim', $order->fresh()->order_status);
    }

    public function test_orders_table_displays_quick_confirm_button_for_unconfirmed_order(): void
    {
        $kasir = $this->createKasirUser();

        $order = Pesanan::create([
            'order_number' => '#ORD-TABLE-1',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Rina',
            'email_pembeli' => 'rina@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 65000,
        ]);

        $response = $this->actingAs($kasir)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('badge-pulse-amber');
        $response->assertSee('Menunggu Konfirmasi');
        $response->assertSee('btn-quick-confirm');
        $response->assertSee('Konfirmasi &amp; Kemas', false);
    }

    public function test_orders_view_renders_synchronized_modal_ids_for_edit_and_detail(): void
    {
        $kasir = $this->createKasirUser();

        $order = Pesanan::create([
            'order_number' => '#ORD-SYNC-99',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Dewi',
            'email_pembeli' => 'dewi@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'menunggu konfirmasi',
            'total' => 85000,
        ]);

        $response = $this->actingAs($kasir)->get('/admin/orders');

        $response->assertStatus(200);
        $response->assertSee('id="edit-order-status-select-' . $order->id . '"', false);
        $response->assertSee('id="modal-detail-status-badge-' . $order->id . '"', false);
        $response->assertSee('id="order-status-badge-' . $order->id . '"', false);
    }
}

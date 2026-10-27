<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_order_history(): void
    {
        $response = $this->get(route('order.history'));
        $response->assertRedirect('/login');
    }

    public function test_customer_can_view_order_history_with_breadcrumb_and_navigation(): void
    {
        $user = User::factory()->create([
            'email' => 'riwayat.pelanggan@example.com',
            'role' => 'pelanggan',
            'nama' => 'Siti Khadijah',
        ]);

        $response = $this->actingAs($user)->get(route('order.history'));

        $response->assertStatus(200);
        $response->assertSee(route('dashboard'));
        $response->assertSee('Order History');
        $response->assertSee('All');
        $response->assertSee('Processing');
        $response->assertSee('On The Way');
        $response->assertSee('Completed');
        $response->assertSee('Canceled');
    }

    public function test_order_history_renders_biteship_modal_and_tracking_payload(): void
    {
        $user = User::factory()->create([
            'email' => 'track.test@example.com',
            'role' => 'pelanggan',
            'nama' => 'Ahmad Dani',
        ]);

        $product = Produk::create([
            'nama_produk' => 'Bolu Kemojo Pandan',
            'harga' => 65000,
            'status_produk' => '15',
            'deskripsi' => 'Bolu khas Pekanbaru yang lembut dan harum',
        ]);

        $order = Pesanan::create([
            'order_number' => '#ORD-TRACK-99',
            'customer_name' => 'Ahmad Dani',
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'sedang dikirim',
            'total' => 65000,
            'payment_method' => 'Midtrans (GoPay)',
            'shipping_method' => 'JNE Express',
            'alamat_pengiriman' => 'Jl. Sudirman No. 123, Pekanbaru',
            'items' => [
                [
                    'id' => $product->id_produk,
                    'name' => $product->nama_produk,
                    'price' => 65000,
                    'qty' => 1,
                    'img' => 'image/logo_circle.png',
                ]
            ],
        ]);

        $response = $this->actingAs($user)->get(route('order.history'));

        $response->assertStatus(200);
        // Modal presence
        $response->assertSee('id="biteshipTrackingModal"', false);
        $response->assertSee('Powered by Biteship');
        $response->assertSee('Status Perjalanan Paket');
        $response->assertSee('Rincian Pesanan');
        $response->assertSee('Hubungi CS Toko');
        $response->assertSee('openOrderHistoryTracking', false);

        // Order card data
        $response->assertSee('#ORD-TRACK-99');
        $response->assertSee('Tracking Order');
        $response->assertSee('Dalam Perjalanan');
        $response->assertSee($order->resi_code);
        $response->assertSee('JNE Express');
    }

    public function test_order_history_displays_accurate_status_labels_without_hardcoded_processing(): void
    {
        $user = User::factory()->create([
            'email' => 'status.accurate@example.com',
            'role' => 'pelanggan',
            'nama' => 'Rina Wijaya',
        ]);

        $orderPending = Pesanan::create([
            'order_number' => '#ORD-PENDING-01',
            'customer_name' => 'Rina Wijaya',
            'email_pembeli' => $user->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 50000,
            'payment_method' => 'Midtrans (BCA)',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => 'Jl. Harapan Raya No. 45',
            'items' => [['id' => 1, 'name' => 'Kue Lapis', 'price' => 50000, 'qty' => 1]],
        ]);

        $this->assertEquals('Menunggu Pembayaran', $orderPending->status_label);
        $this->assertEquals('pending', $orderPending->status_class);

        $orderCompleted = Pesanan::create([
            'order_number' => '#ORD-DONE-02',
            'customer_name' => 'Rina Wijaya',
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 50000,
            'payment_method' => 'COD',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => 'Jl. Harapan Raya No. 45',
            'items' => [['id' => 1, 'name' => 'Kue Lapis', 'price' => 50000, 'qty' => 1]],
        ]);

        $this->assertEquals('Selesai', $orderCompleted->status_label);
        $this->assertEquals('completed', $orderCompleted->status_class);

        $response = $this->actingAs($user)->get(route('order.history'));
        $response->assertStatus(200);
        $response->assertSee('Pay Now');
        $response->assertSee('Cancel Order');
        $response->assertSee('Selesai');
    }

    public function test_customer_can_cancel_pending_order_and_record_reason(): void
    {
        $user = User::factory()->create([
            'email' => 'cancel.cust@example.com',
            'role' => 'pelanggan',
            'nama' => 'Fajar Nugroho',
        ]);

        $product = Produk::create([
            'nama_produk' => 'Brownies Kukus Cokelat',
            'harga' => 75000,
            'status_produk' => '10',
            'deskripsi' => 'Brownies lezat',
        ]);

        $order = Pesanan::create([
            'order_number' => '#ORD-CANCEL-ME',
            'customer_name' => 'Fajar Nugroho',
            'email_pembeli' => $user->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 75000,
            'payment_method' => 'Midtrans (Mandiri)',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => 'Jl. Paus No. 10, Pekanbaru',
            'items' => [
                [
                    'id' => $product->id_produk,
                    'name' => $product->nama_produk,
                    'price' => 75000,
                    'qty' => 2,
                ]
            ],
        ]);

        $response = $this->actingAs($user)->postJson(route('order.cancel', $order->id), [
            'cancel_reason' => 'Salah memilih produk atau varian kue',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $order->refresh();
        $this->assertTrue($order->is_canceled);
        $this->assertEquals('Salah memilih produk atau varian kue', $order->cancel_reason);
    }

    public function test_order_detail_supports_deep_link_tracking(): void
    {
        $user = User::factory()->create([
            'email' => 'deeplink.test@example.com',
            'role' => 'pelanggan',
            'nama' => 'Citra Kirana',
        ]);

        $order = Pesanan::create([
            'order_number' => '#ORD-DEEPLINK',
            'customer_name' => 'Citra Kirana',
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'sedang dikirim',
            'total' => 100000,
            'payment_method' => 'Midtrans (BCA)',
            'shipping_method' => 'SiCepat REG',
            'alamat_pengiriman' => 'Jl. Tuanku Tambusai No. 88, Pekanbaru',
            'items' => [['id' => 99, 'name' => 'Cheesecake', 'price' => 100000, 'qty' => 1]],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id) . '?track=1');

        $response->assertStatus(200);
        $response->assertSee('urlParams.get(\'track\') === \'1\'', false);
        $response->assertSee('openBiteshipTrackingModal()', false);
    }
}

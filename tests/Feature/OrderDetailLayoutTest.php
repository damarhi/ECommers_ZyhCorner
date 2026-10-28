<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDetailLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_view_order_detail_with_refined_layout(): void
    {
        $user = User::factory()->create([
            'email' => 'customer.test@example.com',
            'role' => 'pelanggan',
            'nama' => 'Budi Pengujian',
            'no_hp' => '081298765432',
        ]);

        $product = Produk::create([
            'nama_produk' => 'Kue Cokelat Signature',
            'harga' => 150000,
            'status_produk' => '10',
            'deskripsi' => 'Kue cokelat lezat',
            'bisa_custom_teks' => true,
        ]);

        $order = Pesanan::create([
            'order_number' => '#TEST001',
            'customer_name' => 'Budi Pengujian',
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'total' => 150000,
            'payment_method' => 'Midtrans (BCA)',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => 'Jl. Pengujian No. 99, Kota Pekanbaru',
            'catatan_pesanan' => 'Tolong kirim sore hari',
            'items' => [
                [
                    'id' => $product->id_produk,
                    'name' => $product->nama_produk,
                    'price' => 150000,
                    'qty' => 1,
                    'custom_text' => 'Selamat Ulang Tahun!',
                ]
            ],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Pesanan #TEST001');
        $response->assertSee('Sedang Diproses');
        $response->assertSee('Status Progres Pengiriman');
        $response->assertSee('Alamat Pengiriman');
        $response->assertSee('Info Pengiriman');
        $response->assertSee('Rincian Pembayaran');
        $response->assertSee('Kue Cokelat Signature');
        $response->assertSee('Tulisan Kue: "Selamat Ulang Tahun!"', false);
        $response->assertSee('MIDTRANS (BCA)');
        $response->assertSee('Lunas');
        $response->assertSee('Rp. 150.000');
        // Stepper 5-step milestone assertions
        $response->assertSee('Pesanan Dibuat');
        $response->assertSee('Verifikasi Pembayaran');
        $response->assertSee('Dikonfirmasi & Dikemas');
        $response->assertSee('Sedang Dikirim');
        $response->assertSee('Pesanan Selesai');
        
        // Tracking & actions assertions
        $response->assertSee('Lacak Pesanan');
        $response->assertSee('Batalkan Pesanan');
        $response->assertSee('BS-TEST001');
        $response->assertSee('Biteship');
        $response->assertDontSee('btn-track-card');
        $response->assertSee('biteshipTrackingModal');
        $response->assertSee('header-actions-group');
    }
    public function test_canceled_order_renders_canceled_status_and_banner(): void
    {
        $user = User::factory()->create([
            'email' => 'customer.test2@example.com',
            'role' => 'pelanggan',
            'nama' => 'Siti Batal',
            'no_hp' => '081211112222',
        ]);

        $order = Pesanan::create([
            'order_number' => '#BATAL01',
            'customer_name' => 'Siti Batal',
            'email_pembeli' => $user->email,
            'payment_status' => 'Cancelled',
            'order_status' => 'batal',
            'total' => 85000,
            'payment_method' => 'COD',
            'alamat_pengiriman' => 'Jl. Melati No. 5',
            'items' => [],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Pesanan #BATAL01');
        $response->assertSee('Pesanan Dibatalkan');
        $response->assertSee('Batal');
        $response->assertSee('payment-status-badge badge-canceled', false);
        $response->assertDontSee('<button class="btn-cancel-top', false);
    }

    public function test_customer_cannot_view_other_customers_order(): void
    {
        $userA = User::factory()->create([
            'email' => 'usera@example.com',
            'role' => 'pelanggan',
        ]);

        $userB = User::factory()->create([
            'email' => 'userb@example.com',
            'role' => 'pelanggan',
        ]);

        $orderB = Pesanan::create([
            'order_number' => '#ORDERB',
            'customer_name' => 'User B',
            'email_pembeli' => $userB->email,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 120000,
            'items' => [],
        ]);

        $response = $this->actingAs($userA)->get(route('order.detail', $orderB->id));

        $response->assertStatus(404);
    }

    public function test_order_detail_renders_biteship_modal_and_copyable_resi(): void
    {
        $user = User::factory()->create([
            'email' => 'customer.track@example.com',
            'role' => 'pelanggan',
            'nama' => 'Budi Tracker',
            'no_hp' => '081299998888',
        ]);

        $order = Pesanan::create([
            'order_number' => '#ORD-TRACK-99',
            'customer_name' => 'Budi Tracker',
            'email_pembeli' => $user->email,
            'order_status' => 'dikirim',
            'payment_status' => 'Paid',
            'payment_method' => 'Midtrans',
            'shipping_method' => 'Kurir Toko / Maxim Instant',
            'alamat_pengiriman' => 'Jl. Tuanku Tambusai No. 12, Pekanbaru',
            'total' => 115000,
            'items' => [],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id));

        $response->assertStatus(200);
        $response->assertSee('BS-ORD-TRACK-99');
        $response->assertSee('openBiteshipTrackingModal()', false);
        $response->assertSee('closeBiteshipTrackingModal()', false);
        $response->assertSee('copyTrackingResi', false);
        $response->assertSee('btn-track-order-top');
        $response->assertSee('btn-cancel-top');
    }

    public function test_order_detail_renders_dynamic_store_whatsapp_and_shipping_cost(): void
    {
        \App\Models\StoreSetting::set('store_phone', '081298765432');
        \App\Models\StoreSetting::set('store_name', 'Zyh Corner Bakery');

        $user = User::factory()->create([
            'email' => 'customer.shipping@example.com',
            'role' => 'pelanggan',
            'nama' => 'Rina Shipping',
            'no_hp' => '081377778888',
        ]);

        $product = Produk::create([
            'nama_produk' => 'Strawberry Tartlet',
            'deskripsi' => 'Delicious tartlet',
            'harga' => 50000,
            'status_produk' => '10',
        ]);

        $order = Pesanan::create([
            'order_number' => '#SHIP01',
            'customer_name' => 'Rina Shipping',
            'email_pembeli' => $user->email,
            'order_status' => 'proses',
            'payment_status' => 'Paid',
            'payment_method' => 'Midtrans',
            'shipping_method' => 'JNE Reguler',
            'alamat_pengiriman' => 'Jl. Anggrek No. 10',
            'total' => 65000, // 50000 item + 15000 shipping
            'items' => [
                [
                    'id' => $product->id_produk,
                    'name' => $product->nama_produk,
                    'qty' => 1,
                    'price' => 50000,
                ]
            ],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Rp. 15.000'); // Dynamic shipping cost
        $response->assertSee('6281298765432'); // Dynamic Store WhatsApp
        $response->assertSee('WIB'); // Indonesian localized arrival
    }

    public function test_customer_cancelling_order_persists_cancel_reason(): void
    {
        $user = User::factory()->create([
            'email' => 'customer.cancelreason@example.com',
            'role' => 'pelanggan',
            'nama' => 'Joko Batal',
        ]);

        $order = Pesanan::create([
            'order_number' => '#CANCEL-REASON-1',
            'customer_name' => 'Joko Batal',
            'email_pembeli' => $user->email,
            'order_status' => 'menunggu pembayaran',
            'payment_status' => 'Pending',
            'payment_method' => 'Midtrans',
            'alamat_pengiriman' => 'Jl. Mawar No. 4',
            'total' => 100000,
            'items' => [],
        ]);

        $response = $this->actingAs($user)->postJson(route('order.cancel', $order->id), [
            'cancel_reason' => 'Salah memilih produk atau varian kue'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Pesanan berhasil dibatalkan!'
        ]);

        $this->assertDatabaseHas('pesanans', [
            'id' => $order->id,
            'order_status' => 'batal',
            'payment_status' => 'Cancelled',
            'cancel_reason' => 'Salah memilih produk atau varian kue'
        ]);
    }

    public function test_order_detail_renders_solusi_b_summary_cards_and_address_modal(): void
    {
        $user = User::factory()->create([
            'email' => 'solusib.test@example.com',
            'role' => 'pelanggan',
            'nama' => 'Rina Hartati',
            'no_hp' => '081234567890',
            'alamat' => 'Komplek Melati Indah Blok C No. 12',
        ]);

        $order = Pesanan::create([
            'order_number' => '#SOLUSIB-01',
            'customer_name' => 'Rina Hartati',
            'email_pembeli' => $user->email,
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'total' => 200000,
            'payment_method' => 'Midtrans (BCA)',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => 'Komplek Melati Indah Blok C No. 12, RT 02 / RW 05, Kelurahan Delima, Kec. Tampan, Kota Pekanbaru, Riau 28291',
            'catatan_pesanan' => 'Tolong lilin angka 25 dimasukkan ya.',
            'items' => [],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id));

        $response->assertStatus(200);
        // Card 1 concise view and modal trigger
        $response->assertSee('Alamat Pengiriman');
        $response->assertSee('Rina Hartati');
        $response->assertSee('btn-detail-address', false);
        $response->assertSee('Lihat Detail Alamat');
        $response->assertSee('openAddressDetailModal()', false);

        // Address detail modal
        $response->assertSee('orderAddressDetailModal', false);
        $response->assertSee('Informasi Alamat Pengiriman');
        $response->assertSee('closeAddressDetailModal()', false);
        $response->assertSee('displayBillingPhoneModal', false);
        $response->assertSee('displayBillingEmailModal', false);
        $response->assertSee('Komplek Melati Indah Blok C No. 12, RT 02 / RW 05, Kelurahan Delima, Kec. Tampan, Kota Pekanbaru, Riau 28291');

        // Order notes banner is removed from the main page and kept in the address detail modal
        $response->assertDontSee('order-notes-strip');
        $response->assertDontSee('Catatan Pesanan:');
        $response->assertSee('Tolong lilin angka 25 dimasukkan ya.');

        // Card 2 does not have [Lacak Live Biteship] button
        $response->assertDontSee('Lacak Live Biteship');
    }

    public function test_order_detail_renders_consistent_pay_now_and_track_order_buttons(): void
    {
        $user = User::factory()->create([
            'email' => 'unpaid.customer@example.com',
            'role' => 'pelanggan',
            'nama' => 'Dewi Pembeli',
        ]);

        $order = Pesanan::create([
            'order_number' => '#UNPAID-01',
            'customer_name' => 'Dewi Pembeli',
            'email_pembeli' => $user->email,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'total' => 125000,
            'payment_method' => 'Midtrans (BCA)',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => 'Jl. Sudirman No. 45, Pekanbaru',
            'items' => [],
        ]);

        $response = $this->actingAs($user)->get(route('order.detail', $order->id));

        $response->assertStatus(200);
        // Both buttons use consistent pill design matching btn-track-order-top
        $response->assertSee('Bayar Sekarang');
        $response->assertSee('Lacak Pesanan');
        $response->assertSee('btn-track-order-top btn-pay-now-top btn-pay-now-solid', false);
        $response->assertSee('btn-track-order-top', false);
    }
}

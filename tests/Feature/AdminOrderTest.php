<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Pesanan;

class AdminOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_all_sales_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Pesanan::create([
            'order_number' => '#ALLSALE01',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Walk-in Customer Test',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Cash',
            'total' => 50000,
            'items' => [
                ['id' => 1, 'name' => 'Kue Cokelat', 'price' => 50000, 'qty' => 1]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('all-sales.index'));
        $response->assertStatus(200);
        $response->assertSee('Aksi');
        $response->assertSee('Detail');
        $response->assertSee('orderDetailModal' . $order->id);
        $response->assertSee(route('orders.invoice', $order->id));
    }

    public function test_admin_can_access_invoice_page(): void
    {
        // 1. Create an admin user
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        // 2. Create a test order
        $order = Pesanan::create([
            'order_number' => '#TEST12',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'John Client',
            'email_pembeli' => 'john.client@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 150000,
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Produk A',
                    'price' => 50000,
                    'qty' => 3,
                ]
            ]
        ]);

        $this->actingAs($admin);

        // 3. Request print invoice endpoint
        $response = $this->get(route('orders.invoice', $order->id));

        // 4. Assertions
        $response->assertStatus(200);
        $response->assertSee('#TEST12');
        $response->assertSee('John Client');
        $response->assertSee('Produk A');
        $response->assertSee('Rp. 150.000');
        $response->assertSee('window.print()');
    }

    public function test_guest_cannot_access_invoice_page(): void
    {
        $order = Pesanan::create([
            'order_number' => '#TEST12',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'John Client',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 150000,
        ]);

        $response = $this->get(route('orders.invoice', $order->id));
        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_sales_report_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Pesanan::create([
            'order_number' => '#REPORT01',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Budi Kasir',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 75000,
        ]);

        $response = $this->actingAs($admin)->get(route('all-sales.report'));
        $response->assertStatus(200);
        $response->assertSee('Laporan Penjualan');
        $response->assertSee('#REPORT01');
        $response->assertSee('Budi Kasir');
        $response->assertSee('75.000');
    }

    public function test_sales_report_page_with_multiple_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Pesanan::create([
            'order_number' => '#ORD-ONLINE-01',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Siti Aisyah',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 120000,
            'created_at' => now(),
        ]);

        Pesanan::create([
            'order_number' => '#ORD-OFFLINE-01',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Rudianto',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 50000,
            'created_at' => now()->subDays(10),
        ]);

        // Filter for Online sales + today preset
        $response = $this->actingAs($admin)->get(route('all-sales.report', [
            'jenis_penjualan' => 'Online',
            'preset' => 'today',
        ]));

        $response->assertStatus(200);
        $response->assertSee('#ORD-ONLINE-01');
        $response->assertDontSee('#ORD-OFFLINE-01');
    }

    public function test_sales_report_page_filters_by_diskon_and_expired(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create product with discount
        $kategori = \App\Models\Kategori::create(['nama_kategori' => 'Kategori Promo', 'gambar_kategori' => 'dummy.png']);
        $pDisc = \App\Models\Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => 'Roti Promo Diskon',
            'harga' => 20000,
            'status_produk' => 'Tersedia',
        ]);
        \App\Models\Diskon::create([
            'id_produk' => $pDisc->id_produk,
            'nama_diskon' => 'Promo Diskon 10%',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'status' => 'aktif',
        ]);

        // Create order with discounted product
        Pesanan::create([
            'order_number' => '#ORD-DISC-99',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Pelanggan Diskon',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 18000,
            'items' => [
                ['id' => $pDisc->id_produk, 'name' => 'Roti Promo Diskon', 'price' => 18000, 'qty' => 1]
            ]
        ]);

        // Create expired product record
        \App\Models\ProdukKadaluarsa::create([
            'id_produk' => $pDisc->id_produk,
            'tanggal_expired' => now()->toDateString(),
            'jumlah_stok' => 5,
            'status_tindakan' => 'Dimusnahkan',
        ]);

        // Create completed retur record
        \App\Models\Retur::create([
            'customer_name' => 'Pelanggan Diskon',
            'order_id' => '#ORD-DISC-99',
            'product_name' => 'Roti Promo Diskon',
            'reason' => 'Kemasan Rusak',
            'return_date' => now()->toDateString(),
            'status' => 'Done',
        ]);

        // Filter for Discounted Orders
        $response = $this->actingAs($admin)->get(route('all-sales.report', ['has_discount' => 'Yes']));
        $response->assertStatus(200);
        $response->assertSee('#ORD-DISC-99');
        $response->assertSee('Pelanggan Diskon');
        $response->assertSee('Total Pemasukkan Asli');
        $response->assertSee('Total Pemasukkan Setelah Diskon');
        $response->assertSee('Jumlah Produk Kadaluarsa');
        $response->assertSee('Jumlah Retur Selesai');
    }
}

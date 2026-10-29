<?php

namespace Tests\Feature;

use App\Models\Pesanan;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_kasir_cannot_access_admin_dashboard_and_is_redirected(): void
    {
        $kasir = User::factory()->create(['role' => 'kasir']);
        $response = $this->actingAs($kasir)->get(route('admin.dashboard'));
        $response->assertRedirect(route('orders.index'));
        $response->assertSessionHas('error');
    }

    public function test_admin_can_access_dashboard_with_dynamic_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'nama' => 'Admin Utama']);

        // Create paid order this week
        $order = Pesanan::create([
            'order_number' => 'ORD-DASH-001',
            'customer_name' => 'Budi Santoso',
            'total' => 250000,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'jenis_penjualan' => 'Online',
            'payment_method' => 'Midtrans',
            'items' => [
                ['id' => 1, 'name' => 'Kue Coklat', 'price' => 250000, 'qty' => 1]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Penjualan Mingguan');
        $response->assertSee('Rp 250.000');
        $response->assertSee('Budi Santoso');
        $response->assertSee('ORD-DASH-001');
        // Ensure dummy 2017 tickets are gone
        $response->assertDontSee('David Grey');
        $response->assertDontSee('WD-12345');
        $response->assertDontSee('face1.jpg');
    }

    public function test_invoice_renders_dynamic_store_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        StoreSetting::set('store_name', 'Zyh Corner Official', 'Nama Toko');
        StoreSetting::set('store_phone', '081299998888', 'No Toko');
        StoreSetting::set('store_email', 'toko@zyhcorner.test', 'Email Toko');

        $order = Pesanan::create([
            'order_number' => 'ORD-INV-999',
            'customer_name' => 'Siti Aisyah',
            'total' => 100000,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'jenis_penjualan' => 'Online',
            'items' => [
                ['id' => 1, 'name' => 'Dessert Box', 'price' => 100000, 'qty' => 1]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('orders.invoice', $order->id));

        $response->assertStatus(200);
        $response->assertSee('Zyh Corner Official');
        $response->assertSee('081299998888');
        $response->assertSee('toko@zyhcorner.test');
        $response->assertDontSee('+62 812-3456-7890');
    }
}

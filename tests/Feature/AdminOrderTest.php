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
        $response = $this->actingAs($admin)->get(route('all-sales.index'));
        $response->assertStatus(200);
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
}

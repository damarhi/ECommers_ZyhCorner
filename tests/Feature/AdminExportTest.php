<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Customer;
use App\Models\Retur;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_export_all_sales_csv()
    {
        Pesanan::create([
            'order_number' => 'ORD-TEST-001',
            'customer_name' => 'John Doe',
            'email_pembeli' => 'john@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'jenis_penjualan' => 'Online',
            'total' => 150000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.all-sales'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    }

    public function test_admin_can_export_filtered_sales_csv()
    {
        Pesanan::create([
            'order_number' => 'ORD-MATCH-001',
            'customer_name' => 'Matching Customer',
            'email_pembeli' => 'match@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'jenis_penjualan' => 'Online',
            'created_at' => now(),
            'total' => 300000,
        ]);

        Pesanan::create([
            'order_number' => 'ORD-EXCLUDE-999',
            'customer_name' => 'Excluded Customer',
            'email_pembeli' => 'exclude@example.com',
            'payment_status' => 'Pending',
            'order_status' => 'selesai',
            'jenis_penjualan' => 'Online',
            'created_at' => now()->subYear(),
            'total' => 50000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.all-sales', [
            'preset' => 'today',
            'payment_status' => 'Paid',
            'jenis_penjualan' => 'Online',
            'search' => 'Matching'
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    }

    public function test_admin_can_export_orders_csv()
    {
        Pesanan::create([
            'order_number' => 'ORD-ONLINE-001',
            'customer_name' => 'Jane Doe',
            'email_pembeli' => 'jane@example.com',
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'jenis_penjualan' => 'Online',
            'total' => 200000,
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.orders'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ORD-ONLINE-001', $response->streamedContent());
    }

    public function test_admin_can_export_products_showcase_csv()
    {
        Produk::create([
            'nama_produk' => 'Kue Bolu Keju',
            'harga' => 50000,
            'jenis_produk' => 'showcase',
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.products', ['jenis' => 'showcase']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Kue Bolu Keju', $response->streamedContent());
    }

    public function test_admin_can_export_products_kustom_csv()
    {
        Produk::create([
            'nama_produk' => 'Kue Ulang Tahun Custom',
            'harga' => 100000,
            'harga_max' => 250000,
            'jenis_produk' => 'kustom',
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.products', ['jenis' => 'kustom']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Kue Ulang Tahun Custom', $response->streamedContent());
    }

    public function test_admin_can_export_customers_csv()
    {
        Customer::create([
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'status' => 'Active',
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.customers'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Budi Santoso', $response->streamedContent());
    }

    public function test_admin_can_export_returs_csv()
    {
        Retur::create([
            'order_id' => 'ORD-RETUR-01',
            'return_date' => '2026-07-30',
            'customer_name' => 'Siti Nurhaliza',
            'product_name' => 'Kue Brownies',
            'reason' => 'Kemasan Rusak',
            'status' => 'New',
        ]);

        $response = $this->actingAs($this->admin)->get(route('export.returs'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ORD-RETUR-01', $response->streamedContent());
    }
}

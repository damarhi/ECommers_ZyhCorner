<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Customer;
use App\Models\Retur;
use App\Models\Diskon;

class AdminFilterStandardizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_pagination_preserves_query_string(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create(['nama_kategori' => 'Kue Basah', 'gambar_kategori' => 'kategori.jpg']);

        // Create 15 products
        for ($i = 1; $i <= 15; $i++) {
            Produk::create([
                'id_kategori' => $kategori->id_kategori,
                'nama_produk' => 'Kue Lapis Legit ' . $i,
                'harga' => 50000,
                'jenis_produk' => 'showcase',
                'status_produk' => 'Tersedia',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('products.index', [
            'search' => 'Legit',
            'status' => ['Tersedia'],
        ]));

        $response->assertStatus(200);
        $response->assertSee('search=Legit');
        $response->assertSee('status%5B0%5D=Tersedia');
        $response->assertSee('Reset');
    }

    public function test_custom_products_pagination_preserves_query_string(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create(['nama_kategori' => 'Kue Tart', 'gambar_kategori' => 'kategori.jpg']);

        for ($i = 1; $i <= 15; $i++) {
            Produk::create([
                'id_kategori' => $kategori->id_kategori,
                'nama_produk' => 'Kustom Tart Fondant ' . $i,
                'harga' => 150000,
                'jenis_produk' => 'kustom',
                'status_produk' => 'Tersedia',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('products.custom', [
            'search' => 'Tart',
            'status' => ['Tersedia'],
        ]));

        $response->assertStatus(200);
        $response->assertSee('search=Tart');
        $response->assertSee('status%5B0%5D=Tersedia');
        $response->assertSee('Reset');
    }

    public function test_products_filter_by_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kat1 = Kategori::create(['nama_kategori' => 'Kue Kering', 'gambar_kategori' => 'kue_kering.jpg']);
        $kat2 = Kategori::create(['nama_kategori' => 'Minuman', 'gambar_kategori' => 'minuman.jpg']);

        Produk::create([
            'id_kategori' => $kat1->id_kategori,
            'nama_produk' => 'Nastar Keju Spesial',
            'harga' => 65000,
            'jenis_produk' => 'showcase',
            'status_produk' => 'Tersedia',
        ]);

        Produk::create([
            'id_kategori' => $kat2->id_kategori,
            'nama_produk' => 'Es Teh Manis Segar',
            'harga' => 10000,
            'jenis_produk' => 'showcase',
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->actingAs($admin)->get(route('products.index', [
            'kategori' => [$kat1->id_kategori],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Nastar Keju Spesial');
        $response->assertDontSee('Es Teh Manis Segar');
        $response->assertSee('admin-filter-badge');
        $response->assertSee('Reset');
    }

    public function test_custom_products_filter_by_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kat1 = Kategori::create(['nama_kategori' => 'Kue Pengantin', 'gambar_kategori' => 'wedding.jpg']);
        $kat2 = Kategori::create(['nama_kategori' => 'Snack Box', 'gambar_kategori' => 'snack.jpg']);

        Produk::create([
            'id_kategori' => $kat1->id_kategori,
            'nama_produk' => 'Custom Wedding Cake 3 Tingkat',
            'harga' => 1500000,
            'jenis_produk' => 'kustom',
            'status_produk' => 'Tersedia',
        ]);

        Produk::create([
            'id_kategori' => $kat2->id_kategori,
            'nama_produk' => 'Custom Box Katering Mini',
            'harga' => 35000,
            'jenis_produk' => 'kustom',
            'status_produk' => 'Tersedia',
        ]);

        $response = $this->actingAs($admin)->get(route('products.custom', [
            'kategori' => [$kat1->id_kategori],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Custom Wedding Cake 3 Tingkat');
        $response->assertDontSee('Custom Box Katering Mini');
        $response->assertSee('admin-filter-badge');
        $response->assertSee('Reset');
    }

    public function test_orders_filter_by_order_status_and_preserves_query_string(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create 12 orders for dikemas to trigger pagination links (perPage is 10)
        for ($i = 1; $i <= 12; $i++) {
            Pesanan::create([
                'order_number' => '#ORD-PACK-' . $i,
                'jenis_penjualan' => 'Online',
                'customer_name' => 'Dewi ' . $i,
                'payment_status' => 'Paid',
                'order_status' => 'dikemas',
                'total' => 120000,
            ]);
        }

        // Order with other status
        Pesanan::create([
            'order_number' => '#ORD-NEW-01',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Budi',
            'payment_status' => 'Paid',
            'order_status' => 'menunggu konfirmasi',
            'total' => 100000,
        ]);

        // Filter for dikemas
        $response = $this->actingAs($admin)->get(route('orders.index', [
            'order_status' => ['dikemas'],
        ]));

        $response->assertStatus(200);
        $response->assertSee('#ORD-PACK-1');
        $response->assertDontSee('#ORD-NEW-01');
        $response->assertSee('order_status%5B0%5D=dikemas');
        $response->assertSee('Reset');
    }

    public function test_customer_counts_and_filters_preserves_query_string(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Customer::create([
            'name' => 'Pelanggan Aktif',
            'email' => 'aktif@example.com',
            'status' => 'Active',
            'orders_count' => 5,
            'spent' => 250000,
        ]);

        Customer::create([
            'name' => 'Pelanggan Blokir',
            'email' => 'blokir@example.com',
            'status' => 'Blocked',
            'orders_count' => 1,
            'spent' => 50000,
        ]);

        $response = $this->actingAs($admin)->get(route('customers.index', [
            'filter' => 'Active',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Pelanggan Aktif');
        $response->assertDontSee('Pelanggan Blokir');
        $response->assertSee('Semua Pelanggan');
        $response->assertSee('Aktif');
        $response->assertSee('Diblokir');
        $response->assertSee('filter=Active');
    }

    public function test_pos_filter_by_category_preserves_query_string(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $katPastry = Kategori::create(['nama_kategori' => 'Pastry', 'gambar_kategori' => 'pastry.jpg']);
        $katCake = Kategori::create(['nama_kategori' => 'Cakes', 'gambar_kategori' => 'cakes.jpg']);

        // Create 25 products in Pastry to trigger pagination links (perPage is 20)
        for ($i = 1; $i <= 25; $i++) {
            Produk::create([
                'id_kategori' => $katPastry->id_kategori,
                'nama_produk' => 'Croissant Butter Gurih ' . $i,
                'harga' => 25000,
                'jenis_produk' => 'showcase',
                'status_produk' => 'Tersedia',
                'stok' => 10,
            ]);
        }

        Produk::create([
            'id_kategori' => $katCake->id_kategori,
            'nama_produk' => 'Tiramisu Slice Premium',
            'harga' => 45000,
            'jenis_produk' => 'showcase',
            'status_produk' => 'Tersedia',
            'stok' => 5,
        ]);
        $response = $this->actingAs($admin)->get(route('pos.index', [
            'kategori' => [$katPastry->id_kategori],
        ]));

        $response->assertStatus(200);
        $response->assertSee('Croissant Butter Gurih');
        $response->assertDontSee('Tiramisu Slice Premium');
        $response->assertSee('kategori%5B0%5D=' . $katPastry->id_kategori);
        $response->assertSee('Reset');
    }
}

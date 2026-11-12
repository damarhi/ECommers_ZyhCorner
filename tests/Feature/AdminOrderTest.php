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
    public function test_invoice_renders_complete_data_for_80mm_thermal_receipt(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'sarah@example.com',
            'no_hp' => '081399887766',
        ]);

        $order = Pesanan::create([
            'order_number' => '#THM80MM',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Sarah Amalia',
            'email_pembeli' => 'sarah@example.com',
            'shipping_method' => 'JNE Reguler',
            'alamat_pengiriman' => 'Jl. Mawar No. 45, Pelalawan',
            'catatan_pesanan' => 'Tolong kirimkan sore hari',
            'payment_method' => 'Transfer BCA',
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'total' => 115000,
            'items' => [
                [
                    'id' => 1,
                    'name' => 'Kue Tart Cokelat',
                    'price' => 100000,
                    'qty' => 1,
                    'custom_text' => 'Happy Sweet Seventeen',
                ]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('orders.invoice', $order->id));

        $response->assertStatus(200);
        $response->assertSee('#THM80MM');
        $response->assertSee('Struk Thermal 80mm');
        $response->assertSee('receipt-container');
        $response->assertSee('Sarah Amalia');
        $response->assertSee('081399887766');
        $response->assertSee('JNE Reguler');
        $response->assertSee('Jl. Mawar No. 45, Pelalawan');
        $response->assertSee('Tolong kirimkan sore hari');
        $response->assertSee('Kue Tart Cokelat');
        $response->assertSee('Happy Sweet Seventeen');
        $response->assertSee('Subtotal Produk');
        $response->assertSee('Rp. 100.000');
        $response->assertSee('Ongkos Kirim');
        $response->assertSee('Rp. 15.000');
        $response->assertSee('TOTAL BAYAR');
        $response->assertSee('Rp. 115.000');
        $response->assertSee('[LUNAS]');
    }
    public function test_invoice_renders_customer_address_details_and_fallbacks(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create([
            'role' => 'pelanggan',
            'email' => 'dian@example.com',
            'no_hp' => '081211223344',
        ]);

        \App\Models\UserAddress::create([
            'user_id' => $customer->user_id,
            'label' => 'Rumah Pribadi',
            'recipient_name' => 'Dian Permata',
            'recipient_phone' => '081211223344',
            'destination_area_name' => 'Kec. Pangkalan Kerinci, Pelalawan',
            'postal_code' => '28654',
            'street_address' => 'Jl. Melati Blok B No. 10',
            'notes' => 'Pagar hitam samping warung',
            'latitude' => 0.38472,
            'longitude' => 101.85918,
            'is_default' => true,
        ]);

        // 1. Order online without explicit alamat_pengiriman (resolves from user_addresses)
        $orderOnline = Pesanan::create([
            'order_number' => '#ADDR-ONL',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Dian Permata',
            'email_pembeli' => 'dian@example.com',
            'shipping_method' => 'Kurir Toko',
            'alamat_pengiriman' => null,
            'total' => 85000,
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'items' => [
                ['id' => 1, 'name' => 'Choux Pastry', 'price' => 85000, 'qty' => 1]
            ]
        ]);

        $responseOnline = $this->actingAs($admin)->get(route('orders.invoice', $orderOnline->id));
        $responseOnline->assertStatus(200);
        $responseOnline->assertSee('Rumah Pribadi');
        $responseOnline->assertSee('Jl. Melati Blok B No. 10');
        $responseOnline->assertSee('Kec. Pangkalan Kerinci, Pelalawan');
        $responseOnline->assertSee('28654');
        $responseOnline->assertSee('Pagar hitam samping warung');
        $responseOnline->assertSee('0.38472, 101.85918');

        // 2. Order offline POS (Takeaway)
        $orderOffline = Pesanan::create([
            'order_number' => '#ADDR-POS',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Walk-in Customer',
            'email_pembeli' => null,
            'alamat_pengiriman' => null,
            'total' => 30000,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'items' => [
                ['id' => 1, 'name' => 'Croissant Butter', 'price' => 30000, 'qty' => 1]
            ]
        ]);

        $responseOffline = $this->actingAs($admin)->get(route('orders.invoice', $orderOffline->id));
        $responseOffline->assertStatus(200);
        $responseOffline->assertSee('Ambil di Toko / Kasir Langsung');
    }
    public function test_invoice_replaces_dummy_street_with_reverse_geocoded_location_and_cleans_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        \Illuminate\Support\Facades\Http::fake([
            'nominatim.openstreetmap.org/*' => \Illuminate\Support\Facades\Http::response([
                'name' => 'Simpang Harapan Raya 2',
                'display_name' => 'Simpang Harapan Raya 2, Jalan Jenderal Sudirman, Tangkerang Tengah, Pekanbaru, Marpoyan Damai, Riau, 28288, Indonesia',
                'address' => [
                    'highway' => 'Simpang Harapan Raya 2',
                    'road' => 'Jalan Jenderal Sudirman',
                    'suburb' => 'Tangkerang Tengah',
                    'district' => 'Marpoyan Damai',
                    'city' => 'Pekanbaru',
                    'state' => 'Riau',
                    'postcode' => '28288',
                ]
            ], 200)
        ]);

        $order = Pesanan::create([
            'order_number' => '#IZYJ6',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'nama_nama',
            'email_pembeli' => 'nama@gmail.com',
            'shipping_method' => 'Pick Up',
            'alamat_pengiriman' => 'sdasda (Kode Pos: 1231)',
            'catatan_pesanan' => '[Penerima: nama_nama (8123123123)] [Titik GPS: 0.498718, 101.454788 | https://maps.google.com/?q=0.498718,101.454788]',
            'payment_method' => 'COD',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 375000,
            'items' => [
                ['id' => 1, 'name' => 'Black Forest Cake Classic', 'price' => 100000, 'qty' => 3]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('orders.invoice', $order->id));

        $response->assertStatus(200);
        $response->assertSee('#IZYJ6');
        $response->assertSee('0.498718, 101.454788');
        // Verified that reverse geocoded place and street name are displayed
        $response->assertSee('Jalan Jenderal Sudirman');
        $response->assertSee('28288');
        // Verified that internal bracket annotations are stripped from Catatan
        $response->assertDontSee('Catatan:');
    }
    public function test_order_detail_modal_renders_reverse_geocoded_location_and_cleans_notes(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        \Illuminate\Support\Facades\Http::fake([
            'nominatim.openstreetmap.org/*' => \Illuminate\Support\Facades\Http::response([
                'name' => 'Simpang Harapan Raya 2',
                'display_name' => 'Simpang Harapan Raya 2, Jalan Jenderal Sudirman, Tangkerang Tengah, Pekanbaru, Marpoyan Damai, Riau, 28288, Indonesia',
                'address' => [
                    'highway' => 'Simpang Harapan Raya 2',
                    'road' => 'Jalan Jenderal Sudirman',
                    'suburb' => 'Tangkerang Tengah',
                    'district' => 'Marpoyan Damai',
                    'city' => 'Pekanbaru',
                    'state' => 'Riau',
                    'postcode' => '28288',
                ]
            ], 200)
        ]);

        $order = Pesanan::create([
            'order_number' => '#MODAL-GEO',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Budi Santoso',
            'email_pembeli' => 'budi@gmail.com',
            'shipping_method' => 'Gojek Instant',
            'alamat_pengiriman' => 'sdasda (Kode Pos: 1231)',
            'catatan_pesanan' => '[Penerima: Budi Santoso (081298765432)] [Titik GPS: 0.498718, 101.454788 | https://maps.google.com/?q=0.498718,101.454788] Tolong jangan dibanting',
            'payment_method' => 'COD',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 150000,
            'items' => [
                ['id' => 1, 'name' => 'Brownies Fudgy', 'price' => 150000, 'qty' => 1]
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('orders.index'));

        $response->assertStatus(200);
        $response->assertSee('orderDetailModal' . $order->id);
        // Verified reverse geocoded street and landmark
        $response->assertSee('Jalan Jenderal Sudirman');
        $response->assertSee('Simpang Harapan Raya 2');
        $response->assertSee('Terverifikasi GPS');
        $response->assertSee('Buka Google Maps');
        $response->assertSee('https://maps.google.com/?q=0.498718,101.454788');
        // Verified recipient phone and WhatsApp link
        $response->assertSee('081298765432');
        $response->assertSee('wa.me/6281298765432');
        // Verified sanitized customer note
        $response->assertSee('Tolong jangan dibanting');
        $response->assertDontSee('[Penerima:');
        $response->assertDontSee('[Titik GPS:');
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

    public function test_admin_can_search_all_sales_by_product_name(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Order 1 with "Black Forest Cake Classic" (using nama_produk key)
        $order1 = Pesanan::create([
            'order_number' => '#ORD-BF-01',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Budi Santoso',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Cash',
            'total' => 125000,
            'items' => [
                ['id' => 1, 'nama_produk' => 'Black Forest Cake Classic', 'price' => 125000, 'qty' => 1]
            ]
        ]);

        // Order 2 with "Red Velvet Special Cream Cheese" (using name key)
        $order2 = Pesanan::create([
            'order_number' => '#ORD-RV-02',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Siti Aminah',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Cash',
            'total' => 140000,
            'items' => [
                ['id' => 2, 'name' => 'Red Velvet Special Cream Cheese', 'price' => 140000, 'qty' => 1]
            ]
        ]);

        // 1. Search by "Black Forest"
        $response = $this->actingAs($admin)->get(route('all-sales.index', ['search' => 'Black Forest']));
        $response->assertStatus(200);
        $response->assertSee('#ORD-BF-01');
        $response->assertDontSee('#ORD-RV-02');
        $response->assertSee('black forest cake classic');

        // 2. Search by "Red Velvet"
        $response2 = $this->actingAs($admin)->get(route('all-sales.index', ['search' => 'Red Velvet']));
        $response2->assertStatus(200);
        $response2->assertSee('#ORD-RV-02');
        $response2->assertDontSee('#ORD-BF-01');
        $response2->assertSee('red velvet special cream cheese');
    }

    public function test_admin_can_perform_ajax_live_search_on_all_sales_returning_partial_html(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Pesanan::create([
            'order_number' => '#ORD-MATCH-01',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Dewi Lestari',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Midtrans',
            'total' => 175000,
            'items' => [
                ['id' => 10, 'nama_produk' => 'Matcha Layer Cake', 'price' => 175000, 'qty' => 1]
            ]
        ]);

        Pesanan::create([
            'order_number' => '#ORD-TIRAM-02',
            'jenis_penjualan' => 'Offline',
            'customer_name' => 'Andi Wijaya',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Cash',
            'total' => 135000,
            'items' => [
                ['id' => 11, 'nama_produk' => 'Classic Tiramisu', 'price' => 135000, 'qty' => 1]
            ]
        ]);

        // Request via AJAX header
        $response = $this->actingAs($admin)->get(
            route('all-sales.index', ['search' => 'Matcha']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertSee('#ORD-MATCH-01');
        $response->assertDontSee('#ORD-TIRAM-02');
        $response->assertDontSee('<!DOCTYPE html>'); // Pastikan partial Blade, bukan full layout wrapper
        $response->assertSee('table-responsive');
        $response->assertSee('Menampilkan 1 - 1 dari 1 hasil');
    }

    public function test_admin_can_perform_ajax_live_search_on_all_sales_returning_json_with_kpi_counts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Pesanan::create([
            'order_number' => '#ORD-CHOC-01',
            'jenis_penjualan' => 'Online',
            'customer_name' => 'Fajar Pratama',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'payment_method' => 'Midtrans',
            'total' => 200000,
            'items' => [
                ['id' => 12, 'nama_produk' => 'Dark Chocolate Fudge', 'price' => 200000, 'qty' => 1]
            ]
        ]);

        // Request with Accept application/json
        $response = $this->actingAs($admin)->getJson(
            route('all-sales.index', ['search' => 'Chocolate', 'ajax' => 1]),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'html',
            'total',
            'firstItem',
            'lastItem',
            'totalPendapatan',
            'totalOnlineCount',
            'totalOfflineCount',
        ]);
        $response->assertJson([
            'total' => 1,
            'totalPendapatan' => 'Rp. 200.000',
            'totalOnlineCount' => 1,
            'totalOfflineCount' => 0,
        ]);
    }

    public function test_admin_ajax_live_search_shows_empty_state_with_reset_button(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(
            route('all-sales.index', ['search' => 'KueNanasPanggangGaib']),
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertStatus(200);
        $response->assertSee('Tidak ada data yang cocok');
        $response->assertSee('KueNanasPanggangGaib');
        $response->assertSee('Reset Pencarian');
        $response->assertSee('reset-instant-search-btn');
    }
}

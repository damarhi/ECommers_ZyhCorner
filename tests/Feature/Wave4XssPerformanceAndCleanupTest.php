<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class Wave4XssPerformanceAndCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_rejects_javascript_scheme_in_validation_and_model_accessor(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'judul_banner' => 'Promo berbahaya',
            'posisi_banner' => 'home_hero',
            'link_url' => 'javascript:alert(document.cookie)',
            'status' => 'Aktif',
            'gambar_banner' => UploadedFile::fake()->image('banner.jpg'),
        ]);

        $response->assertSessionHasErrors('link_url');

        // Defense-in-depth accessor neutralizes unsafe scheme even if present in DB
        $banner = new Banner([
            'judul_banner' => 'Legacy',
            'gambar_banner' => 'banners/x.jpg',
            'posisi_banner' => 'home_hero',
            'link_url' => 'javascript:alert(1)',
            'status' => 'Aktif',
        ]);
        $this->assertNull($banner->link_url);

        $safeBanner = new Banner(['link_url' => '/shop']);
        $this->assertEquals('/shop', $safeBanner->link_url);
    }

    public function test_settings_and_product_detail_escape_multiline_and_script_breakout_payloads(): void
    {
        $user = User::factory()->create([
            'nama' => 'Budi "</script><script>alert(1)</script>',
            'alamat' => "Jl. Baris Satu\nBaris Dua \"Kutipan\" </script>",
            'role' => 'pelanggan',
        ]);

        $settingsResp = $this->actingAs($user)->get(route('settings'));
        $settingsResp->assertStatus(200);
        // JSON encoding escapes forward slashes (\u003C / <\/script>) so literal </script><script> never breaks out
        $settingsResp->assertDontSee('"</script><script>alert(1)</script>"', false);

        $kategori = Kategori::create([
            'nama_kategori' => 'Cake',
            'gambar_kategori' => 'dummy.png',
        ]);

        $produk = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'jenis_produk' => 'showcase',
            'nama_produk' => "Kue 'Spesial' </script><script>alert(1)</script>",
            'harga' => 55000,
            'status_produk' => '8',
        ]);

        $detailResp = $this->get(route('product.detail', $produk->id_produk));
        $detailResp->assertStatus(200);
        $detailResp->assertDontSee("productName: 'Kue 'Spesial'", false);
    }

    public function test_order_success_includes_server_side_items_and_admin_layout_has_csrf_and_correct_notif_filter(): void
    {
        $customer = User::factory()->create(['role' => 'pelanggan']);
        $admin = User::factory()->create(['role' => 'admin']);

        $order = Pesanan::create([
            'user_id' => $customer->user_id,
            'order_number' => '#SUCC01',
            'jenis_penjualan' => 'Online',
            'customer_name' => $customer->nama,
            'email_pembeli' => $customer->email,
            'payment_method' => 'COD',
            'payment_status' => 'Pending',
            'order_status' => 'menunggu konfirmasi',
            'total' => 70000,
            'items' => [
                ['id' => 1, 'name' => 'Éclair Vanilla ServerItem', 'price' => 35000, 'qty' => 2],
            ],
        ]);

        $succResp = $this->actingAs($customer)->get(route('order.success', ['order_id' => $order->id]));
        $succResp->assertStatus(200);
        $succResp->assertSee('serverOrderItems', false);
        $succResp->assertSee('clair Vanilla ServerItem', false);

        $adminResp = $this->actingAs($admin)->get(route('orders.index'));
        $adminResp->assertStatus(200);
        $adminResp->assertSee('<meta name="csrf-token"', false);
        $adminResp->assertSee('order_status=menunggu', false);
    }

    public function test_scheduled_expired_products_artisan_command_syncs_expired_stock(): void
    {
        $expired = Produk::create([
            'nama_produk' => 'Roti Jadwal',
            'harga' => 18000,
            'status_produk' => '6',
            'jenis_produk' => 'showcase',
            'tanggal_expired' => Carbon::yesterday()->toDateString(),
        ]);

        Artisan::call('products:sync-expired');

        $this->assertEquals('0', $expired->fresh()->status_produk);
        $this->assertDatabaseHas('produk_kadaluarsas', [
            'id_produk' => $expired->id_produk,
            'jumlah_stok' => '6',
        ]);
    }

    public function test_database_cache_with_serializable_classes_false_handles_admin_layout_and_best_sellers(): void
    {
        config([
            'cache.default' => 'database',
            'cache.serializable_classes' => false,
        ]);

        $admin = User::factory()->create(['role' => 'admin']);

        \App\Models\ContactMessage::create([
            'nama' => 'Rina Pelanggan',
            'email' => 'rina.pesan@zyhcorner.com',
            'subjek' => 'Tanya Stok',
            'pesan' => 'Apakah kue tart ready?',
            'status' => 'Belum Dibaca',
        ]);

        $produk = Produk::create([
            'nama_produk' => 'Cheesecake Lumer',
            'harga' => 65000,
            'status_produk' => '12',
            'jenis_produk' => 'showcase',
        ]);

        Pesanan::create([
            'order_number' => '#CACHE1',
            'jenis_penjualan' => 'Online',
            'payment_method' => 'COD',
            'customer_name' => 'Rina Pelanggan',
            'email_pembeli' => 'rina.pesan@zyhcorner.com',
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 130000,
            'items' => [
                ['id' => $produk->id_produk, 'name' => 'Cheesecake Lumer', 'price' => 65000, 'qty' => 2],
            ],
        ]);

        // 1st request populates database cache; 2nd request unserializes with serializable_classes = false
        $this->actingAs($admin)->get('/admin/dashboard')->assertStatus(200);
        $ordersResp = $this->actingAs($admin)->get(route('orders.index'));
        $ordersResp->assertStatus(200);
        $ordersResp->assertSee('Rina Pelanggan');
        $ordersResp->assertSee('rina.pesan@zyhcorner.com');

        // Storefront best-sellers cache miss -> cache hit with serializable_classes = false
        $this->get(route('home'))->assertStatus(200)->assertSee('Cheesecake Lumer');
        $this->get(route('home'))->assertStatus(200)->assertSee('Cheesecake Lumer');
    }
}

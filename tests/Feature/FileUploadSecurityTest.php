<?php

namespace Tests\Feature;

use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Retur;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_psr4_model_filenames_have_exact_pascal_case(): void
    {
        $files = scandir(app_path('Models'));

        $this->assertContains('Kategori.php', $files);
        $this->assertContains('Pesanan.php', $files);
        $this->assertContains('Retur.php', $files);
        $this->assertNotContains('kategori.php', $files);
        $this->assertNotContains('pesanan.php', $files);
        $this->assertNotContains('retur.php', $files);
    }

    public function test_customer_return_blocks_php_and_arbitrary_field_uploads(): void
    {
        Storage::fake('public');

        $customer = User::factory()->create(['role' => 'pelanggan']);
        $order = Pesanan::create([
            'order_number' => '#SEC-RET-01',
            'jenis_penjualan' => 'Online',
            'payment_method' => 'COD',
            'customer_name' => $customer->nama,
            'email_pembeli' => $customer->email,
            'payment_status' => 'Paid',
            'order_status' => 'selesai',
            'total' => 75000,
            'items' => [],
        ]);

        // 1. Attempt PHP file upload via photo field
        $phpFile = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');
        $res1 = $this->actingAs($customer)->postJson(route('order.return.submit', $order->id), [
            'reason' => 'Produk rusak',
            'photo' => $phpFile,
        ]);
        $res1->assertStatus(422);
        $this->assertDatabaseMissing('returs', ['order_id' => '#SEC-RET-01']);

        // 2. Attempt bypass via unvalidated arbitrary file field
        $arbitraryFile = UploadedFile::fake()->create('backdoor.php', 10, 'text/plain');
        $res2 = $this->actingAs($customer)->post(route('order.return.submit', $order->id), [
            'reason' => 'Produk rusak',
            'evil_payload' => $arbitraryFile,
        ]);
        $res2->assertStatus(422);
        $this->assertDatabaseMissing('returs', ['order_id' => '#SEC-RET-01']);
    }

    public function test_admin_retur_blocks_unvalidated_photo_field_rce_and_cleans_up_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);

        // 1. Block PHP file sent via secondary 'photo' field
        $phpFile = UploadedFile::fake()->create('exploit.php', 10, 'application/x-php');
        $resPhp = $this->actingAs($admin)->post(route('returs.store'), [
            'customer_name' => 'Test User',
            'order_id' => '#ORD-RCE-01',
            'reason' => 'Rusak',
            'return_date' => now()->toDateString(),
            'product_name' => 'Brownies',
            'status' => 'New',
            'photo' => $phpFile,
        ]);
        $resPhp->assertSessionHasErrors('photo');
        $this->assertDatabaseMissing('returs', ['order_id' => '#ORD-RCE-01']);

        // 2. Valid image upload & cleanup on bulkDelete
        $validImage = UploadedFile::fake()->image('evidence.jpg', 300, 300);
        $resOk = $this->actingAs($admin)->post(route('returs.store'), [
            'customer_name' => 'Test User',
            'order_id' => '#ORD-CLEAN-01',
            'reason' => 'Rusak',
            'return_date' => now()->toDateString(),
            'product_name' => 'Brownies',
            'status' => 'New',
            'foto_retur' => $validImage,
        ]);
        $resOk->assertRedirect(route('returs.index'));

        $retur = Retur::where('order_id', '#ORD-CLEAN-01')->firstOrFail();
        $this->assertNotNull($retur->foto_retur);
        $diskPath = preg_replace('#^storage/#', '', $retur->foto_retur);
        Storage::disk('public')->assertExists($diskPath);

        $this->actingAs($admin)->post(route('returs.bulk-delete'), [
            'ids' => [$retur->id],
        ])->assertRedirect(route('returs.index'));

        Storage::disk('public')->assertMissing($diskPath);
    }

    public function test_product_and_banner_uploads_reject_svg_xss_files(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $svgFile = UploadedFile::fake()->createWithContent(
            'xss.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $this->actingAs($admin)->post(route('products.store'), [
            'nama_produk' => 'Kue SVG',
            'harga' => 50000,
            'status_produk' => '10',
            'jenis_produk' => 'showcase',
            'foto_1' => $svgFile,
        ])->assertSessionHasErrors('foto_1');

        $this->actingAs($admin)->post(route('banners.store'), [
            'judul_banner' => 'Banner SVG',
            'posisi_banner' => 'home_hero',
            'status' => 'Aktif',
            'gambar_banner' => $svgFile,
        ])->assertSessionHasErrors('gambar_banner');
    }

    public function test_admin_profile_photo_persists_to_avatar_column_and_exposes_foto_accessor(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $photo = UploadedFile::fake()->image('admin_pic.png', 200, 200);

        $this->actingAs($admin)->post(route('settings.profile.update'), [
            'nama' => 'Admin Foto Baru',
            'email' => $admin->email,
            'no_hp' => '081234567890',
            'foto' => $photo,
        ])->assertRedirect(route('settings.index'));

        $admin->refresh();
        $this->assertNotNull($admin->avatar);
        $this->assertNotNull($admin->foto);
        Storage::disk('public')->assertExists($admin->foto);
    }

    public function test_product_bulk_delete_removes_all_four_photos(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $kategori = Kategori::create(['nama_kategori' => 'Tart', 'gambar_kategori' => 'dummy.jpg']);

        Storage::disk('public')->put('products/p1.jpg', 'img1');
        Storage::disk('public')->put('products/p2.jpg', 'img2');
        Storage::disk('public')->put('products/p3.jpg', 'img3');
        Storage::disk('public')->put('products/p4.jpg', 'img4');

        $product = Produk::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_produk' => 'Tart 4 Foto',
            'harga' => 120000,
            'status_produk' => '5',
            'jenis_produk' => 'showcase',
            'foto_1' => 'products/p1.jpg',
            'foto_2' => 'products/p2.jpg',
            'foto_3' => 'products/p3.jpg',
            'foto_4' => 'products/p4.jpg',
        ]);

        $this->actingAs($admin)->post(route('products.bulk-delete'), [
            'ids' => [$product->id_produk],
        ])->assertRedirect(route('products.index'));

        Storage::disk('public')->assertMissing('products/p1.jpg');
        Storage::disk('public')->assertMissing('products/p2.jpg');
        Storage::disk('public')->assertMissing('products/p3.jpg');
        Storage::disk('public')->assertMissing('products/p4.jpg');
    }
}

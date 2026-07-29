<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Banner;

class AdminBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_banners_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('banners.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Banner Pelanggan');
    }

    public function test_admin_can_create_banner_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $file = UploadedFile::fake()->image('banner_promo.jpg', 1200, 600);

        $response = $this->actingAs($admin)->post(route('banners.store'), [
            'judul_banner' => 'Diskon Lebaran Super Big Sale',
            'subjudul' => 'Potongan hingga 50%',
            'posisi_banner' => 'home_hero',
            'link_url' => '/pelanggan/shop',
            'urutan' => 1,
            'status' => 'Aktif',
            'gambar_banner' => $file,
        ]);

        $response->assertRedirect(route('banners.index'));

        $this->assertDatabaseHas('banners', [
            'judul_banner' => 'Diskon Lebaran Super Big Sale',
            'posisi_banner' => 'home_hero',
            'status' => 'Aktif',
        ]);

        $banner = Banner::first();
        Storage::disk('public')->assertExists($banner->gambar_banner);
    }

    public function test_admin_can_update_banner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $banner = Banner::create([
            'judul_banner' => 'Banner Lama',
            'subjudul' => 'Subjudul Lama',
            'gambar_banner' => 'banners/lama.jpg',
            'posisi_banner' => 'shop_header',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->put(route('banners.update', $banner->id_banner), [
            'judul_banner' => 'Banner Baru Diubah',
            'subjudul' => 'Subjudul Baru',
            'posisi_banner' => 'shop_header',
            'link_url' => '/pelanggan/custom-products',
            'urutan' => 2,
            'status' => 'Nonaktif',
        ]);

        $response->assertRedirect(route('banners.index'));

        $this->assertDatabaseHas('banners', [
            'id_banner' => $banner->id_banner,
            'judul_banner' => 'Banner Baru Diubah',
            'status' => 'Nonaktif',
        ]);
    }

    public function test_admin_can_toggle_banner_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $banner = Banner::create([
            'judul_banner' => 'Banner Promo Toggle',
            'gambar_banner' => 'banners/toggle.jpg',
            'posisi_banner' => 'popup_promo',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->post(route('banners.toggle', $banner->id_banner));

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseHas('banners', [
            'id_banner' => $banner->id_banner,
            'status' => 'Nonaktif',
        ]);
    }

    public function test_admin_can_delete_banner(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $banner = Banner::create([
            'judul_banner' => 'Banner Dihapus',
            'gambar_banner' => 'banners/hapus.jpg',
            'posisi_banner' => 'home_hero',
            'status' => 'Aktif',
        ]);

        $response = $this->actingAs($admin)->delete(route('banners.destroy', $banner->id_banner));

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseMissing('banners', [
            'id_banner' => $banner->id_banner,
        ]);
    }

    public function test_admin_can_bulk_delete_banners(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $b1 = Banner::create(['judul_banner' => 'B1', 'gambar_banner' => 'banners/b1.jpg', 'posisi_banner' => 'home_hero', 'status' => 'Aktif']);
        $b2 = Banner::create(['judul_banner' => 'B2', 'gambar_banner' => 'banners/b2.jpg', 'posisi_banner' => 'shop_header', 'status' => 'Aktif']);

        $response = $this->actingAs($admin)->post(route('banners.bulk-delete'), [
            'ids' => [$b1->id_banner, $b2->id_banner],
        ]);

        $response->assertRedirect(route('banners.index'));
        $this->assertDatabaseMissing('banners', ['id_banner' => $b1->id_banner]);
        $this->assertDatabaseMissing('banners', ['id_banner' => $b2->id_banner]);
    }
}

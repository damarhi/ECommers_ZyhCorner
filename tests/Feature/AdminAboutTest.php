<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AboutSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AdminAboutTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin_about_test@gmail.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_access_about_settings_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.about.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelola Halaman About Us');
    }

    public function test_admin_can_update_about_settings_text_content()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.about.update'), [
            'judul_utama' => 'Zyh Corner Pastry & Bakery',
            'subjudul_utama' => 'Kue Kualitas Terbaik sejak 2020',
            'cerita_singkat' => 'Cerita toko baru yang telah diperbarui.',
            'visi' => 'Menjadi toko terpercaya.',
            'misi' => 'Memberikan layanan terbaik.',
            'jam_operasional' => 'Setiap Hari 08:00 - 22:00',
            'alamat_toko' => 'Jl. Merdeka No. 45',
            'telepon' => '081122334455',
            'email' => 'contact@zyhcorner.com',
            'link_maps' => 'https://maps.google.com/?q=zyh',
            'judul_fitur' => 'Fitur Toko Terbaik',
            'judul_delivery' => 'Pengiriman Cepat & Aman',
            'judul_tim' => 'Tim Profesional Kami',
            'judul_testimoni' => 'Kata Pelanggan Kami',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('about_settings', [
            'judul_utama' => 'Zyh Corner Pastry & Bakery',
            'subjudul_utama' => 'Kue Kualitas Terbaik sejak 2020',
            'email' => 'contact@zyhcorner.com',
            'judul_fitur' => 'Fitur Toko Terbaik',
            'judul_delivery' => 'Pengiriman Cepat & Aman',
        ]);
    }

    public function test_admin_can_upload_about_images()
    {
        Storage::fake('public');

        $mainImage = UploadedFile::fake()->image('about_main.jpg');
        $galleryImage = UploadedFile::fake()->image('gallery1.jpg');

        $response = $this->actingAs($this->admin)->post(route('admin.about.update'), [
            'judul_utama' => 'Tentang Kami',
            'gambar_tentang' => $mainImage,
            'foto_galeri_1' => $galleryImage,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $about = AboutSetting::first();
        $this->assertNotNull($about->gambar_tentang);
        $this->assertNotNull($about->foto_galeri_1);
        Storage::disk('public')->assertExists($about->gambar_tentang);
        Storage::disk('public')->assertExists($about->foto_galeri_1);
    }

    public function test_admin_can_crud_team_members()
    {
        Storage::fake('public');

        // Store
        $photo = UploadedFile::fake()->image('chef.jpg');
        $response = $this->actingAs($this->admin)->post(route('admin.about.team.store'), [
            'nama' => 'Budi Pastry',
            'jabatan' => 'Head Baker',
            'foto' => $photo,
            'urutan' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_members', [
            'nama' => 'Budi Pastry',
            'jabatan' => 'Head Baker',
        ]);

        $member = \App\Models\TeamMember::where('nama', 'Budi Pastry')->first();

        // Update
        $response = $this->actingAs($this->admin)->put(route('admin.about.team.update', $member->id), [
            'nama' => 'Budi Pastry Edit',
            'jabatan' => 'Master Chef',
            'urutan' => 2,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('team_members', [
            'id' => $member->id,
            'nama' => 'Budi Pastry Edit',
            'jabatan' => 'Master Chef',
        ]);

        // Delete
        $response = $this->actingAs($this->admin)->delete(route('admin.about.team.destroy', $member->id));
        $response->assertRedirect();
        $this->assertDatabaseMissing('team_members', [
            'id' => $member->id,
        ]);
    }
}

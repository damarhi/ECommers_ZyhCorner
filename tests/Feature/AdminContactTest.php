<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\AboutSetting;

class AdminContactTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_contact_settings_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.contact.index'));
        $response->assertStatus(200);
        $response->assertSee('Kelola Halaman Contact Us');
    }

    public function test_admin_can_update_contact_settings()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.contact.update'), [
            'alamat_toko' => 'Jl. Merdeka No. 45, Pekanbaru',
            'email' => 'admin@zyhcorner.com',
            'email_2' => 'cs@zyhcorner.com',
            'telepon' => '081299998888',
            'telepon_2' => '081122223333',
            'judul_form_kontak' => 'Hubungi Kami Hari Ini!',
            'deskripsi_form_kontak' => 'Silakan kirimkan pertanyaan atau pesanan kustom Anda.',
            'link_maps' => 'https://www.google.com/maps/embed?pb=test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('about_settings', [
            'alamat_toko' => 'Jl. Merdeka No. 45, Pekanbaru',
            'email' => 'admin@zyhcorner.com',
            'email_2' => 'cs@zyhcorner.com',
            'telepon' => '081299998888',
            'telepon_2' => '081122223333',
            'judul_form_kontak' => 'Hubungi Kami Hari Ini!',
        ]);
    }
}

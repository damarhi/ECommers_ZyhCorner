<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Faq;

class AdminFaqTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_access_faq_settings_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.faq.index'));
        $response->assertStatus(200);
        $response->assertSee('Kelola FAQ Pelanggan');
    }

    public function test_admin_can_create_faq()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.faq.store'), [
            'pertanyaan' => 'Apakah bisa custom ukuran kue?',
            'jawaban' => 'Tentu bisa! Silakan pilih menu Custom Products.',
            'kategori' => 'Pemesanan',
            'urutan' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('faqs', [
            'pertanyaan' => 'Apakah bisa custom ukuran kue?',
            'kategori' => 'Pemesanan',
        ]);
    }

    public function test_admin_can_update_faq()
    {
        $faq = Faq::create([
            'pertanyaan' => 'Pertanyaan Lama',
            'jawaban' => 'Jawaban Lama',
            'kategori' => 'Umum',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.faq.update', $faq->id), [
            'pertanyaan' => 'Pertanyaan Baru',
            'jawaban' => 'Jawaban Baru',
            'kategori' => 'Pengiriman',
            'urutan' => 2,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'pertanyaan' => 'Pertanyaan Baru',
            'kategori' => 'Pengiriman',
        ]);
    }

    public function test_admin_can_toggle_faq_status()
    {
        $faq = Faq::create([
            'pertanyaan' => 'Pertanyaan Test',
            'jawaban' => 'Jawaban Test',
            'kategori' => 'Umum',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.faq.toggle', $faq->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_faq()
    {
        $faq = Faq::create([
            'pertanyaan' => 'Pertanyaan Dihapus',
            'jawaban' => 'Jawaban Dihapus',
            'kategori' => 'Umum',
            'urutan' => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.faq.destroy', $faq->id));
        $response->assertRedirect();

        $this->assertDatabaseMissing('faqs', [
            'id' => $faq->id,
        ]);
    }
}

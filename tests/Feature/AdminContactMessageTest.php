<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class);
    }

    public function test_admin_can_access_contact_messages_page_with_stat_cards(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        ContactMessage::create([
            'nama' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'subjek' => 'Tanya Pengiriman Luar Kota',
            'pesan' => 'Apakah melayani pengiriman ke luar kota?',
            'status' => 'Belum Dibaca',
        ]);

        ContactMessage::create([
            'nama' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'subjek' => 'Custom Cake Order',
            'pesan' => 'Ingin pesan custom kue 3 tingkat.',
            'status' => 'Dibaca',
        ]);

        ContactMessage::create([
            'nama' => 'Andi Wijaya',
            'email' => 'andi@example.com',
            'subjek' => 'Konfirmasi Pembayaran',
            'pesan' => 'Sudah bayar via transfer.',
            'status' => 'Dibalas',
        ]);

        $response = $this->actingAs($admin)->get(route('messages.index'));

        $response->assertStatus(200);
        $response->assertSee('Pesan Kontak Pelanggan');

        // Assert presence of redesigned stat-cards
        $response->assertSee('stat-card stat-card-indigo');
        $response->assertSee('stat-card stat-card-coral');
        $response->assertSee('stat-card stat-card-teal');

        // Assert metric titles and counts
        $response->assertSee('Total Pesan Masuk');
        $response->assertSee('Belum Dibaca');
        $response->assertSee('Sudah Dibalas');
        $response->assertSee('Sudah Dibaca (1)');

        // Assert message details in table
        $response->assertSee('Siti Rahma');
        $response->assertSee('Budi Pratama');
        $response->assertSee('Andi Wijaya');
    }

    public function test_admin_can_filter_contact_messages_by_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $msg1 = ContactMessage::create([
            'nama' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'subjek' => 'Tanya Pengiriman',
            'pesan' => 'Pesan 1',
            'status' => 'Belum Dibaca',
        ]);

        $msg2 = ContactMessage::create([
            'nama' => 'Budi Pratama',
            'email' => 'budi@example.com',
            'subjek' => 'Custom Order',
            'pesan' => 'Pesan 2',
            'status' => 'Dibalas',
        ]);

        // Filter: Belum Dibaca
        $response = $this->actingAs($admin)->get(route('messages.index', ['status' => 'Belum Dibaca']));
        $response->assertStatus(200);
        $response->assertSee('id="row-msg-' . $msg1->id . '"', false);
        $response->assertDontSee('id="row-msg-' . $msg2->id . '"', false);

        // Filter: Dibalas
        $responseDibalas = $this->actingAs($admin)->get(route('messages.index', ['status' => 'Dibalas']));
        $responseDibalas->assertStatus(200);
        $responseDibalas->assertSee('id="row-msg-' . $msg2->id . '"', false);
        $responseDibalas->assertDontSee('id="row-msg-' . $msg1->id . '"', false);
    }

    public function test_admin_can_update_message_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $msg = ContactMessage::create([
            'nama' => 'Rina Melati',
            'email' => 'rina@example.com',
            'subjek' => 'Konsultasi Menu',
            'pesan' => 'Apakah ada menu gluten free?',
            'status' => 'Belum Dibaca',
        ]);

        $response = $this->actingAs($admin)->patch(route('messages.update-status', $msg->id), [
            'status' => 'Dibalas',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'id' => $msg->id,
            'status' => 'Dibalas',
        ]);
    }

    public function test_admin_can_delete_message(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $msg = ContactMessage::create([
            'nama' => 'Doni Siregar',
            'email' => 'doni@example.com',
            'subjek' => 'Spam Message',
            'pesan' => 'Penawaran promosi eksternal.',
            'status' => 'Belum Dibaca',
        ]);

        $response = $this->actingAs($admin)->delete(route('messages.destroy', $msg->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('contact_messages', [
            'id' => $msg->id,
        ]);
    }

    public function test_admin_can_bulk_delete_messages(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $msg1 = ContactMessage::create([
            'nama' => 'User 1',
            'email' => 'user1@example.com',
            'subjek' => 'Subjek 1',
            'pesan' => 'Pesan 1',
            'status' => 'Belum Dibaca',
        ]);

        $msg2 = ContactMessage::create([
            'nama' => 'User 2',
            'email' => 'user2@example.com',
            'subjek' => 'Subjek 2',
            'pesan' => 'Pesan 2',
            'status' => 'Dibaca',
        ]);

        $response = $this->actingAs($admin)->post(route('messages.bulk-delete'), [
            'ids' => "{$msg1->id},{$msg2->id}",
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('contact_messages', ['id' => $msg1->id]);
        $this->assertDatabaseMissing('contact_messages', ['id' => $msg2->id]);
    }
}

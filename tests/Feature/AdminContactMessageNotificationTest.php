<?php

namespace Tests\Feature;

use App\Mail\PesanKontakBaruAdminMail;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminContactMessageNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_message_submission_triggers_admin_email_notification(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_test@zyhcorner.com',
        ]);

        $response = $this->post(route('contact.send'), [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'subjek' => 'Pertanyaan Pemesanan Kue Ultah',
            'pesan' => 'Halo, apakah bisa pesan kue ultah tema luar angkasa?',
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'nama' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'subjek' => 'Pertanyaan Pemesanan Kue Ultah',
            'status' => 'Belum Dibaca',
        ]);

        Mail::assertSent(PesanKontakBaruAdminMail::class, function ($mail) use ($admin) {
            return $mail->hasTo($admin->email) &&
                   $mail->messageData->email === 'budi@example.com';
        });
    }

    public function test_contact_message_ajax_submission_triggers_admin_email_notification(): void
    {
        Mail::fake();

        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_ajax@zyhcorner.com',
        ]);

        $response = $this->postJson(route('contact.send'), [
            'nama' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
            'subjek' => 'Kerjasama Catering Acara',
            'pesan' => 'Kami ingin memesan paket snack box 100 pcs untuk kantor.',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('contact_messages', [
            'nama' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
        ]);

        Mail::assertSent(PesanKontakBaruAdminMail::class, function ($mail) {
            return $mail->messageData->email === 'siti@example.com' &&
                   $mail->messageData->subjek === 'Kerjasama Catering Acara';
        });
    }

    public function test_mailable_renders_expected_content_and_subject(): void
    {
        $message = ContactMessage::create([
            'nama' => 'Dewi Lestari',
            'email' => 'dewi@example.com',
            'subjek' => 'Kue Kering Lebaran',
            'pesan' => 'Apakah ada paket hampers kue kering untuk lebaran?',
            'status' => 'Belum Dibaca',
        ]);

        $mailable = new PesanKontakBaruAdminMail($message);
        $mailable->build();

        $this->assertEquals('[Zyh Corner] Pesan Kontak Baru: Kue Kering Lebaran', $mailable->subject);

        $viewData = $mailable->buildViewData();
        $html = view($mailable->view, $viewData)->render();

        $this->assertStringContainsString('Dewi Lestari', $html);
        $this->assertStringContainsString('dewi@example.com', $html);
        $this->assertStringContainsString('Kue Kering Lebaran', $html);
        $this->assertStringContainsString('Apakah ada paket hampers kue kering untuk lebaran?', $html);
        $this->assertStringContainsString(url('admin/messages'), $html);
    }
}

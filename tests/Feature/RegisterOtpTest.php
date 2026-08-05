<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class RegisterOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_form_submission_sends_otp_email_and_redirects_to_verify(): void
    {
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response([
                'messageId' => 'mock-message-id-123',
            ], 200),
        ]);

        config(['services.brevo.api_key' => 'mock-brevo-api-key']);

        $response = $this->post(route('register.post'), [
            'nama' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'no_hp' => '081234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should redirect to verify page
        $response->assertRedirect(route('register.verify'));

        // Account should NOT be created in database yet
        $this->assertDatabaseMissing('users', [
            'email' => 'budi.santoso@example.com',
        ]);

        // User should NOT be logged in yet
        $this->assertFalse(Auth::check());

        // OTP record should exist in database
        $this->assertDatabaseHas('otp_verifications', [
            'email' => 'budi.santoso@example.com',
        ]);

        // Session should store pending registration info
        $this->assertTrue(session()->has('register_pending'));
        $pending = session('register_pending');
        $this->assertEquals('budi.santoso@example.com', $pending['email']);
        $this->assertEquals('Budi Santoso', $pending['nama']);
    }

    public function test_registration_fails_if_otp_email_delivery_fails(): void
    {
        Http::fake([
            'https://api.brevo.com/v3/smtp/email' => Http::response([
                'message' => 'Invalid email recipient address',
            ], 400),
        ]);

        config(['services.brevo.api_key' => 'mock-brevo-api-key']);

        $response = $this->post(route('register.post'), [
            'nama' => 'User Fake Email',
            'email' => 'fake.email.fail@invalid-domain-test.com',
            'no_hp' => '081299998888',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Should redirect back to register with error
        $response->assertSessionHasErrors(['email']);

        // Account should NOT be created
        $this->assertDatabaseMissing('users', [
            'email' => 'fake.email.fail@invalid-domain-test.com',
        ]);

        // OTP record should be deleted/missing
        $this->assertDatabaseMissing('otp_verifications', [
            'email' => 'fake.email.fail@invalid-domain-test.com',
        ]);

        // Session pending registration should be cleared
        $this->assertFalse(session()->has('register_pending'));
    }

    public function test_verifying_correct_otp_creates_user_account_and_logs_in(): void
    {
        // 1. Prepare session and OTP in database
        session([
            'register_pending' => [
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'no_hp' => '081987654321',
                'password' => 'secret123',
            ]
        ]);

        Otp::create([
            'email' => 'dewi.lestari@example.com',
            'otp_code' => 654321,
            'expires_at' => now()->addMinutes(5),
        ]);

        // 2. Submit correct OTP code
        $response = $this->post(route('register.verify.post'), [
            'otp' => '654321',
        ]);

        // 3. Assert redirect to home
        $response->assertRedirect('/');

        // 4. User should now exist in database
        $this->assertDatabaseHas('users', [
            'email' => 'dewi.lestari@example.com',
            'nama' => 'Dewi Lestari',
            'role' => 'pelanggan',
        ]);

        // 5. User should be logged in
        $this->assertTrue(Auth::check());
        $this->assertEquals('dewi.lestari@example.com', Auth::user()->email);

        // 6. OTP record & pending session cleared
        $this->assertDatabaseMissing('otp_verifications', [
            'email' => 'dewi.lestari@example.com',
        ]);
        $this->assertFalse(session()->has('register_pending'));
    }

    public function test_verifying_incorrect_otp_fails(): void
    {
        session([
            'register_pending' => [
                'nama' => 'Dewi Lestari',
                'email' => 'dewi.lestari@example.com',
                'no_hp' => '081987654321',
                'password' => 'secret123',
            ]
        ]);

        Otp::create([
            'email' => 'dewi.lestari@example.com',
            'otp_code' => 654321,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Submit wrong OTP code
        $response = $this->post(route('register.verify.post'), [
            'otp' => '000000',
        ]);

        // Assert session has error on otp
        $response->assertSessionHasErrors(['otp']);

        // Account should NOT exist in database
        $this->assertDatabaseMissing('users', [
            'email' => 'dewi.lestari@example.com',
        ]);

        // User should NOT be logged in
        $this->assertFalse(Auth::check());
    }
}

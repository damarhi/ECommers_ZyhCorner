<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_logs_in_without_otp(): void
    {
        // 1. Create a registered user
        $user = User::factory()->create([
            'email' => 'john.doe@example.com',
            'google_id' => 'google-id-123',
            'role' => 'pelanggan',
        ]);

        // 2. Fake Google Token response, Google Userinfo response
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock-access-token',
            ], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'google-id-123',
                'email' => 'john.doe@example.com',
                'name' => 'John Doe',
            ], 200),
        ]);

        // 3. Make request to callback
        $response = $this->get('/login?code=mock-auth-code');

        // 4. Assert direct authentication and redirect
        $response->assertRedirect('/');
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->user_id, Auth::user()->user_id);

        // 5. Assert NO OTP was generated for this email in otp_verifications table
        $this->assertDatabaseMissing('otp_verifications', [
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_new_user_login_triggers_otp_and_verification_redirect(): void
    {
        // Fake Google and Brevo API responses
        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'mock-access-token',
            ], 200),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'google-id-456',
                'email' => 'jane.doe@example.com',
                'name' => 'Jane Doe',
            ], 200),
            'https://api.brevo.com/v3/smtp/email' => Http::response([
                'messageId' => 'mock-message-id',
            ], 200),
        ]);

        // Mock Brevo API configuration in services
        config(['services.brevo.api_key' => 'mock-api-key']);

        // Make request to callback
        $response = $this->get('/login?code=mock-auth-code');

        // Assert redirect to verification page
        $response->assertRedirect(route('login.google.verify'));
        
        // Assert NOT authenticated yet
        $this->assertFalse(Auth::check());

        // Assert OTP generated in database
        $this->assertDatabaseHas('otp_verifications', [
            'email' => 'jane.doe@example.com',
        ]);

        // Assert session has google_register_pending
        $response->assertSessionHas('google_register_pending');
        $pending = session('google_register_pending');
        $this->assertEquals('jane.doe@example.com', $pending['email']);
        $this->assertEquals('Jane Doe', $pending['nama']);
        $this->assertEquals('google-id-456', $pending['google_id']);
    }
}

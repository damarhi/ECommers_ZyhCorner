<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    /**
     * Send an email using Brevo HTTP API v3.
     * This bypasses SMTP entirely, using HTTPS (port 443) which is never blocked.
     *
     * @param string $toEmail
     * @param string $toName
     * @param string $subject
     * @param string $htmlContent
     * @return bool
     * @throws \Exception
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        $apiKey = config('services.brevo.api_key');

        if (!$apiKey) {
            throw new \Exception('Brevo API Key belum dikonfigurasi. Tambahkan BREVO_API_KEY di file .env');
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('mail.from.name', config('app.name', 'Zyh Corner')),
                'email' => config('mail.from.address', 'damarn.h46@gmail.com'),
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name' => $toName,
                ],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        if ($response->failed()) {
            $error = $response->json();
            $message = $error['message'] ?? 'Unknown Brevo API error';
            Log::error('Brevo API Error', ['status' => $response->status(), 'body' => $error]);
            throw new \Exception('Gagal mengirim email via Brevo: ' . $message);
        }

        Log::info('Email OTP berhasil dikirim via Brevo API', ['to' => $toEmail]);
        return true;
    }

    /**
     * Send OTP verification email using Brevo HTTP API.
     *
     * @param string $email
     * @param int|string $otp
     * @return bool
     * @throws \Exception
     */
    public static function sendOtp(string $email, $otp): bool
    {
        $htmlContent = view('emails.otp', ['otp' => $otp])->render();

        return self::send(
            toEmail: $email,
            toName: explode('@', $email)[0],
            subject: 'Kode Verifikasi Masuk Zyh Corner',
            htmlContent: $htmlContent
        );
    }
}

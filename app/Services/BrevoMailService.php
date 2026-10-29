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
                'name' => config('services.brevo.sender_name', config('mail.from.name', config('app.name', 'Zyh Corner'))),
                'email' => config('services.brevo.sender_email', config('mail.from.address', 'damarn.h46@gmail.com')),
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
     * Send an email to multiple recipients using Brevo HTTP API v3.
     *
     * @param array $recipients Array of email strings
     * @param string $subject
     * @param string $htmlContent
     * @return bool
     * @throws \Exception
     */
    public static function sendRecipients(array $recipients, string $subject, string $htmlContent): bool
    {
        $apiKey = config('services.brevo.api_key');

        if (!$apiKey) {
            throw new \Exception('Brevo API Key belum dikonfigurasi. Tambahkan BREVO_API_KEY di file .env');
        }

        $toPayload = [];
        foreach ($recipients as $email) {
            if ($email) {
                $toPayload[] = [
                    'email' => $email,
                    'name' => explode('@', $email)[0],
                ];
            }
        }

        if (empty($toPayload)) {
            return false;
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name' => config('services.brevo.sender_name', config('mail.from.name', config('app.name', 'Zyh Corner'))),
                'email' => config('services.brevo.sender_email', config('mail.from.address', 'damarn.h46@gmail.com')),
            ],
            'to' => $toPayload,
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        if ($response->failed()) {
            $error = $response->json();
            $message = $error['message'] ?? 'Unknown Brevo API error';
            Log::error('Brevo API Error', ['status' => $response->status(), 'body' => $error]);
            throw new \Exception('Gagal mengirim email via Brevo: ' . $message);
        }

        Log::info('Email berhasil dikirim via Brevo API ke: ' . implode(', ', $recipients));
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
    public static function sendOtp(string $email, $otp, string $subject = 'Kode Verifikasi Zyh Corner'): bool
    {
        $htmlContent = view('emails.otp', ['otp' => $otp])->render();

        return self::send(
            toEmail: $email,
            toName: explode('@', $email)[0],
            subject: $subject,
            htmlContent: $htmlContent
        );
    }
}

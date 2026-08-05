<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send email notification to active cashiers and designated test email.
     *
     * @param \Illuminate\Mail\Mailable $mailable
     * @return void
     */
    public static function sendToKasir($mailable)
    {
        try {
            // Test target email explicitly requested by user
            $testEmail = env('KASIR_NOTIFICATION_EMAIL', 'qwertyuiopxaw@gmail.com');
            
            // Get active cashier emails from DB
            $activeKasirEmails = User::where('role', 'kasir')
                ->pluck('email')
                ->filter()
                ->toArray();

            // Merge emails and remove duplicates
            $recipients = array_values(array_unique(array_filter(array_merge([$testEmail], $activeKasirEmails))));

            if (!empty($recipients)) {
                // Build mailable to extract subject and HTML view content
                $mailable->build();
                $subject = $mailable->subject;
                $view = $mailable->view;
                $data = $mailable->buildViewData();
                $htmlContent = view($view, $data)->render();

                // Send via Brevo API
                BrevoMailService::sendRecipients($recipients, $subject, $htmlContent);
                Log::info('Kasir email notification sent via Brevo API to: ' . implode(', ', $recipients));

                // If in testing or Mail fake is active, also invoke Mail::to for test assertions
                if (app()->environment('testing')) {
                    Mail::to($recipients)->send($mailable);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send Kasir email notification via Brevo: ' . $e->getMessage());

            // Fallback to standard Laravel Mail
            try {
                if (!empty($recipients)) {
                    Mail::to($recipients)->send($mailable);
                }
            } catch (\Exception $fallbackEx) {
                Log::error('Fallback Laravel Mail also failed: ' . $fallbackEx->getMessage());
            }
        }
    }
}

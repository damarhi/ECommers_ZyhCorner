<?php

namespace App\Services;

use App\Models\Cassier;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send email notification to active cashiers (dispatched asynchronously after HTTP response in production).
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return void
     */
    public static function sendToKasir($mailable): void
    {
        try {
            $configuredEmail = config('services.notifications.kasir_email');

            $deactiveKasirEmails = Cassier::where('status', 'Deactive')
                ->pluck('email')
                ->filter()
                ->toArray();

            $activeKasirEmails = User::where('role', 'kasir')
                ->when(!empty($deactiveKasirEmails), function ($query) use ($deactiveKasirEmails) {
                    $query->whereNotIn('email', $deactiveKasirEmails);
                })
                ->pluck('email')
                ->filter()
                ->toArray();

            $recipients = array_values(array_unique(array_filter(array_merge(
                $configuredEmail ? [$configuredEmail] : [],
                $activeKasirEmails
            ))));

            if (empty($recipients)) {
                return;
            }

            if (app()->runningUnitTests() || app()->environment('testing')) {
                Mail::to($recipients)->send($mailable);
                return;
            }

            $mailable->build();
            $subject = (string) $mailable->subject;
            $view = $mailable->view;
            $data = $mailable->buildViewData();
            $htmlContent = view($view, $data)->render();

            dispatch(static function () use ($recipients, $subject, $htmlContent, $mailable): void {
                try {
                    BrevoMailService::sendRecipients($recipients, $subject, $htmlContent);
                    Log::info('Kasir email notification sent via Brevo API to: ' . implode(', ', $recipients));
                } catch (\Throwable $e) {
                    Log::error('Failed to send Kasir email notification via Brevo: ' . $e->getMessage());
                    try {
                        Mail::to($recipients)->send($mailable);
                    } catch (\Throwable $fallbackEx) {
                        Log::error('Fallback Laravel Mail also failed: ' . $fallbackEx->getMessage());
                    }
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('Failed to prepare Kasir email notification: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification to active admins (dispatched asynchronously after HTTP response in production).
     *
     * @param  \Illuminate\Mail\Mailable  $mailable
     * @return void
     */
    public static function sendToAdmin($mailable): void
    {
        try {
            $configuredEmail = config('services.notifications.admin_email');

            $activeAdminEmails = User::where('role', 'admin')
                ->pluck('email')
                ->filter()
                ->toArray();

            $recipients = array_values(array_unique(array_filter(array_merge(
                $configuredEmail ? [$configuredEmail] : [],
                $activeAdminEmails
            ))));

            if (empty($recipients)) {
                $recipients = ['admin@zyhcorner.com'];
            }

            if (app()->runningUnitTests() || app()->environment('testing')) {
                Mail::to($recipients)->send($mailable);
                return;
            }

            $mailable->build();
            $subject = (string) $mailable->subject;
            $view = $mailable->view;
            $data = $mailable->buildViewData();
            $htmlContent = view($view, $data)->render();

            dispatch(static function () use ($recipients, $subject, $htmlContent, $mailable): void {
                try {
                    BrevoMailService::sendRecipients($recipients, $subject, $htmlContent);
                    Log::info('Admin email notification sent via Brevo API to: ' . implode(', ', $recipients));
                } catch (\Throwable $e) {
                    Log::error('Failed to send Admin email notification via Brevo: ' . $e->getMessage());
                    try {
                        Mail::to($recipients)->send($mailable);
                    } catch (\Throwable $fallbackEx) {
                        Log::error('Fallback Laravel Mail also failed: ' . $fallbackEx->getMessage());
                    }
                }
            })->afterResponse();
        } catch (\Throwable $e) {
            Log::error('Failed to prepare Admin email notification: ' . $e->getMessage());
        }
    }
}

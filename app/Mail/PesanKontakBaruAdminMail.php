<?php

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PesanKontakBaruAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public $messageData;

    /**
     * Create a new message instance.
     *
     * @param ContactMessage $messageData
     */
    public function __construct(ContactMessage $messageData)
    {
        $this->messageData = $messageData;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[Zyh Corner] Pesan Kontak Baru: ' . $this->messageData->subjek)
                    ->view('emails.pesan_kontak_baru_admin');
    }
}

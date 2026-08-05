<?php

namespace App\Mail;

use App\Models\Pesanan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PesananBaruKasirMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pesanan;

    /**
     * Create a new message instance.
     */
    public function __construct(Pesanan $pesanan)
    {
        $this->pesanan = $pesanan;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('[Zyh Corner] Notifikasi Pesanan Baru - ' . $this->pesanan->order_number)
                    ->view('emails.pesanan_baru_kasir');
    }
}

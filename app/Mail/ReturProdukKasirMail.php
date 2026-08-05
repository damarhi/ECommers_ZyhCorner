<?php

namespace App\Mail;

use App\Models\Retur;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReturProdukKasirMail extends Mailable
{
    use Queueable, SerializesModels;

    public $retur;

    /**
     * Create a new message instance.
     */
    public function __construct(Retur $retur)
    {
        $this->retur = $retur;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $orderNum = $this->retur->pesanan->order_number ?? '#' . $this->retur->id_pesanan;
        return $this->subject('[Zyh Corner] Pengajuan Retur Produk Baru - ' . $orderNum)
                    ->view('emails.retur_produk_kasir');
    }
}

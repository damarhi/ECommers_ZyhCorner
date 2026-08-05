<?php

namespace App\Mail;

use App\Models\ProdukKadaluarsa;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProdukKadaluarsaKasirMail extends Mailable
{
    use Queueable, SerializesModels;

    public $produkKadaluarsa;

    /**
     * Create a new message instance.
     */
    public function __construct(ProdukKadaluarsa $produkKadaluarsa)
    {
        $this->produkKadaluarsa = $produkKadaluarsa;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $namaProduk = $this->produkKadaluarsa->produk->nama_produk ?? 'Produk';
        return $this->subject('[Zyh Corner] PERINGATAN: Produk Kadaluarsa - ' . $namaProduk)
                    ->view('emails.produk_kadaluarsa_kasir');
    }
}

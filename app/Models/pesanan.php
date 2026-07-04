<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $fillable = [
        'order_number',
        'jenis_penjualan',
        'payment_method',
        'shipping_method',
        'customer_name',
        'email_pembeli',
        'alamat_pengiriman',
        'catatan_pesanan',
        'payment_status',
        'snap_token',
        'order_status',
        'total',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}

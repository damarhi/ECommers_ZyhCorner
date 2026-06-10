<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $fillable = [
        'order_number',
        'jenis_penjualan',
        'customer_name',
        'email_pembeli',
        'payment_status',
        'order_status',
        'total',
    ];
}

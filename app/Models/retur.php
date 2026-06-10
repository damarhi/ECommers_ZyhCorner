<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Retur extends Model
{
    protected $fillable = [
        'customer_name', 'order_id', 'reason', 'return_date', 'product_name', 'status'
    ];
}

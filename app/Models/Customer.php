<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'location', 'orders_count', 'spent', 'contact', 'status'];
}

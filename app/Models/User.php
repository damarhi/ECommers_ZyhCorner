<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'password',
        'role',
        'google_id',
        'alamat',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'user_id');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class, 'user_id', 'user_id')->orderByDesc('is_default')->orderByDesc('id');
    }

    public function defaultAddress()
    {
        return $this->hasOne(UserAddress::class, 'user_id', 'user_id')->where('is_default', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $attributes = [
        'role' => 'pelanggan',
    ];

    protected $fillable = [
        'nama',
        'email',
        'no_hp',
        'password',
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

    public function getFotoAttribute(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        return preg_replace('#^storage/#', '', (string) $this->avatar);
    }

    public function isBlocked(): bool
    {
        if ($this->role === 'kasir') {
            return Cassier::where('email', $this->email)
                ->where('status', 'Deactive')
                ->exists();
        }

        if ($this->role !== 'pelanggan') {
            return false;
        }

        $customer = $this->customer()->first();
        if ($customer && $customer->status === 'Blocked') {
            return true;
        }

        if (!empty($this->no_hp)) {
            $variants = Customer::phoneVariants($this->no_hp);
            if (!empty($variants)) {
                $isBlockedByPhone = Customer::whereNull('user_id')
                    ->whereIn('contact', $variants)
                    ->where('status', 'Blocked')
                    ->exists();

                if ($isBlockedByPhone) {
                    return true;
                }
            }
        }

        return false;
    }
}

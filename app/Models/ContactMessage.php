<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $table = 'contact_messages';

    protected $fillable = [
        'nama',
        'email',
        'subjek',
        'pesan',
        'status',
    ];

    /**
     * Scope for unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'Belum Dibaca');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_banner';

    protected $fillable = [
        'judul_banner',
        'subjudul',
        'gambar_banner',
        'posisi_banner',
        'link_url',
        'urutan',
        'status',
    ];

    public function getPosisiLabelAttribute()
    {
        $labels = [
            'home_hero' => 'Home Hero Banner',
            'shop_header' => 'Header Produk Showcase',
            'custom_header' => 'Header Produk Kustom',
            'popup_promo' => 'Pop-up Promo',
        ];

        return $labels[$this->posisi_banner] ?? ucfirst($this->posisi_banner);
    }

    public function getLinkUrlAttribute($value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $trimmed = trim((string) $value);
        if ((str_starts_with($trimmed, '/') && !str_starts_with($trimmed, '//'))
            || preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        return null;
    }
}

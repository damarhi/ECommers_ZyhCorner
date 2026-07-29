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
}

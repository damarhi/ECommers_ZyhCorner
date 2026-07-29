<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutSetting extends Model
{
    use HasFactory;

    protected $table = 'about_settings';
    protected $primaryKey = 'id_about';

    protected $fillable = [
        'judul_utama',
        'subjudul_utama',
        'gambar_tentang',
        'cerita_singkat',
        'visi',
        'misi',
        'jam_operasional',
        'alamat_toko',
        'telepon',
        'telepon_2',
        'email',
        'email_2',
        'link_maps',
        'judul_form_kontak',
        'deskripsi_form_kontak',
        'foto_galeri_1',
        'foto_galeri_2',
        'foto_galeri_3',
        'judul_fitur',
        'deskripsi_fitur',
        'judul_delivery',
        'deskripsi_delivery',
        'poin_delivery_1',
        'poin_delivery_2',
        'poin_delivery_3',
        'judul_tim',
        'deskripsi_tim',
        'judul_testimoni',
        'deskripsi_testimoni',
    ];
}

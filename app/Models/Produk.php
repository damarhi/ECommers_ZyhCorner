<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_produk';

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'harga',
        'status_produk',
        'deskripsi',
        'tanggal_expired',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
    ];

    protected $appends = ['stok'];

    public function getStokAttribute()
    {
        $val = $this->status_produk;
        if (is_numeric($val)) {
            return (int)$val;
        }
        if ($val && strtolower($val) === 'tersedia') {
            return 99;
        }
        return 0;
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}

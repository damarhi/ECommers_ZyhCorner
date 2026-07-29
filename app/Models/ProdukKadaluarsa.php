<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukKadaluarsa extends Model
{
    use HasFactory;

    protected $table = 'produk_kadaluarsas';
    protected $primaryKey = 'id_expired';

    protected $fillable = [
        'id_produk',
        'tanggal_expired',
        'jumlah_stok',
        'status_tindakan',
        'catatan',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }
}

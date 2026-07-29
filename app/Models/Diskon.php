<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diskon extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_diskon';

    protected $fillable = [
        'id_produk',
        'nama_diskon',
        'tipe_diskon',
        'nilai_diskon',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id_produk');
    }

    public function getHargaSetelahDiskonAttribute()
    {
        if (!$this->produk) {
            return 0;
        }

        $hargaAwal = $this->produk->harga;
        if ($this->tipe_diskon === 'persen') {
            $potongan = ($hargaAwal * $this->nilai_diskon) / 100;
        } else {
            $potongan = $this->nilai_diskon;
        }

        $hargaAkhir = $hargaAwal - $potongan;
        return max(0, $hargaAkhir);
    }

    public function getNominalPotonganAttribute()
    {
        if (!$this->produk) {
            return 0;
        }

        $hargaAwal = $this->produk->harga;
        if ($this->tipe_diskon === 'persen') {
            return ($hargaAwal * $this->nilai_diskon) / 100;
        }
        return $this->nilai_diskon;
    }
}

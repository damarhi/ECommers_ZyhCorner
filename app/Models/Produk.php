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
        'jenis_produk',
        'nama_produk',
        'harga',
        'harga_max',
        'status_produk',
        'deskripsi',
        'tanggal_expired',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
    ];

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

    /**
     * Reduce product stock quantity by specified amount.
     *
     * @param int $qty
     * @return void
     */
    public function reduceStock(int $qty = 1)
    {
        $currentStok = $this->stok;
        $newStok = max(0, $currentStok - $qty);
        $this->status_produk = (string) $newStok;
        $this->save();
    }

    /**
     * Reduce stock for all items in an order.
     *
     * @param \App\Models\Pesanan $pesanan
     * @return void
     */
    public static function reduceStockForOrder($pesanan)
    {
        if (!$pesanan || empty($pesanan->items) || !is_array($pesanan->items)) {
            return;
        }

        foreach ($pesanan->items as $item) {
            $productId = $item['id'] ?? $item['id_produk'] ?? null;
            $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);

            if ($productId && $qty > 0) {
                $produk = self::find($productId);
                if ($produk) {
                    $produk->reduceStock($qty);
                }
            }
        }
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function kadaluarsa()
    {
        return $this->hasOne(ProdukKadaluarsa::class, 'id_produk', 'id_produk');
    }

    public static function checkAndEmptyExpiredProducts()
    {
        $today = \Carbon\Carbon::today()->toDateString();

        $expiredProducts = self::where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            })
            ->whereNotNull('tanggal_expired')
            ->where('tanggal_expired', '<=', $today)
            ->get();

        foreach ($expiredProducts as $produk) {
            $stokAwal = $produk->status_produk ?? 'Tersedia';

            // Record in dataset table produk_kadaluarsas
            $expiredRecord = ProdukKadaluarsa::firstOrCreate(
                ['id_produk' => $produk->id_produk],
                [
                    'tanggal_expired' => $produk->tanggal_expired,
                    'jumlah_stok' => $stokAwal,
                    'status_tindakan' => 'Perlu Tindakan',
                    'catatan' => 'Otomatis masuk dataset produk kadaluarsa & stok dikosongkan.',
                ]
            );

            if ($expiredRecord->wasRecentlyCreated) {
                \App\Services\NotificationService::sendToKasir(new \App\Mail\ProdukKadaluarsaKasirMail($expiredRecord));
            }

            // Empty the stock on the showcase product
            if ($produk->status_produk !== '0' && strtolower((string)$produk->status_produk) !== 'habis') {
                $produk->status_produk = '0';
                $produk->save();
            }
        }
    }
}

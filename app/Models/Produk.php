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
        'bisa_custom_teks',
        'foto_1',
        'foto_2',
        'foto_3',
        'foto_4',
    ];

    protected $casts = [
        'bisa_custom_teks' => 'boolean',
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
    /**
     * Add stock back to the product.
     *
     * @param int $qty
     * @return void
     */
    public function addStock(int $qty = 1)
    {
        $currentStok = $this->stok;
        $newStok = $currentStok + $qty;
        $this->status_produk = (string) $newStok;
        $this->save();
    }

    /**
     * Restore stock for all items in an order.
     *
     * @param \App\Models\Pesanan $pesanan
     * @return void
     */
    public static function restoreStockForOrder($pesanan)
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
                    $produk->addStock($qty);
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

    public function diskons()
    {
        return $this->hasMany(Diskon::class, 'id_produk', 'id_produk');
    }

    public function diskonAktif()
    {
        $today = \Carbon\Carbon::today()->toDateString();
        return $this->hasOne(Diskon::class, 'id_produk', 'id_produk')
            ->where('status', 'Aktif')
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_mulai')
                  ->orWhere('tanggal_mulai', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', $today);
            });
    }

    public function getHargaDiskonAttribute()
    {
        if ($this->diskonAktif) {
            return $this->diskonAktif->harga_setelah_diskon;
        }
        return (float) $this->harga;
    }

    public function getHasDiskonAttribute()
    {
        return $this->diskonAktif !== null;
    }

    public function getDiskonBadgeAttribute()
    {
        if (!$this->diskonAktif) {
            return null;
        }
        $diskon = $this->diskonAktif;
        if ($diskon->tipe_diskon === 'persen') {
            return (int) $diskon->nilai_diskon . '% OFF';
        }
        return 'Hemat Rp ' . number_format($diskon->nilai_diskon, 0, ',', '.');
    }

    public static function getBestSellers(int $limit = 3)
    {
        $productSales = [];
        $orders = Pesanan::where('payment_status', 'Paid')
            ->whereNotNull('items')
            ->get(['items']);

        foreach ($orders as $order) {
            if (is_array($order->items)) {
                foreach ($order->items as $item) {
                    $pid = $item['id'] ?? $item['id_produk'] ?? null;
                    $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
                    if ($pid) {
                        $productSales[$pid] = ($productSales[$pid] ?? 0) + $qty;
                    }
                }
            }
        }

        $topProducts = collect();
        if (!empty($productSales)) {
            arsort($productSales);
            $topIds = array_slice(array_keys($productSales), 0, $limit);
            $topProducts = self::with(['kategori', 'diskonAktif'])
                ->whereIn('id_produk', $topIds)
                ->get()
                ->sortBy(function ($p) use ($productSales) {
                    return -($productSales[$p->id_produk] ?? 0);
                })
                ->values();

            if ($topProducts->count() >= $limit) {
                return $topProducts;
            }
        }

        $excludeIds = $topProducts->pluck('id_produk')->toArray();
        $fallback = self::with(['kategori', 'diskonAktif'])
            ->whereNotIn('id_produk', $excludeIds)
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            })
            ->orderBy('id_produk', 'asc')
            ->limit($limit - $topProducts->count())
            ->get();

        return $topProducts->concat($fallback);
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
            // Check if showcase product stock is not already emptied to '0' or 'habis'
            if ($produk->status_produk !== '0' && strtolower((string)$produk->status_produk) !== 'habis') {
                $stokAwal = $produk->status_produk ?? 'Tersedia';

                // Create a BRAND NEW record in produk_kadaluarsas table (distinct id_expired)
                $expiredRecord = ProdukKadaluarsa::create([
                    'id_produk' => $produk->id_produk,
                    'tanggal_expired' => $produk->tanggal_expired,
                    'jumlah_stok' => $stokAwal,
                    'status_tindakan' => 'Perlu Tindakan',
                    'catatan' => 'Otomatis masuk dataset produk kadaluarsa & stok dikosongkan.',
                ]);

                \App\Services\NotificationService::sendToKasir(new \App\Mail\ProdukKadaluarsaKasirMail($expiredRecord));

                // Empty the stock on the showcase product
                $produk->status_produk = '0';
                $produk->save();
            }
        }
    }
}

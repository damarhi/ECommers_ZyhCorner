<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Produk extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_produk';

    protected static function booted(): void
    {
        $invalidate = static function (): void {
            Cache::forget('produk_stock_map');
            Cache::forget('produk_expired_sync_lock');
            foreach ([3, 4, 6, 8, 12] as $lim) {
                Cache::forget("produk_best_sellers_{$lim}");
            }
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }

    public static function getCachedStockMap(): array
    {
        return Cache::remember('produk_stock_map', 60, function () {
            return self::select(['id_produk', 'status_produk'])
                ->get()
                ->pluck('stok', 'id_produk')
                ->toArray();
        });
    }

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
        'is_signature',
    ];

    protected $casts = [
        'bisa_custom_teks' => 'boolean',
        'is_signature' => 'boolean',
    ];

    public function setTanggalExpiredAttribute($value)
    {
        $this->attributes['tanggal_expired'] = !empty($value) ? \Carbon\Carbon::parse($value)->format('Y-m-d') : null;
    }
    public function getStokAttribute()
    {
        $val = $this->status_produk;
        if (is_numeric($val)) {
            return (int)$val;
        }
        if ($val && strtolower($val) === 'tersedia') {
            return 99;
        }
        if (is_string($val) && preg_match('/^\s*(\d+)/', $val, $m)) {
            return (int) $m[1];
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
     * Reduce stock atomically (with row-level lock) for all items in an order.
     *
     * @param \App\Models\Pesanan $pesanan
     * @return void
     */
    public static function reduceStockForOrder($pesanan)
    {
        if (!$pesanan || empty($pesanan->items) || !is_array($pesanan->items)) {
            return;
        }

        $aggregated = [];
        foreach ($pesanan->items as $item) {
            $productId = $item['id'] ?? $item['id_produk'] ?? null;
            $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
            if ($productId && $qty > 0) {
                $aggregated[(int) $productId] = ($aggregated[(int) $productId] ?? 0) + $qty;
            }
        }

        if (empty($aggregated)) {
            return;
        }

        DB::transaction(function () use ($aggregated) {
            ksort($aggregated);
            foreach ($aggregated as $productId => $qty) {
                $produk = self::where('id_produk', $productId)->lockForUpdate()->first();
                if (!$produk) {
                    throw new \DomainException('Produk tidak ditemukan!');
                }
                if ($produk->stok < $qty) {
                    throw new \DomainException('Stok produk "' . $produk->nama_produk . '" tidak mencukupi (tersisa ' . $produk->stok . ').');
                }
                $produk->reduceStock($qty);
            }
        });
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
     * Idempotently restore stock for all items in an order (guarded by stock_restored_at + row lock).
     *
     * @param \App\Models\Pesanan $pesanan
     * @return void
     */
    public static function restoreStockForOrder($pesanan)
    {
        if (!$pesanan || empty($pesanan->items) || !is_array($pesanan->items)) {
            return;
        }

        DB::transaction(function () use ($pesanan) {
            $lockedOrder = null;
            if (!empty($pesanan->id)) {
                $lockedOrder = Pesanan::where('id', $pesanan->id)->lockForUpdate()->first();
                if (!$lockedOrder || $lockedOrder->stock_restored_at !== null) {
                    return;
                }
            } elseif ($pesanan->stock_restored_at !== null) {
                return;
            }

            $aggregated = [];
            foreach ($pesanan->items as $item) {
                $productId = $item['id'] ?? $item['id_produk'] ?? null;
                $qty = (int) ($item['qty'] ?? $item['quantity'] ?? 1);
                if ($productId && $qty > 0) {
                    $aggregated[(int) $productId] = ($aggregated[(int) $productId] ?? 0) + $qty;
                }
            }

            ksort($aggregated);
            foreach ($aggregated as $productId => $qty) {
                $produk = self::where('id_produk', $productId)->lockForUpdate()->first();
                if ($produk) {
                    $produk->addStock($qty);
                }
            }

            $now = now();
            if ($lockedOrder) {
                $lockedOrder->forceFill(['stock_restored_at' => $now])->save();
            }
            $pesanan->stock_restored_at = $now;
        });
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
            })
            ->orderByDesc('id_diskon');
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
        $computeBestSellerIds = static function () use ($limit): array {
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
                            $productSales[(int) $pid] = ($productSales[(int) $pid] ?? 0) + $qty;
                        }
                    }
                }
            }

            $orderedIds = [];
            if (!empty($productSales)) {
                arsort($productSales);
                $candidateIds = array_slice(array_keys($productSales), 0, $limit);
                $existingIds = self::whereIn('id_produk', $candidateIds)
                    ->pluck('id_produk')
                    ->map(fn ($id) => (int) $id)
                    ->all();
                $existingLookup = array_flip($existingIds);

                foreach ($candidateIds as $cid) {
                    if (isset($existingLookup[(int) $cid])) {
                        $orderedIds[] = (int) $cid;
                    }
                }

                if (count($orderedIds) >= $limit) {
                    return array_slice($orderedIds, 0, $limit);
                }
            }

            $fallbackIds = self::whereNotIn('id_produk', $orderedIds)
                ->where(function ($q) {
                    $q->where('jenis_produk', 'showcase')
                      ->orWhereNull('jenis_produk');
                })
                ->orderBy('id_produk', 'asc')
                ->limit($limit - count($orderedIds))
                ->pluck('id_produk')
                ->map(fn ($id) => (int) $id)
                ->all();

            return array_values(array_merge($orderedIds, $fallbackIds));
        };

        $cacheKey = "produk_best_sellers_{$limit}";
        $orderedIds = Cache::remember($cacheKey, 300, $computeBestSellerIds);

        if (!is_array($orderedIds)) {
            Cache::forget($cacheKey);
            $orderedIds = Cache::remember($cacheKey, 300, $computeBestSellerIds);
        }

        if (empty($orderedIds)) {
            return collect();
        }

        $productsById = self::with(['kategori', 'diskonAktif'])
            ->whereIn('id_produk', $orderedIds)
            ->get()
            ->keyBy('id_produk');

        $orderedProducts = collect();
        foreach ($orderedIds as $id) {
            if ($productsById->has($id)) {
                $orderedProducts->push($productsById->get($id));
            }
        }

        return $orderedProducts;
    }

    public static function checkAndEmptyExpiredProducts()
    {
        if (Cache::has('produk_expired_sync_lock')) {
            return;
        }
        Cache::put('produk_expired_sync_lock', true, 60);

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
                $stokAwal = (string) $produk->stok;

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

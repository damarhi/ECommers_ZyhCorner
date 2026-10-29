<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\ContactMessage;
use App\Models\Kategori;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use App\Mail\PesanKontakBaruAdminMail;
use Illuminate\Support\Facades\Log;

class PelangganController extends Controller
{
    public function home()
    {
        Produk::checkAndEmptyExpiredProducts();

        $featuredProducts = Produk::with(['kategori', 'diskonAktif'])
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            })
            ->where('status_produk', '!=', '0')
            ->orderBy('id_produk', 'asc')
            ->limit(4)
            ->get();

        // Hot Deals: Real active discounted products from database
        $hotDeals = Produk::with(['kategori', 'diskonAktif'])
            ->whereHas('diskonAktif')
            ->limit(3)
            ->get();

        if ($hotDeals->count() < 3) {
            $excludeIds = $hotDeals->pluck('id_produk')->toArray();
            $extra = Produk::with(['kategori', 'diskonAktif'])
                ->whereNotIn('id_produk', $excludeIds)
                ->where(function($q) {
                    $q->where('jenis_produk', 'showcase')
                      ->orWhereNull('jenis_produk');
                })
                ->limit(3 - $hotDeals->count())
                ->get();
            $hotDeals = $hotDeals->concat($extra);
        }

        // Best Sellers: Real sales aggregated from completed/paid order items
        $bestSellers = Produk::getBestSellers(3);

        // Top Rated / Recommended
        $topRated = Produk::with(['kategori', 'diskonAktif'])
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            })
            ->orderBy('id_produk', 'desc')
            ->limit(3)
            ->get();

        $categories = Kategori::withCount('produks')->get();
        $newestProducts = Produk::with(['kategori', 'diskonAktif'])
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            })
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();

        // Home Hero Banner Slideshow (Dynamic from database)
        $heroBanners = Banner::where('posisi_banner', 'home_hero')
            ->where('status', 'Aktif')
            ->orderBy('urutan', 'asc')
            ->get();

        $promoBanners = Banner::where('posisi_banner', 'popup_promo')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
        $popupBanner = $promoBanners->first();

        return view('Pelanggan.home', compact(
            'featuredProducts',
            'hotDeals',
            'bestSellers',
            'topRated',
            'categories',
            'newestProducts',
            'heroBanners',
            'promoBanners',
            'popupBanner'
        ));
    }

    public function shop(Request $request)
    {
        Produk::checkAndEmptyExpiredProducts();

        $query = Produk::with(['kategori', 'diskonAktif'])
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            });

        // Filter by Search Query
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $search . '%')
                  ->orWhereHas('kategori', function($catQuery) use ($search) {
                      $catQuery->where('nama_kategori', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('id_kategori', $request->input('category'));
        }

        // Filter by Price Range
        if ($request->filled('price')) {
            $priceRange = explode('-', $request->input('price'));
            if (count($priceRange) === 2) {
                $minPrice = intval($priceRange[0]) * 1000;
                $maxPrice = intval($priceRange[1]) * 1000;
                $query->whereBetween('harga', [$minPrice, $maxPrice]);
            }
        }

        // Sorting (Prioritaskan produk yang memiliki stok)
        $query->orderByRaw("CASE 
            WHEN LOWER(status_produk) = 'tersedia' THEN 1 
            WHEN status_produk IS NOT NULL AND status_produk != '' AND status_produk != '0' AND LOWER(status_produk) != 'habis' THEN 1 
            ELSE 0 
        END DESC");

        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'price_asc') {
            $query->orderBy('harga', 'asc');
        } elseif ($sortBy === 'price_desc') {
            $query->orderBy('harga', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination page size
        $perPage = intval($request->input('show', 16));
        if (!in_array($perPage, [16, 32])) {
            $perPage = 16;
        }

        $products = $query->paginate($perPage)->withQueryString();
        $categories = Kategori::all();

        $shopBanners = Banner::where('posisi_banner', 'shop_header')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
        $shopBanner = $shopBanners->first();

        return view('Pelanggan.shop', compact('products', 'categories', 'shopBanners', 'shopBanner'));
    }

    public function customProducts(Request $request)
    {
        Produk::checkAndEmptyExpiredProducts();

        $query = Produk::with('kategori')
            ->where('jenis_produk', 'kustom');

        // Filter by Search Query
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
                  ->orWhere('deskripsi', 'like', '%' . $search . '%')
                  ->orWhereHas('kategori', function($catQuery) use ($search) {
                      $catQuery->where('nama_kategori', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('id_kategori', $request->input('category'));
        }

        // Filter by Price Range
        if ($request->filled('price')) {
            $priceRange = explode('-', $request->input('price'));
            if (count($priceRange) === 2) {
                $minPrice = intval($priceRange[0]) * 1000;
                $maxPrice = intval($priceRange[1]) * 1000;
                $query->whereBetween('harga', [$minPrice, $maxPrice]);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'latest');
        if ($sortBy === 'price_asc') {
            $query->orderBy('harga', 'asc');
        } elseif ($sortBy === 'price_desc') {
            $query->orderBy('harga', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination page size
        $perPage = intval($request->input('show', 16));
        if (!in_array($perPage, [16, 32])) {
            $perPage = 16;
        }

        $products = $query->paginate($perPage)->withQueryString();
        $categories = Kategori::all();
        $customBanners = Banner::where('posisi_banner', 'custom_header')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
        $customBanner = $customBanners->first();

        return view('Pelanggan.custom_products', compact('products', 'categories', 'customBanners', 'customBanner'));
    }

    public function about()
    {
        return view('Pelanggan.about');
    }

    public function contact()
    {
        return view('Pelanggan.contact');
    }

    public function sendContactMessage(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subjek' => 'required|string|max:255',
            'pesan' => 'required|string|max:3000',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'subjek.required' => 'Subjek pesan wajib diisi.',
            'pesan.required' => 'Isi pesan wajib diisi.',
            'pesan.max' => 'Pesan maksimal 3000 karakter.',
        ]);

        $message = ContactMessage::create([
            'nama' => strip_tags($validated['nama']),
            'email' => filter_var($validated['email'], FILTER_SANITIZE_EMAIL),
            'subjek' => strip_tags($validated['subjek']),
            'pesan' => strip_tags($validated['pesan']),
            'status' => 'Belum Dibaca',
        ]);

        try {
            NotificationService::sendToAdmin(new PesanKontakBaruAdminMail($message));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim notifikasi email admin untuk pesan kontak: ' . $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Terima kasih atas pesan Anda! Tim Zyh Corner akan segera merespons melalui email.',
                'data' => $message,
            ]);
        }

        return back()->with('success', 'Terima kasih atas pesan Anda! Tim Zyh Corner akan segera merespons melalui email.');
    }

    public function faq()
    {
        return view('Pelanggan.faq');
    }

    /**
     * @param  int|string  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function detail($id)
    {
        Produk::checkAndEmptyExpiredProducts();

        $product = Produk::with(['kategori', 'diskonAktif'])->findOrFail($id);

        $isCustom = request()->query('custom') == 1 || ($product->jenis_produk ?? '') === 'kustom';

        $relatedProducts = Produk::with(['kategori', 'diskonAktif'])
            ->where('id_kategori', $product->id_kategori)
            ->where('id_produk', '!=', $product->id_produk)
            ->when($isCustom, function ($q) {
                $q->where('jenis_produk', 'kustom');
            }, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('jenis_produk', 'showcase')
                       ->orWhereNull('jenis_produk');
                });
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        if ($relatedProducts->count() < 4) {
            $excludeIds = array_merge([$product->id_produk], $relatedProducts->pluck('id_produk')->toArray());
            $extra = Produk::with(['kategori', 'diskonAktif'])
                ->whereNotIn('id_produk', $excludeIds)
                ->when($isCustom, function ($q) {
                    $q->where('jenis_produk', 'kustom');
                }, function ($q) {
                    $q->where(function ($sq) {
                        $sq->where('jenis_produk', 'showcase')
                           ->orWhereNull('jenis_produk');
                    });
                })
                ->inRandomOrder()
                ->limit(4 - $relatedProducts->count())
                ->get();

            $relatedProducts = $relatedProducts->concat($extra);
        }

        $unpaidOrder = null;
        if (Auth::check()) {
            // Cancel expired orders older than 24 hours
            $user = Auth::user();
            $expiredOrders = Pesanan::where('email_pembeli', $user->email)
                ->whereIn('order_status', ['menunggu pembayaran', 'menunggu', 'pending'])
                ->where('payment_status', '!=', 'Paid')
                ->where('created_at', '<', now()->subHours(24))
                ->get();
                
            foreach ($expiredOrders as $order) {
                $order->update([
                    'order_status' => 'batal',
                    'payment_status' => 'Cancelled'
                ]);
            }

            $orders = Pesanan::where('email_pembeli', $user->email)
                ->whereIn('order_status', ['menunggu pembayaran', 'menunggu', 'pending'])
                ->where('payment_status', '!=', 'Paid')
                ->orderBy('created_at', 'desc')
                ->get();

            $pembayaranCtrl = app(\App\Http\Controllers\Pelanggan\PembayaranController::class);
            foreach ($orders as $o) {
                $items = $o->items;
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (isset($item['id']) && $item['id'] == $id) {
                            $isPaid = $pembayaranCtrl->verifyAndSyncOrderPayment($o);
                            if (!$isPaid && $o->fresh()->payment_status !== 'Paid' && !in_array($o->fresh()->order_status, ['batal', 'cancelled', 'failed'])) {
                                $unpaidOrder = $o->fresh();
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        return view('Pelanggan.detail', compact('product', 'relatedProducts', 'unpaidOrder'));
    }

    /**
     * Return JSON suggestions for live search bar.
     */
    public function searchSuggestions(Request $request)
    {
        $keyword = trim($request->input('q', $request->input('search', '')));

        if (mb_strlen($keyword) < 2) {
            return response()->json([]);
        }

        Produk::checkAndEmptyExpiredProducts();

        $products = Produk::with('kategori')
            ->where(function ($query) use ($keyword) {
                $query->where('nama_produk', 'like', '%' . $keyword . '%')
                    ->orWhere('deskripsi', 'like', '%' . $keyword . '%')
                    ->orWhereHas('kategori', function ($catQuery) use ($keyword) {
                        $catQuery->where('nama_kategori', 'like', '%' . $keyword . '%');
                    });
            })
            ->orderByRaw("CASE 
                WHEN LOWER(status_produk) = 'tersedia' THEN 1 
                WHEN status_produk IS NOT NULL AND status_produk != '' AND status_produk != '0' AND LOWER(status_produk) != 'habis' THEN 1 
                ELSE 0 
            END DESC")
            ->latest()
            ->limit(6)
            ->get();

        $results = $products->map(function ($item) {
            $image = $item->foto_1 ? asset('storage/' . $item->foto_1) : null;

            $isCustom = $item->jenis_produk === 'kustom';
            $detailUrl = route('product.detail', $item->id_produk) . ($isCustom ? '?custom=1' : '');

            $formattedPrice = 'Rp. ' . number_format($item->harga, 0, ',', '.');
            if ($isCustom && $item->harga_max && $item->harga_max > $item->harga) {
                $formattedPrice = 'Rp. ' . number_format($item->harga, 0, ',', '.') . ' - Rp. ' . number_format($item->harga_max, 0, ',', '.');
            }

            return [
                'id' => $item->id_produk,
                'name' => $item->nama_produk,
                'category' => $item->kategori->nama_kategori ?? 'Umum',
                'price' => $formattedPrice,
                'image' => $image,
                'url' => $detailUrl,
                'stok' => $item->status_produk,
                'is_kustom' => $isCustom,
            ];
        });

        return response()->json($results);
    }
}

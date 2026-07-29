<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PelangganController extends Controller
{
    public function home()
    {
        $featuredProducts = Produk::with('kategori')->inRandomOrder()->limit(4)->get();
        $hotDeals = Produk::with('kategori')->inRandomOrder()->limit(3)->get();
        $bestSellers = Produk::with('kategori')->inRandomOrder()->limit(3)->get();
        $topRated = Produk::with('kategori')->inRandomOrder()->limit(3)->get();
        $categories = Kategori::withCount('produks')->get();
        $newestProducts = Produk::with('kategori')->orderBy('created_at', 'desc')->limit(4)->get();

        $heroBanners = \App\Models\Banner::where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
        $promoBanners = \App\Models\Banner::where('posisi_banner', 'popup_promo')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
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
        $query = Produk::with('kategori')
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            });

        // Filter by Search Query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
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

        $shopBanners = \App\Models\Banner::where('posisi_banner', 'shop_header')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
        $shopBanner = $shopBanners->first();

        return view('Pelanggan.shop', compact('products', 'categories', 'shopBanners', 'shopBanner'));
    }

    public function customProducts(Request $request)
    {
        $query = Produk::with('kategori')
            ->where('jenis_produk', 'kustom');

        // Filter by Search Query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%')
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
        $customBanners = \App\Models\Banner::where('posisi_banner', 'custom_header')->where('status', 'Aktif')->orderBy('urutan', 'asc')->get();
        $customBanner = $customBanners->first();

        return view('Pelanggan.custom_products', compact('products', 'categories', 'customBanners', 'customBanner'));
    }

    public function about()
    {
        $about = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('about_settings')) {
            $about = \App\Models\AboutSetting::first();
        }
        $teamMembers = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('team_members')) {
            $teamMembers = \App\Models\TeamMember::orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();
        }
        return view('Pelanggan.about', compact('about', 'teamMembers'));
    }

    public function contact()
    {
        $contact = null;
        if (\Illuminate\Support\Facades\Schema::hasTable('about_settings')) {
            $contact = \App\Models\AboutSetting::first();
        }
        return view('Pelanggan.contact', compact('contact'));
    }

    public function faq()
    {
        $faqs = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('faqs')) {
            $faqs = \App\Models\Faq::where('is_active', true)->orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();
        }
        return view('Pelanggan.faq', compact('faqs'));
    }

    public function detail($id)
    {
        $product = Produk::with('kategori')->findOrFail($id);
        $relatedProducts = Produk::with('kategori')
            ->where('id_kategori', $product->id_kategori)
            ->where('id_produk', '!=', $product->id_produk)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        $unpaidOrder = null;
        if (Auth::check()) {
            $this->cancelExpiredOrders();
            $user = Auth::user();
            $orders = \App\Models\Pesanan::where('email_pembeli', $user->email)
                ->whereIn('order_status', ['menunggu pembayaran', 'menunggu', 'pending'])
                ->where('payment_status', '!=', 'Paid')
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($orders as $o) {
                $items = $o->items;
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if (isset($item['id']) && $item['id'] == $id) {
                            $unpaidOrder = $o;
                            break 2;
                        }
                    }
                }
            }
        }

        return view('Pelanggan.detail', compact('product', 'relatedProducts', 'unpaidOrder'));
    }

    public function userChart()
    {
        return view('Pelanggan.userChart');
    }

    public function checkout()
    {
        return view('Pelanggan.checkout');
    }

    public function processCheckout(Request $request)
    {
        $items = $request->input('items');
        if (empty($items)) {
            return response()->json([
                'success' => false,
                'message' => 'Keranjang belanja kosong!'
            ], 400);
        }

        // Validate stock for all items before placing order
        $total = 0;
        foreach ($items as $item) {
            $product = \App\Models\Produk::find($item['id']);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan!'
                ], 404);
            }
            if ($product->stok <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produk "' . $product->nama_produk . '" sudah habis!'
                ], 400);
            }
            if ($product->stok < $item['qty']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok produk "' . $product->nama_produk . '" tidak mencukupi (tersisa ' . $product->stok . ').'
                ], 400);
            }
            $total += $product->harga * $item['qty'];
        }

        $address = $request->input('address');
        $notes = $request->input('notes');
        $paymentMethod = $request->input('payment_method', 'COD');
        $shippingMethod = $request->input('shipping_method', 'Maxim');

        if (Auth::check()) {
            $user = Auth::user();
            $customerName = $user->nama;
            $email = $user->email;

            // Save address to user if not set
            if ($address) {
                if (!$user->alamat) {
                    $user->update(['alamat' => $address]);
                }
                // Sync to customer profile
                $customer = \App\Models\Customer::where('user_id', $user->user_id)->first();
                if ($customer) {
                    if (!$customer->location) {
                        $customer->update(['location' => $address]);
                    }
                } else {
                    \App\Models\Customer::create([
                        'user_id' => $user->user_id,
                        'name' => $user->nama,
                        'email' => $user->email,
                        'contact' => $user->no_hp,
                        'location' => $address
                    ]);
                }
            }
        } else {
            $customerName = 'Guest Customer';
            $email = null;
        }

        do {
            $orderNumber = '#' . strtoupper(\Illuminate\Support\Str::random(6));
        } while (\App\Models\Pesanan::where('order_number', $orderNumber)->exists());

        try {
            $orderStatus = (strtoupper($paymentMethod) === 'COD') ? 'dikemas' : 'menunggu pembayaran';

            $pesanan = \App\Models\Pesanan::create([
                'order_number' => $orderNumber,
                'jenis_penjualan' => 'Online',
                'payment_method' => $paymentMethod,
                'shipping_method' => $shippingMethod,
                'customer_name' => $customerName,
                'email_pembeli' => $email,
                'alamat_pengiriman' => $address,
                'catatan_pesanan' => $notes,
                'payment_status' => 'Pending',
                'order_status' => $orderStatus,
                'total' => $total,
                'items' => $items,
            ]);

            // Generate Midtrans Snap token if not COD
            $snapToken = null;
            if (strtoupper($paymentMethod) !== 'COD') {
                try {
                    $snapToken = $this->getMidtransSnapToken($pesanan);
                } catch (\Exception $me) {
                    // Log the error and proceed without Snap Token. The user can retry payment from order history.
                    Log::error('Midtrans Snap Token generation failed: ' . $me->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibuat dengan nomor order ' . $orderNumber,
                'order' => $pesanan,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat pesanan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function orderSuccess(Request $request)
    {
        $this->cancelExpiredOrders();
        $orderId = $request->query('order_id');
        $order = null;
        if ($orderId) {
            $order = \App\Models\Pesanan::where('id', $orderId)
                ->where('email_pembeli', Auth::user()->email)
                ->first();
        }

        if (!$order) {
            return redirect()->route('home')->with('error', 'Pesanan tidak ditemukan.');
        }

        $deadline = $order->created_at->addHours(24);
        $remainingSeconds = max(0, now()->diffInSeconds($deadline, false));

        return view('Pelanggan.order_success', compact('order', 'deadline', 'remainingSeconds'));
    }

    public function dashboard()
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();

        // Fallback to customer table location if user's alamat is null
        if (!$user->alamat) {
            $customer = \App\Models\Customer::where(function($q) use ($user) {
                if ($user->user_id) {
                    $q->where('user_id', $user->user_id);
                }
                if ($user->nama) {
                    $q->orWhere('name', $user->nama);
                }
                if ($user->no_hp) {
                    $rawPhone = ltrim((string)$user->no_hp, '0');
                    $q->orWhere('contact', 'like', '%' . $rawPhone . '%');
                }
            })->first();

            if ($customer && $customer->location) {
                \App\Models\User::where('user_id', $user->user_id)->update(['alamat' => $customer->location]);
                $user->alamat = $customer->location;
                if (!$customer->user_id) {
                    $customer->user_id = $user->user_id;
                    $customer->save();
                }
            }
        }

        $recentOrders = \App\Models\Pesanan::where('email_pembeli', $user->email)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('Pelanggan.dashboard', compact('user', 'recentOrders'));
    }

    public function orderHistory(Request $request)
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();
        $statusFilter = $request->query('status');

        $query = \App\Models\Pesanan::where('email_pembeli', $user->email);

        if ($statusFilter) {
            if ($statusFilter === 'processing') {
                $query->whereIn('order_status', ['menunggu pembayaran', 'processing', 'proses', 'ready']);
            } elseif ($statusFilter === 'on_the_way') {
                $query->whereIn('order_status', ['on the way', 'otw', 'kirim', 'sedang dikirim']);
            } elseif ($statusFilter === 'completed') {
                $query->whereIn('order_status', ['selesai', 'completed', 'delivered']);
            } elseif ($statusFilter === 'canceled') {
                $query->whereIn('order_status', ['batal', 'cancel', 'failed']);
            }
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(5);

        // Fetch returns for the customer
        $returs = collect();
        if ($statusFilter === 'return' || is_null($statusFilter)) {
            $returs = \App\Models\Retur::where('customer_name', $user->nama)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('Pelanggan.order_history', compact('user', 'orders', 'statusFilter', 'returs'));
    }

    public function orderDetail($id)
    {
        $this->cancelExpiredOrders();
        $user = Auth::user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        $items = [];
        $orderItems = $order->items;

        if (is_array($orderItems) && count($orderItems) > 0) {
            foreach ($orderItems as $item) {
                $product = \App\Models\Produk::find($item['id']);
                if (!$product) {
                    $product = new \App\Models\Produk();
                    $product->id_produk = $item['id'];
                    $product->nama_produk = $item['name'] ?? 'Product Removed';
                    $product->harga = $item['price'] ?? 0;
                    $product->foto_1 = null;
                }
                
                $qty = isset($item['qty']) ? intval($item['qty']) : 1;
                $price = isset($item['price']) ? floatval($item['price']) : floatval($product->harga);
                
                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                    'img' => $item['img'] ?? null
                ];
            }
        } else {
            $products = \App\Models\Produk::inRandomOrder()->limit(min(3, \App\Models\Produk::count() ?: 1))->get();
            $total = floatval($order->total);
            $count = count($products);

            if ($count > 0) {
                $remaining = $total;
                for ($i = 0; $i < $count; $i++) {
                    $product = $products[$i];
                    $qty = ($i === 0 && $count > 1) ? 2 : 1;

                    if ($i === $count - 1) {
                        $itemPrice = $remaining / $qty;
                    } else {
                        $itemPrice = round(($total / ($count + 1)) / 1000) * 1000;
                        if ($itemPrice * $qty >= $remaining) {
                            $itemPrice = round(($remaining / 2) / 1000) * 1000;
                        }
                    }

                    $items[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price' => $itemPrice,
                        'subtotal' => $itemPrice * $qty
                    ];

                    $remaining -= $itemPrice * $qty;
                }
            }
        }

        $estimatedArrival = $order->created_at->addDays(1)->format('l') . ', 2 PM - 4 PM';
        $expiryTimestamp = $order->created_at->addHours(24)->timestamp;

        return view('Pelanggan.order_detail', compact('user', 'order', 'items', 'estimatedArrival', 'expiryTimestamp'));
    }

    public function cancelOrder($id)
    {
        $user = Auth::user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if (\Illuminate\Support\Str::contains(strtolower($order->order_status), ['menunggu', 'pending'])) {
            $order->update([
                'order_status' => 'batal',
                'payment_status' => 'Cancelled'
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Pesanan berhasil dibatalkan!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Pesanan tidak dapat dibatalkan.'
        ], 400);
    }

    public function returnOrder($id)
    {
        $user = Auth::user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        // Only allow returns for completed/delivered orders
        $isDelivered = \Illuminate\Support\Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']);
        if (!$isDelivered) {
            return redirect()->route('order.detail', $order->id)->with('error', 'Pengembalian hanya tersedia untuk pesanan yang sudah dikirim.');
        }

        return view('Pelanggan.return_order', compact('user', 'order'));
    }

    public function submitReturn(Request $request, $id)
    {
        $user = Auth::user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        // Only allow returns for completed/delivered orders
        $isDelivered = \Illuminate\Support\Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']);
        if (!$isDelivered) {
            return response()->json([
                'success' => false,
                'message' => 'Pengembalian hanya tersedia untuk pesanan yang sudah dikirim.'
            ], 400);
        }

        try {
            \App\Models\Retur::create([
                'customer_name' => $user->nama,
                'order_id' => $order->order_number,
                'reason' => $request->reason . ($request->description ? ' - ' . $request->description : ''),
                'return_date' => now()->toDateString(),
                'product_name' => 'Order ' . $order->order_number,
                'status' => 'New'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengembalian berhasil diajukan! Tim kami akan segera memprosesnya.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengajukan pengembalian: ' . $e->getMessage()
            ], 500);
        }
    }

    public function returnDetail($id)
    {
        $user = Auth::user();
        $retur = \App\Models\Retur::where('customer_name', $user->nama)->findOrFail($id);

        // Find the associated order by order_number
        $order = \App\Models\Pesanan::where('order_number', $retur->order_id)
            ->where('email_pembeli', $user->email)
            ->first();

        // Generate order items (same logic as orderDetail)
        $items = [];
        if ($order) {
            $products = \App\Models\Produk::inRandomOrder()->limit(min(3, \App\Models\Produk::count() ?: 1))->get();
            $total = floatval($order->total);
            $count = count($products);

            if ($count > 0) {
                $remaining = $total;
                for ($i = 0; $i < $count; $i++) {
                    $product = $products[$i];
                    $qty = ($i === 0 && $count > 1) ? 2 : 1;

                    if ($i === $count - 1) {
                        $itemPrice = $remaining / $qty;
                    } else {
                        $itemPrice = round(($total / ($count + 1)) / 1000) * 1000;
                        if ($itemPrice * $qty >= $remaining) {
                            $itemPrice = round(($remaining / 2) / 1000) * 1000;
                        }
                    }

                    $items[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price' => $itemPrice,
                        'subtotal' => $itemPrice * $qty
                    ];

                    $remaining -= $itemPrice * $qty;
                }
            }
        }

        return view('Pelanggan.return_detail', compact('user', 'retur', 'order', 'items'));
    }

    public function settings()
    {
        $user = Auth::user();
        if (!$user->alamat) {
            $customer = \App\Models\Customer::where('user_id', $user->user_id)
                ->orWhere('contact', $user->no_hp)
                ->orWhere('name', $user->nama)
                ->first();
            if ($customer) {
                if (!$customer->user_id) {
                    $customer->update(['user_id' => $user->user_id]);
                }
                if ($customer->location) {
                    $user->update(['alamat' => $customer->location]);
                }
            }
        }
        return view('Pelanggan.settings', compact('user'));
    }

    public function updateAccount(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'no_hp' => 'required|numeric|unique:users,no_hp,' . $user->user_id . ',user_id',
        ]);

        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui!',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|different:current_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.'
            ], 400);
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui!'
        ]);
    }

    public function updateAddress(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'alamat' => 'required|string',
        ]);

        $user->update([
            'alamat' => $request->alamat,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat tagihan berhasil diperbarui!',
            'user' => $user
        ]);
    }

    public function updateAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $file->move($path, $filename);

            // Delete old avatar if exists and not default
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            $avatarPath = 'uploads/avatars/' . $filename;
            $user->update([
                'avatar' => $avatarPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui!',
                'avatar_url' => asset($avatarPath)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada file yang diunggah.'
        ], 400);
    }

    private function cancelExpiredOrders()
    {
        if (Auth::check()) {
            $user = Auth::user();
            $expiredOrders = \App\Models\Pesanan::where('email_pembeli', $user->email)
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
        }
    }

    private function getMidtransSnapToken($pesanan)
    {
        if ($pesanan->snap_token) {
            return $pesanan->snap_token;
        }

        $serverKey = config('services.midtrans.server_key');
        if (!$serverKey) {
            throw new \Exception('Midtrans Server Key is not configured.');
        }

        $isProduction = config('services.midtrans.is_production', false);
        $url = $isProduction 
            ? 'https://app.midtrans.com/snap/v1/transactions' 
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $pesanan->order_number,
                'gross_amount' => (int) $pesanan->total,
            ],
            'customer_details' => [
                'first_name' => $pesanan->customer_name,
                'email' => $pesanan->email_pembeli,
                'phone' => Auth::check() ? Auth::user()->no_hp : '',
            ],
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withBasicAuth($serverKey, '')
          ->post($url, $payload);

        if ($response->failed()) {
            throw new \Exception('Midtrans API Error: ' . $response->body());
        }

        $result = $response->json();
        $snapToken = $result['token'] ?? null;

        if ($snapToken) {
            $pesanan->update(['snap_token' => $snapToken]);
        }

        return $snapToken;
    }

    public function midtransCallback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            Log::warning('Midtrans Webhook: Invalid signature key: ' . $request->signature_key);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = \App\Models\Pesanan::where('order_number', $request->order_id)->first();
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $transactionStatus = $request->transaction_status;
        $type = $request->payment_type;

        Log::info("Midtrans Webhook: Processing order {$order->order_number} with status {$transactionStatus}");

        if ($transactionStatus == 'capture') {
            if ($type == 'credit_card') {
                if ($request->fraud_status == 'challenge') {
                    $order->update([
                        'payment_status' => 'Pending',
                        'order_status' => 'menunggu pembayaran'
                    ]);
                } else {
                    $order->update([
                        'payment_status' => 'Paid',
                        'order_status' => 'dikemas'
                    ]);
                }
            }
        } elseif ($transactionStatus == 'settlement') {
            $order->update([
                'payment_status' => 'Paid',
                'order_status' => 'dikemas'
            ]);
        } elseif (in_array($transactionStatus, ['pending'])) {
            $order->update([
                'payment_status' => 'Pending',
                'order_status' => 'menunggu pembayaran'
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->update([
                'payment_status' => 'Failed',
                'order_status' => 'batal'
            ]);
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }

    public function getSnapTokenForOrder($id)
    {
        $user = Auth::user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if ($order->payment_status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibayar.'
            ], 400);
        }

        try {
            $snapToken = $this->getMidtransSnapToken($order);
            return response()->json([
                'success' => true,
                'snap_token' => $snapToken
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mendapatkan token pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkPaymentStatus($id)
    {
        $user = Auth::user();
        $order = \App\Models\Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        if ($order->payment_status === 'Paid') {
            return response()->json([
                'success' => true,
                'status' => 'Paid',
                'message' => 'Pembayaran Anda telah lunas!'
            ]);
        }

        $serverKey = config('services.midtrans.server_key');
        $isProduction = config('services.midtrans.is_production', false);
        $url = $isProduction
            ? "https://api.midtrans.com/v2/{$order->order_number}/status"
            : "https://api.sandbox.midtrans.com/v2/{$order->order_number}/status";

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->withBasicAuth($serverKey, '')
              ->get($url);

            if ($response->successful()) {
                $result = $response->json();
                $transactionStatus = $result['transaction_status'] ?? '';
                
                if (in_array($transactionStatus, ['capture', 'settlement'])) {
                    $order->update([
                        'payment_status' => 'Paid',
                        'order_status' => 'dikemas'
                    ]);
                    return response()->json([
                        'success' => true,
                        'status' => 'Paid',
                        'message' => 'Pembayaran berhasil dikonfirmasi!'
                    ]);
                } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    $order->update([
                        'payment_status' => 'Failed',
                        'order_status' => 'batal'
                    ]);
                    return response()->json([
                        'success' => false,
                        'status' => 'Failed',
                        'message' => 'Pembayaran gagal atau kedaluwarsa.'
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Check Payment Status failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => false,
            'status' => $order->payment_status,
            'message' => 'Pembayaran masih pending atau belum dilakukan.'
        ]);
    }
}

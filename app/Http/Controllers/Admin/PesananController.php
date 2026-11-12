<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Customer;
use App\Services\GeoLocationService;
use Illuminate\Http\Request;

class PesananController extends Controller
{
    /**
     * Menampilkan daftar pesanan online dengan filter pencarian dan status pembayaran.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Pesanan::query()->where('jenis_penjualan', 'Online');
        
        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('order_number', 'like', $searchTerm)
                  ->orWhere('customer_name', 'like', $searchTerm);
            });
        }

        if ($request->has('payment_status') && is_array($request->payment_status)) {
            $query->whereIn('payment_status', $request->payment_status);
        } elseif ($request->filled('payment_status') && $request->payment_status !== 'All') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('order_status') && is_array($request->order_status)) {
            $query->whereIn('order_status', $request->order_status);
        } elseif ($request->filled('order_status') && $request->order_status !== 'All') {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();
        return view('Admin.kelola_penjualan.orders', compact('orders'));
    }

    /**
     * Pengalihan ke POS kasir untuk penambahan manual transaksi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        return redirect()->route('pos.index')->with('info', 'Penambahan transaksi manual dilayani melalui sistem Kasir POS agar stok dan item produk tercatat dengan benar.');
    }

    /**
     * Memperbarui informasi dan status pesanan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $pesanan = Pesanan::findOrFail($id);

        $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'payment_status' => 'required|string|in:Pending,Paid',
            'order_status' => 'required|string|in:menunggu pembayaran,menunggu konfirmasi,dikemas,dikirim,selesai,batal',
            'total' => 'nullable|numeric|min:0',
        ]);

        $updateData = $request->only('payment_status', 'order_status');
        if ($request->filled('customer_name')) {
            $updateData['customer_name'] = $request->customer_name;
        }
        if ($request->filled('total')) {
            $updateData['total'] = $request->total;
        }

        $pesanan->update($updateData);

        return redirect()->route('orders.index')->with('success', 'Status pesanan berhasil diperbarui.');
    }

    /**
     * Menghapus data pesanan berdasarkan ID.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $pesanan = Pesanan::findOrFail($id);
        $pesanan->delete();

        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Konfirmasi cepat pesanan berstatus menunggu konfirmasi agar langsung diproses di dapur (dikemas).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function quickConfirm(Request $request, $id)
    {
        $order = Pesanan::findOrFail($id);

        if ($order->is_bank_transfer) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan dengan metode Transfer Bank harus diverifikasi melalui bukti pembayaran terlebih dahulu.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Pesanan dengan metode Transfer Bank harus diverifikasi melalui bukti pembayaran terlebih dahulu.');
        }

        if (!in_array(strtolower($order->order_status ?? ''), ['menunggu konfirmasi', 'unconfirmed'])) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ini sudah dikonfirmasi atau statusnya bukan Menunggu Konfirmasi.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Status pesanan bukan Menunggu Konfirmasi.');
        }

        $order->update([
            'order_status' => 'dikemas',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pesanan ' . $order->order_number . ' berhasil dikonfirmasi dan masuk antrean dapur!',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'order_status' => 'dikemas',
                'status_label' => 'Sedang Dikemas',
            ]);
        }

        return redirect()->back()->with('success', 'Pesanan ' . $order->order_number . ' berhasil dikonfirmasi dan masuk antrean dapur!');
    }

    /**
     * Menampilkan lembar invoice cetak untuk pesanan tertentu.
     *
     * @param  int|string  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function printInvoice($id)
    {
        $order = Pesanan::findOrFail($id);
        
        $items = [];
        $orderItems = $order->items;

        if (is_array($orderItems) && count($orderItems) > 0) {
            foreach ($orderItems as $item) {
                $product = Produk::find($item['id'] ?? null);
                if (!$product) {
                    $product = new Produk();
                    $product->id_produk = $item['id'] ?? 0;
                    $product->nama_produk = $item['name'] ?? 'Product Removed';
                    $product->harga = $item['price'] ?? 0;
                    $product->foto_1 = null;
                }
                
                $qty = isset($item['qty']) ? intval($item['qty']) : (isset($item['quantity']) ? intval($item['quantity']) : 1);
                $price = isset($item['price']) ? floatval($item['price']) : floatval($product->harga);
                
                $items[] = [
                    'name' => $item['name'] ?? $product->nama_produk,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $price * $qty,
                    'custom_text' => $item['custom_text'] ?? null,
                ];
            }
        }
        $customerPhone = null;
        $customerAddress = [
            'label' => null,
            'street' => $order->alamat_pengiriman,
            'area' => null,
            'postal_code' => null,
            'notes' => null,
            'gps' => null,
            'is_pickup' => ($order->jenis_penjualan === 'Offline' && empty($order->alamat_pengiriman)),
        ];

        if (!empty($order->email_pembeli)) {
            $customerUser = User::where('email', $order->email_pembeli)->first();
            if ($customerUser) {
                if (!empty($customerUser->no_hp)) {
                    $customerPhone = $customerUser->no_hp;
                }

                $userAddr = UserAddress::where('user_id', $customerUser->user_id)
                    ->orderByDesc('is_default')
                    ->first();

                if ($userAddr) {
                    $customerAddress['label'] = $userAddr->label;
                    if (empty($customerAddress['street'])) {
                        $customerAddress['street'] = $userAddr->street_address;
                    }
                    $customerAddress['area'] = $userAddr->destination_area_name;
                    $customerAddress['postal_code'] = $userAddr->postal_code;
                    $customerAddress['notes'] = $userAddr->notes;
                    if ($userAddr->latitude && $userAddr->longitude) {
                        $customerAddress['gps'] = $userAddr->latitude . ', ' . $userAddr->longitude;
                    }
                } elseif (empty($customerAddress['street'])) {
                    $customerAddress['street'] = $customerUser->alamat;
                    $customerRec = Customer::where('user_id', $customerUser->user_id)->first();
                    if (empty($customerAddress['street']) && $customerRec && !empty($customerRec->location)) {
                        $customerAddress['street'] = $customerRec->location;
                    }
                }
            }
        }

        if (empty($customerPhone) && empty($customerAddress['street']) && !empty($order->customer_name)) {
            $matchedUser = User::where('nama', $order->customer_name)->first();
            if ($matchedUser) {
                if (!empty($matchedUser->no_hp)) {
                    $customerPhone = $matchedUser->no_hp;
                }
                $userAddr = UserAddress::where('user_id', $matchedUser->user_id)->orderByDesc('is_default')->first();
                if ($userAddr) {
                    $customerAddress['label'] = $userAddr->label;
                    $customerAddress['street'] = $userAddr->street_address;
                    $customerAddress['area'] = $userAddr->destination_area_name;
                    $customerAddress['postal_code'] = $userAddr->postal_code;
                    $customerAddress['notes'] = $userAddr->notes;
                    if ($userAddr->latitude && $userAddr->longitude) {
                        $customerAddress['gps'] = $userAddr->latitude . ', ' . $userAddr->longitude;
                    }
                } elseif (!empty($matchedUser->alamat)) {
                    $customerAddress['street'] = $matchedUser->alamat;
                }
            }
        }

        if (!empty($order->catatan_pesanan)) {
            if (empty($customerAddress['gps']) && preg_match('/\[Titik GPS:\s*([-\d.]+),\s*([-\d.]+)/i', $order->catatan_pesanan, $matches)) {
                $customerAddress['gps'] = $matches[1] . ', ' . $matches[2];
            }
            if (empty($customerPhone) && preg_match('/\[Penerima:\s*[^()]*\(([^)]+)\)\]/i', $order->catatan_pesanan, $matches)) {
                $customerPhone = trim($matches[1]);
            }
        }
        $customerAddress['detected_place'] = null;
        $customerAddress['detected_road'] = null;

        if (!empty($customerAddress['gps'])) {
            $coords = explode(',', (string) $customerAddress['gps']);
            if (count($coords) === 2) {
                $lat = trim($coords[0]);
                $lng = trim($coords[1]);
                $geoInfo = GeoLocationService::reverseGeocode($lat, $lng);
                if ($geoInfo) {
                    $customerAddress['detected_place'] = $geoInfo['place_name'];
                    $customerAddress['detected_road'] = $geoInfo['road'];

                    $cleanStreet = trim(preg_replace('/\(Kode Pos:.*?\)/i', '', (string)$customerAddress['street']));
                    $isDummyStreet = false;
                    if (empty($cleanStreet) || (strlen($cleanStreet) <= 8 && preg_match('/^[a-z0-9_-]+$/i', $cleanStreet))) {
                        $isDummyStreet = true;
                    }

                    if ($isDummyStreet) {
                        $resolvedParts = array_filter([$geoInfo['place_name'], $geoInfo['road']]);
                        if (!empty($resolvedParts)) {
                            $customerAddress['street'] = implode(', ', $resolvedParts);
                        } elseif (!empty($geoInfo['display_name'])) {
                            $customerAddress['street'] = $geoInfo['display_name'];
                        }
                    }

                    if (empty($customerAddress['area']) && !empty($geoInfo['area'])) {
                        $customerAddress['area'] = $geoInfo['area'];
                    }
                    if (empty($customerAddress['postal_code']) && !empty($geoInfo['postal_code'])) {
                        $customerAddress['postal_code'] = $geoInfo['postal_code'];
                    }
                }
            }
        }

        if (empty($customerAddress['postal_code']) && !empty($customerAddress['street'])) {
            if (preg_match('/(?:Kode Pos:\s*|,\s*)(\d{5})/i', $customerAddress['street'], $matches)) {
                $customerAddress['postal_code'] = $matches[1];
            }
        }

        // Sanitize catatan_pesanan for the customer note box (strip internal brackets [Penerima: ...] & [Titik GPS: ...])
        $cleanNotes = $order->catatan_pesanan ?? '';
        $cleanNotes = preg_replace('/\[Penerima:\s*[^()]*\([^)]+\)\]/i', '', $cleanNotes);
        $cleanNotes = preg_replace('/\[Titik GPS:\s*[^\]]+\]/i', '', $cleanNotes);
        $cleanNotes = trim($cleanNotes);

        return view('Admin.kelola_penjualan.invoice', compact('order', 'items', 'customerPhone', 'customerAddress', 'cleanNotes'));
    }

    /**
     * Menghapus pesanan secara massal berdasarkan daftar ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:pesanans,id',
        ]);

        $count = count($validated['ids']);
        Pesanan::whereIn('id', $validated['ids'])->delete();

        return redirect()->route('orders.index')->with('success', "{$count} data pesanan berhasil dihapus.");
    }
}

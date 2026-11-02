<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
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
                ];
            }
        }
        
        return view('Admin.kelola_penjualan.invoice', compact('order', 'items'));
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

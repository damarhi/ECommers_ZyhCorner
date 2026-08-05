<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Retur;
use App\Services\NotificationService;
use App\Mail\ReturProdukKasirMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CustomerReturnController extends Controller
{
    /**
     * @param  int|string  $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function returnOrder($id)
    {
        $user = Auth::user();
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        // Only allow returns for completed/delivered orders
        $isDelivered = Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']);
        if (!$isDelivered) {
            return redirect()->route('order.detail', $order->id)->with('error', 'Pengembalian hanya tersedia untuk pesanan yang sudah dikirim.');
        }

        // Cek apakah pesanan sudah pernah diretur
        $existingRetur = Retur::where('order_id', $order->order_number)->first();
        if ($existingRetur) {
            return redirect()->route('order.detail', $order->id)->with('error', 'Pesanan ini sudah pernah diajukan retur.');
        }

        // Cek apakah pesanan selesai lebih dari 1 hari yang lalu
        $completedAt = $order->updated_at;
        if ($completedAt && Carbon::parse($completedAt)->addDay()->isPast()) {
            return redirect()->route('order.detail', $order->id)->with('error', 'Batas waktu pengajuan retur telah lewat (maksimal 24 jam setelah pesanan selesai).');
        }

        return view('Pelanggan.return_order', compact('user', 'order'));
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitReturn(Request $request, $id)
    {
        $user = Auth::user();
        $order = Pesanan::where('email_pembeli', $user->email)->findOrFail($id);

        // Only allow returns for completed/delivered orders
        $isDelivered = Str::contains(strtolower($order->order_status), ['selesai', 'completed', 'delivered']);
        if (!$isDelivered) {
            return response()->json([
                'success' => false,
                'message' => 'Pengembalian hanya tersedia untuk pesanan yang sudah dikirim.'
            ], 400);
        }

        // Cek apakah pesanan sudah pernah diretur
        $existingRetur = Retur::where('order_id', $order->order_number)->first();
        if ($existingRetur) {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah pernah diajukan retur.'
            ], 400);
        }

        // Cek apakah pesanan selesai lebih dari 1 hari yang lalu
        $completedAt = $order->updated_at;
        if ($completedAt && Carbon::parse($completedAt)->addDay()->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'Batas waktu pengajuan retur telah lewat (maksimal 24 jam setelah pesanan selesai).'
            ], 400);
        }

        try {
            $fotoPath = null;
            $file = $request->file('photo') ?? $request->file('foto_retur') ?? $request->file('image') ?? $request->file('file');
            if (!$file && !empty($request->allFiles())) {
                $files = $request->allFiles();
                $file = reset($files);
            }

            Log::info('RETUR DEBUG: hasFile photo=' . ($request->hasFile('photo') ? 'YES' : 'NO') . ' hasFile foto_retur=' . ($request->hasFile('foto_retur') ? 'YES' : 'NO') . ' allFiles=' . json_encode(array_keys($request->allFiles())) . ' file=' . ($file ? $file->getClientOriginalName() : 'NULL'));

            if ($file) {
                $extension = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $path = public_path('uploads/returs');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $file->move($path, $filename);
                $fotoPath = 'uploads/returs/' . $filename;
            }

            Log::info('RETUR DEBUG: fotoPath=' . json_encode($fotoPath));

            // Simpan retur ke database - gunakan cara eksplisit (bukan mass-assignment)
            $retur = new Retur();
            $retur->customer_name = $user->nama;
            $retur->order_id = $order->order_number;
            $retur->reason = $request->reason . ($request->description ? ' - ' . $request->description : '');
            $retur->return_date = now()->toDateString();
            $retur->product_name = 'Order ' . $order->order_number;
            $retur->status = 'New';
            $retur->foto_retur = $fotoPath;
            $retur->save();

            Log::info('RETUR DEBUG: after save() id=' . $retur->id . ' foto_retur=' . json_encode($retur->foto_retur));

            // Failsafe: selalu paksa update foto_retur lewat DB::table agar pasti tersimpan
            if ($fotoPath) {
                DB::table('returs')
                    ->where('id', $retur->id)
                    ->update(['foto_retur' => $fotoPath]);
            }

            // Ambil data pasti yang telah tersimpan di database
            $returSaved = Retur::findOrFail($retur->id);

            Log::info('RETUR DEBUG: final foto_retur=' . json_encode($returSaved->foto_retur));

            // Kirim notifikasi email berdasarkan data tersimpan tersebut
            try {
                NotificationService::sendToKasir(new ReturProdukKasirMail($returSaved));
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email retur kasir: ' . $e->getMessage());
            }

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

    /**
     * @param  int|string  $id
     * @return \Illuminate\Contracts\View\View
     */
    public function returnDetail($id)
    {
        $user = Auth::user();
        $retur = Retur::where('customer_name', $user->nama)->findOrFail($id);

        // Find the associated order by order_number
        $order = Pesanan::where('order_number', $retur->order_id)
            ->where('email_pembeli', $user->email)
            ->first();

        // Generate order items (same logic as orderDetail)
        $items = [];
        if ($order) {
            $products = Produk::inRandomOrder()->limit(min(3, Produk::count() ?: 1))->get();
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
}

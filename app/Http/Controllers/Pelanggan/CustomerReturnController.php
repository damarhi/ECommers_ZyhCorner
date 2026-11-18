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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
        $order = Pesanan::ownedBy($user)->findOrFail($id);

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
        $order = Pesanan::ownedBy($user)->findOrFail($id);

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

        $unexpectedFileKeys = array_diff(array_keys($request->allFiles()), ['photo', 'foto_retur']);
        if (!empty($unexpectedFileKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'Field berkas unggahan tidak valid.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'extensions:jpg,jpeg,png,gif,webp', 'max:5120'],
            'foto_retur' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'extensions:jpg,jpeg,png,gif,webp', 'max:5120'],
        ], [
            'reason.required' => 'Alasan pengembalian wajib dipilih.',
            'photo.image' => 'Berkas foto bukti retur harus berupa gambar yang valid.',
            'photo.mimes' => 'Format foto retur hanya mendukung JPG, PNG, GIF, atau WEBP.',
            'photo.extensions' => 'Ekstensi berkas foto retur tidak diizinkan.',
            'photo.max' => 'Ukuran foto retur maksimal adalah 5MB.',
            'foto_retur.image' => 'Berkas foto bukti retur harus berupa gambar yang valid.',
            'foto_retur.mimes' => 'Format foto retur hanya mendukung JPG, PNG, GIF, atau WEBP.',
            'foto_retur.extensions' => 'Ekstensi berkas foto retur tidak diizinkan.',
            'foto_retur.max' => 'Ukuran foto retur maksimal adalah 5MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $storedRelativePath = null;
        try {
            $fotoPath = null;
            $file = $request->file('foto_retur') ?? $request->file('photo');

            if ($file) {
                $storedRelativePath = Storage::disk('public')->putFile('uploads/returs', $file);
                $fotoPath = 'storage/' . $storedRelativePath;
            }

            $combinedReason = trim((string) $request->input('reason'));
            if ($request->filled('description')) {
                $combinedReason .= ' - ' . trim((string) $request->input('description'));
            }
            $combinedReason = Str::limit($combinedReason, 250, '...');

            $itemNames = [];
            if (is_array($order->items)) {
                foreach ($order->items as $ordItem) {
                    $nm = $ordItem['name'] ?? $ordItem['nama_produk'] ?? null;
                    if ($nm) {
                        $itemNames[] = $nm;
                    }
                }
            }
            $resolvedProductName = !empty($itemNames)
                ? Str::limit(implode(', ', $itemNames), 250, '...')
                : ('Order ' . $order->order_number);

            // Simpan retur ke database - gunakan cara eksplisit (bukan mass-assignment)
            $retur = new Retur();
            $retur->user_id = $user->user_id;
            $retur->customer_name = $user->nama;
            $retur->order_id = $order->order_number;
            $retur->reason = $combinedReason;
            $retur->return_date = now()->toDateString();
            $retur->product_name = $resolvedProductName;
            $retur->status = 'New';
            $retur->foto_retur = $fotoPath;
            $retur->save();

            // Ambil data pasti yang telah tersimpan di database
            $returSaved = Retur::findOrFail($retur->id);

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
            if ($storedRelativePath && Storage::disk('public')->exists($storedRelativePath)) {
                Storage::disk('public')->delete($storedRelativePath);
            }
            Log::error('Customer submitReturn error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengajukan pengembalian. Silakan coba kembali.'
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
        $retur = Retur::ownedBy($user)->findOrFail($id);

        // Find the associated order owned by this user
        $order = Pesanan::ownedBy($user)
            ->where('order_number', $retur->order_id)
            ->first();

        // Populate actual items from $order->items (preserving exact view data structure)
        $items = [];
        if ($order && is_array($order->items) && count($order->items) > 0) {
            $productIds = array_values(array_filter(array_map(
                fn ($it) => $it['id'] ?? $it['id_produk'] ?? null,
                $order->items
            )));
            $productsMap = !empty($productIds)
                ? Produk::whereIn('id_produk', $productIds)->get()->keyBy('id_produk')
                : collect();

            foreach ($order->items as $item) {
                $pid = $item['id'] ?? $item['id_produk'] ?? null;
                $product = $pid ? $productsMap->get($pid) : null;

                if (!$product) {
                    $product = new Produk();
                    $product->id_produk = $pid;
                    $product->nama_produk = $item['name'] ?? $item['nama_produk'] ?? 'Produk Pesanan';
                    $product->harga = $item['price'] ?? 0;
                    $product->foto_1 = null;
                }

                $qty = max(1, (int) ($item['qty'] ?? $item['quantity'] ?? 1));
                $itemPrice = isset($item['price']) ? (float) $item['price'] : (float) $product->harga;

                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'price' => $itemPrice,
                    'subtotal' => $itemPrice * $qty,
                ];
            }
        }

        return view('Pelanggan.return_detail', compact('user', 'retur', 'order', 'items'));
    }
}

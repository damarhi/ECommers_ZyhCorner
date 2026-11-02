<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BuktiBayarController extends Controller
{
    /**
     * Konfirmasi bukti pembayaran transfer bank.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function confirmProof(Request $request, $id)
    {
        $order = Pesanan::findOrFail($id);

        if ($order->payment_status === 'Paid') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pesanan ini sudah berstatus Lunas.',
                ], 422);
            }
            return redirect()->back()->with('error', 'Pesanan ini sudah berstatus Lunas.');
        }

        $order->update([
            'payment_status' => 'Paid',
            'order_status' => 'dikemas',
            'catatan_verifikasi_pembayaran' => 'Diverifikasi Lunas oleh Kasir/Admin pada ' . now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran pesanan ' . $order->order_number . ' berhasil dikonfirmasi! Pesanan kini LUNAS dan sedang dikemas.',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => 'Paid',
                'order_status' => 'dikemas',
            ]);
        }

        return redirect()->back()->with('success', 'Bukti pembayaran pesanan ' . $order->order_number . ' berhasil dikonfirmasi!');
    }

    /**
     * Tolak bukti transfer bank dengan alasan yang jelas.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function rejectProof(Request $request, $id)
    {
        $order = Pesanan::findOrFail($id);

        $request->validate([
            'alasan_penolakan' => 'required|string|max:255',
        ], [
            'alasan_penolakan.required' => 'Wajib mengisi alasan penolakan bukti pembayaran.',
        ]);

        $alasan = strip_tags(trim($request->input('alasan_penolakan')));

        if ($order->bukti_pembayaran && Storage::disk('public')->exists($order->bukti_pembayaran)) {
            Storage::disk('public')->delete($order->bukti_pembayaran);
        }

        $order->update([
            'bukti_pembayaran' => null,
            'bukti_pembayaran_uploaded_at' => null,
            'payment_status' => 'Pending',
            'order_status' => 'menunggu pembayaran',
            'catatan_verifikasi_pembayaran' => 'Bukti ditolak: ' . $alasan . ' (' . now()->isoFormat('D MMMM YYYY, HH:mm') . ' WIB)',
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran pesanan ' . $order->order_number . ' telah ditolak. Pelanggan dapat mengunggah bukti baru.',
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'payment_status' => 'Pending',
                'order_status' => 'menunggu pembayaran',
                'catatan' => $order->catatan_verifikasi_pembayaran,
            ]);
        }

        return redirect()->back()->with('success', 'Bukti pembayaran pesanan ' . $order->order_number . ' telah ditolak.');
    }
}

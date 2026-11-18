<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Retur;
use App\Models\Pesanan;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Services\NotificationService;
use App\Mail\ReturProdukKasirMail;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $query = Retur::query();
        
        if ($request->filled('search')) {
            $rawTerm = trim($request->search);
            $searchTerm = '%' . $rawTerm . '%';
            
            $statusMap = [
                'baru'      => 'New',
                'diproses'  => 'Processed',
                'diterima'  => 'Accepted',
                'disetujui' => 'Accepted',
                'ditolak'   => 'Rejected',
                'selesai'   => 'Done',
            ];
            $mappedStatus = $statusMap[strtolower($rawTerm)] ?? null;

            $query->where(function($q) use ($searchTerm, $mappedStatus) {
                $q->where('customer_name', 'like', $searchTerm)
                  ->orWhere('order_id', 'like', $searchTerm)
                  ->orWhere('product_name', 'like', $searchTerm)
                  ->orWhere('reason', 'like', $searchTerm)
                  ->orWhere('status', 'like', $searchTerm);
                  
                if ($mappedStatus) {
                    $q->orWhere('status', $mappedStatus);
                }
            });
        }

        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status', $request->status);
        } elseif ($request->has('filter') && $request->filter != 'All') {
            $query->where('status', $request->filter);
        }

        $returs = $query->latest()->paginate(10)->withQueryString();

        // Status counts for tab navigation
        $statusCounts = [
            'All'       => Retur::count(),
            'New'       => Retur::where('status', 'New')->count(),
            'Processed' => Retur::where('status', 'Processed')->count(),
            'Accepted'  => Retur::where('status', 'Accepted')->count(),
            'Rejected'  => Retur::where('status', 'Rejected')->count(),
            'Done'      => Retur::where('status', 'Done')->count(),
        ];

        // Completed orders for smart selection
        $completedOrders = Pesanan::whereIn(DB::raw('LOWER(order_status)'), ['selesai', 'completed', 'delivered'])
            ->latest()
            ->limit(50)
            ->get();

        // Active products for quick selector
        $allProducts = Produk::orderBy('nama_produk')->get();

        return view('Admin.kelola_penjualan.returs', compact('returs', 'statusCounts', 'completedOrders', 'allProducts'));
    }

    private function deleteReturPhoto(?string $fotoPath): void
    {
        if (empty($fotoPath)) {
            return;
        }

        if (str_starts_with($fotoPath, 'storage/')) {
            $diskPath = substr($fotoPath, 8);
            if ($diskPath !== '' && Storage::disk('public')->exists($diskPath)) {
                Storage::disk('public')->delete($diskPath);
            }
        } elseif (str_starts_with($fotoPath, 'uploads/returs/') && !str_contains($fotoPath, '..')) {
            $fullPath = public_path($fotoPath);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }
    }

    public function store(Request $request)
    {
        $unexpectedFileKeys = array_diff(array_keys($request->allFiles()), ['foto_retur', 'photo']);
        if (!empty($unexpectedFileKeys)) {
            throw ValidationException::withMessages([
                'foto_retur' => 'Field berkas unggahan tidak valid.',
            ]);
        }

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
            'reason' => 'required|string|max:255',
            'return_date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'status' => 'required|string|in:New,Processed,Accepted,Rejected,Done,rejected',
            'foto_retur' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:5120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = $request->only(['customer_name', 'order_id', 'reason', 'return_date', 'product_name', 'status']);
        $file = $request->file('foto_retur') ?? $request->file('photo');

        if ($file) {
            $storedPath = Storage::disk('public')->putFile('uploads/returs', $file);
            $data['foto_retur'] = 'storage/' . $storedPath;
        }

        // Simpan retur ke database - gunakan cara eksplisit
        $retur = new Retur();
        $retur->customer_name = $data['customer_name'];
        $retur->order_id = $data['order_id'];
        $retur->reason = $data['reason'];
        $retur->return_date = $data['return_date'];
        $retur->product_name = $data['product_name'];
        $retur->status = $data['status'];
        $retur->foto_retur = $data['foto_retur'] ?? null;
        $retur->save();

        $returSaved = Retur::findOrFail($retur->id);

        // Kirim notifikasi email berdasarkan data tersimpan tersebut
        try {
            NotificationService::sendToKasir(new ReturProdukKasirMail($returSaved));
        } catch (\Exception $e) {
            Log::error('Gagal mengirim email retur kasir: ' . $e->getMessage());
        }

        return redirect()->route('returs.index')->with('success', 'Return added successfully!');
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $retur = Retur::findOrFail($id);

        $unexpectedFileKeys = array_diff(array_keys($request->allFiles()), ['foto_retur', 'photo']);
        if (!empty($unexpectedFileKeys)) {
            throw ValidationException::withMessages([
                'foto_retur' => 'Field berkas unggahan tidak valid.',
            ]);
        }

        $request->validate([
            'status' => 'required|string|in:New,Processed,Accepted,Rejected,Done,rejected',
            'catatan_admin' => 'nullable|string|max:2000',
            'customer_name' => 'nullable|string|max:255',
            'order_id' => 'nullable|string|max:255',
            'reason' => 'nullable|string|max:255',
            'return_date' => 'nullable|date',
            'product_name' => 'nullable|string|max:255',
            'foto_retur' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:5120',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $data = [
            'status' => $request->status,
            'catatan_admin' => $request->catatan_admin,
        ];

        // Fallback untuk pemanggilan legacy/test
        if ($request->filled('customer_name')) {
            $data['customer_name'] = $request->customer_name;
        }
        if ($request->filled('order_id')) {
            $data['order_id'] = $request->order_id;
        }
        if ($request->filled('reason')) {
            $data['reason'] = $request->reason;
        }
        if ($request->filled('return_date')) {
            $data['return_date'] = $request->return_date;
        }
        if ($request->filled('product_name')) {
            $data['product_name'] = $request->product_name;
        }

        $file = $request->file('foto_retur') ?? $request->file('photo');
        if ($file) {
            $this->deleteReturPhoto($retur->foto_retur);
            $storedPath = Storage::disk('public')->putFile('uploads/returs', $file);
            $data['foto_retur'] = 'storage/' . $storedPath;
        }

        $retur->update($data);

        return redirect()->route('returs.index')->with('success', 'Return updated successfully!');
    }

    /**
     * Quick update return status from modal or detail view
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:New,Processed,Accepted,Rejected,Done,rejected',
            'catatan_admin' => 'nullable|string|max:2000',
        ]);

        $retur = Retur::findOrFail($id);
        $retur->status = $request->status;
        if ($request->has('catatan_admin')) {
            $retur->catatan_admin = $request->catatan_admin;
        }
        $retur->save();

        return redirect()->back()->with('success', 'Status retur #' . $retur->id . ' berhasil diubah menjadi ' . $retur->status_label . '.');
    }


    /**
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $retur = Retur::findOrFail($id);
        $this->deleteReturPhoto($retur->foto_retur);
        $retur->delete();

        return redirect()->route('returs.index')->with('success', 'Return deleted successfully!');
    }

    public function exportReturs()
    {
        $fileName = 'data-retur-produk-' . date('Y-m-d-His') . '.csv';
        $returs = Retur::latest()->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($returs) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputs($file, "sep=;\n");
            fputcsv($file, ['ID Retur', 'No. Pesanan', 'Tanggal Retur', 'Nama Pelanggan', 'Nama Produk', 'Alasan Retur', 'Status'], ';');

            foreach ($returs as $r) {
                fputcsv($file, [
                    $r->id,
                    \App\Services\SalesReportService::sanitizeSpreadsheetCell($r->order_id ?? '-'),
                    $r->return_date ? date('d-m-Y', strtotime($r->return_date)) : '-',
                    \App\Services\SalesReportService::sanitizeSpreadsheetCell($r->customer_name ?? '-'),
                    \App\Services\SalesReportService::sanitizeSpreadsheetCell($r->product_name ?? '-'),
                    \App\Services\SalesReportService::sanitizeSpreadsheetCell($r->reason ?? '-'),
                    \App\Services\SalesReportService::sanitizeSpreadsheetCell($r->status ?? 'New')
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:returs,id',
        ]);

        $returs = Retur::whereIn('id', $validated['ids'])->get();
        foreach ($returs as $retur) {
            $this->deleteReturPhoto($retur->foto_retur);
            $retur->delete();
        }

        $count = count($validated['ids']);
        return redirect()->route('returs.index')->with('success', "{$count} data retur berhasil dihapus.");
    }
}

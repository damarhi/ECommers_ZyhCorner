<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Diskon;
use App\Models\Produk;
use Illuminate\Http\Request;

class DiskonController extends Controller
{
    public function index(Request $request)
    {
        $query = Diskon::with('produk');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama_diskon', 'like', '%' . $search . '%')
                  ->orWhereHas('produk', function($pQuery) use ($search) {
                      $pQuery->where('nama_produk', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tipe')) {
            $query->where('tipe_diskon', $request->input('tipe'));
        }

        $diskons = $query->latest()->paginate(10)->withQueryString();
        $produks = Produk::all();

        // Statistical Data
        $totalAktif = Diskon::where('status', 'Aktif')->count();
        $totalProdukBerdiskon = Diskon::where('status', 'Aktif')->distinct('id_produk')->count('id_produk');
        $avgDiskonPersen = Diskon::where('status', 'Aktif')->where('tipe_diskon', 'persen')->avg('nilai_diskon') ?? 0;

        return view('Admin.kelola_produk.discounts', compact(
            'diskons',
            'produks',
            'totalAktif',
            'totalProdukBerdiskon',
            'avgDiskonPersen'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_produk' => 'required',
            'nama_diskon' => 'required|string|max:255',
            'tipe_diskon' => 'required|in:persen,nominal',
            'nilai_diskon' => 'required|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        $productIds = is_array($request->input('id_produk')) ? $request->input('id_produk') : [$request->input('id_produk')];

        // Ensure each selected product exists
        $request->validate([
            'id_produk.*' => 'exists:produks,id_produk',
        ]);

        unset($validated['id_produk']);

        foreach ($productIds as $idProduk) {
            Diskon::create(array_merge($validated, [
                'id_produk' => $idProduk,
            ]));
        }

        $count = count($productIds);
        return redirect()->route('discounts.index')->with('success', "Diskon berhasil ditambahkan untuk {$count} produk.");
    }

    public function update(Request $request, Diskon $discount)
    {
        $validated = $request->validate([
            'id_produk' => 'required|exists:produks,id_produk',
            'nama_diskon' => 'required|string|max:255',
            'tipe_diskon' => 'required|in:persen,nominal',
            'nilai_diskon' => 'required|numeric|min:0',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        $discount->update($validated);

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil diperbarui.');
    }

    public function destroy(Diskon $discount)
    {
        $discount->delete();

        return redirect()->route('discounts.index')->with('success', 'Diskon berhasil dihapus.');
    }

    public function toggleStatus(Diskon $discount)
    {
        $discount->status = ($discount->status === 'Aktif') ? 'Nonaktif' : 'Aktif';
        $discount->save();

        return redirect()->route('discounts.index')->with('success', 'Status diskon berhasil diubah.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:diskons,id_diskon',
        ]);

        $count = count($validated['ids']);
        Diskon::whereIn('id_diskon', $validated['ids'])->delete();

        return redirect()->route('discounts.index')->with('success', "{$count} diskon berhasil dihapus.");
    }
}

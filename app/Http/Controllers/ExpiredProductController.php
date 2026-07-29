<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\ProdukKadaluarsa;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ExpiredProductController extends Controller
{
    /**
     * Otomatis melakukan sinkronisasi produk showcase yang telah memasuki tanggal kadaluarsa ke tabel dataset produk_kadaluarsas.
     */
    protected function syncExpiredProducts()
    {
        Produk::checkAndEmptyExpiredProducts();
    }

    public function index(Request $request)
    {
        // Jalankan sinkronisasi otomatis setiap halaman diakses
        $this->syncExpiredProducts();

        $query = ProdukKadaluarsa::with('produk.kategori');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('produk', function($q) use ($search) {
                $q->where('nama_produk', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status_tindakan', $request->input('status'));
        }

        $expiredItems = $query->latest('tanggal_expired')->paginate(10)->withQueryString();

        // Data KPI Rangkuman
        $totalExpired = ProdukKadaluarsa::count();
        $perluTindakan = ProdukKadaluarsa::where('status_tindakan', 'Perlu Tindakan')->count();
        $sudahDitindak = ProdukKadaluarsa::where('status_tindakan', '!=', 'Perlu Tindakan')->count();

        return view('Admin.kelola_produk.expired_products', compact(
            'expiredItems',
            'totalExpired',
            'perluTindakan',
            'sudahDitindak'
        ));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status_tindakan' => 'required|in:Perlu Tindakan,Diarsipkan,Dimusnahkan,Diretur',
            'catatan' => 'nullable|string',
        ]);

        $item = ProdukKadaluarsa::findOrFail($id);
        $item->update($validated);

        return redirect()->route('products.expired.index')->with('success', 'Status produk kadaluarsa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = ProdukKadaluarsa::findOrFail($id);
        $item->delete();

        return redirect()->route('products.expired.index')->with('success', 'Data produk kadaluarsa berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:produk_kadaluarsas,id_expired',
        ]);

        $count = count($validated['ids']);
        ProdukKadaluarsa::whereIn('id_expired', $validated['ids'])->delete();

        return redirect()->route('products.expired.index')->with('success', "{$count} data produk kadaluarsa berhasil dihapus.");
    }
}

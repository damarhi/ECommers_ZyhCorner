<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class ProdukController extends Controller
{
    public function index(Request $request)
    {
        Produk::checkAndEmptyExpiredProducts();

        $query = Produk::with('kategori')
            ->where(function($q) {
                $q->where('jenis_produk', 'showcase')
                  ->orWhereNull('jenis_produk');
            });

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_produk', 'like', $searchTerm)
                  ->orWhere('deskripsi', 'like', $searchTerm);
            });
        }

        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status_produk', $request->status);
        }

        if ($request->has('diskon')) {
            // Mock discount by filtering products under 100,000
            $query->where('harga', '<', 100000);
        }

        if ($request->has('terbaru')) {
            $query->latest();
        } else {
            $query->latest();
        }

        $produks = $query->paginate(10);
        return view('Admin.kelola_produk.products', compact('produks'));
    }

    public function customIndex(Request $request)
    {
        $query = Produk::with('kategori')
            ->where('jenis_produk', 'kustom');

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('nama_produk', 'like', $searchTerm)
                  ->orWhere('deskripsi', 'like', $searchTerm);
            });
        }

        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status_produk', $request->status);
        }

        if ($request->has('diskon')) {
            // Mock discount by filtering products under 100,000
            $query->where('harga', '<', 100000);
        }

        if ($request->has('terbaru')) {
            $query->latest();
        } else {
            $query->latest();
        }

        $produks = $query->paginate(10);
        return view('Admin.kelola_produk.custom_products', compact('produks'));
    }

    public function create(Request $request)
    {
        $kategoris = Kategori::all();
        $produk = new Produk();
        if ($request->filled('jenis')) {
            $produk->jenis_produk = $request->query('jenis');
        }
        return view('Admin.kelola_produk.products_form', compact('produk', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:kategoris,id_kategori',
            'jenis_produk' => 'nullable|string|in:showcase,kustom',
            'harga' => 'required|integer|min:0',
            'harga_max' => 'nullable|integer|min:0',
            'status_produk' => 'required',
            'deskripsi' => 'nullable|string',
            'tanggal_expired' => 'nullable|date',
            'foto_1' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_4' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        if (empty($validated['jenis_produk'])) {
            $validated['jenis_produk'] = $request->input('jenis_produk', 'showcase');
        }

        if ($validated['jenis_produk'] === 'showcase') {
            $validated['harga_max'] = null;
        } else if ($validated['jenis_produk'] === 'kustom') {
            $validated['tanggal_expired'] = null;
        }

        foreach (['foto_1', 'foto_2', 'foto_3', 'foto_4'] as $foto) {
            if ($request->hasFile($foto)) {
                $validated[$foto] = $request->file($foto)->store('products', 'public');
            }
        }

        Produk::create($validated);

        $redirectRoute = ($validated['jenis_produk'] === 'kustom') ? 'products.custom' : 'products.index';
        return redirect()->route($redirectRoute)->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(Produk $product)
    {
        $kategoris = Kategori::all();
        return view('Admin.kelola_produk.products_form', ['produk' => $product, 'kategoris' => $kategoris]);
    }

    public function update(Request $request, Produk $product)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:kategoris,id_kategori',
            'jenis_produk' => 'nullable|string|in:showcase,kustom',
            'harga' => 'required|integer|min:0',
            'harga_max' => 'nullable|integer|min:0',
            'status_produk' => 'required',
            'deskripsi' => 'nullable|string',
            'tanggal_expired' => 'nullable|date',
            'foto_1' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_4' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        if (empty($validated['jenis_produk'])) {
            $validated['jenis_produk'] = $product->jenis_produk ?? 'showcase';
        }

        if ($validated['jenis_produk'] === 'showcase') {
            $validated['harga_max'] = null;
        } else if ($validated['jenis_produk'] === 'kustom') {
            $validated['tanggal_expired'] = null;
        }

        foreach (['foto_1', 'foto_2', 'foto_3', 'foto_4'] as $foto) {
            if ($request->hasFile($foto)) {
                if ($product->$foto) {
                    Storage::disk('public')->delete($product->$foto);
                }
                $validated[$foto] = $request->file($foto)->store('products', 'public');
            }
        }

        $product->update($validated);

        $redirectRoute = ($validated['jenis_produk'] === 'kustom') ? 'products.custom' : 'products.index';
        return redirect()->route($redirectRoute)->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Produk $product)
    {
        $isKustom = ($product->jenis_produk === 'kustom');
        foreach (['foto_1', 'foto_2', 'foto_3', 'foto_4'] as $foto) {
            if ($product->$foto) {
                Storage::disk('public')->delete($product->$foto);
            }
        }
        $product->delete();

        $redirectRoute = $isKustom ? 'products.custom' : 'products.index';
        return redirect()->route($redirectRoute)->with('success', 'Produk berhasil dihapus');
    }
}

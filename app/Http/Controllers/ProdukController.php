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
        $query = Produk::with('kategori');

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
        return view('admin.products', compact('produks'));
    }

    public function create()
    {
        $kategoris = Kategori::all();
        $produk = new Produk();
        return view('admin.products_form', compact('produk', 'kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:kategoris,id_kategori',
            'harga' => 'required|integer|min:0',
            'status_produk' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'tanggal_expired' => 'nullable|date',
            'foto_1' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_4' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        foreach (['foto_1', 'foto_2', 'foto_3', 'foto_4'] as $foto) {
            if ($request->hasFile($foto)) {
                $validated[$foto] = $request->file($foto)->store('products', 'public');
            }
        }

        Produk::create($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    public function edit(Produk $product)
    {
        $kategoris = Kategori::all();
        return view('admin.products_form', ['produk' => $product, 'kategoris' => $kategoris]);
    }

    public function update(Request $request, Produk $product)
    {
        $validated = $request->validate([
            'nama_produk' => 'required|string|max:255',
            'id_kategori' => 'nullable|exists:kategoris,id_kategori',
            'harga' => 'required|integer|min:0',
            'status_produk' => 'required|integer|min:0',
            'deskripsi' => 'nullable|string',
            'tanggal_expired' => 'nullable|date',
            'foto_1' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_4' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        foreach (['foto_1', 'foto_2', 'foto_3', 'foto_4'] as $foto) {
            if ($request->hasFile($foto)) {
                if ($product->$foto) {
                    Storage::disk('public')->delete($product->$foto);
                }
                $validated[$foto] = $request->file($foto)->store('products', 'public');
            }
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui');
    }

    public function destroy(Produk $product)
    {
        foreach (['foto_1', 'foto_2', 'foto_3', 'foto_4'] as $foto) {
            if ($product->$foto) {
                Storage::disk('public')->delete($product->$foto);
            }
        }
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus');
    }
}

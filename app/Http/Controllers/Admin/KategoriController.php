<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = Kategori::withCount('produks')->get();
        return view('Admin.kelola_produk.categories', compact('kategoris'));
    }

    public function create()
    {
        return view('Admin.kelola_produk.categories');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'gambar_kategori' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('gambar_kategori')->store('kategori_images', 'public');

        Kategori::create([
            'nama_kategori' => $request->nama_kategori,
            'gambar_kategori' => $imagePath,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function show(Kategori $kategori)
    {
        //
    }

    public function edit($id)
    {
        $kategori = Kategori::findOrFail($id);
        return view('Admin.kelola_produk.categories', compact('kategori'));
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::findOrFail($id);

        $request->validate([
            'nama_kategori' => 'required|string|max:255',
            'gambar_kategori' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('gambar_kategori')) {
            if ($kategori->gambar_kategori) {
                Storage::disk('public')->delete($kategori->gambar_kategori);
            }
            $imagePath = $request->file('gambar_kategori')->store('kategori_images', 'public');
            $kategori->gambar_kategori = $imagePath;
        }

        $kategori->nama_kategori = $request->nama_kategori;
        $kategori->save();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kategori = Kategori::findOrFail($id);
        if ($kategori->gambar_kategori) {
            Storage::disk('public')->delete($kategori->gambar_kategori);
        }
        $kategori->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}

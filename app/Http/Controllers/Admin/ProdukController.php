<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        Produk::checkAndEmptyExpiredProducts();

        $query = Produk::with(['kategori', 'diskonAktif'])
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
            $query->whereHas('diskonAktif');
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
        $query = Produk::with(['kategori', 'diskonAktif'])
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
            $query->whereHas('diskonAktif');
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
            'bisa_custom_teks' => 'nullable|boolean',
            'is_signature' => 'nullable|boolean',
            'foto_1' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_4' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        $validated['bisa_custom_teks'] = $request->boolean('bisa_custom_teks');
        $validated['is_signature'] = $request->boolean('is_signature');

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
            'bisa_custom_teks' => 'nullable|boolean',
            'is_signature' => 'nullable|boolean',
            'foto_1' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_2' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_3' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
            'foto_4' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        $validated['bisa_custom_teks'] = $request->boolean('bisa_custom_teks');
        $validated['is_signature'] = $request->boolean('is_signature');

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

    public function exportProducts(Request $request)
    {
        $jenis = $request->get('jenis', 'showcase');
        $fileName = 'data-produk-' . $jenis . '-' . date('Y-m-d-His') . '.csv';

        $query = Produk::with('kategori');
        if ($jenis === 'kustom') {
            $query->where('jenis_produk', 'kustom');
        } else {
            $query->where(function($q) {
                $q->where('jenis_produk', 'showcase')->orWhereNull('jenis_produk');
            });
        }
        $products = $query->latest()->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($products, $jenis) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputs($file, "sep=;\n");
            if ($jenis === 'kustom') {
                fputcsv($file, ['ID Produk', 'Nama Produk', 'Kategori', 'Jenis', 'Status Stok', 'Harga Minimum', 'Harga Maksimum'], ';');
                foreach ($products as $p) {
                    fputcsv($file, [
                        $p->id_produk,
                        $p->nama_produk,
                        $p->kategori ? $p->kategori->nama_kategori : 'Tanpa Kategori',
                        'Kustom',
                        $p->status_produk,
                        'Rp ' . number_format($p->harga ?? 0, 0, ',', '.'),
                        'Rp ' . number_format($p->harga_max ?? $p->harga ?? 0, 0, ',', '.')
                    ], ';');
                }
            } else {
                fputcsv($file, ['ID Produk', 'Nama Produk', 'Kategori', 'Jenis', 'Status Stok', 'Harga Produk', 'Tanggal Expired'], ';');
                foreach ($products as $p) {
                    fputcsv($file, [
                        $p->id_produk,
                        $p->nama_produk,
                        $p->kategori ? $p->kategori->nama_kategori : 'Tanpa Kategori',
                        'Showcase',
                        $p->status_produk,
                        'Rp ' . number_format($p->harga ?? 0, 0, ',', '.'),
                        $p->tanggal_expired ? date('d-m-Y', strtotime($p->tanggal_expired)) : '-'
                    ], ';');
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:produks,id_produk',
        ]);

        $products = Produk::whereIn('id_produk', $validated['ids'])->get();
        foreach ($products as $product) {
            if ($product->foto_1 && Storage::disk('public')->exists($product->foto_1)) {
                Storage::disk('public')->delete($product->foto_1);
            }
            if ($product->foto_2 && Storage::disk('public')->exists($product->foto_2)) {
                Storage::disk('public')->delete($product->foto_2);
            }
            if ($product->foto_3 && Storage::disk('public')->exists($product->foto_3)) {
                Storage::disk('public')->delete($product->foto_3);
            }
            $product->delete();
        }

        $count = count($validated['ids']);
        $redirectRoute = $request->input('redirect_to') === 'custom' ? 'products.custom' : 'products.index';

        return redirect()->route($redirectRoute)->with('success', "{$count} produk berhasil dihapus.");
    }
}

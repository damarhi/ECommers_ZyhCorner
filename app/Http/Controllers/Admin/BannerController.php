<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('judul_banner', 'like', '%' . $search . '%')
                  ->orWhere('subjudul', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('posisi')) {
            $query->where('posisi_banner', $request->input('posisi'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $banners = $query->orderBy('urutan', 'asc')->latest()->paginate(10)->withQueryString();

        // Rangkuman Statistik KPI
        $totalBanners = Banner::count();
        $totalAktif = Banner::where('status', 'Aktif')->count();
        $totalPosisi = Banner::distinct('posisi_banner')->count('posisi_banner');

        return view('Admin.kelola_carousel.banners', compact(
            'banners',
            'totalBanners',
            'totalAktif',
            'totalPosisi'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul_banner' => 'required|string|max:255',
            'subjudul' => 'nullable|string|max:255',
            'gambar_banner' => 'required|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'posisi_banner' => 'required|in:home_hero,shop_header,custom_header,popup_promo',
            'link_url' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        if ($request->hasFile('gambar_banner')) {
            $validated['gambar_banner'] = $request->file('gambar_banner')->store('banners', 'public');
        }

        $validated['urutan'] = $validated['urutan'] ?? 0;

        Banner::create($validated);

        return redirect()->route('banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'judul_banner' => 'required|string|max:255',
            'subjudul' => 'nullable|string|max:255',
            'gambar_banner' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:4096',
            'posisi_banner' => 'required|in:home_hero,shop_header,custom_header,popup_promo',
            'link_url' => 'nullable|string|max:255',
            'urutan' => 'nullable|integer|min:0',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        if ($request->hasFile('gambar_banner')) {
            if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner)) {
                Storage::disk('public')->delete($banner->gambar_banner);
            }
            $validated['gambar_banner'] = $request->file('gambar_banner')->store('banners', 'public');
        }

        $validated['urutan'] = $validated['urutan'] ?? 0;

        $banner->update($validated);

        return redirect()->route('banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner)) {
            Storage::disk('public')->delete($banner->gambar_banner);
        }

        $banner->delete();

        return redirect()->route('banners.index')->with('success', 'Banner berhasil dihapus.');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->status = ($banner->status === 'Aktif') ? 'Nonaktif' : 'Aktif';
        $banner->save();

        return redirect()->route('banners.index')->with('success', 'Status banner berhasil diubah.');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:banners,id_banner',
        ]);

        $banners = Banner::whereIn('id_banner', $validated['ids'])->get();

        foreach ($banners as $banner) {
            if ($banner->gambar_banner && Storage::disk('public')->exists($banner->gambar_banner)) {
                Storage::disk('public')->delete($banner->gambar_banner);
            }
            $banner->delete();
        }

        $count = count($validated['ids']);
        return redirect()->route('banners.index')->with('success', "{$count} banner berhasil dihapus.");
    }
}

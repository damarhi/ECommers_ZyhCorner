<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

class AdminFaqController extends Controller
{
    /**
     * Display a listing of the FAQs for Admin.
     */
    public function index()
    {
        $faqs = Faq::orderBy('urutan', 'asc')->orderBy('id', 'asc')->get();
        return view('Admin.kelola_carousel.faq_settings', compact('faqs'));
    }

    /**
     * Store a newly created FAQ item.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pertanyaan' => 'required|string',
            'jawaban' => 'required|string',
            'kategori' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
        ]);

        Faq::create([
            'pertanyaan' => $request->pertanyaan,
            'jawaban' => $request->jawaban,
            'kategori' => $request->kategori,
            'urutan' => $request->urutan ?? 0,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'FAQ baru berhasil ditambahkan!');
    }

    /**
     * Update the specified FAQ item.
     */
    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'pertanyaan' => 'required|string',
            'jawaban' => 'required|string',
            'kategori' => 'required|string|max:100',
            'urutan' => 'nullable|integer',
        ]);

        $faq->update([
            'pertanyaan' => $request->pertanyaan,
            'jawaban' => $request->jawaban,
            'kategori' => $request->kategori,
            'urutan' => $request->urutan ?? 0,
        ]);

        return redirect()->back()->with('success', 'FAQ berhasil diperbarui!');
    }

    /**
     * Toggle active status of the specified FAQ item.
     */
    public function toggleStatus($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_active = !$faq->is_active;
        $faq->save();

        return redirect()->back()->with('success', 'Status FAQ berhasil diubah!');
    }

    /**
     * Remove the specified FAQ item.
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return redirect()->back()->with('success', 'FAQ berhasil dihapus!');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('subjek', 'like', '%' . $search . '%')
                  ->orWhere('pesan', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $messages = $query->latest()->paginate(10)->withQueryString();

        $totalMessages = ContactMessage::count();
        $unreadMessages = ContactMessage::where('status', 'Belum Dibaca')->count();
        $readMessages = ContactMessage::where('status', 'Dibaca')->count();
        $repliedMessages = ContactMessage::where('status', 'Dibalas')->count();

        return view('Admin.pesan_kontak.index', compact(
            'messages',
            'totalMessages',
            'unreadMessages',
            'readMessages',
            'repliedMessages'
        ));
    }

    public function show($id)
    {
        $message = ContactMessage::findOrFail($id);

        if ($message->status === 'Belum Dibaca') {
            $message->update(['status' => 'Dibaca']);
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $message,
            ]);
        }

        return redirect()->route('messages.index');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:Belum Dibaca,Dibaca,Dibalas',
        ]);

        $message = ContactMessage::findOrFail($id);
        $message->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Status pesan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'Pesan kontak berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (!empty($ids) && is_array($ids)) {
            ContactMessage::whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', count($ids) . ' pesan kontak berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak ada pesan yang dipilih untuk dihapus.');
    }
}

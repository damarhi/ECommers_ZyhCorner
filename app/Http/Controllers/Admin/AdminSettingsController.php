<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $admin = auth()->user();

        // Standard or cached store info fallback
        $storeInfo = [
            'name' => config('app.store_name', 'Zyh Corner'),
            'phone' => config('app.store_phone', '08123456789'),
            'email' => config('app.store_email', 'contact@zyhcorner.com'),
            'address' => config('app.store_address', 'Jl. Utama No. 123, Kota Subang'),
            'operating_hours' => config('app.store_hours', 'Senin - Minggu (08:00 - 21:00)'),
        ];

        return view('Admin.kelola_akun.settings', compact('admin', 'storeInfo'));
    }

    public function updateProfile(Request $request)
    {
        $admin = auth()->user();

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->user_id . ',user_id',
            'no_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:4096',
        ]);

        if ($request->hasFile('foto')) {
            if ($admin->foto && Storage::disk('public')->exists($admin->foto)) {
                Storage::disk('public')->delete($admin->foto);
            }
            $validated['foto'] = $request->file('foto')->store('profiles', 'public');
        }

        $admin->update($validated);

        return redirect()->route('settings.index')->with('success', 'Profil Admin berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $admin = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini tidak cocok.']);
        }

        $admin->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return redirect()->route('settings.index')->with('success', 'Password Admin berhasil diperbarui.');
    }

    public function updateStoreInfo(Request $request)
    {
        $validated = $request->validate([
            'store_name' => 'required|string|max:255',
            'store_phone' => 'required|string|max:30',
            'store_email' => 'required|email|max:255',
            'store_address' => 'required|string|max:500',
            'store_hours' => 'nullable|string|max:255',
        ]);

        // Store configuration parameters in session or file cache if needed
        session([
            'store_name' => $validated['store_name'],
            'store_phone' => $validated['store_phone'],
            'store_email' => $validated['store_email'],
            'store_address' => $validated['store_address'],
            'store_hours' => $validated['store_hours'],
        ]);

        return redirect()->route('settings.index')->with('success', 'Informasi Toko berhasil diperbarui.');
    }
}

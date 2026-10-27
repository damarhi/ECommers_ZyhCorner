<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
class AdminSettingsController extends Controller
{
    public function index()
    {
        $admin = auth()->user();

        // Ambil informasi toko langsung dari database store_settings
        $storeInfo = [
            'name' => StoreSetting::get('store_name', config('app.store_name', 'Zyh Corner')),
            'phone' => StoreSetting::get('store_phone', config('app.store_phone', '08123456789')),
            'email' => StoreSetting::get('store_email', config('app.store_email', 'contact@zyhcorner.com')),
            'address' => StoreSetting::get('store_address', config('app.store_address', 'Jl. Utama No. 123, Kota Subang')),
            'operating_hours' => StoreSetting::get('store_hours', config('app.store_hours', 'Senin - Minggu (08:00 - 21:00)')),
            'tagline' => StoreSetting::get('store_tagline', 'Pastry & Bakery Berkualitas'),
            'social_instagram' => StoreSetting::get('social_instagram', 'https://instagram.com/zyhcorner'),
            'social_facebook' => StoreSetting::get('social_facebook', 'https://facebook.com/zyhcorner'),
            'social_tiktok' => StoreSetting::get('social_tiktok', 'https://tiktok.com/@zyhcorner'),
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
            'store_tagline' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_tiktok' => 'nullable|string|max:255',
        ]);

        // Simpan langsung ke database store_settings
        StoreSetting::set('store_name', $validated['store_name'], 'Nama Toko', 'general');
        StoreSetting::set('store_phone', $validated['store_phone'], 'Nomor WhatsApp / Telepon', 'contact');
        StoreSetting::set('store_email', $validated['store_email'], 'Email Layanan Pelanggan', 'contact');
        StoreSetting::set('store_address', $validated['store_address'], 'Alamat Lengkap Toko', 'contact');
        StoreSetting::set('store_hours', $validated['store_hours'] ?? '', 'Jam Operasional Toko', 'general');

        if ($request->has('store_tagline')) {
            StoreSetting::set('store_tagline', $validated['store_tagline'] ?? '', 'Slogan / Tagline Toko', 'general');
        }
        if ($request->has('social_instagram')) {
            StoreSetting::set('social_instagram', $validated['social_instagram'] ?? '', 'Instagram', 'social');
        }
        if ($request->has('social_facebook')) {
            StoreSetting::set('social_facebook', $validated['social_facebook'] ?? '', 'Facebook', 'social');
        }
        if ($request->has('social_tiktok')) {
            StoreSetting::set('social_tiktok', $validated['social_tiktok'] ?? '', 'TikTok', 'social');
        }

        // Tetap sinkronkan session untuk kompatibilitas
        session([
            'store_name' => $validated['store_name'],
            'store_phone' => $validated['store_phone'],
            'store_email' => $validated['store_email'],
            'store_address' => $validated['store_address'],
            'store_hours' => $validated['store_hours'] ?? '',
        ]);

        return redirect(route('settings.index') . '#store-tab')->with('success', 'Informasi Toko berhasil disimpan ke database.');
    }
}

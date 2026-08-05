<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerSettingsController extends Controller
{
    public function settings()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user->alamat) {
            $customer = Customer::where('user_id', $user->user_id)
                ->orWhere('contact', $user->no_hp)
                ->orWhere('name', $user->nama)
                ->first();
            if ($customer) {
                if (!$customer->user_id) {
                    $customer->update(['user_id' => $user->user_id]);
                }
                if ($customer->location) {
                    $user->update(['alamat' => $customer->location]);
                }
            }
        }
        return view('Pelanggan.settings', compact('user'));
    }

    public function updateAccount(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'no_hp' => 'required|numeric|unique:users,no_hp,' . $user->user_id . ',user_id',
        ]);

        $user->update([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun berhasil diperbarui!',
            'user' => $user
        ]);
    }

    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|different:current_password',
            'confirm_password' => 'required|same:new_password',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah.'
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui!'
        ]);
    }

    public function updateAddress(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'alamat' => 'required|string',
        ]);

        $user->update([
            'alamat' => $request->alamat,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat tagihan berhasil diperbarui!',
            'user' => $user
        ]);
    }

    public function updateAvatar(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            // Create directory if not exists
            $path = public_path('uploads/avatars');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }
            
            $file->move($path, $filename);

            // Delete old avatar if exists and not default
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                @unlink(public_path($user->avatar));
            }

            $avatarPath = 'uploads/avatars/' . $filename;
            $user->update([
                'avatar' => $avatarPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diperbarui!',
                'avatar_url' => asset($avatarPath)
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Tidak ada file yang diunggah.'
        ], 400);
    }
}

<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CustomerSettingsController extends Controller
{
    public function settings()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $defaultAddress = \App\Models\UserAddress::where('user_id', $user->user_id)->where('is_default', true)->first();

        // If user has default address in user_addresses, ensure $user->alamat is synced
        if ($defaultAddress && $defaultAddress->street_address) {
            if ($user->alamat !== $defaultAddress->street_address) {
                $user->update(['alamat' => $defaultAddress->street_address]);
                $user->alamat = $defaultAddress->street_address;
            }
        } elseif (!$user->alamat) {
            $customer = Customer::where('user_id', $user->user_id)->first();
            if (!$customer && !empty($user->no_hp)) {
                $variants = Customer::phoneVariants($user->no_hp);
                if (!empty($variants)) {
                    $customer = Customer::whereNull('user_id')->whereIn('contact', $variants)->first();
                }
            }
            if ($customer) {
                if (!$customer->user_id) {
                    $customer->update(['user_id' => $user->user_id]);
                }
                if ($customer->location) {
                    $user->update(['alamat' => $customer->location]);
                    $user->alamat = $customer->location;
                }
            }
        }

        // If user has alamat but no user_addresses record yet, auto-seed default address
        if ($user->alamat && !$defaultAddress) {
            $defaultAddress = \App\Models\UserAddress::create([
                'user_id' => $user->user_id,
                'label' => 'Alamat Utama',
                'recipient_name' => $user->nama,
                'recipient_phone' => $user->no_hp ?? '',
                'street_address' => $user->alamat,
                'is_default' => true,
            ]);
        }

        $allAddresses = \App\Models\UserAddress::where('user_id', $user->user_id)->orderByDesc('is_default')->get();

        return view('Pelanggan.settings', compact('user', 'defaultAddress', 'allAddresses'));
    }

    public function updateAccount(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $oldEmail = $user->email;

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

        // Keep legacy orders & customer profile synchronized across email/phone/name changes
        if ($oldEmail && $oldEmail !== $request->email) {
            Pesanan::where(function ($q) use ($user, $oldEmail) {
                $q->where('user_id', $user->user_id)
                  ->orWhere(function ($sub) use ($oldEmail) {
                      $sub->whereNull('user_id')->where('email_pembeli', $oldEmail);
                  });
            })->update([
                'user_id' => $user->user_id,
                'email_pembeli' => $request->email,
            ]);
        }

        Customer::where('user_id', $user->user_id)->update([
            'name' => $request->nama,
            'contact' => $request->no_hp,
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
            'notes' => 'nullable|string',
        ]);
        $user->update([
            'alamat' => $request->alamat,
        ]);

        $customer = Customer::where('user_id', $user->user_id)->first();
        if ($customer) {
            $customer->update(['location' => $request->alamat]);
        }
        // Sync or update default address in user_addresses
        $defaultAddr = \App\Models\UserAddress::where('user_id', $user->user_id)->where('is_default', true)->first();
        if ($defaultAddr) {
            $dataToUpdate = ['street_address' => $request->alamat];
            if ($request->has('notes')) {
                $dataToUpdate['notes'] = $request->notes;
            }
            $defaultAddr->update($dataToUpdate);
        } else {
            \App\Models\UserAddress::create([
                'user_id' => $user->user_id,
                'label' => 'Alamat Utama',
                'recipient_name' => $user->nama,
                'recipient_phone' => $user->no_hp ?? '',
                'street_address' => $request->alamat,
                'notes' => $request->notes ?? null,
                'is_default' => true,
            ]);
        }
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
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Delete old avatar safely if exists and not default static asset
            if (!empty($user->avatar)) {
                if (str_starts_with($user->avatar, 'storage/')) {
                    $oldDiskPath = substr($user->avatar, 8);
                    if ($oldDiskPath !== '' && Storage::disk('public')->exists($oldDiskPath)) {
                        Storage::disk('public')->delete($oldDiskPath);
                    }
                } elseif (str_starts_with($user->avatar, 'uploads/avatars/') && !str_contains($user->avatar, '..')) {
                    $oldFullPath = public_path($user->avatar);
                    if (is_file($oldFullPath)) {
                        @unlink($oldFullPath);
                    }
                }
            }

            $storedPath = Storage::disk('public')->putFile('uploads/avatars', $file);
            $avatarPath = 'storage/' . $storedPath;
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

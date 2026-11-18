<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cassier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CassierController extends Controller
{
    public function index()
    {
        $cassiers = Cassier::latest()->get();
        return view('Admin.kelola_akun.cassiers', compact('cassiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:cassiers,email|unique:users,email',
            'password' => 'required|string|min:6',
            'status' => 'required|string|in:Active,Deactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cassier_images', 'public');
        }

        $hashedPassword = Hash::make($request->password);

        DB::transaction(function () use ($request, $imagePath, $hashedPassword) {
            Cassier::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'status' => $request->status,
                'image' => $imagePath,
            ]);

            $user = new User();
            $user->forceFill([
                'nama' => $request->name,
                'email' => $request->email,
                'password' => $hashedPassword,
                'role' => 'kasir',
                'no_hp' => null,
            ])->save();
        });

        return redirect()->route('cassiers.index')->with('success', 'Cassier added successfully!');
    }

    public function update(Request $request, $id)
    {
        $cassier = Cassier::findOrFail($id);
        $user = User::where('email', $cassier->email)->where('role', 'kasir')->first()
            ?? User::where('email', $cassier->email)->first();
        $userId = $user ? $user->user_id : 'NULL';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:cassiers,email,' . $id . '|unique:users,email,' . $userId . ',user_id',
            'password' => 'nullable|string|min:6',
            'status' => 'required|string|in:Active,Deactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|extensions:jpeg,png,jpg,gif,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($cassier->image) {
                Storage::disk('public')->delete($cassier->image);
            }
            $cassier->image = $request->file('image')->store('cassier_images', 'public');
        }

        $hashedPassword = $request->filled('password') ? Hash::make($request->password) : null;

        DB::transaction(function () use ($request, $cassier, $user, $hashedPassword) {
            $cassier->name = $request->name;
            $cassier->email = $request->email;
            if ($hashedPassword !== null) {
                $cassier->password = $hashedPassword;
            }
            $cassier->status = $request->status;
            $cassier->save();

            if ($user) {
                $user->nama = $request->name;
                $user->email = $request->email;
                if ($hashedPassword !== null) {
                    $user->password = $hashedPassword;
                }
                $user->save();
            } else {
                $newUser = new User();
                $newUser->forceFill([
                    'nama' => $request->name,
                    'email' => $request->email,
                    'password' => $hashedPassword ?? $cassier->password,
                    'role' => 'kasir',
                    'no_hp' => null,
                ])->save();
            }
        });

        return redirect()->route('cassiers.index')->with('success', 'Cassier updated successfully!');
    }

    public function destroy($id)
    {
        $cassier = Cassier::findOrFail($id);
        if ($cassier->image) {
            Storage::disk('public')->delete($cassier->image);
        }

        DB::transaction(function () use ($cassier) {
            User::where('email', $cassier->email)
                ->where('role', 'kasir')
                ->delete();

            $cassier->delete();
        });

        return redirect()->route('cassiers.index')->with('success', 'Cassier deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:cassiers,id',
        ]);

        $cassiers = Cassier::whereIn('id', $validated['ids'])->get();
        foreach ($cassiers as $c) {
            if ($c->image) {
                Storage::disk('public')->delete($c->image);
            }
        }

        DB::transaction(function () use ($cassiers) {
            foreach ($cassiers as $c) {
                User::where('email', $c->email)
                    ->where('role', 'kasir')
                    ->delete();
                $c->delete();
            }
        });

        $count = count($validated['ids']);
        return redirect()->route('cassiers.index')->with('success', "{$count} akun kasir berhasil dihapus.");
    }
}

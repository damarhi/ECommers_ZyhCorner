<?php

namespace App\Http\Controllers;

use App\Models\Cassier;
use App\Models\User;
use Illuminate\Http\Request;
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cassier_images', 'public');
        }

        Cassier::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'image' => $imagePath
        ]);

        User::create([
            'nama' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'kasir',
            'no_hp' => null,
        ]);

        return redirect()->route('cassiers.index')->with('success', 'Cassier added successfully!');
    }

    public function update(Request $request, $id)
    {
        $cassier = Cassier::findOrFail($id);
        $user = User::where('email', $cassier->email)->first();
        $userId = $user ? $user->user_id : 'NULL';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:cassiers,email,' . $id . '|unique:users,email,' . $userId . ',user_id',
            'password' => 'nullable|string|min:6',
            'status' => 'required|string|in:Active,Deactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($request->hasFile('image')) {
            if ($cassier->image) {
                Storage::disk('public')->delete($cassier->image);
            }
            $cassier->image = $request->file('image')->store('cassier_images', 'public');
        }

        $cassier->name = $request->name;
        $cassier->email = $request->email;
        if ($request->filled('password')) {
            $cassier->password = Hash::make($request->password);
        }
        $cassier->status = $request->status;
        $cassier->save();

        if ($user) {
            $user->nama = $request->name;
            $user->email = $request->email;
            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        } else {
            User::create([
                'nama' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : Hash::make('default_password'),
                'role' => 'kasir',
                'no_hp' => null,
            ]);
        }

        return redirect()->route('cassiers.index')->with('success', 'Cassier updated successfully!');
    }

    public function destroy($id)
    {
        $cassier = Cassier::findOrFail($id);
        if ($cassier->image) {
            Storage::disk('public')->delete($cassier->image);
        }

        $user = User::where('email', $cassier->email)->first();
        if ($user) {
            $user->delete();
        }

        $cassier->delete();

        return redirect()->route('cassiers.index')->with('success', 'Cassier deleted successfully!');
    }
}

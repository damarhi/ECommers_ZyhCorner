<?php

namespace App\Http\Controllers;

use App\Models\Cassier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CassierController extends Controller
{
    public function index()
    {
        $cassiers = Cassier::latest()->get();
        return view('admin.cassiers', compact('cassiers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|string|in:Active,Deactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('cassier_images', 'public');
        }

        Cassier::create([
            'name' => $request->name,
            'status' => $request->status,
            'image' => $imagePath
        ]);

        return redirect()->route('cassiers.index')->with('success', 'Cassier added successfully!');
    }

    public function update(Request $request, $id)
    {
        $cassier = Cassier::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
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
        $cassier->status = $request->status;
        $cassier->save();

        return redirect()->route('cassiers.index')->with('success', 'Cassier updated successfully!');
    }

    public function destroy($id)
    {
        $cassier = Cassier::findOrFail($id);
        if ($cassier->image) {
            Storage::disk('public')->delete($cassier->image);
        }
        $cassier->delete();

        return redirect()->route('cassiers.index')->with('success', 'Cassier deleted successfully!');
    }
}

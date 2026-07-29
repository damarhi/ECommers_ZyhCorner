<?php

namespace App\Http\Controllers;

use App\Models\Retur;
use Illuminate\Http\Request;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $query = Retur::query();
        
        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('customer_name', 'like', $searchTerm)
                  ->orWhere('order_id', 'like', $searchTerm)
                  ->orWhere('product_name', 'like', $searchTerm);
            });
        }

        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status', $request->status);
        } elseif ($request->has('filter') && $request->filter != 'All') {
            $query->where('status', $request->filter);
        }

        $returs = $query->latest()->paginate(10);
        return view('Admin.kelola_penjualan.returs', compact('returs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
            'reason' => 'required|string',
            'return_date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'status' => 'required|string|in:New,Processed,rejected,Accepted,Done'
        ]);

        Retur::create($request->all());

        return redirect()->route('returs.index')->with('success', 'Return added successfully!');
    }

    public function update(Request $request, $id)
    {
        $retur = Retur::findOrFail($id);

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
            'reason' => 'required|string',
            'return_date' => 'required|date',
            'product_name' => 'required|string|max:255',
            'status' => 'required|string|in:New,Processed,rejected,Accepted,Done'
        ]);

        $retur->update($request->all());

        return redirect()->route('returs.index')->with('success', 'Return updated successfully!');
    }

    public function destroy($id)
    {
        $retur = Retur::findOrFail($id);
        $retur->delete();

        return redirect()->route('returs.index')->with('success', 'Return deleted successfully!');
    }
}

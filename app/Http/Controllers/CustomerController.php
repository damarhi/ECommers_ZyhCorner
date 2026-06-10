<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search') && $request->search != '') {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('location', 'like', $searchTerm);
            });
        }

        if ($request->has('status') && is_array($request->status)) {
            $query->whereIn('status', $request->status);
        } elseif ($request->has('filter') && $request->filter != 'All') {
            $query->where('status', $request->filter);
        }

        $customers = $query->latest()->paginate(10);
        return view('admin.customers', compact('customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'orders_count' => 'nullable|integer|min:0',
            'spent' => 'nullable|numeric|min:0',
            'contact' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Blocked'
        ]);

        Customer::create([
            'name' => $request->name,
            'location' => $request->location,
            'orders_count' => $request->orders_count ?? 0,
            'spent' => $request->spent ?? 0,
            'contact' => $request->contact,
            'status' => $request->status
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer added successfully!');
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'orders_count' => 'nullable|integer|min:0',
            'spent' => 'nullable|numeric|min:0',
            'contact' => 'nullable|string|max:255',
            'status' => 'required|string|in:Active,Blocked'
        ]);

        $customer->update([
            'name' => $request->name,
            'location' => $request->location,
            'orders_count' => $request->orders_count ?? 0,
            'spent' => $request->spent ?? 0,
            'contact' => $request->contact,
            'status' => $request->status
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully!');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully!');
    }
}

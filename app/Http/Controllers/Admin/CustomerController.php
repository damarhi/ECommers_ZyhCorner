<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        return view('Admin.kelola_akun.customers', compact('customers'));
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

    public function exportCustomers()
    {
        $fileName = 'data-pelanggan-' . date('Y-m-d-His') . '.csv';
        $customers = Customer::latest()->get();

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputs($file, "sep=;\n");
            fputcsv($file, ['ID Pelanggan', 'Nama Pelanggan', 'Email', 'Kontak/Telepon', 'Lokasi', 'Total Pesanan', 'Total Transaksi', 'Status'], ';');

            foreach ($customers as $c) {
                fputcsv($file, [
                    $c->id,
                    $c->name,
                    $c->email ?? '-',
                    $c->contact ?? '-',
                    $c->location ?? '-',
                    $c->orders_count ?? 0,
                    'Rp ' . number_format($c->spent ?? 0, 0, ',', '.'),
                    $c->status ?? 'Active'
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

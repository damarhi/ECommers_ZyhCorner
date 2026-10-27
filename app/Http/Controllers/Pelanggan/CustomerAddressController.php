<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAddressController extends Controller
{
    /**
     * Get all addresses for the authenticated customer.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $addresses = UserAddress::where('user_id', $user->user_id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'success' => true,
            'addresses' => $addresses
        ]);
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:25',
            'destination_area_id' => 'nullable|string|max:100',
            'destination_area_name' => 'nullable|string|max:150',
            'postal_code' => 'nullable|string|max:10',
            'street_address' => 'required|string',
            'notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        $label = !empty($validated['label']) ? trim($validated['label']) : 'Alamat Rumah';
        $isDefault = !empty($validated['is_default']);

        // Check if user has no existing addresses; if none, force this one as default
        $hasExisting = UserAddress::where('user_id', $user->user_id)->exists();
        if (!$hasExisting) {
            $isDefault = true;
        }

        if ($isDefault) {
            UserAddress::where('user_id', $user->user_id)->update(['is_default' => false]);
            // Sync to user profile as primary address
            $user->update(['alamat' => $validated['street_address']]);
        }

        $address = UserAddress::create([
            'user_id' => $user->user_id,
            'label' => $label,
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'destination_area_id' => $validated['destination_area_id'] ?? null,
            'destination_area_name' => $validated['destination_area_name'] ?? null,
            'postal_code' => $validated['postal_code'] ?? null,
            'street_address' => $validated['street_address'],
            'notes' => $validated['notes'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => $isDefault,
        ]);

        // Sync with customers table if exists
        $customer = Customer::where('user_id', $user->user_id)->first();
        if ($customer && $isDefault) {
            $customer->update(['location' => $validated['street_address']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan!',
            'address' => $address
        ]);
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->user_id)->findOrFail($id);

        $validated = $request->validate([
            'label' => 'nullable|string|max:50',
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:25',
            'destination_area_id' => 'nullable|string|max:100',
            'destination_area_name' => 'nullable|string|max:150',
            'postal_code' => 'nullable|string|max:10',
            'street_address' => 'required|string',
            'notes' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        $label = !empty($validated['label']) ? trim($validated['label']) : $address->label;
        $isDefault = isset($validated['is_default']) ? (bool)$validated['is_default'] : $address->is_default;

        if ($isDefault) {
            if (!$address->is_default) {
                UserAddress::where('user_id', $user->user_id)->where('id', '!=', $id)->update(['is_default' => false]);
            }
            $user->update(['alamat' => $validated['street_address']]);
            $customer = Customer::where('user_id', $user->user_id)->first();
            if ($customer) {
                $customer->update(['location' => $validated['street_address']]);
            }
        }

        $address->update([
            'label' => $label,
            'recipient_name' => $validated['recipient_name'],
            'recipient_phone' => $validated['recipient_phone'],
            'destination_area_id' => $validated['destination_area_id'] ?? $address->destination_area_id,
            'destination_area_name' => $validated['destination_area_name'] ?? $address->destination_area_name,
            'postal_code' => $validated['postal_code'] ?? $address->postal_code,
            'street_address' => $validated['street_address'],
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $address->notes,
            'latitude' => $validated['latitude'] ?? $address->latitude,
            'longitude' => $validated['longitude'] ?? $address->longitude,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui!',
            'address' => $address
        ]);
    }

    /**
     * Remove the specified address.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->user_id)->findOrFail($id);
        $wasDefault = $address->is_default;

        $address->delete();

        // If the deleted address was default, promote the newest remaining address to default
        if ($wasDefault) {
            $nextAddress = UserAddress::where('user_id', $user->user_id)->orderByDesc('id')->first();
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
                $user->update(['alamat' => $nextAddress->street_address]);
            } else {
                $user->update(['alamat' => null]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus!'
        ]);
    }

    /**
     * Set the specified address as default.
     */
    public function setDefault($id)
    {
        $user = Auth::user();
        $address = UserAddress::where('user_id', $user->user_id)->findOrFail($id);

        UserAddress::where('user_id', $user->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        // Sync primary address to user
        $user->update(['alamat' => $address->street_address]);

        $customer = Customer::where('user_id', $user->user_id)->first();
        if ($customer) {
            $customer->update(['location' => $address->street_address]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat utama berhasil diperbarui!',
            'address' => $address
        ]);
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->latest()->get();
        return view('customer.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $request->merge(['pincode' => preg_replace('/\D+/', '', (string) $request->pincode)]);
        $request->validate([
            'label'        => 'required|string|max:50',
            'full_address' => 'required|string|max:500',
            'pincode'      => ['required', 'regex:/^\d{6}$/'],
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
        ]);

        $user = auth()->user();

        // If is_default, unset others
        if ($request->is_default) {
            $user->addresses()->update(['is_default' => false]);
        }

        // If this is first address, make it default
        $isFirst = $user->addresses()->count() === 0;

        $user->addresses()->create([
            'label'        => $request->label,
            'full_address' => $request->full_address,
            'pincode'      => preg_replace('/\D+/', '', $request->pincode),
            'lat'          => $request->lat,
            'lng'          => $request->lng,
            'is_default'   => $request->is_default || $isFirst,
        ]);

        $redirect = $request->redirect_to ?? route('customer.addresses.index');
        return redirect($redirect)->with('success', 'Address saved!');
    }

    public function update(Request $request, Address $address)
    {
        $request->merge(['pincode' => preg_replace('/\D+/', '', (string) $request->pincode)]);
        abort_if($address->user_id !== auth()->id(), 403);

        $request->validate([
            'label'        => 'required|string|max:50',
            'full_address' => 'required|string|max:500',
            'pincode'      => ['required', 'regex:/^\d{6}$/'],
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
        ]);

        $user = auth()->user();

        if ($request->is_default) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'label'        => $request->label,
            'full_address' => $request->full_address,
            'pincode'      => preg_replace('/\D+/', '', $request->pincode),
            // Keep the existing pin if the edit form didn't touch the map (e.g. the
            // simple addresses-page modal has no map picker).
            'lat'          => $request->filled('lat') ? $request->lat : $address->lat,
            'lng'          => $request->filled('lng') ? $request->lng : $address->lng,
            'is_default'   => $request->boolean('is_default') || $address->is_default,
        ]);

        $redirect = $request->redirect_to ?? route('customer.addresses.index');
        return redirect($redirect)->with('success', 'Address updated!');
    }

    public function setDefault(Address $address)
    {
        abort_if($address->user_id !== auth()->id(), 403);
        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
        return back()->with('success', 'Default address updated!');
    }

    public function destroy(Address $address)
    {
        abort_if($address->user_id !== auth()->id(), 403);

        $wasDefault = $address->is_default;
        $address->delete(); // soft delete — past orders keep their address reference intact

        // If they just removed their default address, quietly promote the
        // next most recent one so checkout always has a sensible default.
        if ($wasDefault) {
            auth()->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Address removed!');
    }
}

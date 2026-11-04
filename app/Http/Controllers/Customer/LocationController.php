<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    // Sets the customer's active delivery location in the session — pincode
    // (used for the actual restaurant filtering) plus an optional full
    // address + lat/lng picked from the map, kept just for display and to
    // pre-fill the map next time the modal opens.
    public function update(Request $request)
    {
        $request->validate([
            'pincode'      => 'required|string|max:10',
            'full_address' => 'nullable|string|max:500',
            'label'        => 'nullable|string|max:255',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
            'redirect_to'  => 'nullable|string',
        ]);

        $pincode = trim($request->pincode);
        $fullAddress = $request->full_address ? trim($request->full_address) : null;

        session([
            'customer_pincode'        => $pincode,
            'customer_full_address'   => $fullAddress,
            'customer_lat'            => $request->lat,
            'customer_lng'            => $request->lng,
            'customer_location_label' => $request->label
                ?: ($fullAddress ? Str::limit($fullAddress, 40) : $pincode),
        ]);

        $redirect = $request->redirect_to ?: route('customer.dashboard');

        return redirect($redirect)->with('success', 'Delivery location set to ' . session('customer_location_label'));
    }

    // Removes the location filter — dashboard goes back to showing all restaurants.
    public function clear(Request $request)
    {
        session()->forget([
            'customer_pincode',
            'customer_full_address',
            'customer_lat',
            'customer_lng',
            'customer_location_label',
        ]);

        $redirect = $request->redirect_to ?: route('customer.dashboard');

        return redirect($redirect)->with('success', 'Location cleared — showing restaurants from everywhere.');
    }
}


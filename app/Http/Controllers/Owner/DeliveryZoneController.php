<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;

class DeliveryZoneController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $zones = $restaurant->deliveryZones()->orderBy('pincode')->get();

        return view('owner.delivery-zones.index', compact('restaurant', 'zones'));
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $request->merge(['pincode' => preg_replace('/\D+/', '', (string) $request->pincode)]);

        $request->validate([
            'pincode'      => ['required', 'regex:/^\d{6}$/'],
            'zone_type'    => 'required|in:metro,remote,blocked',
            'delivery_fee' => 'required_unless:zone_type,blocked|nullable|numeric|min:0',
            'lat'          => 'nullable|numeric|between:-90,90',
            'lng'          => 'nullable|numeric|between:-180,180',
        ]);

        $exists = $restaurant->deliveryZones()->where('pincode', $request->pincode)->exists();
        if ($exists) {
            return back()->with('error', 'A delivery zone for this pincode already exists.');
        }

        // Blocked zones don't deliver at all — fee/time are irrelevant.
        $isBlocked = $request->zone_type === 'blocked';
        $estimatedTime = $isBlocked
            ? null
            : ($request->estimated_time ?: ($request->zone_type === 'metro' ? '30 min' : '60 min'));

        $restaurant->deliveryZones()->create([
            'pincode'        => $request->pincode,
            'zone_type'      => $request->zone_type,
            'delivery_fee'   => $isBlocked ? 0 : $request->delivery_fee,
            'estimated_time' => $estimatedTime,
            'lat'            => $request->lat,
            'lng'            => $request->lng,
            // Legacy columns from the table's original design — not used for
            // restaurant zones, but required (NOT NULL) so we fill them in.
            'user_id'      => $restaurant->user_id,
            'label'        => 'Zone: ' . $request->pincode,
            'full_address' => $restaurant->address,
        ]);

        return back()->with('success', 'Delivery zone added!');
    }

    public function destroy(Restaurant $restaurant, DeliveryZone $zone)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if($zone->restaurant_id !== $restaurant->id, 404);

        $zone->delete();

        return back()->with('success', 'Delivery zone removed!');
    }
}

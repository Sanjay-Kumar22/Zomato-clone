<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantHour;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('pincode', 'like', "%{$term}%")
                    ->orWhere('cuisines', 'like', "%{$term}%")
                    ->orWhere('address', 'like', "%{$term}%");
            });
        }

        $restaurants = $query->latest()->paginate(10)->withQueryString();
        return view('owner.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        return view('owner.restaurants.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
            'pincode' => 'required|string|max:10',
            'lat'     => 'nullable|numeric|between:-90,90',
            'lng'     => 'nullable|numeric|between:-180,180',
            'image'   => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        // Trimmed so a stray leading/trailing space doesn't break every
        // pincode-based delivery match against this restaurant later on.
        $data['pincode'] = trim($data['pincode']);
        $data['user_id'] = auth()->id();
        $data['slug'] = Str::slug($request->name . '-' . uniqid());
        $data['is_approved'] = false; // Admin approve karega

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('restaurants', 'public');
        }

        Restaurant::create($data);
        return redirect()->route('owner.restaurants.index')
            ->with('success', 'Restaurant submitted for approval!');
    }

    public function edit(Restaurant $restaurant)
    {
        // Sirf apna restaurant edit kar sake
        abort_if($restaurant->user_id !== auth()->id(), 403);
        return view('owner.restaurants.edit', compact('restaurant'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $request->validate([
            'name'     => 'required|string|max:255',
            'address'  => 'required|string',
            'cuisines' => 'nullable|string|max:500',
            'pincode'  => 'required|string|max:10',
            'lat'      => 'nullable|numeric|between:-90,90',
            'lng'      => 'nullable|numeric|between:-180,180',
            'image'    => 'nullable|image|max:2048',
        ]);

        // Whitelist explicitly — never trust $request->except('image') here, since a
        // tampered request could otherwise slip in is_approved / user_id and let an
        // owner self-approve their restaurant or reassign ownership.
        $data = $request->only(['name', 'address', 'cuisines', 'pincode', 'lat', 'lng']);
        $data['pincode'] = trim($data['pincode']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('restaurants', 'public');
        }

        $restaurant->update($data);
        return redirect()->route('owner.restaurants.index')
            ->with('success', 'Restaurant updated!');
    }

    // Open/Close toggle
    public function toggleOpen(Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        $restaurant->update(['is_open' => !$restaurant->is_open]);
        return back()->with('success', 'Status updated!');
    }

    // Weekly opening-hours schedule
    public function editHours(Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $hours = $restaurant->hours()->get()->keyBy('day_of_week');

        return view('owner.restaurants.hours', compact('restaurant', 'hours'));
    }

    public function updateHours(Request $request, Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $request->validate([
            'days'                => 'required|array',
            'days.*.is_closed'    => 'nullable|boolean',
            'days.*.opens_at'     => 'nullable|date_format:H:i',
            'days.*.closes_at'    => 'nullable|date_format:H:i',
        ]);

        foreach ($request->input('days') as $day => $data) {
            $isClosed = !empty($data['is_closed']);

            RestaurantHour::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'day_of_week' => (int) $day],
                [
                    'is_closed' => $isClosed,
                    'opens_at'  => $isClosed ? null : ($data['opens_at'] ?? null),
                    'closes_at' => $isClosed ? null : ($data['closes_at'] ?? null),
                ]
            );
        }

        return back()->with('success', 'Opening hours updated!');
    }
}
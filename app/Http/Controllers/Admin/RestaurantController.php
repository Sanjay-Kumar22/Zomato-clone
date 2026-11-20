<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\AdminAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::with('owner');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('pincode', 'like', "%{$term}%")
                    ->orWhereHas('owner', fn ($o) => $o->where('name', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('approval')) {
            $query->where('is_approved', $request->approval === 'approved' ? 1 : 0);
        }

        $restaurants = $query->latest()->paginate(10)->withQueryString();
        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        $owners = User::where('role', 'restaurant_owner')->get();
        return view('admin.restaurants.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'user_id'  => 'required|exists:users,id',
            'address'  => 'required|string',
            'pincode'  => 'required|string|max:10',
            'image'    => 'nullable|image|max:2048',
        ]);

        $data = $request->all();
        $data['pincode'] = trim($data['pincode']);
        $data['slug'] = Str::slug($request->name . '-' . uniqid());

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('restaurants', 'public');
        }

        Restaurant::create($data);
        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant created!');
    }

    public function edit(Restaurant $restaurant)
    {
        $owners = User::where('role', 'restaurant_owner')->get();
        return view('admin.restaurants.edit', compact('restaurant', 'owners'));
    }

    public function update(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'address' => 'required|string',
            'pincode' => 'required|string|max:10',
            'image'   => 'nullable|image|max:2048',
        ]);

        $data = $request->except('image');
        $data['pincode'] = trim($data['pincode']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('restaurants', 'public');
        }

        $restaurant->update($data);
        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant updated!');
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->delete();
        return redirect()->route('admin.restaurants.index')
            ->with('success', 'Restaurant deleted!');
    }

    // Approve/Disapprove toggle
    public function toggleApprove(Restaurant $restaurant)
    {
        $restaurant->update([
            'is_approved' => !$restaurant->is_approved
        ]);
        return back()->with('success', 'Status updated!');
    }

    public function toggleFeatured(Restaurant $restaurant)
    {
        $restaurant->update(['is_featured' => !$restaurant->is_featured]);
        AdminAuditLog::create([
            'actor_id' => auth()->id(),
            'action' => 'restaurant.featured_toggled',
            'subject_type' => Restaurant::class,
            'subject_id' => (string) $restaurant->id,
            'description' => ($restaurant->is_featured ? 'Featured' : 'Unfeatured') . " restaurant {$restaurant->name}.",
            'ip_address' => request()->ip(),
            'metadata' => ['is_featured' => $restaurant->is_featured],
        ]);

        return back()->with('success', $restaurant->is_featured
            ? 'Restaurant added to featured discovery.'
            : 'Restaurant removed from featured discovery.');
    }
}
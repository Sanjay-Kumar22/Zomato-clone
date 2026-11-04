<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\FoodItem;
use App\Models\Order;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    // Powers the "search everything" box in the owner topbar — one query,
    // three grouped result sections, all scoped to the logged-in owner's
    // own restaurants only.
    public function index(Request $request)
    {
        $term = trim((string) $request->q);
        $ownerId = auth()->id();

        $restaurants = collect();
        $foodItems = collect();
        $orders = collect();

        if (mb_strlen($term) >= 2) {
            $restaurants = Restaurant::where('user_id', $ownerId)
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('pincode', 'like', "%{$term}%")
                        ->orWhere('cuisines', 'like', "%{$term}%");
                })
                ->limit(10)
                ->get();

            $foodItems = FoodItem::whereHas('restaurant', fn ($q) => $q->where('user_id', $ownerId))
                ->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('tags', 'like', "%{$term}%");
                })
                ->with('restaurant:id,name')
                ->limit(10)
                ->get();

            $orders = Order::whereHas('restaurant', fn ($q) => $q->where('user_id', $ownerId))
                ->where(function ($q) use ($term) {
                    $q->where('id', 'like', "%{$term}%")
                        ->orWhereHas('user', function ($u) use ($term) {
                            $u->where('name', 'like', "%{$term}%")
                              ->orWhere('email', 'like', "%{$term}%");
                        });
                })
                ->with('restaurant:id,name', 'user:id,name')
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('owner.search', compact('term', 'restaurants', 'foodItems', 'orders'));
    }
}

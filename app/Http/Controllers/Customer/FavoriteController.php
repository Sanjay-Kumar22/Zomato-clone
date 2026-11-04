<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Favorite;

class FavoriteController extends Controller
{
    // List all restaurants the customer has favorited
    
    public function index()
    {
        $restaurants = auth()->user()
            ->favoriteRestaurants()
            ->withCount('foodItems')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->latest('favorites.created_at')
            ->paginate(12);

        return view('customer.favorites', compact('restaurants'));
    }

    // Toggle favorite on/off for a restaurant
    public function toggle(Restaurant $restaurant)
    {
        $favorite = Favorite::where('user_id', auth()->id())
            ->where('restaurant_id', $restaurant->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $message = 'Removed from favorites.';
        } else {
            Favorite::create([
                'user_id'       => auth()->id(),
                'restaurant_id' => $restaurant->id,
            ]);
            $message = 'Added to favorites!';
        }

        return back()->with('success', $message);
    }
}

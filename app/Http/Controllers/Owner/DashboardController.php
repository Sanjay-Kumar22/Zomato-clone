<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\FoodItem;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $ownerId = auth()->id();
        $restaurantIds = Restaurant::where('user_id', $ownerId)->pluck('id');

        $myRestaurantsCount = $restaurantIds->count();
        $totalFoodItems = FoodItem::whereIn('restaurant_id', $restaurantIds)->count();
        $totalOrders = Order::whereIn('restaurant_id', $restaurantIds)->count();

        // Orders that actually need the owner's attention right now.
        $pendingOrdersCount = Order::whereIn('restaurant_id', $restaurantIds)
            ->whereIn('status', ['pending', 'confirmed', 'preparing'])
            ->count();

        $totalRevenue = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('status', 'delivered')
            ->sum('total');

        $todayRevenue = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->sum('total');

        $todayOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->whereDate('created_at', today())
            ->count();

        $myRestaurants = Restaurant::where('user_id', $ownerId)->latest()->take(5)->get();

        $recentOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->with('restaurant', 'user')
            ->latest()
            ->take(6)
            ->get();

        return view('owner.dashboard', compact(
            'myRestaurantsCount', 'totalFoodItems', 'totalOrders', 'pendingOrdersCount',
            'totalRevenue', 'todayRevenue', 'todayOrders', 'myRestaurants', 'recentOrders'
        ));
    }
}

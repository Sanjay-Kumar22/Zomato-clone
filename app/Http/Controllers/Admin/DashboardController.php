<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalRestaurants = Restaurant::count();
        $totalCustomers = User::where('role', 'customer')->count();
        $totalOrders = Order::count();
        $pendingApprovals = Restaurant::where('is_approved', false)->count();

        // Lifetime + today's revenue — the two numbers an admin actually
        // checks first thing, delivered orders only (revenue that's real,
        // not sitting in an abandoned cart or a cancelled order).
        $totalRevenue = Order::where('status', 'delivered')->sum('total');
        $todayRevenue = Order::where('status', 'delivered')->whereDate('updated_at', today())->sum('total');
        $todayOrders = Order::whereDate('created_at', today())->count();
        $todayNewUsers = User::whereDate('created_at', today())->count();

        $pendingRestaurants = Restaurant::with('owner')
            ->where('is_approved', false)
            ->latest()
            ->take(5)
            ->get();

        $recentRestaurants = Restaurant::with('owner')->latest()->take(5)->get();

        $recentOrders = Order::with('restaurant', 'user')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'totalRestaurants', 'totalCustomers', 'totalOrders', 'pendingApprovals',
            'totalRevenue', 'todayRevenue', 'todayOrders', 'todayNewUsers',
            'pendingRestaurants', 'recentRestaurants', 'recentOrders'
        ));
    }
}

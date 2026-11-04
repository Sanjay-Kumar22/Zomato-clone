<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $restaurantIds = Restaurant::where('user_id', auth()->id())->pluck('id');

        // Date range filter — defaults to last 30 days
        $days  = (int) ($request->range ?? 30);
        $days  = in_array($days, [7, 30, 90]) ? $days : 30;
        $since = now()->subDays($days - 1)->startOfDay();

        $deliveredOrders = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('status', 'delivered')
            ->where('created_at', '>=', $since);

        $totalRevenue  = (clone $deliveredOrders)->sum('total');
        $totalOrders   = (clone $deliveredOrders)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Revenue trend, one point per day, so the chart has no gaps
        $revenueRows = (clone $deliveredOrders)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $ordersByDay = (clone $deliveredOrders)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('COUNT(*) as orders'))
            ->groupBy('day')
            ->pluck('orders', 'day');

        $labels = [];
        $revenueSeries = [];
        $ordersSeries = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $revenueSeries[] = (float) ($revenueRows[$date] ?? 0);
            $ordersSeries[] = (int) ($ordersByDay[$date] ?? 0);
        }

        // Best-selling food items across the owner's restaurants
        $bestSellers = OrderItem::select(
                'food_item_id',
                'name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(price_snapshot * quantity) as total_revenue')
            )
            ->whereHas('order', function ($q) use ($restaurantIds, $since) {
                $q->whereIn('restaurant_id', $restaurantIds)
                  ->where('status', 'delivered')
                  ->where('created_at', '>=', $since);
            })
            ->groupBy('food_item_id', 'name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        // Per-restaurant revenue breakdown (useful when an owner runs more than one restaurant)
        $revenueByRestaurant = Order::select('restaurant_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->whereIn('restaurant_id', $restaurantIds)
            ->where('status', 'delivered')
            ->where('created_at', '>=', $since)
            ->groupBy('restaurant_id')
            ->with('restaurant:id,name')
            ->orderByDesc('revenue')
            ->get();

        // Order status breakdown (all-time, not just delivered) for the funnel view
        $statusCounts = Order::whereIn('restaurant_id', $restaurantIds)
            ->where('created_at', '>=', $since)
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('owner.analytics', compact(
            'days', 'totalRevenue', 'totalOrders', 'avgOrderValue',
            'labels', 'revenueSeries', 'ordersSeries',
            'bestSellers', 'revenueByRestaurant', 'statusCounts'
        ));
    }
}

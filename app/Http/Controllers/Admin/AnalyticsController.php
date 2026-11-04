<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $days  = (int) ($request->range ?? 30);
        $days  = in_array($days, [7, 30, 90]) ? $days : 30;
        $since = now()->subDays($days - 1)->startOfDay();

        $deliveredOrders = Order::where('status', 'delivered')->where('created_at', '>=', $since);

        $totalRevenue  = (clone $deliveredOrders)->sum('total');
        $totalOrders   = (clone $deliveredOrders)->count();
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $revenueRows = (clone $deliveredOrders)
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('SUM(total) as revenue'))
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $labels = [];
        $revenueSeries = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d M');
            $revenueSeries[] = (float) ($revenueRows[$date] ?? 0);
        }

        // Top restaurants platform-wide by revenue
        $topRestaurants = Order::select('restaurant_id', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->where('status', 'delivered')
            ->where('created_at', '>=', $since)
            ->groupBy('restaurant_id')
            ->with('restaurant:id,name,image')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();

        // Top-selling food items platform-wide
        $bestSellers = OrderItem::select(
                'food_item_id',
                'name',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('SUM(price_snapshot * quantity) as total_revenue')
            )
            ->whereHas('order', function ($q) use ($since) {
                $q->where('status', 'delivered')->where('created_at', '>=', $since);
            })
            ->groupBy('food_item_id', 'name')
            ->orderByDesc('total_qty')
            ->limit(8)
            ->get();

        $statusCounts = Order::where('created_at', '>=', $since)
            ->select('status', DB::raw('COUNT(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('admin.analytics', compact(
            'days', 'totalRevenue', 'totalOrders', 'avgOrderValue',
            'labels', 'revenueSeries', 'topRestaurants', 'bestSellers', 'statusCounts'
        ));
    }
}

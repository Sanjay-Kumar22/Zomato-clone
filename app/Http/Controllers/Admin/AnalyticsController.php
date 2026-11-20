<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PlatformSettings;

class AnalyticsController extends Controller
{
    public function index(Request $request, PlatformSettings $settings)
    {
        $days  = (int) ($request->range ?? 30);
        $days  = in_array($days, [7, 30, 90]) ? $days : 30;
        $since = now()->subDays($days - 1)->startOfDay();

        $deliveredOrders = Order::where('status', 'delivered')->where('created_at', '>=', $since);

        $commissionRate = $settings->value('commission_percent') / 100;
        $totalRevenue = (float) (clone $deliveredOrders)
            ->selectRaw('COALESCE(SUM(platform_fee + ((subtotal - discount - points_discount) * ?)), 0) as platform_revenue', [$commissionRate])
            ->value('platform_revenue');
        $grossSales = (float) (clone $deliveredOrders)->selectRaw('COALESCE(SUM(total + wallet_used), 0) as gross_sales')->value('gross_sales');
        $totalOrders   = (clone $deliveredOrders)->count();
        $avgOrderValue = $totalOrders > 0 ? $grossSales / $totalOrders : 0;

        $revenueRows = (clone $deliveredOrders)
            ->selectRaw('DATE(created_at) as day, SUM(platform_fee + ((subtotal - discount - points_discount) * ?)) as revenue', [$commissionRate])
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

    public function export(Request $request)
    {
        $request->validate(['range' => 'nullable|in:7,30,90']);
        $days = (int) ($request->range ?? 30);
        $since = now()->subDays($days - 1)->startOfDay();
        $orders = Order::with('restaurant:id,name', 'user:id,name')
            ->where('status', 'delivered')
            ->where('created_at', '>=', $since)
            ->orderBy('id')
            ->cursor();

        return response()->streamDownload(function () use ($orders): void {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Order ID', 'Created At', 'Updated At', 'Restaurant', 'Customer',
                'Status', 'Payment Method', 'Payment Status', 'Items Subtotal',
                'Coupon Discount', 'Rewards Discount', 'Delivery Fee', 'Packaging',
                'GST', 'Platform Fee', 'Tip', 'Wallet Used', 'Customer Total',
            ]);

            foreach ($orders as $order) {
                fputcsv($output, [
                    $order->id,
                    $order->created_at?->toIso8601String(),
                    $order->updated_at?->toIso8601String(),
                    $this->spreadsheetSafe($order->restaurant?->name),
                    $this->spreadsheetSafe($order->user?->name),
                    $order->status,
                    $order->payment_method,
                    $order->payment_status,
                    $order->subtotal,
                    $order->discount,
                    $order->points_discount,
                    $order->delivery_fee,
                    $order->packaging_charge,
                    $order->tax_amount,
                    $order->platform_fee,
                    $order->tip_amount,
                    $order->wallet_used,
                    (float) $order->total + (float) $order->wallet_used,
                ]);
            }

            fclose($output);
        }, 'platform-orders-' . now()->format('Ymd-His') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function spreadsheetSafe(?string $value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\RestaurantSettlement;
use App\Models\RestaurantSettlementOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\PlatformSettings;

class SettlementController extends Controller
{
    public function index(PlatformSettings $settings)
    {
        $restaurants = Restaurant::where('user_id', auth()->id())
            ->withCount(['orders as eligible_orders_count' => fn ($query) => $query
                ->where('status', 'delivered')
                ->where('payment_status', 'paid')
                ->whereDoesntHave('settlementLine', fn ($line) => $line->whereHas('settlement', fn ($settlement) => $settlement->where('status', '!=', 'rejected')))])
            ->withSum(['orders as eligible_gross_total' => fn ($query) => $query
                ->where('status', 'delivered')
                ->where('payment_status', 'paid')
                ->whereDoesntHave('settlementLine', fn ($line) => $line->whereHas('settlement', fn ($settlement) => $settlement->where('status', '!=', 'rejected')))], 'subtotal')
            ->get();

        $settlements = RestaurantSettlement::whereIn('restaurant_id', $restaurants->pluck('id'))
            ->with('restaurant')
            ->withCount('orders')
            ->latest()
            ->paginate(15);

        $commissionRate = $settings->value('commission_percent');

        return view('owner.settlements.index', compact('restaurants', 'settlements', 'commissionRate'));
    }

    public function store(Request $request, PlatformSettings $settings)
    {
        $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
        ]);

        $restaurant = Restaurant::where('user_id', auth()->id())
            ->findOrFail($request->integer('restaurant_id'));
        $commissionRate = $settings->value('commission_percent');

        $settlement = DB::transaction(function () use ($restaurant, $commissionRate): ?RestaurantSettlement {
            Restaurant::whereKey($restaurant->id)->lockForUpdate()->firstOrFail();

            $orders = Order::where('restaurant_id', $restaurant->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'paid')
                ->whereDoesntHave('settlementLine', fn ($line) => $line->whereHas('settlement', fn ($settlement) => $settlement->where('status', '!=', 'rejected')))
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                return null;
            }

            $settlement = RestaurantSettlement::create([
                'restaurant_id' => $restaurant->id,
                'requested_by' => auth()->id(),
                'period_start' => $orders->min('created_at')->toDateString(),
                'period_end' => $orders->max('created_at')->toDateString(),
                'gross_amount' => 0,
                'commission_amount' => 0,
                'payout_amount' => 0,
                'status' => 'pending',
            ]);

            $grossTotal = 0;
            $commissionTotal = 0;
            $payoutTotal = 0;
            $rows = [];
            foreach ($orders as $order) {
                $gross = round(max(0, (float) $order->subtotal - (float) $order->discount - (float) $order->points_discount), 2);
                $commission = round($gross * $commissionRate / 100, 2);
                $payout = max(0, $gross - $commission);
                $grossTotal += $gross;
                $commissionTotal += $commission;
                $payoutTotal += $payout;
                $rows[] = [
                    'restaurant_settlement_id' => $settlement->id,
                    'order_id' => $order->id,
                    'gross_amount' => $gross,
                    'commission_amount' => $commission,
                    'payout_amount' => $payout,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($rows, 500) as $chunk) {
                RestaurantSettlementOrder::insert($chunk);
            }

            $settlement->update([
                'gross_amount' => $grossTotal,
                'commission_amount' => $commissionTotal,
                'payout_amount' => $payoutTotal,
            ]);

            return $settlement;
        });

        if (!$settlement) {
            return back()->with('error', 'There are no paid, delivered orders available for settlement.');
        }

        return back()->with('success', 'Payout request #' . $settlement->id . ' submitted for admin review.');
    }
}

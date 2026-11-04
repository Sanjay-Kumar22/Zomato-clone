<?php

namespace App\Http\Controllers\DeliveryAgent;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\WalletService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Dashboard: stats + orders this agent is currently delivering
    public function index()
    {
        $agentId = auth()->id();

        $availableCount = Order::whereNull('delivery_agent_id')
            ->where('status', 'preparing')
            ->count();

        $myActiveOrders = Order::where('delivery_agent_id', $agentId)
            ->where('status', 'out_for_delivery')
            ->with('restaurant', 'address', 'user', 'items')
            ->latest()
            ->get();

        $deliveredCount = Order::where('delivery_agent_id', $agentId)
            ->where('status', 'delivered')
            ->count();

        $deliveredToday = Order::where('delivery_agent_id', $agentId)
            ->where('status', 'delivered')
            ->whereDate('updated_at', today())
            ->get();

        // Earnings — the delivery_fee + any tip collected on every order this
        // agent has actually delivered. Simple and honest: no guesswork, no
        // separate ledger table needed since both are already on the order.
        $earningsToday = $deliveredToday->sum('delivery_fee') + $deliveredToday->sum('tip_amount');

        $thisWeekDelivered = Order::where('delivery_agent_id', $agentId)
            ->where('status', 'delivered')
            ->whereBetween('updated_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->get(['delivery_fee', 'tip_amount']);
        $earningsThisWeek = $thisWeekDelivered->sum('delivery_fee') + $thisWeekDelivered->sum('tip_amount');

        $allDelivered = Order::where('delivery_agent_id', $agentId)
            ->where('status', 'delivered')
            ->get(['delivery_fee', 'tip_amount']);
        $earningsTotal = $allDelivered->sum('delivery_fee') + $allDelivered->sum('tip_amount');
        $tipsTotal = $allDelivered->sum('tip_amount');

        return view('delivery.dashboard', compact(
            'availableCount', 'myActiveOrders', 'deliveredCount',
            'deliveredToday', 'earningsToday', 'earningsThisWeek', 'earningsTotal', 'tipsTotal'
        ));
    }

    // Agent flips themselves online/offline right from the dashboard —
    // no need to log out just to stop showing up for new pickups.
    public function toggleOnline(Request $request)
    {
        $user = auth()->user();
        $user->update(['is_online' => !$user->is_online]);

        return back()->with('success', $user->is_online
            ? "You're online — you'll be visible for new pickups."
            : "You're offline — you won't receive new pickup alerts.");
    }

    // Orders ready for pickup, not yet claimed by any agent
    public function available()
    {
        $availableOrders = Order::whereNull('delivery_agent_id')
            ->where('status', 'preparing')
            ->with('restaurant', 'address')
            ->latest()
            ->get();

        return view('delivery.available', compact('availableOrders'));
    }

    // This agent's delivery history
    public function history(Request $request)
    {
        $query = Order::where('delivery_agent_id', auth()->id())
            ->where('status', 'delivered')
            ->with('restaurant', 'address', 'user', 'items');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', "%{$term}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$term}%"));
            });
        }

        $myDeliveredOrders = $query->latest()->paginate(10)->withQueryString();

        return view('delivery.history', compact('myDeliveredOrders'));
    }

    public function accept(Order $order)
    {
        if ($order->delivery_agent_id !== null || $order->status !== 'preparing') {
            return back()->with('error', 'This order is no longer available for pickup.');
        }

        $order->update([
            'delivery_agent_id' => auth()->id(),
            'status'            => 'out_for_delivery',
        ]);

        $order->user?->notify(new OrderStatusUpdated($order, 'out_for_delivery'));

        return back()->with('success', 'Order #' . $order->id . ' accepted! Head to the restaurant for pickup.');
    }

    // Called every ~15s by the agent's browser (navigator.geolocation) while an
    // order is out_for_delivery, so the customer's live tracking map can show the
    // agent's real position instead of a simulated one.
    public function updateLocation(Request $request, Order $order)
    {
        abort_if($order->delivery_agent_id !== auth()->id(), 403);

        if ($order->status !== 'out_for_delivery') {
            return response()->json(['ok' => false], 422);
        }

        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $order->update([
            'agent_lat'                  => $request->lat,
            'agent_lng'                  => $request->lng,
            'agent_location_updated_at'  => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    public function markDelivered(Order $order, WalletService $wallet)
    {
        abort_if($order->delivery_agent_id !== auth()->id(), 403);

        if ($order->status !== 'out_for_delivery') {
            return back()->with('error', 'This order cannot be marked as delivered.');
        }

        $order->update(['status' => 'delivered']);

        // Credit loyalty points now that the order is actually complete —
        // keeps cancelled/undelivered orders from earning free points.
        if ($order->points_earned > 0 && $order->user) {
            $order->user->increment('loyalty_points', $order->points_earned);
        }

        // Referral bonus — paid to the referrer once their friend's first
        // order is actually delivered (not just placed), to discourage fake signups.
        $customer = $order->user;
        if ($customer && $customer->referred_by && !$customer->referral_bonus_paid) {
            $isFirstDeliveredOrder = !Order::where('user_id', $customer->id)
                ->where('status', 'delivered')
                ->where('id', '!=', $order->id)
                ->exists();

            if ($isFirstDeliveredOrder && $customer->referrer) {
                $wallet->credit($customer->referrer, 100, 'referral_bonus');
                $customer->update(['referral_bonus_paid' => true]);
            }
        }

        $order->user?->notify(new OrderStatusUpdated($order, 'delivered'));

        return back()->with('success', 'Order #' . $order->id . ' marked as delivered. Great job!');
    }
}

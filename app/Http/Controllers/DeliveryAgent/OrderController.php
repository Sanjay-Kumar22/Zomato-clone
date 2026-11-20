<?php

namespace App\Http\Controllers\DeliveryAgent;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\DeliveryDispatchService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        $cashCollectedToday = (float) Order::where('delivery_agent_id', $agentId)
            ->where('status', 'delivered')
            ->whereDate('cod_collected_at', today())
            ->sum('cod_collected_amount');
        $averageRating = (float) \App\Models\Review::where('moderation_status', 'visible')
            ->whereHas('order', fn ($query) => $query->where('delivery_agent_id', $agentId))
            ->whereNotNull('delivery_rating')
            ->avg('delivery_rating');

        return view('delivery.dashboard', compact(
            'availableCount', 'myActiveOrders', 'deliveredCount',
            'deliveredToday', 'earningsToday', 'earningsThisWeek', 'earningsTotal',
            'tipsTotal', 'cashCollectedToday', 'averageRating'
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
        if (!auth()->user()->is_online) {
            return back()->with('error', 'Go online before accepting a delivery.');
        }

        if (auth()->user()->kyc_status !== 'approved') {
            return back()->with('error', 'Your delivery profile is awaiting KYC approval.');
        }

        $accepted = DB::transaction(function () use ($order): bool {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($lockedOrder->delivery_agent_id !== null || $lockedOrder->status !== 'preparing') {
                return false;
            }

            $lockedOrder->update([
                'delivery_agent_id' => auth()->id(),
                'status' => 'out_for_delivery',
            ]);

            return true;
        });

        if (!$accepted) {
            return back()->with('error', 'This order is no longer available for pickup.');
        }

        $order->refresh();

        $order->user?->notify(new OrderStatusUpdated($order, 'out_for_delivery'));

        return back()->with('success', 'Order #' . $order->id . ' accepted! Head to the restaurant for pickup.');
    }

    public function reject(Order $order, DeliveryDispatchService $dispatch)
    {
        abort_if((int) $order->delivery_agent_id !== (int) auth()->id(), 403);

        if ($order->status !== 'out_for_delivery') {
            return back()->with('error', 'This delivery can no longer be reassigned.');
        }

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ((int) $lockedOrder->delivery_agent_id !== (int) auth()->id()
                || $lockedOrder->status !== 'out_for_delivery') {
                abort(409, 'This delivery has already changed.');
            }

            $lockedOrder->update([
                'delivery_agent_id' => null,
                'status' => 'preparing',
                'delivery_otp' => null,
                'delivery_otp_verified_at' => null,
                'agent_lat' => null,
                'agent_lng' => null,
                'agent_location_updated_at' => null,
            ]);
        });

        $order->refresh();
        $dispatch->notifyAvailableAgents($order, [auth()->id()]);

        return redirect()->route('delivery.orders.available')
            ->with('success', 'Delivery released for reassignment.');
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

        $lastPoint = $order->locationHistory()->latest('recorded_at')->first();
        if (!$lastPoint || $lastPoint->recorded_at->lte(now()->subSeconds(10))) {
            $order->locationHistory()->create([
                'user_id' => auth()->id(),
                'lat' => $request->lat,
                'lng' => $request->lng,
                'recorded_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function markDelivered(Request $request, Order $order, WalletService $wallet)
    {
        abort_if($order->delivery_agent_id !== auth()->id(), 403);

        $request->validate([
            'otp' => 'required|digits:6',
            'cod_collected_amount' => 'nullable|numeric|min:0|max:99999999.99',
        ]);

        $delivered = DB::transaction(function () use ($request, $order): bool {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ((int) $lockedOrder->delivery_agent_id !== (int) auth()->id()
                || $lockedOrder->status !== 'out_for_delivery'
                || $lockedOrder->delivery_otp_verified_at) {
                return false;
            }

            if (!$lockedOrder->delivery_otp
                || !hash_equals((string) $lockedOrder->delivery_otp, (string) $request->input('otp'))) {
                throw ValidationException::withMessages([
                    'otp' => 'The delivery code is incorrect. Ask the customer to confirm the code.',
                ]);
            }

            $updates = [
                'delivery_otp_verified_at' => now(),
                'status' => 'delivered',
            ];
            if ($lockedOrder->payment_method === 'cod' && (float) $lockedOrder->total > 0) {
                $collected = round((float) $request->input('cod_collected_amount', -1), 2);
                if ($collected !== round((float) $lockedOrder->total, 2)) {
                    throw ValidationException::withMessages([
                        'cod_collected_amount' => 'Record the exact cash amount due for this order.',
                    ]);
                }
                $updates['cod_collected_amount'] = $collected;
                $updates['cod_collected_at'] = now();
                $updates['payment_status'] = 'paid';
            } elseif ($lockedOrder->payment_method !== 'cod' && $lockedOrder->payment_status !== 'paid') {
                throw ValidationException::withMessages([
                    'otp' => 'This online payment is not confirmed yet. Contact support before completing delivery.',
                ]);
            }

            $lockedOrder->update($updates);
            if ($lockedOrder->points_earned > 0) {
                $lockedOrder->user()->lockForUpdate()->firstOrFail()
                    ->increment('loyalty_points', $lockedOrder->points_earned);
            }

            return true;
        });

        if (!$delivered) {
            return back()->with('error', 'This delivery has already changed or been completed.');
        }

        // Credit loyalty points now that the order is actually complete —
        // keeps cancelled/undelivered orders from earning free points.
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

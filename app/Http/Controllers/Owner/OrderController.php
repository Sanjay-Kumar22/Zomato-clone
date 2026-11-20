<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use App\Services\DeliveryDispatchService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::whereHas('restaurant', fn($q) => $q->where('user_id', auth()->id()))
            ->with('restaurant', 'user', 'items', 'deliveryAgent');

        // Search by order #, customer name/email, or restaurant name — the
        // stuff an owner actually types when a customer calls asking
        // "where's my order".
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', "%{$term}%")
                    ->orWhereHas('user', function ($u) use ($term) {
                        $u->where('name', 'like', "%{$term}%")
                          ->orWhere('email', 'like', "%{$term}%")
                          ->orWhere('phone', 'like', "%{$term}%");
                    })
                    ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$term}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('owner.orders', compact('orders'));
    }

    public function updateStatus(Request $request, Order $order, DeliveryDispatchService $dispatch)
    {
        // Ensure this order belongs to owner's restaurant
        abort_if($order->restaurant->user_id !== auth()->id(), 403);

        if ($order->payment_method !== 'cod' && $order->payment_status !== 'paid') {
            return back()->with('error', 'This order cannot enter the kitchen until its online payment is confirmed.');
        }

        // Owners only manage the kitchen side of the order lifecycle.
        // Once food is ready ("preparing" done), a delivery agent claims it and
        // takes it from there (out_for_delivery -> delivered) — the owner can no
        // longer change status past that point.
        if (in_array($order->status, ['out_for_delivery', 'delivered', 'cancelled'])) {
            return back()->with('error', 'This order has moved past kitchen control and can no longer be updated here.');
        }

        $request->validate([
            'status' => 'required|in:confirmed,preparing,cancelled',
            'cooking_notes' => 'nullable|string|max:2000',
        ]);

        $updates = ['status' => $request->status];
        if ($request->has('cooking_notes')) {
            $updates['cooking_notes'] = $request->input('cooking_notes');
        }
        $order->update($updates);

        $statusChanged = $order->wasChanged('status');
        if ($statusChanged) {
            $order->user?->notify(new OrderStatusUpdated($order, $request->status));
        }

        // Food is ready — alert online, approved agents that this order is up for grabs.
        if ($statusChanged && $request->status === 'preparing') {
            $dispatch->notifyAvailableAgents($order);
        }

        return back()->with('success', 'Order status updated!');
    }

    // Kitchen-friendly print slip for a single order
    public function print(Order $order)
    {
        abort_if($order->restaurant->user_id !== auth()->id(), 403);

        $order->load('restaurant', 'user', 'items', 'address');

        return view('owner.order-print', compact('order'));
    }
}

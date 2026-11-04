<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('restaurant', 'user', 'items', 'deliveryAgent');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $restaurants = \App\Models\Restaurant::orderBy('name')->get(['id', 'name']);

        return view('admin.orders.index', compact('orders', 'restaurants'));
    }

    public function show(Order $order)
    {
        $order->load('restaurant', 'user', 'items', 'address', 'deliveryAgent', 'review');
        return view('admin.orders.show', compact('order'));
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function store(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);

        if ($order->status !== 'delivered') {
            return back()->with('error', 'You can only report an issue on a delivered order.');
        }

        if ($order->dispute) {
            return back()->with('error', 'You have already reported an issue for this order.');
        }

        $request->validate([
            'reason'      => 'required|in:Item Missing,Wrong Item,Quality Issue,Late Delivery,Other',
            'description' => 'required|string|max:1000',
        ]);

        $order->dispute()->create([
            'user_id'     => auth()->id(),
            'reason'      => $request->reason,
            'description' => $request->description,
            'status'      => 'open',
        ]);

        return back()->with('success', 'Your issue has been reported. Our team will review it shortly.');
    }
}

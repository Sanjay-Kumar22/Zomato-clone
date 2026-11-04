<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403);
        abort_if($order->status !== 'delivered', 403, 'You can only review delivered orders.');

        if ($order->review()->exists()) {
            return back()->with('error', 'You have already reviewed this order!');
        }

        $request->validate([
            'rating'          => 'required|integer|min:1|max:5',
            'food_rating'     => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:1000',
        ]);

        Review::create([
            'user_id'         => auth()->id(),
            'restaurant_id'   => $order->restaurant_id,
            'order_id'        => $order->id,
            'rating'          => $request->rating,
            'food_rating'     => $request->food_rating,
            'delivery_rating' => $request->delivery_rating,
            'comment'         => $request->comment,
        ]);

        return back()->with('success', 'Thanks for rating your order! ⭐');
    }

    public function update(Request $request, Review $review)
    {
        abort_if($review->user_id !== auth()->id(), 403);

        $request->validate([
            'rating'          => 'required|integer|min:1|max:5',
            'food_rating'     => 'nullable|integer|min:1|max:5',
            'delivery_rating' => 'nullable|integer|min:1|max:5',
            'comment'         => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating'          => $request->rating,
            'food_rating'     => $request->food_rating,
            'delivery_rating' => $request->delivery_rating,
            'comment'         => $request->comment,
        ]);

        return back()->with('success', 'Review updated!');
    }

    public function destroy(Review $review)
    {
        abort_if($review->user_id !== auth()->id(), 403);
        $review->delete();

        return back()->with('success', 'Review removed!');
    }
}

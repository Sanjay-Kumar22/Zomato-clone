<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'photo'           => 'nullable|image|max:5120',
        ]);

        $data = [
            'user_id'         => auth()->id(),
            'restaurant_id'   => $order->restaurant_id,
            'order_id'        => $order->id,
            'rating'          => $request->rating,
            'food_rating'     => $request->food_rating,
            'delivery_rating' => $request->delivery_rating,
            'comment'         => $request->comment,
        ];
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('review-photos', 'public');
        }

        Review::create($data);

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
            'photo'           => 'nullable|image|max:5120',
        ]);

        $data = [
            'rating'          => $request->rating,
            'food_rating'     => $request->food_rating,
            'delivery_rating' => $request->delivery_rating,
            'comment'         => $request->comment,
        ];
        if ($request->hasFile('photo')) {
            if ($review->photo) {
                Storage::disk('public')->delete($review->photo);
            }
            $data['photo'] = $request->file('photo')->store('review-photos', 'public');
        }

        $review->update($data);

        return back()->with('success', 'Review updated!');
    }

    public function destroy(Review $review)
    {
        abort_if($review->user_id !== auth()->id(), 403);
        if ($review->photo) {
            Storage::disk('public')->delete($review->photo);
        }
        $review->delete();

        return back()->with('success', 'Review removed!');
    }

    public function helpful(Review $review)
    {
        abort_if($review->user_id === auth()->id(), 403, 'You cannot vote on your own review.');
        if ($review->moderation_status !== 'visible') {
            abort(404);
        }

        $vote = $review->helpfulVotes()->where('user_id', auth()->id())->first();
        if ($vote) {
            $vote->delete();
            $message = 'Helpful vote removed.';
        } else {
            $review->helpfulVotes()->create(['user_id' => auth()->id()]);
            $message = 'Thanks for your feedback.';
        }

        return back()->with('success', $message);
    }

    public function report(Request $request, Review $review)
    {
        abort_if($review->user_id === auth()->id(), 403, 'You cannot report your own review.');
        $request->validate([
            'reason' => 'required|in:spam,abusive,irrelevant,other',
            'description' => 'nullable|string|max:500',
        ]);

        $review->reports()->firstOrCreate(
            ['reported_by' => auth()->id()],
            ['reason' => $request->reason, 'description' => $request->description],
        );

        return back()->with('success', 'Review reported for moderation.');
    }
}

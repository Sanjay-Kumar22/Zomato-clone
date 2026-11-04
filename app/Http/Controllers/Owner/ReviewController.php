<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::whereHas('restaurant', fn ($q) => $q->where('user_id', auth()->id()))
            ->with('user', 'restaurant');

        if ($request->filled('restaurant')) {
            $query->where('restaurant_id', $request->restaurant);
        }

        if ($request->filter === 'unreplied') {
            $query->whereNull('owner_reply');
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        $restaurants = auth()->user()->restaurants;

        return view('owner.reviews', compact('reviews', 'restaurants'));
    }

    public function reply(Request $request, Review $review)
    {
        abort_if($review->restaurant->user_id !== auth()->id(), 403);

        $request->validate([
            'owner_reply' => 'required|string|max:1000',
        ]);

        $review->update([
            'owner_reply'    => $request->owner_reply,
            'owner_reply_at' => now(),
        ]);

        return back()->with('success', 'Reply posted!');
    }
}

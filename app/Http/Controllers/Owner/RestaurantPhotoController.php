<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantPhoto;
use Illuminate\Http\Request;

class RestaurantPhotoController extends Controller
{
    // Upload one or more gallery photos at once
    public function store(Request $request, Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $request->validate([
            'photos'   => 'required|array|max:10',
            'photos.*' => 'image|max:2048',
        ]);

        $nextOrder = (int) $restaurant->photos()->max('sort_order') + 1;

        foreach ($request->file('photos') as $i => $file) {
            RestaurantPhoto::create([
                'restaurant_id' => $restaurant->id,
                'path'          => $file->store('restaurants/gallery', 'public'),
                'sort_order'    => $nextOrder + $i,
            ]);
        }

        return back()->with('success', 'Photos added to your gallery!');
    }

    public function destroy(Restaurant $restaurant, RestaurantPhoto $photo)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if($photo->restaurant_id !== $restaurant->id, 404);

        \Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo removed.');
    }
}

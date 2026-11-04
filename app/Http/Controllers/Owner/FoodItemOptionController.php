<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\FoodItem;
use App\Models\FoodItemOption;
use Illuminate\Http\Request;

class FoodItemOptionController extends Controller
{
    // Add a new add-on/variant option to a food item (e.g. "Size: Large" +₹50)
    public function store(Request $request, Restaurant $restaurant, FoodItem $foodItem)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if($foodItem->restaurant_id !== $restaurant->id, 404);

        $request->validate([
            'label'       => 'required|string|max:100',
            'value'       => 'required|string|max:100',
            'extra_price' => 'required|numeric|min:0',
        ]);

        $foodItem->options()->create($request->only('label', 'value', 'extra_price'));

        return back()->with('success', 'Option added!');
    }

    // Remove an add-on/variant option
    public function destroy(Restaurant $restaurant, FoodItem $foodItem, FoodItemOption $option)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if($option->food_item_id !== $foodItem->id, 404);

        $option->delete();

        return back()->with('success', 'Option removed!');
    }
}

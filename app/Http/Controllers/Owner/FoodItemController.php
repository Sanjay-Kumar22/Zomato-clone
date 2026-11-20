<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\FoodItem;
use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class FoodItemController extends Controller
{
    // Restaurant ke saare food items — search by name/description/tags,
    // plus category / veg / availability filters.
    public function index(Request $request, Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $query = $restaurant->foodItems()->with('category', 'menuSection');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%")
                    ->orWhere('tags', 'like', "%{$term}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('veg')) {
            $query->where('is_veg', $request->veg === 'veg' ? 1 : 0);
        }

        if ($request->filled('availability')) {
            $query->where('is_available', $request->availability === 'available' ? 1 : 0);
        }

        $foodItems = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::all();
        $sections = $restaurant->menuSections()->get();

        return view('owner.food-items.index', compact('restaurant', 'foodItems', 'categories', 'sections'));
    }

    // Full menu (all items, not paginated) as a print-friendly page —
    // the owner uses the browser's "Print → Save as PDF" to export it.
    public function exportMenu(Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $menuByCategory = $restaurant->foodItems()
            ->with('category', 'menuSection')
            ->orderBy('name')
            ->get()
            ->groupBy(fn ($item) => $item->menuSection->name ?? $item->category->name ?? 'Other');

        return view('owner.food-items.menu-export', compact('restaurant', 'menuByCategory'));
    }

    // Add form
    public function create(Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        $categories = Category::all();
        $sections = $restaurant->menuSections()->where('is_active', true)->get();
        return view('owner.food-items.create', compact('restaurant', 'categories', 'sections'));
    }

    // Save food item
    public function store(Request $request, Restaurant $restaurant)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'menu_section_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('menu_sections', 'id')->where('restaurant_id', $restaurant->id)],
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
            'is_veg'      => 'boolean',
        ]);

        $data = $request->only(['name', 'category_id', 'menu_section_id', 'price', 'description', 'tags']);
        $data['restaurant_id'] = $restaurant->id;
        $data['is_veg']        = $request->has('is_veg') ? 1 : 0;
        $data['is_available']  = 1;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('food-items', 'public');
        }

        FoodItem::create($data);

        return redirect()
            ->route('owner.restaurants.food-items.index', $restaurant)
            ->with('success', 'Food item added!');
    }

    // Edit form
    public function edit(Restaurant $restaurant, FoodItem $foodItem)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if((int) $foodItem->restaurant_id !== (int) $restaurant->id, 404);
        $categories = Category::all();
        $sections = $restaurant->menuSections()->where('is_active', true)->get();
        return view('owner.food-items.edit', compact('restaurant', 'foodItem', 'categories', 'sections'));
    }

    // Update food item
    public function update(Request $request, Restaurant $restaurant, FoodItem $foodItem)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if((int) $foodItem->restaurant_id !== (int) $restaurant->id, 404);

        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'menu_section_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('menu_sections', 'id')->where('restaurant_id', $restaurant->id)],
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'tags'        => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
        ]);

        // Whitelist explicitly — never trust $request->except('image') here, since a
        // tampered request could otherwise slip in restaurant_id and move this item
        // into a restaurant the owner doesn't own.
        $data = $request->only(['name', 'category_id', 'menu_section_id', 'price', 'description', 'tags']);
        $data['is_veg'] = $request->has('is_veg') ? 1 : 0;

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')
                ->store('food-items', 'public');
        }

        $foodItem->update($data);

        return redirect()
            ->route('owner.restaurants.food-items.index', $restaurant)
            ->with('success', 'Food item updated!');
    }

    // Delete food item
    public function destroy(Restaurant $restaurant, FoodItem $foodItem)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if((int) $foodItem->restaurant_id !== (int) $restaurant->id, 404);
        $foodItem->delete();
        return back()->with('success', 'Food item deleted!');
    }

    // Toggle availability
    public function toggleAvailability(Restaurant $restaurant, FoodItem $foodItem)
    {
        abort_if($restaurant->user_id !== auth()->id(), 403);
        abort_if((int) $foodItem->restaurant_id !== (int) $restaurant->id, 404);
        $foodItem->update(['is_available' => !$foodItem->is_available]);
        return back()->with('success', 'Availability updated!');
    }
}
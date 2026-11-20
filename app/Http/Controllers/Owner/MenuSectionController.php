<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\MenuSection;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MenuSectionController extends Controller
{
    public function store(Request $request, Restaurant $restaurant)
    {
        $this->authorizeRestaurant($restaurant);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('menu_sections')->where('restaurant_id', $restaurant->id)],
            'sort_order' => 'required|integer|min:0|max:65535',
        ]);
        $restaurant->menuSections()->create($data);

        return back()->with('success', 'Menu section created.');
    }

    public function update(Request $request, Restaurant $restaurant, MenuSection $section)
    {
        $this->authorizeSection($restaurant, $section);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('menu_sections')->where('restaurant_id', $restaurant->id)->ignore($section->id)],
            'sort_order' => 'required|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $section->update($data);

        return back()->with('success', 'Menu section updated.');
    }

    public function destroy(Restaurant $restaurant, MenuSection $section)
    {
        $this->authorizeSection($restaurant, $section);
        $section->delete();

        return back()->with('success', 'Menu section deleted. Its items remain in their original categories.');
    }

    private function authorizeRestaurant(Restaurant $restaurant): void
    {
        abort_if((int) $restaurant->user_id !== (int) auth()->id(), 403);
    }

    private function authorizeSection(Restaurant $restaurant, MenuSection $section): void
    {
        $this->authorizeRestaurant($restaurant);
        abort_if((int) $section->restaurant_id !== (int) $restaurant->id, 404);
    }
}

<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\Category;
use App\Models\FoodItem;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Restaurant::where('is_approved', true);

        // Open only filter (default shows all incl closed)
        if ($request->filter === 'open') {
            $query->where('is_open', true);
        }

        // Search filter
       if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                ->orWhereHas('foodItems', function ($fq) use ($request) {
                    $fq->where('name', 'like', '%' . $request->search . '%')
                        ->where('is_available', true);
                });
            });
        }

        // Location — no longer used to hide restaurants from the listing.
        // The customer's selected pincode is still tracked (for the location
        // label, the "doesn't deliver here" banner on a restaurant's page,
        // and the hard block at checkout) — it just doesn't filter out
        // restaurants here anymore. ?show_all=1 is kept as a harmless no-op
        // so any old links using it still work exactly the same as before.
        $pincode = $request->filled('pincode') ? $request->pincode : session('customer_pincode');
        $showAll = $request->boolean('show_all');
        if ($showAll) {
            $pincode = null;
        }

        // Cuisine tag filter
        if ($request->filled('cuisine')) {
            $query->where('cuisines', 'like', '%' . $request->cuisine . '%');
        }

        // Category filter (restaurants that have food items in this category)
        if ($request->filled('category')) {
            $query->whereHas('foodItems', function ($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        // Veg filter — only restaurants with at least 1 veg item
        if ($request->filter === 'veg') {
            $query->whereHas('foodItems', function ($q) {
                $q->where('is_veg', true)->where('is_available', true);
            });
        }

        // Ratings + popularity always computed so sort/filter can use them
        $query->withCount('foodItems')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withAvg('foodItems', 'price');

        // Minimum rating filter (4.0+ / 4.5+) — separate from sort, so a
        // customer can combine "4.0 and above" with "cheapest first" etc.
        if ($request->filled('min_rating')) {
            $query->having('reviews_avg_rating', '>=', (float) $request->min_rating);
        }

        // Sort — defaults to newest first (kept as "Relevance" in the UI)
        $sort = $request->sort ?: ($request->filter === 'top_rated' ? 'rating' : 'relevance');
        switch ($sort) {
            case 'rating':
                $query->orderByDesc('reviews_avg_rating');
                break;
            case 'cost_low':
                $query->orderBy('food_items_avg_price');
                break;
            case 'cost_high':
                $query->orderByDesc('food_items_avg_price');
                break;
            default:
                $query->latest();
        }

        $restaurants = $query->paginate(12)->withQueryString();

        $categories = Category::all();

        $cuisines = Restaurant::where('is_approved', true)
            ->whereNotNull('cuisines')
            ->pluck('cuisines')
            ->flatMap(fn ($c) => array_map('trim', explode(',', $c)))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        // "Order it again" — last few distinct restaurants this customer has
        // ordered from, only shown on the plain landing view (no active
        // search/filters) so it doesn't compete with search results.
        $reorderSuggestions = collect();
        if (auth()->check() && !$request->hasAny(['search', 'category', 'cuisine', 'filter', 'min_rating'])) {
            $reorderSuggestions = \App\Models\Order::where('user_id', auth()->id())
                ->whereIn('status', ['delivered'])
                ->with('restaurant')
                ->latest()
                ->get()
                ->unique('restaurant_id')
                ->filter(fn ($o) => $o->restaurant && $o->restaurant->is_approved)
                ->take(4)
                ->values();
        }

        return view('customer.dashboard', compact('restaurants', 'categories', 'cuisines', 'pincode', 'showAll', 'sort', 'reorderSuggestions'));
    }

    // Powers the search-as-you-type dropdown on the dashboard search box.
    // Not scoped to location anymore — same "show everything, only check
    // delivery at checkout" behavior as the main listing.
    public function suggestions(Request $request)
    {
        $term = trim((string) $request->q);

        if (mb_strlen($term) < 2) {
            return response()->json(['restaurants' => [], 'dishes' => []]);
        }

        $restaurants = Restaurant::where('is_approved', true)
            ->where('name', 'like', '%' . $term . '%')
            ->limit(5)
            ->get(['id', 'name', 'pincode'])
            ->map(fn ($r) => [
                'name'    => $r->name,
                'pincode' => $r->pincode,
                'url'     => route('customer.restaurant.show', $r),
            ]);

        $dishes = FoodItem::where('name', 'like', '%' . $term . '%')
            ->where('is_available', true)
            ->whereHas('restaurant', function ($q) {
                $q->where('is_approved', true);
            })
            ->with('restaurant:id,name')
            ->limit(5)
            ->get()
            ->map(fn ($d) => [
                'name'       => $d->name,
                'restaurant' => $d->restaurant->name ?? '',
                'price'      => $d->price,
                'url'        => route('customer.restaurant.show', $d->restaurant_id),
            ]);

        return response()->json(['restaurants' => $restaurants, 'dishes' => $dishes]);
    }

    public function show(Restaurant $restaurant)
    {
        abort_if(!$restaurant->is_approved, 404);

        $menuByCategory = $restaurant->foodItems()
            ->where('is_available', true)
            ->with(['category', 'options'])
            ->get()
            ->groupBy('category.name');

        // Bestseller badge — top 3 most-ordered items at this restaurant,
        // by how many times they've shown up across all order line items.
        $bestsellerIds = \App\Models\OrderItem::whereIn(
                'food_item_id',
                $restaurant->foodItems()->pluck('id')
            )
            ->selectRaw('food_item_id, COUNT(*) as order_count')
            ->groupBy('food_item_id')
            ->orderByDesc('order_count')
            ->limit(3)
            ->pluck('food_item_id');

        $reviews = $restaurant->reviews()
            ->with('user')
            ->latest()
            ->paginate(10);

        $isFavorited = auth()->check()
            ? auth()->user()->favoriteRestaurants()->where('restaurants.id', $restaurant->id)->exists()
            : false;

        // Lets the page warn the customer up front if this restaurant is
        // outside their currently selected delivery location — most useful
        // after browsing "all locations" from the dashboard.
        $customerPincode = session('customer_pincode');
        $deliversToCustomer = $restaurant->deliversToLocation(
            $customerPincode, session('customer_lat'), session('customer_lng')
        );

        // "You might also like" — other approved restaurants sharing the
        // same pincode or a cuisine tag, so browsing doesn't dead-end here.
        $cuisineTags = collect(explode(',', $restaurant->cuisines ?? ''))->map(fn ($c) => trim($c))->filter();

        $similarRestaurants = Restaurant::where('is_approved', true)
            ->where('id', '!=', $restaurant->id)
            ->where(function ($q) use ($restaurant, $cuisineTags) {
                $q->where('pincode', $restaurant->pincode);
                foreach ($cuisineTags as $tag) {
                    $q->orWhere('cuisines', 'like', "%{$tag}%");
                }
            })
            ->withCount('foodItems')
            ->withAvg('reviews', 'rating')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('customer.restaurant', compact(
            'restaurant', 'menuByCategory', 'reviews', 'isFavorited', 'customerPincode', 'deliversToCustomer', 'similarRestaurants', 'bestsellerIds'
        ));
    }
}

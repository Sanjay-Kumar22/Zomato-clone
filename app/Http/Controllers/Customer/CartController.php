<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use App\Models\DeliveryZone;
use App\Models\FoodItem;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected CartService $cart;

    public function __construct(CartService $cart)
    {
        $this->cart = $cart;
    }

    public function index(CouponService $coupons)
    {
        $allCarts = $this->cart->all();
        $defaultPincode = auth()->user()->addresses()->where('is_default', true)->value('pincode');
        $cartGroups = [];

        foreach ($allCarts as $restaurantId => $items) {
            if (empty($items)) continue;

            $restaurantId = (int) $restaurantId;
            $restaurantName = collect($items)->first()['restaurant'] ?? 'Restaurant';
            $subtotal = collect($items)->sum(fn ($item) => $item['price'] * $item['quantity']);
            $deliveryFee = 0;

            if ($defaultPincode) {
                $deliveryFee = (float) (DeliveryZone::where('restaurant_id', $restaurantId)
                    ->where('pincode', $defaultPincode)
                    ->value('delivery_fee') ?? 0);
            }

            $couponResult = $coupons->resolve($restaurantId, $subtotal);
            $discount = $couponResult['discount'];
            $appliedCoupon = $couponResult['coupon'];

            $cartGroups[] = [
                'restaurant_id' => $restaurantId,
                'restaurant' => $restaurantName,
                'items' => $items,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'discount' => $discount,
                'total' => max(0, $subtotal - $discount + $deliveryFee),
                'applied_coupon' => $appliedCoupon,
            ];
        }

        return view('customer.cart', compact('cartGroups'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'food_item_id' => 'required|exists:food_items,id',
            'quantity' => 'integer|min:1|max:10',
            'option_ids' => 'array',
            'option_ids.*' => 'integer|exists:food_item_options,id',
        ]);

        $foodItem = FoodItem::with('restaurant')->findOrFail($request->food_item_id);
        if (!$foodItem->restaurant || !$foodItem->restaurant->isCurrentlyOpen()) {
            return back()->with('error', 'Sorry, this restaurant is currently closed and not accepting orders right now.');
        }

        // Multi-restaurant carts are supported. Each restaurant gets its own cart.
        $this->cart->add(
            $request->food_item_id,
            $request->quantity ?? 1,
            $request->option_ids ?? []
        );

        return back()->with('success', 'Item added to cart!');
    }

    // Kept for backward compatibility with the old route; multi-cart no longer needs confirmation.
    public function confirmReplace()
    {
        return redirect()->route('customer.cart.index')->with('success', 'Your restaurant carts are kept separately.');
    }

    public function update(Request $request)
    {
        $request->validate([
            'food_item_id' => 'required',
            'restaurant_id' => 'required|integer',
            'quantity' => 'required|integer|min:0',
        ]);

        $this->cart->select((int) $request->restaurant_id);
        $this->cart->update((string) $request->food_item_id, $request->quantity);
        return back()->with('success', 'Cart updated!');
    }

    public function remove(Request $request)
    {
        $request->validate(['food_item_id' => 'required', 'restaurant_id' => 'required|integer']);
        $this->cart->select((int) $request->restaurant_id);
        $this->cart->remove((string) $request->food_item_id);
        return back()->with('success', 'Item removed!');
    }

    public function clear(Request $request)
    {
        $request->validate(['restaurant_id' => 'required|integer']);
        $this->cart->select((int) $request->restaurant_id);
        $this->cart->clear();
        return back()->with('success', 'Restaurant cart cleared!');
    }
}

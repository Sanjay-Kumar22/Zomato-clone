<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\CouponService;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request, CartService $cart, CouponService $coupons)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'restaurant_id' => 'required|integer',
        ]);

        $cart->select((int) $request->restaurant_id);
        $restaurantId = $cart->restaurantId();
        if (!$restaurantId) {
            return back()->with('error', 'Restaurant cart not found.');
        }

        $coupons->apply($request->code, $restaurantId);
        $result = $coupons->resolve($restaurantId, $cart->total());

        if ($result['error']) {
            $coupons->remove($restaurantId);
            return back()->with('error', $result['error']);
        }

        return back()->with('success', 'Coupon applied! You saved ₹' . number_format($result['discount'], 2));
    }

    public function remove(Request $request, CartService $cart, CouponService $coupons)
    {
        $request->validate(['restaurant_id' => 'required|integer']);
        $cart->select((int) $request->restaurant_id);
        $coupons->remove((int) $request->restaurant_id);
        return back()->with('success', 'Coupon removed.');
    }
}

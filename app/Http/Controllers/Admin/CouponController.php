<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::with('restaurant');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();
        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        $restaurants = Restaurant::orderBy('name')->get();
        return view('admin.coupons.create', compact('restaurants'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'              => 'required|string|max:50|unique:coupons,code',
            'description'       => 'nullable|string|max:255',
            'discount_type'     => 'required|in:flat,percent',
            'discount_value'    => 'required|numeric|min:0.01',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'max_discount'      => 'nullable|numeric|min:0',
            'restaurant_id'     => 'nullable|exists:restaurants,id',
            'usage_limit'       => 'nullable|integer|min:1',
            'per_user_limit'    => 'nullable|integer|min:1',
            'first_order_only'  => 'nullable|boolean',
            'expires_at'        => 'nullable|date',
        ]);

        $data = $request->only([
            'code', 'description', 'discount_type', 'discount_value', 'min_order_amount',
            'max_discount', 'restaurant_id', 'usage_limit', 'per_user_limit', 'expires_at',
        ]);
        $data['code'] = strtoupper($request->code);
        $data['is_active'] = $request->has('is_active');
        $data['first_order_only'] = $request->boolean('first_order_only');

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created!');
    }

    public function edit(Coupon $coupon)
    {
        $restaurants = Restaurant::orderBy('name')->get();
        return view('admin.coupons.edit', compact('coupon', 'restaurants'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code'              => 'required|string|max:50|unique:coupons,code,' . $coupon->id,
            'description'       => 'nullable|string|max:255',
            'discount_type'     => 'required|in:flat,percent',
            'discount_value'    => 'required|numeric|min:0.01',
            'min_order_amount'  => 'nullable|numeric|min:0',
            'max_discount'      => 'nullable|numeric|min:0',
            'restaurant_id'     => 'nullable|exists:restaurants,id',
            'usage_limit'       => 'nullable|integer|min:1',
            'per_user_limit'    => 'nullable|integer|min:1',
            'first_order_only'  => 'nullable|boolean',
            'expires_at'        => 'nullable|date',
        ]);

        $data = $request->only([
            'code', 'description', 'discount_type', 'discount_value', 'min_order_amount',
            'max_discount', 'restaurant_id', 'usage_limit', 'per_user_limit', 'expires_at',
        ]);
        $data['code'] = strtoupper($request->code);
        $data['is_active'] = $request->has('is_active');
        $data['first_order_only'] = $request->boolean('first_order_only');

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated!');
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return back()->with('success', 'Coupon deleted!');
    }
}

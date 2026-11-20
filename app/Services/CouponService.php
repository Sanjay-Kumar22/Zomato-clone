<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\Order;

class CouponService
{
    protected string $legacyKey = 'applied_coupon_code';
    protected string $key = 'applied_coupon_codes';

    public function code(?int $restaurantId = null): ?string
    {
        $restaurantId = $restaurantId ?: $this->requestRestaurantId();
        $codes = session($this->key, []);
        if ($restaurantId && isset($codes[(string) $restaurantId])) {
            return $codes[(string) $restaurantId];
        }

        // Migrate the old single-cart coupon into the currently active restaurant cart.
        $legacy = session($this->legacyKey);
        if ($legacy && $restaurantId) {
            $codes[(string) $restaurantId] = $legacy;
            session([$this->key => $codes]);
            session()->forget($this->legacyKey);
            return $legacy;
        }

        return null;
    }

    public function apply(string $code, ?int $restaurantId = null): void
    {
        $restaurantId = $restaurantId ?: $this->requestRestaurantId();
        if (!$restaurantId) return;

        $codes = session($this->key, []);
        $codes[(string) $restaurantId] = strtoupper(trim($code));
        session([$this->key => $codes]);
        session()->forget($this->legacyKey);
    }

    public function remove(?int $restaurantId = null): void
    {
        $restaurantId = $restaurantId ?: $this->requestRestaurantId();
        $codes = session($this->key, []);
        if ($restaurantId) {
            unset($codes[(string) $restaurantId]);
        }
        session([$this->key => $codes]);
        session()->forget($this->legacyKey);
    }

    public function resolve(?int $restaurantId, float $subtotal): array
    {
        $code = $this->code($restaurantId);

        if (!$code) {
            return ['coupon' => null, 'discount' => 0, 'error' => null];
        }

        $coupon = Coupon::where('code', $code)->first();
        if (!$coupon) {
            return ['coupon' => null, 'discount' => 0, 'error' => 'Coupon not found.'];
        }

        if (!$coupon->isValid()) {
            return ['coupon' => null, 'discount' => 0, 'error' => 'This coupon has expired or is no longer active.'];
        }

        if ($coupon->restaurant_id && $coupon->restaurant_id !== $restaurantId) {
            return ['coupon' => null, 'discount' => 0, 'error' => 'This coupon is not valid for this restaurant.'];
        }

        if (auth()->check()) {
            if ($coupon->first_order_only && Order::where('user_id', auth()->id())
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) {
                    $query->where('payment_status', 'paid')->orWhere('payment_method', 'cod');
                })->exists()) {
                return ['coupon' => null, 'discount' => 0, 'error' => 'This offer is only available on your first order.'];
            }

            if ($coupon->per_user_limit !== null
                && $coupon->usages()->where('user_id', auth()->id())->count() >= $coupon->per_user_limit) {
                return ['coupon' => null, 'discount' => 0, 'error' => 'You have already used this coupon the maximum number of times.'];
            }
        }

        if ($subtotal < $coupon->min_order_amount) {
            return ['coupon' => null, 'discount' => 0, 'error' => 'Minimum order of ₹' . number_format($coupon->min_order_amount, 2) . ' required for this coupon.'];
        }

        return ['coupon' => $coupon, 'discount' => $coupon->calculateDiscount($subtotal), 'error' => null];
    }

    protected function requestRestaurantId(): ?int
    {
        $id = request()->input('restaurant_id', request()->query('restaurant_id'));
        return is_numeric($id) ? (int) $id : null;
    }
}

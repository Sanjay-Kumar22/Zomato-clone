<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'discount_type', 'discount_value',
        'min_order_amount', 'max_discount', 'restaurant_id',
        'usage_limit', 'used_count', 'expires_at', 'is_active',
        'first_order_only', 'per_user_limit',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'expires_at' => 'date',
        'first_order_only' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    // Check if coupon is currently usable (ignoring order amount / restaurant match)
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) return false;

        return true;
    }

    // Calculate discount amount for a given subtotal
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->discount_type === 'percent') {
            $discount = $subtotal * ($this->discount_value / 100);
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
        } else {
            $discount = $this->discount_value;
        }

        return round(min($discount, $subtotal), 2);
    }
}

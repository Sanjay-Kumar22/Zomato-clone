<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'restaurant_id', 'address_id', 'delivery_agent_id',
        'agent_lat', 'agent_lng', 'agent_location_updated_at',
        'subtotal', 'delivery_fee', 'tip_amount', 'delivery_slot', 'total', 'coupon_code', 'discount',
        'points_earned', 'points_redeemed', 'points_discount', 'wallet_used',
        'status', 'payment_id', 'payment_status', 'payment_method', 'payment_finalized_at'
    ];

    protected $casts = [
        'agent_lat'                  => 'decimal:7',
        'agent_lng'                  => 'decimal:7',
        'agent_location_updated_at'  => 'datetime',
        'payment_finalized_at'       => 'datetime',
    ];

    // ✅ Yeh relationship add karo
    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryAgent()
    {
        return $this->belongsTo(User::class, 'delivery_agent_id');
    }

    public function address()
    {
        // withTrashed so a delivered order still shows exactly where it was
        // sent even after the customer removes that address from their list.
        return $this->belongsTo(Address::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function dispute()
    {
        return $this->hasOne(OrderDispute::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }
}
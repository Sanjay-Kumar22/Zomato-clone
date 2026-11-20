<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'restaurant_id', 'address_id', 'delivery_agent_id',
        'agent_lat', 'agent_lng', 'agent_location_updated_at',
        'subtotal', 'delivery_fee', 'tip_amount', 'delivery_slot', 'total', 'coupon_code', 'discount',
        'points_earned', 'points_redeemed', 'points_discount', 'wallet_used',
        'status', 'payment_id', 'payment_status', 'payment_method', 'payment_finalized_at',
        'special_instructions', 'cooking_notes', 'packaging_charge', 'tax_amount',
        'platform_fee', 'delivery_otp', 'delivery_otp_verified_at', 'estimated_delivery_at',
        'cod_collected_amount', 'cod_collected_at',
        'membership_free_delivery',
    ];

    protected static function booted(): void
    {
        static::updating(function (Order $order): void {
            if ($order->isDirty('status')
                && $order->status === 'out_for_delivery'
                && !$order->delivery_otp) {
                $order->delivery_otp = (string) random_int(100000, 999999);
            }
        });

        static::created(function (Order $order): void {
            $order->statusHistory()->create([
                'changed_by' => auth()->id(),
                'to_status' => $order->status,
            ]);
        });

        static::updated(function (Order $order): void {
            if (!$order->wasChanged('status')) {
                return;
            }

            $order->statusHistory()->create([
                'changed_by' => auth()->id(),
                'from_status' => $order->getOriginal('status'),
                'to_status' => $order->status,
            ]);
        });
    }

    protected $casts = [
        'agent_lat'                  => 'decimal:7',
        'agent_lng'                  => 'decimal:7',
        'agent_location_updated_at'  => 'datetime',
        'payment_finalized_at'       => 'datetime',
        'delivery_otp'               => 'encrypted',
        'delivery_otp_verified_at'   => 'datetime',
        'estimated_delivery_at'      => 'datetime',
        'cod_collected_amount'       => 'decimal:2',
        'cod_collected_at'           => 'datetime',
        'membership_free_delivery'   => 'boolean',
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

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function paymentAttempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class)->latest();
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class)->latest();
    }

    public function dispute()
    {
        return $this->hasOne(OrderDispute::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function settlementLine()
    {
        return $this->hasOne(RestaurantSettlementOrder::class, 'order_id');
    }

    public function locationHistory()
    {
        return $this->hasMany(DeliveryAgentLocationHistory::class, 'order_id')->orderBy('recorded_at');
    }
}
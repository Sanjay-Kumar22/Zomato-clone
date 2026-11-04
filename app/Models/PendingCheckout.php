<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A draft of an online-payment (Stripe/Razorpay) checkout that hasn't been
 * paid yet. It only turns into a real Order once the gateway confirms
 * payment — see CheckoutFinalizer.
 */
class PendingCheckout extends Model
{
    protected $fillable = [
        'user_id', 'restaurant_id', 'payment_method', 'payable_amount',
        'payload', 'gateway_reference', 'status', 'order_id',
    ];

    protected $casts = [
        'payload'        => 'array',
        'payable_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}

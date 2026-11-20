<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantSettlementOrder extends Model
{
    protected $fillable = [
        'restaurant_settlement_id', 'order_id', 'gross_amount', 'commission_amount', 'payout_amount',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(RestaurantSettlement::class, 'restaurant_settlement_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

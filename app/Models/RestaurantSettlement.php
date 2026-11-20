<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantSettlement extends Model
{
    protected $fillable = [
        'restaurant_id', 'requested_by', 'reviewed_by', 'period_start', 'period_end',
        'gross_amount', 'commission_amount', 'payout_amount', 'status',
        'payout_reference', 'review_note', 'reviewed_at', 'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'reviewed_at' => 'datetime',
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(RestaurantSettlementOrder::class);
    }
}

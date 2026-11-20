<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'order_id', 'payment_attempt_id', 'dispute_id', 'requested_by',
        'amount', 'method', 'status', 'gateway_refund_id', 'failure_reason',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentAttempt(): BelongsTo
    {
        return $this->belongsTo(PaymentAttempt::class);
    }

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(OrderDispute::class);
    }
}

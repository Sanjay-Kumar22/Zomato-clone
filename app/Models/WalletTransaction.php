<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'balance_after', 'reason', 'order_id',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getReasonLabelAttribute(): string
    {
        return match ($this->reason) {
            'refund'          => 'Refund',
            'referral_bonus'  => 'Referral Bonus',
            'signup_bonus'    => 'Welcome Bonus',
            'order_payment'   => 'Order Payment',
            'order_refund_cancel' => 'Order Cancelled — Refund',
            'wallet_topup'    => 'Added Money',
            default           => ucfirst(str_replace('_', ' ', $this->reason)),
        };
    }
}

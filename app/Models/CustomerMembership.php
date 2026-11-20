<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerMembership extends Model
{
    protected $fillable = ['user_id', 'plan', 'price_paid', 'starts_at', 'expires_at', 'status'];

    protected $casts = [
        'price_paid' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

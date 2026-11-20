<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAgentLocationHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'order_id', 'lat', 'lng', 'recorded_at'];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'recorded_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

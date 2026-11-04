<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDispute extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'reason', 'description', 'status',
        'refund_amount', 'resolution_note', 'resolved_by', 'resolved_at',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'resolved_at'   => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}

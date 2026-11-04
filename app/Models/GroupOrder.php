<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupOrder extends Model
{
    protected $fillable = [
        'code', 'restaurant_id', 'host_id', 'status', 'order_id', 'address_id',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(GroupOrderItem::class);
    }

    public function total(): float
    {
        return $this->items->sum(fn ($i) => $i->price * $i->quantity);
    }
}

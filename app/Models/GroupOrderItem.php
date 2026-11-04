<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupOrderItem extends Model
{
    protected $fillable = [
        'group_order_id', 'user_id', 'food_item_id', 'name', 'price', 'quantity', 'selected_options',
    ];

    protected $casts = [
        'price'             => 'decimal:2',
        'selected_options'  => 'array',
    ];

    public function groupOrder()
    {
        return $this->belongsTo(GroupOrder::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class);
    }
}

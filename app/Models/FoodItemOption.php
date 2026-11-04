<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodItemOption extends Model
{
    protected $fillable = ['food_item_id', 'label', 'value', 'extra_price'];

    protected $casts = [
        'extra_price' => 'decimal:2',
    ];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class);
    }
}

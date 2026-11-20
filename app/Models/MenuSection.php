<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuSection extends Model
{
    protected $fillable = ['restaurant_id', 'name', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function foodItems(): HasMany
    {
        return $this->hasMany(FoodItem::class);
    }
}

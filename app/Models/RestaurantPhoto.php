<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantPhoto extends Model
{
    protected $fillable = ['restaurant_id', 'path', 'sort_order'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}

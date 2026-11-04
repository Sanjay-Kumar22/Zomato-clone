<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'category_id', 'name',
        'description', 'price', 'image',
        'is_veg', 'tags', 'is_available'
    ];

    protected $casts = [
        'is_veg'       => 'boolean',
        'is_available' => 'boolean',
        'price'        => 'decimal:2',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function options()
    {
        return $this->hasMany(FoodItemOption::class);
    }

    // Works whether `image` holds a local storage path or a full external URL
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        return str_starts_with($this->image, 'http')
            ? $this->image
            : asset('storage/' . $this->image);
    }

    // Tags like "Spicy, Best Seller" -> ['Spicy', 'Best Seller']
    public function getTagsListAttribute(): array
    {
        if (!$this->tags) return [];
        return array_filter(array_map('trim', explode(',', $this->tags)));
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Restaurant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'slug', 'address',
        'pincode', 'lat', 'lng', 'cuisines',
        'is_approved', 'is_open', 'image', 'minimum_order_value',
        'preparation_time_minutes', 'delivery_radius_km', 'average_cost_for_two',
        'packaging_charge', 'is_featured',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_open'     => 'boolean',
        'lat'         => 'decimal:7',
        'lng'         => 'decimal:7',
        'minimum_order_value' => 'decimal:2',
        'delivery_radius_km' => 'decimal:2',
        'average_cost_for_two' => 'decimal:2',
        'packaging_charge' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function foodItems()
    {
        return $this->hasMany(FoodItem::class);
    }

    public function menuSections()
    {
        return $this->hasMany(MenuSection::class)->orderBy('sort_order');
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function photos()
    {
        return $this->hasMany(RestaurantPhoto::class)->orderBy('sort_order');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }

    public function hours()
    {
        return $this->hasMany(RestaurantHour::class);
    }

    // Whether the restaurant is accepting orders right now — combines the
    // owner's manual is_open switch with the weekly schedule (if one is set).
    // A restaurant with no configured schedule falls back to the manual
    // switch only, so this stays backward-compatible with existing data.
    public function isCurrentlyOpen(): bool
    {
        if (!$this->is_open) {
            return false;
        }

        $today = $this->relationLoaded('hours')
            ? $this->hours->firstWhere('day_of_week', now()->dayOfWeek)
            : $this->hours()->where('day_of_week', now()->dayOfWeek)->first();

        if (!$today) {
            return true; // no schedule configured for today — manual switch decides
        }

        if ($today->is_closed || !$today->opens_at || !$today->closes_at) {
            return false;
        }

        $now   = now()->format('H:i:s');
        $opens = $today->opens_at;
        $closes = $today->closes_at;

        // Overnight window (e.g. 18:00 - 02:00) wraps past midnight.
        if ($closes < $opens) {
            return $now >= $opens || $now <= $closes;
        }

        return $now >= $opens && $now <= $closes;
    }

    // Average rating accessor — uses the eager-loaded `withAvg('reviews', 'rating')`
    // value when available (no extra query), and only falls back to a live query
    // when the caller didn't eager-load it (e.g. a single restaurant detail page).
    public function getAvgRatingAttribute()
    {
        if (array_key_exists('reviews_avg_rating', $this->attributes)) {
            return $this->attributes['reviews_avg_rating'] ?? 0;
        }
        return $this->reviews()->avg('rating') ?? 0;
    }

    // Works whether `image` holds a local storage path (uploaded via the owner panel)
    // or a full external URL (e.g. pasted directly into the database) — always
    // returns a ready-to-use <img src> value.
    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image) return null;
        return str_starts_with($this->image, 'http')
            ? $this->image
            : asset('storage/' . $this->image);
    }

    // Cuisine tags as a clean array, e.g. "North Indian, Chinese" -> ['North Indian', 'Chinese']
    public function getCuisinesListAttribute(): array
    {
        if (!$this->cuisines) return [];
        return array_filter(array_map('trim', explode(',', $this->cuisines)));
    }

    // Average food quality / delivery experience ratings (from rating breakdown)
    public function getAvgFoodRatingAttribute()
    {
        return $this->reviews()->whereNotNull('food_rating')->avg('food_rating') ?? 0;
    }

    public function getAvgDeliveryRatingAttribute()
    {
        return $this->reviews()->whereNotNull('delivery_rating')->avg('delivery_rating') ?? 0;
    }

    // Scope — pincode filter
    public function scopeByPincode($query, $pincode)
    {
        return $query->where('pincode', $pincode);
    }

    public function deliversToPincode(?string $pincode): bool
    {
        return $this->deliversToLocation($pincode);
    }

    // Whether this restaurant actually delivers to a given pincode/location:
    //  - its own base pincode always counts as served
    //  - an explicit delivery zone for that pincode wins (open or blocked),
    //    regardless of distance
    //  - otherwise, if the customer's coordinates are known, fall back to a
    //    straight-line radius check (DEFAULT_DELIVERY_RADIUS_KM) instead of
    //    blocking outright — previously ANY pincode without a manually-added
    //    zone was rejected even if it was a few streets over from the
    //    restaurant's own pincode
    //  - with no zone and no coordinates to fall back on, treated as out of
    //    the delivery area
    const DEFAULT_DELIVERY_RADIUS_KM = 7.0;

    public function deliversToLocation(?string $pincode, $lat = null, $lng = null, ?float $radiusKm = null): bool
    {
        $radiusKm = $radiusKm ?? ((float) $this->delivery_radius_km ?: self::DEFAULT_DELIVERY_RADIUS_KM);

        // Trim so a pincode with a stray leading/trailing space (very easy to
        // pick up when copy-pasting an address) still matches correctly
        // instead of being wrongly treated as "outside the delivery area".
        $pincode = $pincode !== null ? trim($pincode) : null;

        if (!$pincode && $lat === null) {
            return true; // no location selected yet — don't block anything
        }

        if ($pincode) {
            if ($pincode === trim((string) $this->pincode)) {
                return true;
            }

            $zone = $this->deliveryZones()->where('pincode', $pincode)->first();
            if ($zone) {
                return $zone->zone_type !== 'blocked';
            }
        }

        if ($lat !== null && $lng !== null && $this->lat !== null && $this->lng !== null) {
            return $this->distanceKmTo((float) $lat, (float) $lng) <= $radiusKm;
        }

        return false;
    }

    // Straight-line (Haversine) distance in km from this restaurant to a point.
    public function distanceKmTo(float $lat, float $lng): float
    {
        $earthRadiusKm = 6371;
        $lat1 = deg2rad((float) $this->lat);
        $lng1 = deg2rad((float) $this->lng);
        $lat2 = deg2rad($lat);
        $lng2 = deg2rad($lng);

        $dLat = $lat2 - $lat1;
        $dLng = $lng2 - $lng1;

        $a = sin($dLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($dLng / 2) ** 2;

        return $earthRadiusKm * (2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    // Scope — only approved & open
    public function scopeActive($query)
    {
        return $query->where('is_approved', true)
                     ->where('is_open', true);
    }
}
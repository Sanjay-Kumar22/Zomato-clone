<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantHour extends Model
{
    protected $fillable = [
        'restaurant_id', 'day_of_week', 'is_closed', 'opens_at', 'closes_at',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public static function dayName(int $day): string
    {
        return ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$day] ?? '';
    }
}

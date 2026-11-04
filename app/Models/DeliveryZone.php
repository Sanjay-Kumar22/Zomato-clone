<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = ['restaurant_id', 'pincode', 'zone_type', 'delivery_fee', 'estimated_time', 'lat', 'lng', 'user_id', 'label', 'full_address'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}

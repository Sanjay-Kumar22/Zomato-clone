<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'label', 'full_address', 'pincode', 'lat', 'lng', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
        'lat'        => 'decimal:7',
        'lng'        => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

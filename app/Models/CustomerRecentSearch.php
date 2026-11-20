<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerRecentSearch extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'term', 'searched_at'];

    protected $casts = ['searched_at' => 'datetime'];
}

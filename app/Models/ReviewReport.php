<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{
    protected $fillable = [
        'review_id', 'reported_by', 'reason', 'description',
        'status', 'moderated_by', 'moderated_at',
    ];

    protected $casts = ['moderated_at' => 'datetime'];

    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }
}

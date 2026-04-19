<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationDraft extends Model
{
    protected $guarded = [];

    protected $casts = [
        'details_payload' => 'array',
        'visitor_payload' => 'array',
        'payment_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

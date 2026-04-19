<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IntegrationLog extends Model
{
    protected $casts = [
        'payload'  =>  'array',
        'response'  =>  'array',
    ];

    public function getReservationAttribute($value)
    {
        return Reservation::find($this->payload['id'] ?? null);
    }
}

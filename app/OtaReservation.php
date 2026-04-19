<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OtaReservation extends Model
{
    protected $fillable = [
        'team_id', 'action', 'hotel_code', 'channel',
        'booking_id', 'cm_booking_id', 'booked_on',
        'checkin', 'checkout', 'segment', 'special_requests',
        'pah', 'is_posted', 'amount', 'guest', 'rooms',
        'request', 'is_open', 'unit'
    ];

    protected $casts = [
        'amount' => 'array',
        'guest' => 'array',
        'rooms' => 'array',
        'booked_on' => 'datetime',
        'checkin' => 'date',
        'checkout' => 'date',
        'pah' => 'boolean',
        'is_posted' => 'boolean',
        'request' => 'array',
        'is_open' => 'boolean',
        'unit' => 'string',
    ];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $guarded = [];

    protected $casts = [
        'price_per_day' => 'float',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function roomFloor()
    {
        return $this->belongsTo(RoomFloor::class);
    }
}

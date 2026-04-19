<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class ReservationMessageLog extends Model
{
    protected $fillable = [
        'reservation_id',
        'type',
        'message'
    ];

    protected $appends = [
        'date'
    ];

    public function getDateAttribute()
    {
        $date = Carbon::make($this->created_at);
        return $date->format('Y/m/d h:i a');
    }
}

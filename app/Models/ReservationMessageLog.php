<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationMessageLog extends Model
{
    protected $fillable = [
        'team_id',
        'reservation_id',
        'type',
        'subject',
        'message',
        'status',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function sentBy()
    {
        return $this->belongsTo(\App\User::class, 'sent_by');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}

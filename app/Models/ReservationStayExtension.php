<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationStayExtension extends Model
{
    protected $fillable = [
        'team_id',
        'reservation_id',
        'old_check_out',
        'new_check_out',
        'extension_days',
        'reason',
        'created_by',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}

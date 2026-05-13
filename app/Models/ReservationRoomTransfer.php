<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationRoomTransfer extends Model
{
    protected $fillable = [
        'team_id',
        'reservation_id',
        'from_unit_id',
        'to_unit_id',
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

    public function fromUnit()
    {
        return $this->belongsTo(Unit::class, 'from_unit_id');
    }

    public function toUnit()
    {
        return $this->belongsTo(Unit::class, 'to_unit_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

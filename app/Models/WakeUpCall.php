<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WakeUpCall extends Model
{
    protected $fillable = ['team_id', 'reservation_id', 'unit_number', 'call_time', 'call_date', 'repeat', 'status', 'created_by'];

    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function createdBy()   { return $this->belongsTo(\App\User::class, 'created_by'); }
    public function team()        { return $this->belongsTo(Team::class); }
}

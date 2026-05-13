<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IptvGuestNeed extends Model
{
    protected $fillable = ['team_id', 'reservation_id', 'request_type', 'request_details', 'status', 'handled_by', 'handled_at'];

    protected $casts = ['handled_at' => 'datetime'];

    public function reservation() { return $this->belongsTo(Reservation::class); }
    public function handledBy()   { return $this->belongsTo(\App\User::class, 'handled_by'); }
    public function team()        { return $this->belongsTo(Team::class); }
}

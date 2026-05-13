<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StayChargeOverride extends Model
{
    protected $table = 'stay_charge_overrides';

    protected $fillable = [
        'team_id',
        'reservation_id',
        'charge_type',
        'original_amount',
        'overridden_amount',
        'reason',
        'user_id',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'overridden_amount' => 'decimal:2',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

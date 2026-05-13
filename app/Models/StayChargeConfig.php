<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StayChargeConfig extends Model
{
    use SoftDeletes;

    protected $table = 'stay_charge_configs';

    protected $fillable = [
        'team_id',
        'charge_type',
        'tier_from_hour',
        'tier_to_hour',
        'rate_type',
        'rate_amount',
        'applies_to',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rate_amount' => 'decimal:2',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

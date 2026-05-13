<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Team;
use App\User;

class EarlyLateChargeConfig extends Model
{
    protected $table = 'early_late_charge_configs';

    protected $fillable = [
        'team_id',
        'charge_type',
        'tier_from_hour',
        'tier_to_hour',
        'rate_type',
        'rate_amount',
        'applies_to',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'rate_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Relationship to team
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Relationship to user who created
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
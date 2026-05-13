<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NoShowChargeRule extends Model
{
    use SoftDeletes;

    protected $table = 'no_show_charge_rules';

    protected $fillable = [
        'team_id',
        'name',
        'start_date',
        'end_date',
        'charge_type',
        'charge_amount',
        'applies_to',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'charge_amount' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function team()
    {
        return $this->belongsTo(\App\Team::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}

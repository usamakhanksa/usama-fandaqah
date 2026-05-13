<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Role;

class NightAuditLog extends Model
{
    protected $table = 'night_audit_log';
    public $timestamps = false;

    protected $fillable = [
        'team_id',
        'business_date',
        'run_number',
        'status',
        'triggered_by',
        'triggered_by_user_id',
        'started_at',
        'completed_at',
        'steps_completed',
        'steps_failed',
        'noshows_flagged',
        'noshow_charges_posted',
        'transactions_frozen',
        'occupancy_snapshot_id',
        'rerun_of_log_id',
        'notes'
    ];

    protected $casts = [
        'steps_completed' => 'array',
        'steps_failed' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(\App\Team::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'triggered_by_user_id');
    }

    public function snapshot()
    {
        return $this->belongsTo(NightAuditOccupancySnapshot::class, 'occupancy_snapshot_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationAuditLock extends Model
{
    public $timestamps = false;

    protected $table = 'reservation_audit_locks';

    protected $primaryKey = 'reservation_id';

    public $incrementing = false;

    protected $fillable = [
        'reservation_id',
        'locked_from_date',
        'locked_by_audit',
        'team_id',
        'created_at',
    ];

    protected $casts = [
        'locked_from_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function auditLog()
    {
        return $this->belongsTo(\App\Models\NightAuditLog::class, 'locked_by_audit');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}

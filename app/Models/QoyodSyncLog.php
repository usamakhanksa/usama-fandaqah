<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QoyodSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'sync_type',
        'status',
        'records_synced',
        'records_failed',
        'started_at',
        'completed_at',
        'error_message',
        'qoyod_response',
        'triggered_by',
    ];

    protected $casts = [
        'qoyod_response' => 'json',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function triggerer()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }
}

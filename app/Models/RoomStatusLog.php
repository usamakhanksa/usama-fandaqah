<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RoomStatusLog extends Model
{
    protected $fillable = [
        'unit_id',
        'team_id',
        'from_status',
        'to_status',
        'changed_by',
        'change_reason',
        'reference_type',
        'reference_id',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}

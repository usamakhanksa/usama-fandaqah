<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Scopes\TeamScope;

class TurnawayLog extends Model
{
    protected $fillable = [
        'team_id',
        'guest_id',
        'guest_name',
        'guest_phone',
        'requested_room_type',
        'requested_date',
        'requested_nights',
        'reason',
        'reason_detail',
        'estimated_revenue_loss',
        'alternative_offered',
        'alternative_details',
        'turned_away_by'
    ];

    protected $casts = [
        'requested_date' => 'date',
        'alternative_offered' => 'boolean',
        'estimated_revenue_loss' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'turned_away_by');
    }
}

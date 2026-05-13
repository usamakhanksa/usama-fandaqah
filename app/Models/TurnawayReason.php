<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Scopes\TeamScope;

class TurnawayReason extends Model
{
    protected $fillable = [
        'team_id',
        'name',
        'name_ar',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Building extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name_en',
        'name_ar',
        'address',
        'total_floors'
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

    public function floors(): HasMany
    {
        return $this->hasMany(RoomFloor::class);
    }
}

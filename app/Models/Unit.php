<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'unit_number',      // Added this new field
        'number',           // Keeping old field as well
        'status',
        'room_id',
        'room_floor_id',
        'unit_type_id',
        'unit_status_id',
        'unit_category_id', // Adding this field
        'capacity',
        'beds',
        'baths',
        'thumbnail',
        'is_demo',
        'floor',            // Adding this field
        'is_active',        // Adding this field
        'enabled'           // Adding this field
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function roomFloor(): BelongsTo
    {
        return $this->belongsTo(RoomFloor::class);
    }

    public function unitType(): BelongsTo
    {
        return $this->belongsTo(UnitType::class);
    }

    public function unitStatus(): BelongsTo
    {
        return $this->belongsTo(UnitStatus::class);
    }

    public function unitCategory(): BelongsTo
    {
        return $this->belongsTo(\App\UnitCategory::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckInRecord::class);
    }

    public function checkOuts(): HasMany
    {
        return $this->hasMany(CheckOutRecord::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getNumberAttribute()
    {
        return $this->unit_number;
    }
}
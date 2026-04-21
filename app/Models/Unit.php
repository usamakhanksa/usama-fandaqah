<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $guarded = [];

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

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckInRecord::class);
    }

    public function checkOuts(): HasMany
    {
        return $this->hasMany(CheckOutRecord::class);
    }

    public function getNumberAttribute()
    {
        return $this->unit_number;
    }
}

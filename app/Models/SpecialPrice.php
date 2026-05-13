<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class SpecialPrice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'name_ar',
        'price_type',
        'room_type_id',
        'rate_amount',
        'rate_type',
        'base_rate',
        'meal_plan',
        'valid_from',
        'valid_to',
        'days_of_week',
        'min_los',
        'max_los',
        'is_active',
        'priority',
        'created_by'
    ];

    protected $casts = [
        'days_of_week' => 'json',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
        'rate_amount' => 'decimal:2',
        'base_rate' => 'decimal:2'
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

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(UnitCategory::class, 'room_type_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

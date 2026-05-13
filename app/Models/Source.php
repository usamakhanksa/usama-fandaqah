<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Source extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'deleteable',
        'order',
        'status',
        'is_travel_agent',
        'iata_number',
        'commission_rate',
        'commission_type'
    ];

    protected $casts = [
        'name' => 'json',
        'deleteable' => 'boolean',
        'is_travel_agent' => 'boolean',
        'commission_rate' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function commissionPayments(): HasMany
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
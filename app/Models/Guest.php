<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Guest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'company_profile_id',
        'name',
        'email',
        'phone',
        'avatar',
        'type',
        'gender',
        'card_id',
        'date_of_birth',
        'drop_down_civn',
        'address',
        'nationality',
        'id_type',
        'id_number',
        'shomoos_verified_at',
        'shomoos_reference',
        'shomoos_status',
        'shomoos_response'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'shomoos_verified_at' => 'datetime',
        'shomoos_response' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function companyProfile(): BelongsTo
    {
        return $this->belongsTo(CompanyProfile::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckInRecord::class);
    }

    public function checkOuts(): HasMany
    {
        return $this->hasMany(CheckOutRecord::class);
    }

    public function rating(): HasMany
    {
        return $this->hasMany(ReservationRating::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
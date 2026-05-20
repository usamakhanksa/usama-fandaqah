<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;
use App\User; // Adding explicit import for User model

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'entity_type',
        'user_id',
        'customer_id',
        'name',
        'phone',
        'email',
        'city',
        'person_incharge_name',
        'person_incharge_phone',
        'address',
        'tax_number',
        'postal_code',
        'district',
        'building_number',
        'street_name',
        'country_id',
        'company_group_id',
        'payment_terms_days',
        'credit_limit',
        'currency'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class, 'company_group_id');
    }

    public function companyNotes(): HasMany
    {
        return $this->hasMany(CompanyNote::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function promissories(): HasMany
    {
        return $this->hasMany(Promissory::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
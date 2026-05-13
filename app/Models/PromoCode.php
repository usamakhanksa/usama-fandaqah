<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class PromoCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'code',
        'name',
        'name_ar',
        'description',
        'discount_type',
        'discount_value',
        'applicable_to',
        'applicable_ids',
        'valid_from',
        'valid_to',
        'max_usage',
        'current_usage',
        'max_usage_per_guest',
        'min_booking_amount',
        'min_nights',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'applicable_ids' => 'json',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_booking_amount' => 'decimal:2'
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

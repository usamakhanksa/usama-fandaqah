<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'offer_type',
        'discount_value',
        'discount_percentage',
        'applicable_room_types',
        'applicable_sources',
        'min_nights',
        'max_nights',
        'min_advance_days',
        'max_advance_days',
        'valid_from',
        'valid_to',
        'booking_window_from',
        'booking_window_to',
        'is_stackable',
        'max_usage',
        'current_usage',
        'max_usage_per_guest',
        'is_active',
        'image_path',
        'terms_conditions',
        'sort_order',
        'created_by'
    ];

    protected $casts = [
        'applicable_room_types' => 'json',
        'applicable_sources' => 'json',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'booking_window_from' => 'date',
        'booking_window_to' => 'date',
        'is_stackable' => 'boolean',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'discount_percentage' => 'decimal:2'
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class ReservationRating extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'guest_id',
        'rating',
        'feedback',
        'published',
        'team_id'
    ];

    protected $casts = [
        'rating' => 'integer',
        'published' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
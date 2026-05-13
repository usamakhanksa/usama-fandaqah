<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class ReservationExtension extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reservation_id',
        'original_date_out',
        'extended_date_out',
        'extension_cost',
        'reason',
        'created_by',
        'team_id'
    ];

    protected $casts = [
        'original_date_out' => 'date',
        'extended_date_out' => 'date',
        'extension_cost' => 'decimal:2'
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;

class Voucher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'code',
        'name',
        'name_ar',
        'voucher_type',
        'value',
        'initial_value',
        'remaining_value',
        'currency',
        'is_percentage',
        'purchaser_name',
        'purchaser_email',
        'recipient_name',
        'recipient_email',
        'message',
        'valid_from',
        'valid_to',
        'status',
        'redeemed_at',
        'redeemed_by',
        'reservation_id',
        'created_by'
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'redeemed_at' => 'datetime',
        'is_percentage' => 'boolean',
        'value' => 'decimal:2',
        'initial_value' => 'decimal:2',
        'remaining_value' => 'decimal:2'
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

    public function redemptions(): HasMany
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    public function redeemer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'redeemed_by');
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

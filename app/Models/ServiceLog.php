<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use App\Scopes\TeamScope;

class ServiceLog extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'team_id',
        'user_id',
        'transaction_id',
        'type',
        'number',
        'amount',
        'decimals',
        'meta',
        'is_subtraction',
        'active_note',
        'zatca_invoice_number',
        'is_freezed',
        'business_date',
        'correction_reason',
        'corrected_by',
        'zatca_status',
        'zatca_invoice_id',
        'zatca_uuid',
        'zatca_qr_code',
        'zatca_response',
        'zatca_submitted_at',
        'zatca_accepted_at'
    ];

    protected $casts = [
        'meta' => 'json',
        'is_subtraction' => 'boolean',
        'is_freezed' => 'boolean',
        'business_date' => 'date',
        'amount' => 'integer',
        'decimals' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'payable_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getId()
    {
        return $this->id;
    }
}
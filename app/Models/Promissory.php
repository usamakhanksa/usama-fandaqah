<?php

namespace App\Models;

use App\Scopes\TeamScope;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promissory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'serial',
        'reservation_id',
        'team_id',
        'user_id',
        'total_amount',
        'collected_amount',
        'status',
        'due_date',
        'due_location',
        'due_for',
        'due_owner',
        'notes',
        'company_id',
        'fulfilled_at',
        'signature_status',
        'unsigned_reason',
        'business_date'
    ];

    protected $appends = ['hash_id'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'collected_amount' => 'decimal:2',
        'due_date' => 'date',
        'fulfilled_at' => 'datetime',
        'business_date' => 'date'
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function promissoryPaymentLogs(): HasMany
    {
        return $this->hasMany(PromissoryPaymentLog::class);
    }

    public function getHashIdAttribute()
    {
        return Hashids::encode($this->id);
    }

    public static function getPromissoryCustomerSignature($reservation_team_id, $reservation_id)
    {
        $signature = DigitalSignature::where('team_id', $reservation_team_id)
            ->where('ref_id', $reservation_id)
            ->where('type', DigitalSignature::TYPE_PROMISSORY)
            ->whereNull('user_id')
            ->first();
        if (isset($signature)) {
            try {
                $signature['signature'] = gzuncompress(base64_decode($signature->signature_base64));
                $signature = (object) $signature;
            } catch (\Exception $e) {
                \Log::error('Error decoding customer or company digital signature: ' . $e->getMessage() . ' for team ' . $reservation_team_id . ' and reservation id ' . $reservation_id);
                $signature = null;
            }
        }
        return $signature;
    }

    public static function getPromissoryOfficialSignature($reservation_team_id, $reservation_id)
    {
        $signature = DigitalSignature::where('team_id', $reservation_team_id)
            ->where('ref_id', $reservation_id)
            ->where('type', DigitalSignature::TYPE_PROMISSORY)
            ->whereNotNull('user_id')
            ->first();
        if (isset($signature)) {
            try {
                $signature['user_id'] = $signature->user_id;
                $signature['signature'] = gzuncompress(base64_decode($signature->signature_base64));
                $signature = (object) $signature;
            } catch (\Exception $e) {
                \Log::error('Error decoding official digital signature: ' . $e->getMessage() . ' for team ' . $reservation_team_id . ' and reservation id ' . $reservation_id);
                $signature = null;
            }
        }
        return $signature;
    }
}
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Scopes\TeamScope;
use App\Traits\Filterable;
use App\User; // Adding explicit import for User model

class Reservation extends Model
{
    use Filterable, SoftDeletes;

    protected $searchable = ['code', 'status'];
    protected $fillable = [
        'team_id',
        'code',
        'guest_id',
        'room_id',
        'unit_id',
        'status',
        'reservation_category_type',
        'special_request',
        'company_id',
        'audit_locks',
        'shomoos_verification_status',
        'noshow_flag',
        'extension_reason',
        'cancellation_reason',
        'primary_payment_method',
        'expected_check_in_time',
        'expected_check_out_time',
        'check_in',
        'check_out',
        'total_amount',
        'room_revenue',
        'no_show_charge',
        'refund_amount',
        'cancelled_at',
    ];
    
    protected $guarded = [];
    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'audit_locks' => 'json',
        'noshow_flag' => 'boolean',
        'expected_check_in_time' => 'datetime:H:i',
        'expected_check_out_time' => 'datetime:H:i',
        'total_amount' => 'decimal:2',
        'room_revenue' => 'decimal:2',
        'no_show_charge' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'cancelled_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new TeamScope());
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function commissionPayment(): HasOne
    {
        return $this->hasOne(CommissionPayment::class);
    }

    public function reservationExtensions(): HasMany
    {
        return $this->hasMany(ReservationExtension::class);
    }

    public function reservationRatings(): HasMany
    {
        return $this->hasMany(ReservationRating::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'payable_id')
                   ->where('payable_type', self::class);
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
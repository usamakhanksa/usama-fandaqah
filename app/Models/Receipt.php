<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenant;
use App\Services\Finance\ReceiptService;

class Receipt extends Model
{
    use HasFactory, SoftDeletes, MultiTenant;

    protected $fillable = [
        'team_id',
        'reservation_id',
        'guest_id',
        'company_id',
        'receipt_number',
        'receipt_date',
        'amount',
        'payment_method',
        'currency',
        'exchange_rate',
        'reference_number',
        'bank_name',
        'cheque_number',
        'card_last_four',
        'description',
        'notes',
        'status',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = (new ReceiptService(new self))->generateReceiptNumber($receipt->team_id);
            }
        });
    }

    // Relationships
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('receipt_date', [$from, $to]);
    }

    // Accessor
    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'SAR' ? '﷼' : ($this->currency === 'USD' ? '$' : $this->currency);
        return $symbol . ' ' . number_format($this->amount, 2);
    }

    // Method
    public function cancel($userId, $reason)
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
        ]);
    }

    public function canBeEdited()
    {
        return $this->status === 'draft';
    }
}

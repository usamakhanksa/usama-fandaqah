<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\MultiTenant;
use App\Services\Finance\PaymentService;

class Payment extends Model
{
    use HasFactory, SoftDeletes, MultiTenant;

    protected $fillable = [
        'team_id',
        'reservation_id',
        'folio_id',
        'guest_id',
        'company_id',
        'invoice_id',
        'receipt_id',
        'payment_number',
        'payment_date',
        'amount',
        'original_amount',
        'currency',
        'exchange_rate',
        'payment_method',
        'payment_type',
        'reference_number',
        'bank_name',
        'cheque_number',
        'card_last_four',
        'card_authorization',
        'description',
        'notes',
        'status',
        'is_advance',
        'is_deposit',
        'cashier_shift_id',
        'confirmed_at',
        'confirmed_by',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'transaction_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
        'is_advance' => 'boolean',
        'is_deposit' => 'boolean',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_number)) {
                $payment->payment_number = (new PaymentService())->generatePaymentNumber($payment->team_id);
            }
            
            if (empty($payment->created_by) && auth()->check()) {
                $payment->created_by = auth()->id();
            }
        });
        
        static::updating(function ($payment) {
            if (auth()->check()) {
                $payment->updated_by = auth()->id();
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

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receipt()
    {
        return $this->belongsTo(Receipt::class);
    }

    public function cashierShift()
    {
        return $this->belongsTo(CashierShift::class, 'cashier_shift_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Scopes
    public function scopeForTeam($query, $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('payment_date', [$from, $to]);
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('payment_type', $type);
    }

    public function scopeAdvances($query)
    {
        return $query->where('is_advance', true);
    }

    public function scopeDeposits($query)
    {
        return $query->where('is_deposit', true);
    }

    // Methods
    public function confirm($userId)
    {
        return $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'confirmed_by' => $userId,
        ]);
    }

    public function cancel($userId, $reason)
    {
        return $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
        ]);
    }

    public function reverse($userId, $reason)
    {
        return $this->update([
            'status' => 'reversed',
            'cancelled_at' => now(), // Using cancelled_at for reversal too or we could add reversed_at
            'cancelled_by' => $userId,
            'cancellation_reason' => $reason,
        ]);
    }

    public function isEditable()
    {
        return $this->status === 'pending';
    }
}

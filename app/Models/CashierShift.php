<?php

namespace App\Models;

use App\User;
use App\Models\Team;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashierShift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'team_id',
        'user_id',
        'shift_number',
        'opened_at',
        'closed_at',
        'opening_balance',
        'expected_closing_balance',
        'actual_closing_balance',
        'variance',
        'variance_reason',
        'total_cash_received',
        'total_cash_paid',
        'total_card_received',
        'total_other_received',
        'total_transactions',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'expected_closing_balance' => 'decimal:2',
        'actual_closing_balance' => 'decimal:2',
        'variance' => 'decimal:2',
        'total_cash_received' => 'decimal:2',
        'total_cash_paid' => 'decimal:2',
        'total_card_received' => 'decimal:2',
        'total_other_received' => 'decimal:2',
        'total_transactions' => 'integer',
    ];

    public const STATUS_OPEN = 'open';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_PENDING = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    // Relationships
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(Transaction::class, 'cashier_shift_transactions', 'cashier_shift_id', 'transaction_id')
            ->withTimestamps();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('opened_at', $date);
    }

    // Methods
    public function calculateExpected()
    {
        // This would typically sum up all transactions linked to this shift
        // For simplicity, we assume the totals are updated as transactions occur,
        // but here we can implement a recalculation logic.
        $totals = $this->transactions()
            ->selectRaw('SUM(CASE WHEN transaction_flag = "payment" THEN amount ELSE 0 END) as total_received')
            ->selectRaw('SUM(CASE WHEN transaction_flag = "refund" THEN amount ELSE 0 END) as total_paid')
            ->first();

        $this->expected_closing_balance = $this->opening_balance + ($totals->total_received ?? 0) - ($totals->total_paid ?? 0);
        return $this->expected_closing_balance;
    }

    public function close($actualBalance, $varianceReason = null)
    {
        $this->calculateExpected();
        $this->actual_closing_balance = $actualBalance;
        $this->variance = $this->actual_closing_balance - $this->expected_closing_balance;
        $this->variance_reason = $varianceReason;
        $this->closed_at = now();
        $this->status = self::STATUS_PENDING;
        $this->save();
    }

    public function approve($userId, $notes = null)
    {
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->approval_notes = $notes;
        $this->status = self::STATUS_APPROVED;
        $this->save();
    }

    public function reject($userId, $reason)
    {
        $this->rejected_by = $userId;
        $this->rejected_at = now();
        $this->rejection_reason = $reason;
        $this->status = self::STATUS_REJECTED;
        $this->save();
    }
}

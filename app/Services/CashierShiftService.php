<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashierShiftService
{
    /**
     * Open a new shift for a cashier.
     */
    public function openShift(User $user, float $openingBalance, ?string $notes = null): CashierShift
    {
        // Prevent cashier from opening multiple active shifts
        if ($this->getActiveShift($user)) {
            throw ValidationException::withMessages([
                'shift' => __('You already have an active shift open.'),
            ]);
        }

        return CashierShift::create([
            'team_id' => $user->team_id,
            'user_id' => $user->id,
            'shift_date' => now()->toDateString(),
            'opened_at' => now(),
            'opening_balance' => $openingBalance,
            'notes' => $notes,
            'status' => CashierShift::STATUS_OPEN,
        ]);
    }

    /**
     * Close an active shift.
     */
    public function closeShift(CashierShift $shift, float $closingBalance, ?string $notes = null): CashierShift
    {
        if ($shift->status !== CashierShift::STATUS_OPEN) {
            throw ValidationException::withMessages([
                'shift' => __('This shift is already closed.'),
            ]);
        }

        $systemBalance = $this->calculateSystemBalance($shift);
        $variance = $closingBalance - $systemBalance;

        $shift->update([
            'closed_at' => now(),
            'closing_balance' => $closingBalance,
            'system_balance' => $systemBalance,
            'variance' => $variance,
            'notes' => $notes ? ($shift->notes ? $shift->notes . "\n" . $notes : $notes) : $shift->notes,
            'status' => CashierShift::STATUS_CLOSED,
        ]);

        return $shift;
    }

    /**
     * Approve a closed shift.
     */
    public function approveShift(CashierShift $shift, User $approver, ?string $notes = null): CashierShift
    {
        if ($shift->status !== CashierShift::STATUS_CLOSED) {
            throw ValidationException::withMessages([
                'shift' => __('Only closed shifts can be approved.'),
            ]);
        }

        // Require manager approval for variance above threshold
        $threshold = config('pms.cashier_shift_variance_threshold', 100); // Default to 100
        if (abs($shift->variance) > $threshold && !$approver->hasPermissionTo('cashier.approve_shift')) {
             throw ValidationException::withMessages([
                'shift' => __('Variance is above threshold. Manager approval required.'),
            ]);
        }

        $shift->update([
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'status' => CashierShift::STATUS_APPROVED,
            'notes' => $notes ? ($shift->notes ? $shift->notes . "\n" . $notes : $notes) : $shift->notes,
        ]);

        return $shift;
    }

    /**
     * Get the currently active shift for a user.
     */
    public function getActiveShift(User $user): ?CashierShift
    {
        return CashierShift::where('user_id', $user->id)
            ->where('status', CashierShift::STATUS_OPEN)
            ->first();
    }

    /**
     * Calculate system balance based on transactions.
     */
    public function calculateSystemBalance(CashierShift $shift): float
    {
        // System balance = Opening Balance + Total Deposits - Total Withdrawals
        $deposits = Transaction::where('cashier_shift_id', $shift->id)
            ->where('type', Transaction::TYPE_DEPOSIT)
            ->sum('amount');

        $withdrawals = Transaction::where('cashier_shift_id', $shift->id)
            ->where('type', Transaction::TYPE_WITHDRAW)
            ->sum('amount');

        return $shift->opening_balance + $deposits - $withdrawals;
    }

    /**
     * Link unlinked transactions to the current shift for a user.
     * This is useful if transactions were made while a shift was open but not linked yet.
     * Or we can link them automatically during transaction creation (recommended).
     */
    public function linkPendingTransactions(CashierShift $shift)
    {
        Transaction::where('created_by', $shift->user_id)
            ->whereNull('cashier_shift_id')
            ->whereBetween('created_at', [$shift->opened_at, now()])
            ->update(['cashier_shift_id' => $shift->id]);
    }
}

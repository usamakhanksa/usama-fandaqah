<?php

namespace App\Services\Finance;

use App\Models\CashierShift;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashierShiftService
{
    public function openShift($userId, $teamId, $openingBalance, $notes = null)
    {
        // Check if user already has an open shift
        $existingShift = CashierShift::where('user_id', $userId)
            ->where('team_id', $teamId)
            ->where('status', CashierShift::STATUS_OPEN)
            ->first();

        if ($existingShift) {
            throw new \Exception("Cashier already has an open shift.");
        }

        return CashierShift::create([
            'team_id' => $teamId,
            'user_id' => $userId,
            'shift_number' => 'SH-' . strtoupper(uniqid()),
            'opened_at' => now(),
            'opening_balance' => $openingBalance,
            'status' => CashierShift::STATUS_OPEN,
            'notes' => $notes,
        ]);
    }

    public function closeShift(CashierShift $shift, $actualBalance, $varianceReason = null)
    {
        if ($shift->status !== CashierShift::STATUS_OPEN) {
            throw new \Exception("Only open shifts can be closed.");
        }

        $shift->close($actualBalance, $varianceReason);
        return $shift;
    }

    public function approveShift(CashierShift $shift, $userId, $notes = null)
    {
        if ($shift->status !== CashierShift::STATUS_PENDING) {
            throw new \Exception("Only pending shifts can be approved.");
        }

        $shift->approve($userId, $notes);
        return $shift;
    }

    public function rejectShift(CashierShift $shift, $userId, $reason)
    {
        if ($shift->status !== CashierShift::STATUS_PENDING) {
            throw new \Exception("Only pending shifts can be rejected.");
        }

        $shift->reject($userId, $reason);
        return $shift;
    }

    public function getCurrentShift($userId, $teamId)
    {
        return CashierShift::where('user_id', $userId)
            ->where('team_id', $teamId)
            ->where('status', CashierShift::STATUS_OPEN)
            ->first();
    }

    public function getShiftReport(CashierShift $shift)
    {
        $transactions = $shift->transactions;
        
        $breakdown = [
            'cash' => 0,
            'card' => 0,
            'other' => 0,
            'total_received' => 0,
            'total_paid' => 0,
            'transaction_count' => $transactions->count(),
        ];

        // This assumes transactions have some way to identify payment method
        // If transactions are linked to payments, we'd look there.
        // For now, let's assume a generic breakdown logic or placeholder.
        
        return [
            'shift' => $shift,
            'breakdown' => $breakdown,
            'transactions' => $transactions
        ];
    }
}

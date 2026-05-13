<?php

namespace App\Services\Finance;

use App\Models\Payment;
use App\Models\Transaction;
use App\Models\TeamCounter;
use App\Models\CashierShift;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService
{
    /**
     * Process a payment and create a transaction row.
     */
    public function processPayment(Payment $payment)
    {
        return DB::transaction(function () use ($payment) {
            // Create Transaction row
            $transaction = Transaction::create([
                'team_id' => $payment->team_id,
                'payable_type' => $payment->reservation_id ? \App\Models\Reservation::class : \App\Models\Guest::class,
                'payable_id' => $payment->reservation_id ?? $payment->guest_id,
                'type' => in_array($payment->payment_type, ['refund', 'adjustment']) ? 'withdraw' : 'deposit',
                'amount' => $payment->amount,
                'confirmed' => true,
                'description' => $payment->description ?? "Payment {$payment->payment_number}",
                'cashier_shift_id' => $payment->cashier_shift_id,
                'created_by' => $payment->created_by,
                'uuid' => \Illuminate\Support\Str::uuid(),
                'meta' => [
                    'payment_id' => $payment->id,
                    'payment_number' => $payment->payment_number,
                    'payment_method' => $payment->payment_method,
                    'payment_type' => $payment->payment_type,
                    'date' => $payment->payment_date->format('Y-m-d'),
                ]
            ]);

            $payment->update(['transaction_id' => $transaction->id]);

            return $payment;
        });
    }

    /**
     * Reverse a payment and create a reversal transaction.
     */
    public function reversePayment(Payment $payment, $userId, $reason)
    {
        return DB::transaction(function () use ($payment, $userId, $reason) {
            $payment->reverse($userId, $reason);

            // Create reversal transaction
            Transaction::create([
                'team_id' => $payment->team_id,
                'payable_type' => $payment->reservation_id ? \App\Models\Reservation::class : \App\Models\Guest::class,
                'payable_id' => $payment->reservation_id ?? $payment->guest_id,
                'type' => in_array($payment->payment_type, ['refund', 'adjustment']) ? 'deposit' : 'withdraw',
                'amount' => $payment->amount,
                'confirmed' => true,
                'description' => "Reversal of {$payment->payment_number}: {$reason}",
                'cashier_shift_id' => $payment->cashier_shift_id,
                'created_by' => $userId,
                'uuid' => \Illuminate\Support\Str::uuid(),
                'meta' => [
                    'reversal_of_payment_id' => $payment->id,
                    'reason' => $reason,
                    'date' => now()->format('Y-m-d'),
                ]
            ]);

            return $payment;
        });
    }

    /**
     * Generate a unique payment number thread-safely.
     */
    public function generatePaymentNumber($teamId): string
    {
        $yearMonth = now()->format('Ym');
        $prefix = "PAY-{$yearMonth}-";

        return DB::transaction(function () use ($teamId, $prefix) {
            $counter = TeamCounter::lockForUpdate()->firstOrCreate(
                [
                    'team_id' => $teamId,
                    'type' => 'payment',
                    'prefix' => $prefix,
                ],
                ['value' => 0]
            );

            $counter->increment('value');
            
            return $prefix . str_pad($counter->value, 4, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Get daily payment summary by method.
     */
    public function getPaymentSummary($teamId, $date)
    {
        return Payment::forTeam($teamId)
            ->whereDate('payment_date', $date)
            ->confirmed()
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Get outstanding payments for a reservation.
     */
    public function getOutstandingPayments($reservationId)
    {
        // This is a placeholder logic, usually involves calculating reservation total - payments
        // For now, return payments linked to reservation
        return Payment::where('reservation_id', $reservationId)->get();
    }

    /**
     * Validate payment business rules.
     */
    public function validatePayment(array $data)
    {
        if (($data['amount'] ?? 0) <= 0) {
            throw new \Exception('Payment amount must be greater than zero.');
        }

        $validMethods = ['cash', 'visa', 'mastercard', 'mada', 'apple_pay', 'bank_transfer', 'cheque', 'online', 'other'];
        if (!in_array($data['payment_method'] ?? '', $validMethods)) {
            throw new \Exception('Invalid payment method.');
        }

        if (!empty($data['cashier_shift_id'])) {
            $shift = CashierShift::find($data['cashier_shift_id']);
            if (!$shift || $shift->status !== 'open') {
                throw new \Exception('The selected cashier shift is not open.');
            }
        }
    }

    /**
     * Get payment statistics for a date range.
     */
    public function getPaymentStats($teamId, $from, $to)
    {
        $payments = Payment::forTeam($teamId)
            ->whereBetween('payment_date', [$from, $to])
            ->confirmed()
            ->get();

        $byMethod = $payments->groupBy('payment_method')->map(fn($group) => $group->sum('amount'));
        $byType = $payments->groupBy('payment_type')->map(fn($group) => $group->sum('amount'));
        
        $dailyBreakdown = $payments->groupBy(fn($p) => $p->payment_date->format('Y-m-d'))
            ->map(fn($group) => $group->sum('amount'));

        return [
            'total_amount' => $payments->sum('amount'),
            'by_method' => $byMethod,
            'by_type' => $byType,
            'daily_breakdown' => $dailyBreakdown,
        ];
    }
}

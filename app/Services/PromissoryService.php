<?php

namespace App\Services;

use App\Models\Promissory;
use App\Models\PromissoryPaymentLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PromissoryService
{
    /**
     * Apply a payment to a promissory.
     */
    public function applyPayment(Promissory $promissory, $amount, $paymentType, $transactionId = null, $notes = null)
    {
        return DB::transaction(function () use ($promissory, $amount, $paymentType, $transactionId, $notes) {
            $promissory->increment('collected_amount', $amount);
            
            return PromissoryPaymentLog::create([
                'promissory_id' => $promissory->id,
                'team_id' => $promissory->team_id,
                'transaction_id' => $transactionId,
                'amount_applied' => $amount,
                'payment_type' => $paymentType,
                'applied_at' => now(),
                'applied_by' => Auth::id(),
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Reverse a previously applied payment.
     */
    public function reversePayment(PromissoryPaymentLog $log)
    {
        return DB::transaction(function () use ($log) {
            if ($log->is_reversed) {
                throw new \Exception('Payment is already reversed.');
            }

            $log->promissory->decrement('collected_amount', $log->amount_applied);
            $log->update(['is_reversed' => true]);

            return $log;
        });
    }
}

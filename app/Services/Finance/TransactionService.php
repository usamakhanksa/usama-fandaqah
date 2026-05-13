<?php

namespace App\Services\Finance;

use App\Models\Transaction;
use App\Models\Receipt;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class TransactionService
{
    /**
     * Create a transaction from a receipt
     */
    public static function createFromReceipt(Receipt $receipt)
    {
        return Transaction::create([
            'team_id' => $receipt->team_id,
            'payable_type' => Receipt::class,
            'payable_id' => $receipt->id,
            'type' => 'deposit',
            'amount' => $receipt->amount,
            'amount_without_tax' => $receipt->amount,
            'currency' => $receipt->currency,
            'exchange_rate' => $receipt->exchange_rate,
            'number' => $receipt->receipt_number,
            'description' => $receipt->description ?? "Receipt {$receipt->receipt_number}",
            'confirmed' => $receipt->status === 'confirmed',
            'created_by' => $receipt->created_by ?? Auth::id(),
            'business_date' => $receipt->receipt_date,
            'meta' => [
                'payment_method' => $receipt->payment_method,
                'reference_number' => $receipt->reference_number,
                'bank_name' => $receipt->bank_name,
                'cheque_number' => $receipt->cheque_number,
                'card_last_four' => $receipt->card_last_four,
                'category' => 'receipt',
            ]
        ]);
    }

    /**
     * Reverse a transaction for a receipt
     */
    public static function reverseReceipt(Receipt $receipt, $userId, $reason)
    {
        $transaction = Transaction::where('payable_type', Receipt::class)
            ->where('payable_id', $receipt->id)
            ->where('type', 'deposit')
            ->first();

        if ($transaction) {
            return Transaction::create([
                'team_id' => $receipt->team_id,
                'payable_type' => Receipt::class,
                'payable_id' => $receipt->id,
                'type' => 'withdraw',
                'amount' => -$receipt->amount,
                'amount_without_tax' => -$receipt->amount,
                'currency' => $receipt->currency,
                'exchange_rate' => $receipt->exchange_rate,
                'number' => "REV-{$receipt->receipt_number}",
                'description' => "Reversal: {$reason}",
                'confirmed' => true,
                'created_by' => $userId,
                'business_date' => now()->toDateString(),
                'correction_of_transaction_id' => $transaction->id,
                'correction_reason' => $reason,
                'meta' => [
                    'category' => 'reversal',
                    'original_receipt' => $receipt->receipt_number,
                ]
            ]);
        }
        
        return null;
    }

    /**
     * Placeholder for getTransactions (needed by controller)
     */
    public function getTransactions($team, array $filters)
    {
        $query = Transaction::where('team_id', $team->id);
        
        if (!empty($filters['search'])) {
            $query->where('number', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
        }
        
        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 25);
    }
}

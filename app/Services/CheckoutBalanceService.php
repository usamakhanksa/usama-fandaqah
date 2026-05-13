<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\FinancialRecord;
use App\Models\CheckoutBalanceTransfer;
use App\Models\Promissory;
use App\Models\CreditNote;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CheckoutBalanceService
{
    /**
     * Calculate current balance for a reservation.
     */
    public function getBalance(Reservation $reservation)
    {
        $booking = $reservation->booking;
        if (!$booking) {
            return 0;
        }

        $records = FinancialRecord::where('booking_id', $booking->id)->get();
        
        $charges = $records->where('type', 'charge')->sum('amount');
        $payments = $records->whereIn('type', ['payment', 'credit', 'deposit'])->sum('amount');
        
        return round($charges - $payments, 2);
    }

    /**
     * Resolve a non-zero balance during checkout.
     */
    public function resolveBalance(Reservation $reservation, array $data)
    {
        return DB::transaction(function () use ($reservation, $data) {
            $balance = $this->getBalance($reservation);
            
            if ($balance == 0) {
                return null;
            }

            $resolutionType = $data['resolution_type'];
            $amountResolved = $data['amount'] ?? abs($balance);
            $referenceId = null;
            $referenceType = null;

            switch ($resolutionType) {
                case 'collect_now':
                    $record = FinancialRecord::create([
                        'team_id' => $reservation->team_id,
                        'booking_id' => $reservation->booking?->id,
                        'label' => 'Balance Collection - Checkout',
                        'amount' => $amountResolved,
                        'type' => 'payment'
                    ]);
                    $referenceId = $record->id;
                    $referenceType = FinancialRecord::class;
                    break;

                case 'signed_promissory':
                case 'unsigned_promissory':
                case 'waived_promissory':
                    $status = $resolutionType === 'signed_promissory' ? 'signed' : ($resolutionType === 'unsigned_promissory' ? 'unsigned' : 'waived');
                    $promissory = Promissory::create([
                        'team_id' => $reservation->team_id,
                        'reservation_id' => $reservation->id,
                        'user_id' => Auth::id(),
                        'total_amount' => $amountResolved,
                        'status' => 'pending',
                        'due_date' => $data['due_date'] ?? now()->addDays(30),
                        'signature_status' => $status,
                        'unsigned_reason' => $data['unsigned_reason'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]);
                    
                    // Also post a credit record to balance the reservation's folio
                    FinancialRecord::create([
                        'team_id' => $reservation->team_id,
                        'booking_id' => $reservation->booking?->id,
                        'label' => 'Transfer to Promissory #' . $promissory->id,
                        'amount' => $amountResolved,
                        'type' => 'credit'
                    ]);
                    
                    $referenceId = $promissory->id;
                    $referenceType = Promissory::class;
                    break;

                case 'corporate_transfer':
                    $record = FinancialRecord::create([
                        'team_id' => $reservation->team_id,
                        'booking_id' => $reservation->booking?->id,
                        'label' => 'Transfer to Corporate Ledger',
                        'amount' => $amountResolved,
                        'type' => 'credit'
                    ]);
                    $referenceId = $record->id;
                    $referenceType = FinancialRecord::class;
                    break;

                case 'refund_now':
                    $record = FinancialRecord::create([
                        'team_id' => $reservation->team_id,
                        'booking_id' => $reservation->booking?->id,
                        'label' => 'Balance Refund - Checkout',
                        'amount' => $amountResolved,
                        'type' => 'charge' // Post a charge to zero out negative balance
                    ]);
                    $referenceId = $record->id;
                    $referenceType = FinancialRecord::class;
                    break;

                case 'credit_note':
                    $creditNote = CreditNote::create([
                        'team_id' => $reservation->team_id,
                        'reservation_id' => $reservation->id,
                        'amount' => $amountResolved,
                        'status' => 'pending',
                        'employee_id' => Auth::id(),
                        'payload' => $data,
                    ]);
                    
                    FinancialRecord::create([
                        'team_id' => $reservation->team_id,
                        'booking_id' => $reservation->booking?->id,
                        'label' => 'Transfer to Credit Note #' . $creditNote->id,
                        'amount' => $amountResolved,
                        'type' => 'charge'
                    ]);
                    
                    $referenceId = $creditNote->id;
                    $referenceType = CreditNote::class;
                    break;
            }

            $newBalance = $this->getBalance($reservation);

            return CheckoutBalanceTransfer::create([
                'team_id' => $reservation->team_id,
                'reservation_id' => $reservation->id,
                'balance_before' => $balance,
                'amount_resolved' => $amountResolved,
                'balance_after' => $newBalance,
                'resolution_type' => $resolutionType,
                'reference_id' => $referenceId,
                'reference_type' => $referenceType,
                'user_id' => Auth::id(),
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}

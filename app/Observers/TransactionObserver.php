<?php

namespace App\Observers;

use App\Team;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\Events\TransactionCreated;
use App\Services\AuditEnforcementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionObserver
{
    protected $enforcement;

    public function __construct(AuditEnforcementService $enforcement)
    {
        $this->enforcement = $enforcement;
    }

    /**
     * Handle the transaction "creating" event.
     */
    public function creating(Transaction $transaction)
    {
        // Block creating transactions for closed business dates unless permitted
        $teamId = $transaction->team_id ?? Auth::user()->current_team_id ?? null;
        $date = $transaction->meta['date'] ?? null;
        
        if ($teamId && $date) {
            if ($this->enforcement->isDateClosed($teamId, $date) && !$this->enforcement->canBackdate()) {
                abort(403, 'Cannot create transactions for a closed business date.');
            }
        }

        // Link to active cashier shift
        if (Auth::check() && empty($transaction->cashier_shift_id)) {
            $activeShift = \App\Models\CashierShift::where('user_id', Auth::id())
                ->where('status', \App\Models\CashierShift::STATUS_OPEN)
                ->first();
            
            if ($activeShift) {
                $transaction->cashier_shift_id = $activeShift->id;
            }
        }

        if (TeamCounter::get()->count())
            $counter = TeamCounter::first();
        else
            $counter = TeamCounter::create();

        if ($transaction->type == 'withdraw' &&  $transaction->is_public ) {
                $transaction->number = $counter->payment_num;
        }

        if ($transaction->type == 'deposit' && $transaction->is_public) {
            $transaction->number = $counter->receipt_num;
            if($transaction->meta['category'] == 'reservation-promissory'){
                $transaction->is_promissory = 1;
            }
        }

        if(!$transaction->is_public){
            $transaction->disableLogging();
        }
    }

    /**
     * Handle the transaction "updating" event.
     */
    public function updating(Transaction $transaction)
    {
        // Rule: Block updating frozen transactions
        if ($transaction->getOriginal('is_freezed')) {
            abort(403, 'This transaction is frozen by Night Audit and cannot be modified.');
        }

        if(!$transaction->is_public){
            $transaction->disableLogging();
        }
    }

    /**
     * Handle the transaction "deleting" event.
     */
    public function deleting(Transaction $transaction)
    {
        // Rule: Block deleting frozen transactions
        if ($transaction->is_freezed) {
            abort(403, 'This transaction is frozen by Night Audit and cannot be deleted.');
        }
    }

    /**
     * Handle the transaction "created" event.
     */
    public function created(Transaction $transaction)
    {
        if ($transaction->payable_type == Reservation::class)
            event(new TransactionCreated($transaction));
        $this->incrementCounter($transaction);

        if ($transaction->payable_type === Reservation::class) {
            $reservation = DB::table('reservations')->where('id', $transaction->payable_id)->first();
            if ($reservation) {
                $transaction->team_id = $reservation->team_id;
            }
        } elseif ($transaction->payable_type === Team::class) {
            $transaction->team_id = $transaction->payable_id;
        }
        $transaction->save();
    }

    public function incrementCounter($transaction)
    {
        $counter = TeamCounter::first();

        if ($transaction->type == 'withdraw' && $transaction->is_public) {
                $counter->last_payment_number = $counter->payment_num;
        }

        if ($transaction->type == 'deposit' && $transaction->is_public) {
            $counter->last_receipt_number = $counter->receipt_num;
        }

        return $counter->save();
    }
}

<?php

namespace App\Observers;

use App\Team;
use App\Reservation;
use App\TeamCounter;
use App\Transaction;
use App\Events\TransactionCreated;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{

    /**
     * Handle the transaction "creating" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function creating(Transaction $transaction)
    {
        if (TeamCounter::get()->count())
            $counter = TeamCounter::first();
        else
            $counter = TeamCounter::create();

        // if ($transaction->meta['category'] == 'service') {
        //     $transaction->number = $counter->service_num;
        // } else {



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
        // }
    }

    /**
     * Handle the transaction "created" event.
     *
     * @param Transaction $transaction
     * @return void
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

    /**
     * Handle increment Counter.
     *
     * @param Transaction $transaction
     * @return boolean
     */
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

    public function updating(Transaction $transaction)
    {
        if(!$transaction->is_public){
            $transaction->disableLogging();
        }
    }

    /**
     * Handle the transaction "updated" event.
     *
     * @param Transaction $transaction
     * @return void
     */

    public function updated(Transaction $transaction)
    {

    }

    /**
     * Handle the transaction "deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function deleted(Transaction $transaction)
    {

    }

    /**
     * Handle the transaction "restored" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function restored(Transaction $transaction)
    {

    }

    /**
     * Handle the transaction "force deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function forceDeleted(Transaction $transaction)
    {

    }
}

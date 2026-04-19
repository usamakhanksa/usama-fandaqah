<?php

namespace App\Events;

use App\Handlers\Settings;
use App\Jobs\SCTH\ExpenseBooking;
use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldQueue
{
    use  SerializesModels, Queueable;

    public $transaction;
    public $reservation;

    /**
     * Create a new event instance.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
        $this->reservation = $transaction->reservation;
        $this->checkSCTH();
    }

    protected function checkSCTH()
    {

        $credentials = Settings::checkIntegration('SCTH', $this->transaction->payable()->value('team_id'));

        if ($credentials && !is_null($this->reservation->scth_reference) && $this->transaction->type == 'deposit' && $this->transaction->is_public == 1 && $this->reservation->checked_out) {
            ExpenseBooking::dispatch($this->reservation, $this->transaction, $credentials);
        }
    }

}

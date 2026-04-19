<?php

namespace App\Jobs\SCTH;

use App\Integration\SCTH;
use App\Reservation;
use App\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpenseBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $reservation;
    protected $transaction;
    protected $credentials;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, Transaction $transaction, $credentials)
    {
        $this->reservation = $reservation;
        $this->transaction = $transaction;
        $this->credentials = $credentials;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $expenseBooking = SCTH::expense($this->reservation, $this->transaction, $this->credentials);
        // Log::info($expenseBooking);
    }
}

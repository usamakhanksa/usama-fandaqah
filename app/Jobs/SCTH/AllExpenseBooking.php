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

class AllExpenseBooking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $reservation;
    protected $credentials;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, $credentials)
    {
        $this->reservation = $reservation;
        $this->credentials = $credentials;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $allExpenseBooking = SCTH::AllExpense($this->reservation, $this->credentials);
        // Log::info($allExpenseBooking);
    }
}

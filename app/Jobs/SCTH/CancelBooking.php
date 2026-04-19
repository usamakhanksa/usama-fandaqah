<?php

namespace App\Jobs\SCTH;

use App\Handlers\Settings;
use App\Integration\SCTH;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class CancelBooking implements ShouldQueue
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
        $cancelBooking = SCTH::cancel($this->reservation, $this->credentials);
        // Log::info($cancelBooking);
    }
}

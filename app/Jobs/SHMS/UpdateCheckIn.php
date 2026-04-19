<?php

namespace App\Jobs\SHMS;

use App\Integration\SHMS;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class UpdateCheckIn implements ShouldQueue
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scth = SHMS::update($this->reservation, $this->credentials);
    }

}

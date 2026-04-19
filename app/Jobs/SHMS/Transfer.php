<?php

namespace App\Jobs\SHMS;

use App\Integration\SHMS;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Transfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $reservation;
    protected $from;
    protected $to;
    protected $credentials;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, $credentials, $from, $to)
    {
        $this->reservation = $reservation;
        $this->credentials = $credentials;
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scth = SHMS::transfer($this->reservation, $this->credentials,  $this->from, $this->to);
    }
}

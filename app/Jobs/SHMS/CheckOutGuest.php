<?php

namespace App\Jobs\SHMS;

use App\Integration\SHMS;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckOutGuest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $guest;
    protected $credentials;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($guest, $credentials)
    {
        $this->guest = $guest;
        $this->credentials = $credentials;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scth = SHMS::checkOutGuest($this->guest, $this->credentials);
    }
}

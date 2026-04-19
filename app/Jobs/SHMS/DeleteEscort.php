<?php

namespace App\Jobs\SHMS;

use App\Guest;
use App\Integration\SHMS;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DeleteEscort implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    protected $reservation_id;
    protected $escort;
    protected $credentials;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($reservation_id,Guest $escort, $credentials)
    {
        $this->reservation_id = $reservation_id;
        $this->escort = $escort;
        $this->credentials = $credentials;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        SHMS::deleteEscort($this->reservation_id,$this->escort, $this->credentials);
    }
}

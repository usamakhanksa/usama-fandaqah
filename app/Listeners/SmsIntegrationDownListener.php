<?php


namespace App\Listeners;

use App\Jobs\SmsIntegrationDown;
use App\Reservation;

class SmsIntegrationDownListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ReservationCreated  $event
     * @return void
     */
    public function handle($event)
    {
        SmsIntegrationDown::dispatch($event);
    }
}

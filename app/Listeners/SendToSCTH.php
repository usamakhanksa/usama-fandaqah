<?php


namespace App\Listeners;


use App\Reservation;

class SendToSCTH
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\ReservationCreated  $event
     * @return void
     */
    public function handle(Reservation $event)
    {
        // Access the order using $event->order...
    }
}

<?php

namespace App\Listeners;
use App\Integration\SHMS;
use App\Handlers\Settings;

class SHMSCheckInListener
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
     * @param  ReservationCheckIn  $event
     * @return void
     */
    public function handle($event)
    {
        $credentials = Settings::checkIntegration('SHMS', $event->reservation->team_id);
        if ($credentials) {
            SHMS::checkIn($event->reservation, $credentials);
        }
    }

}

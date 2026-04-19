<?php

namespace App\Listeners;

use App\Handlers\Settings;
use App\Jobs\SCTH\UpdateBooking;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SCTHCheckInListener
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
        $this->checkSCTH($event->reservation);
    }

    protected function checkSCTH($reservation)
    {
        $credentials = Settings::checkIntegration('SCTH', $reservation->unit->team_id);

        if ($credentials and !is_null($reservation->scth_reference)) {
            UpdateBooking::dispatch($reservation, $credentials, true);
        }
    }
}

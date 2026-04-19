<?php

namespace App\Events;

use App\Reservation;
use App\Integration\SHMS;
use App\Handlers\Settings;
use Illuminate\Foundation\Bus\Dispatchable;

class ShomosReservationUpdated 
{

    public $reservation;

    /**
     * Create a new event instance.
     *
     * @param \App\Reservation $reservation
     * @param bool $checkInOrOut
     * @return void
     */
    public function __construct(Reservation $reservation, bool $checkInOrOut = false)
    {
        $this->reservation = $reservation;
        $this->checkSHMS();
    }

    protected function checkSHMS()
    {
        $credentials = Settings::checkIntegration('SHMS', $this->reservation->unit->team_id);
        if ($credentials && $this->reservation->shomoos_id && is_null($this->reservation->checked_out) ) {
            SHMS::update($this->reservation, $credentials);
        }

        // in case reservation checked in from fandaqah but didn't create in SHMS it self , so we need to send it back and get Identity from SHMS
        if($credentials && is_null($this->reservation->shomoos_id) && $this->reservation->checked_in){
            SHMS::checkIn($this->reservation, $credentials);
        }
    }
}

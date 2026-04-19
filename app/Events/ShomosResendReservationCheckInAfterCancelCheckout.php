<?php

namespace App\Events;

use App\Reservation;
use App\Integration\SHMS;
use App\Handlers\Settings;

class ShomosResendReservationCheckInAfterCancelCheckout 
{
    /**
     * @var Reservation
     */
    public $reservation;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->checkSHMS();
    }


    protected function checkSHMS()
    {
        $credentials = Settings::checkIntegration('SHMS', $this->reservation->team_id);
        if($credentials  && $this->reservation->checked_in && !$this->reservation->checked_out){
            SHMS::checkInAfterCancelCheckout($this->reservation, $credentials);
        }
    }
}

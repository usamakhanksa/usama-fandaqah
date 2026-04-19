<?php

namespace App\Listeners;

use App\Handlers\Settings;
use App\Jobs\SCTH\CreateBooking;
use App\Jobs\SCTH\UpdateBooking;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationUpdatedListener
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        // if the contract wasn't reopened after checkout
        if(!$event->reservation->occ){
            $this->checkSCTH($event->reservation);
        }
    }

    protected function checkSCTH($reservation)
    {
        $credentials = Settings::checkIntegration('SCTH', $reservation->unit->team_id);

        if ($credentials) {
            /**
             * If no scth reference found , please try to create booking again in scth 
             */
            if(!is_null($reservation->scth_reference)){
                UpdateBooking::dispatch($reservation, $credentials, false);
            }else{ 
                CreateBooking::dispatch($reservation, $credentials);
            }
        }
    }
}

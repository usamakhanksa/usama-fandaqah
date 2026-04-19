<?php

namespace App\Listeners;

use App\Broadcasting\SMSChannel;
use App\Events\ReservationCheckout;
use App\Events\ReservationTransferred;
use App\Handlers\Settings;
use App\Integration;
use App\Jobs\SHMS\CheckOut;
use App\Jobs\SHMS\Transfer;
use App\Unit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ShomosUpdateReservationTransferred implements ShouldQueue
{
    use InteractsWithQueue;

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
     * @param  ReservationCheckout  $event
     * @return void
     */
    public function handle(ReservationTransferred $event)
    {
        $reservation = $event->reservation;
        $credentials = Settings::checkIntegration('SHMS', $reservation->team_id);

        if ($credentials) {
            if(isset($reservation->checked_in)){
                Transfer::dispatch($reservation, $credentials, $event->old_unit_number, $event->new_unit_number);
            }
        }
    }
}

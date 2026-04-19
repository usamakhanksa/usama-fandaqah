<?php

namespace App\Listeners;

use App\Events\ShomosActionCreated;
use App\Handlers\Settings;
use App\Jobs\SCTH\UpdateBooking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UpdateReservationShomosStatus implements ShouldQueue
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
     * @param  object  $event
     * @return void
     */
    public function handle(ShomosActionCreated $event)
    {
 
        $reservation = $event->reservation;
        $reservation->shomos_status = $event->shomos_status;
        $reservation->save();
    }

}

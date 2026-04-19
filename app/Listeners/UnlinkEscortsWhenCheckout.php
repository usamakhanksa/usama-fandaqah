<?php

namespace App\Listeners;

use App\Guest;
use App\Events\ReservationCheckout;
use Illuminate\Queue\InteractsWithQueue;

class UnlinkEscortsWhenCheckout
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
    public function handle(ReservationCheckout $event)
    {
        if ($event->reservation && $event->reservation->checked_out) {
            $fetch_guests = Guest::where('team_id', $event->reservation->team_id)->where('reservation_id', $event->reservation->id)->get();
            if (count($fetch_guests)) {
                foreach ($fetch_guests as $guest) {
                    $guest->reservation_id = null;
                    $guest->save();
                }
            }
        }
    }
}

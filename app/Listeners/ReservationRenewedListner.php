<?php

namespace App\Listeners;

use App\Events\ReservationRenewed;
use App\Reservation;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationRenewedListner implements ShouldQueue
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
     * @param  ReservationRenewed  $event
     * @return void
     */
    public function handle(ReservationRenewed $event)
    {

        $reservation = Reservation::withoutGlobalScopes()->find($event->id);

        $reservation->forceWithdrawFloat($event->amount, [
                'category' => 'update_reservation',
                'statement' => 'auto renew reservation',
            ], true, false);

        $reservation->wallet->refreshBalance();

    }
}

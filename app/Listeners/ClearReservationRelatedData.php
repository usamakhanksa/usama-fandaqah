<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClearReservationRelatedData
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
        $event->reservation->transactionsToDelete()->delete();
        $event->reservation->invoices()->delete();
        $event->reservation->guests()->delete();
        $event->reservation->promissory()->delete();

    }
}

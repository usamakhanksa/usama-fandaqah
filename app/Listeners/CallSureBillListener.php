<?php

namespace App\Listeners;

use App\Reservation;
use GuzzleHttp\Client;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CallSureBillListener
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

        if($event->reservation->is_online && $event->reservation->team->sure_bills_client_id && $event->reservation->team->payment_preprocessor == 'sure-bills'){
            $event->reservation->cancelBill();
        }




    }
}

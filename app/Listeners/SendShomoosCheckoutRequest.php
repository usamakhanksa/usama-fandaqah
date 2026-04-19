<?php

namespace App\Listeners;

use App\Broadcasting\SMSChannel;
use App\Events\ReservationCheckout;
use App\Handlers\Settings;
use App\Integration;
use App\Integration\SHMS;
use App\Jobs\SHMS\CheckOut;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendShomoosCheckoutRequest implements ShouldQueue
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
        $reservation = $event->reservation;
        $credentials = Settings::checkIntegration('SHMS', $reservation->team_id);
        if ($credentials && $reservation->shomoos_id) {
            SHMS::checkOut($reservation,$credentials);
        }
    }
}

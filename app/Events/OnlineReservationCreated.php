<?php

namespace App\Events;

use App\OnlineReservation;
use App\Reservation;
use App\Unit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OnlineReservationCreated
{
    use SerializesModels;

    /** @var OnlineReservation */
    private $onlineReservation;

    /**
     * OnlineReservationCreated constructor.
     * @param OnlineReservation $onlineReservation
     */
    public function __construct(OnlineReservation $onlineReservation)
    {
        $this->onlineReservation = $onlineReservation;
    }
}

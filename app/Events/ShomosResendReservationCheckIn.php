<?php

namespace App\Events;

use App\Broadcasting\SMSChannel;
use App\Handlers\Settings;
use App\Jobs\SHMS\CheckIn;
use App\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShomosResendReservationCheckIn 
{
    use SerializesModels;

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
    }

}

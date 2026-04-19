<?php

namespace App\Events;

use App\Broadcasting\SMSChannel;
use App\Handlers\Settings;
use App\Jobs\SHMS\CheckOut;
use App\Notifications\ReservationRating;
use App\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ShomosActionCreated
{
    use SerializesModels;

    /**
     * @var Reservation
     */
    public $reservation;

    /**
     * @var Shomos Status
     */
    public $shomos_status;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation, $shomos_status)
    {
        $this->reservation = $reservation;
        $this->shomos_status = $shomos_status == 2 ? 0 :1;
    }

}

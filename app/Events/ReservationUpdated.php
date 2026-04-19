<?php

namespace App\Events;

use App\Handlers\Settings;
use App\Jobs\SCTH\UpdateBooking;
use App\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationUpdated 
{
    use SerializesModels;

    public $reservation;

    public $checkInOrOut;

    /**
     * Create a new event instance.
     *
     * @param \App\Reservation $reservation
     * @param bool $checkInOrOut
     * @return void
     */
    public function __construct(Reservation $reservation, bool $checkInOrOut = false)
    {
        $this->reservation = $reservation;
        $this->checkInOrOut = $checkInOrOut;
    }


}

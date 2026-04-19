<?php

namespace App\Events;

use App\Handlers\Settings;
use App\Integration;
use App\Mail\Owner\ReservationDeleted as ReservationDeletedMail;
use App\Notifications\Owner\ReservationDeleted as ReservationDeletedNotification;
use App\Reservation;
use Config;
use Exception;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Liliom\Unifonic\UnifonicFacade;
use Mail;

class ReservationDeleted
{
    use SerializesModels;

    public $reservation;

    /**
     * Create a new event instance.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->reservation->team->notify(new ReservationDeletedNotification($reservation));
    }


}

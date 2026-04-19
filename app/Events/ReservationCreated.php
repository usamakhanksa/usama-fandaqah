<?php

namespace App\Events;

use App\Handlers\Settings;
use App\Integration;
use App\Jobs\SCTH\CreateBooking;
use App\Jobs\SHMS\CheckIn;
use App\Mail\Owner\ReservationCreated as ReservationCreatedMail;
use App\Mail\ReservationCancelled;
use App\Notifications\Owner\ReservationCreated as ReservationCreatedNotification;
use App\OnlineReservation;
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
use Illuminate\Support\Facades\Log;
use Liliom\Unifonic\UnifonicFacade;


class ReservationCreated
{
    use SerializesModels;

    public $reservation;

    /**
     * Create a new event instance.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function __construct(Reservation $reservation, $default = true)
    {
        $this->reservation = $reservation;
        if ($default) {
            $this->checkSCTH();
        }
        $this->reservation->team->notify(new ReservationCreatedNotification($reservation));
    }

    protected function checkSCTH()
    {
        $credentials = Settings::checkIntegration('SCTH', $this->reservation->team_id);
        if ($credentials) {
            CreateBooking::dispatch($this->reservation, $credentials);
        }
    }


}

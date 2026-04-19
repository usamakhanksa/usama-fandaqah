<?php

namespace App\Events;

use App\Handlers\Settings;
use App\Integration;
use App\Jobs\SCTH\CancelBooking;
use App\Jobs\SHMS\CheckOut;
use App\Jobs\SHMS\UpdateCheckIn;
use App\Mail\Owner\ReservationCanceled as ReservationCanceledMail;
use App\Notifications\Owner\ReservationCancelled as ReservationCanceledNotification;
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

class ReservationCanceled
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
            $this->checkSHMS();
            $this->reservation->team->notify(new ReservationCanceledNotification($reservation));
        }
    }

    /**
     * Canceling booking for SCTH is only allowed befire checkin as mentioned on documentation
     *
     * @return void
     */
    protected function checkSCTH()
    {
        $credentials = Settings::checkIntegration('SCTH', $this->reservation->unit->team_id);

        if ($credentials
            and !is_null($this->reservation->scth_reference)
            and (is_null($this->reservation->checked_in) and is_null($this->reservation->checked_out))
        ) {
            CancelBooking::dispatch($this->reservation, $credentials);
        }
    }

    protected function checkSHMS()
    {
        $credentials = Settings::checkIntegration('SHMS', $this->reservation->team_id);
        if ($credentials && isset($this->reservation->shomoos_id)) {
            CheckOut::dispatch($this->reservation, $credentials);
        }

    }

}

<?php

namespace App\Mail\Owner;

use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var OnlineReservation */
    private $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nights = $this->reservation->nights;
        $number = $this->reservation->number;

        return $this
            ->view('email.owner.reservation_created')
            ->with(['reservation'   =>  $this->reservation])
            ->subject("حجز جديد رقم $number - $nights ليالي")
            ;
    }
}

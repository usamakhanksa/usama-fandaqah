<?php

namespace App\Mail;

use App\OnlineReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationCancelled extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var OnlineReservation */
    private $reservation;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OnlineReservation $reservation)
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
        $subject = 'تم الغاء حجزك رقم ';
        $subject .= $this->reservation->number;
        $subject .= ' - ';
        $subject .= $this->reservation->team->name;

        $url = $this->reservation->team->private_domain ? $this->reservation->team->private_domain :  $this->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');

        return $this
            ->subject($subject)
            ->view('email.reservation_cancelled')
            ->with(['reservation' =>  $this->reservation, 'url' =>  $url])
            ;
    }
}

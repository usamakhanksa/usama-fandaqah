<?php

namespace App\Mail\Customer;

use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationCheckInWelcomeMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var OnlineReservation */
    public $reservation;

    public $optionObject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Reservation $reservation , $optionObject)
    {
        $this->reservation = $reservation;
        $this->optionObject = $optionObject ;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $hotelEmail =  $this->reservation->team->hotelEmail() ;
        return $this
            ->view('email.customer.reservation_checkin')
            ->with(['reservation'   =>  $this->reservation , 'options' => $this->optionObject])
            ->from( $hotelEmail && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) ? $hotelEmail : 'no-reply@fandaqah.com' , $this->reservation->team->name)
            ->replyTo($hotelEmail && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) ? $hotelEmail : 'no-reply@fandaqah.com')
            ->subject($this->optionObject['content']);
    }
}

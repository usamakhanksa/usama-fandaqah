<?php

namespace App\Mail\Customer;

use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OnlineReservationConfirmed extends Mailable implements ShouldQueue
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
    public function __construct(OnlineReservation $reservation , $optionObject)
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

        return $this
            ->view('email.customer.reservation_online_confirmed')
            ->with(['reservation'   =>  $this->reservation , 'option' => $this->optionObject->value])
            ->from( 'no-replay@fandaqah.com' , $this->reservation->team->name)
            ->replyTo($this->reservation->team->owner->email)
            ->subject($this->optionObject->value->content);
    }
}

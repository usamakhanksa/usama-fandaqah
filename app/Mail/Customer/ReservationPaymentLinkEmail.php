<?php

namespace App\Mail\Customer;

use App\OnlineReservation;
use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationPaymentLinkEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reservation_id;
    public $payment_link;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($reservation_id,$payment_link)
    {
        $this->reservation_id = $reservation_id;
        $this->payment_link = $payment_link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $reservation = Reservation::with('team')->find($this->reservation_id);
        return $this
            ->view('email.customer.reservation_payment_link')
            ->with(['reservation'   =>  $reservation , 'payment_link' => $this->payment_link])
            ->subject('Reservation Payment Link');
    }
}

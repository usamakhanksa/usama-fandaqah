<?php

namespace App\Mail;

use App\OnlineReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Vinkla\Hashids\Facades\Hashids;

class ReservationConfirmed extends Mailable implements ShouldQueue
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
        $subject = 'تم تآكيد حجزك رقم ';
        $subject .= $this->reservation->number;
        $subject .= ' - ';
        $subject .= $this->reservation->team->name;

        $res_url = '//' . $this->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN') . '/' . Hashids::encode($this->reservation->id);
        $url = $this->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN') . '';

        return $this
            ->subject($subject)
            ->view('email.reservation_confirmed')
            ->with(['reservation' =>  $this->reservation, 'settings'    =>  $this->reservation->team->websiteSetting, 'res_url' =>  $res_url, 'url' =>  $url])
            ;
    }
}

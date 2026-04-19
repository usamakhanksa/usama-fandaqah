<?php

namespace App\Mail\Customer;

use App\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\App;
use Vinkla\Hashids\Facades\Hashids;

class OnlineReservationConfirmedForNewWebsite extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var OnlineReservation */
    public $reservation;


    /**
     * OnlineReservationConfirmedForNewWebsite constructor.
     * @param OnlineReservation $reservation
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

        $url =  $this->reservation->team->private_domain ? '//' . $this->reservation->team->private_domain . '/confirmation?id=' . Hashids::encode($this->reservation->id) :  $this->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN')  . '/confirmation?id=' . Hashids::encode($this->reservation->id) ;


        $subject = 'تم تآكيد حجزك رقم ';
        $subject .= $this->reservation->number;

        $res_url =  $this->reservation->team->private_domain ? '//' . $this->reservation->team->private_domain . '/confirmation?id=' . Hashids::encode($this->reservation->id) :  $this->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN')  . '/confirmation?id=' . Hashids::encode($this->reservation->id);


        return $this
            ->from('no-replay@fandaqah.com' , $this->reservation->team->name)
            ->replyTo($this->reservation->team->owner->email)
            ->subject($subject)
            ->view('email.reservation_confirmed')
            ->with(['reservation' =>  $this->reservation, 'settings'    =>  $this->reservation->team->websiteSetting, 'res_url' =>  $res_url, 'url' =>  $url])
            ;
    }
}

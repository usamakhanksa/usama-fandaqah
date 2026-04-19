<?php

namespace App\Mail;

use App\OnlineReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NewReservation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /** @var OnlineReservation */
    private $reservation;

    /** @var bool */
    private $owner;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(OnlineReservation $reservation, bool $owner = false)
    {
        $this->reservation = $reservation;
        $this->owner = $owner;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'حجز جديد رقم ';
        if (!$this->owner)
            $subject = 'تفاصيل حجزك رقم ';

        $subject .= $this->reservation->number;
        $subject .= ' - بانتظار التاكيد - ';
        $subject .= $this->reservation->team->name;

// till confirm
//        ($this->reservation->team->private_domain && $this->reservation->team->private_domain_status === 'installed')
        $url = $this->reservation->team->private_domain ? $this->reservation->team->private_domain :  $this->reservation->team->slug . '.' . env('CUSTOMER_WEBSITE_DOMAIN');

        return $this
            ->subject($subject)
            ->view('email.new_reservation')
                ->with(['reservation' =>  $this->reservation, 'owner' => $this->owner, 'url'    =>  $url])
            ;
    }
}

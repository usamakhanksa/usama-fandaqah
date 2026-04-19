<?php

namespace App\Mail\Customer;

use App\Reservation;
use App\OnlineReservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AwaitingConfirmationReservationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $reservation;

    public $tries = 4;

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
        $hotelEmail =  DB::table('settings')->where('key' , '=' , 'day_end')->where('team_id' , '=' , $this->reservation->team_id)->value('value');
        $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';
        $awaitingStatusLabel = $this->reservation->status == 'awaiting-confirmation' ? 'التاكيد' : 'الدفع';
        return $this
            ->view('email.customer.awaiting-confirmation-reservation')
            ->with(['reservation'   =>  $this->reservation])
            ->from( $hotelEmail && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) ? $hotelEmail : 'no-reply@fandaqah.com' , $this->reservation->team->name)
            ->replyTo($hotelEmail && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL) ? $hotelEmail : 'no-reply@fandaqah.com')
            ->subject('حجز بانتظار التاكيد');
    }
}

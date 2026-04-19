<?php

namespace App\Listeners;

use App\Events\ReservationCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Customer\AwaitingConfirmationReservationMail;

class SendAwaitingConfirmationMailToCustomer
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(ReservationCreated $event)
    {
        $this->sendEmail($event);
    }

    function sendEmail($event)
    {
        if ($event->reservation->customer && $event->reservation->customer->email) {
            $hotelEmail =  DB::table('settings')->where('key', '=', 'day_end')->where('team_id', '=', $event->reservation->team_id)->value('value');
            $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

            $data = [
                'to' => $event->reservation->customer->email,
                'reply_to' => $hotelEmail,
                'subject' => __('Awaiting Confirmation'),
                'html' => view('email.customer.awaiting-confirmation-reservation')->with(['reservation' => $event->reservation])->render(),
            ];

            $send = sendMailUsingMailMicroservice($data);
            // Mail::to($event->reservation->customer->email)->send(new AwaitingConfirmationReservationMail($event->reservation));
        }
    }
}

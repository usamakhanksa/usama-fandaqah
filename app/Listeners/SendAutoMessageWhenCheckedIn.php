<?php

namespace App\Listeners;

use Mail;
use Config;

use App\Integration;
use App\NotificationControl;
use App\Events\ReservationCheckIn;
use Illuminate\Support\Facades\DB;
use Liliom\Unifonic\UnifonicFacade;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Customer\ReservationCheckInWelcomeMessage;

class SendAutoMessageWhenCheckedIn implements ShouldQueue
{
    use InteractsWithQueue;

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
     * @param  ReservationCheckIn  $event
     * @return void
     */
    public function handle(ReservationCheckIn $event)
    {
        $query = NotificationControl::query();
        $alertReservationCheckedIn = $query->whereRaw(" `key` = ? ", ['alert_reservation_checked_in'])->whereRaw(" team_id = ? " , [$event->reservation->team_id])->first();
        $alertReservationCheckedIn = $alertReservationCheckedIn ? $alertReservationCheckedIn->value : null;
        if(!is_null($alertReservationCheckedIn)){
            $this->sendEmail($event , $alertReservationCheckedIn);
            $this->sendSms($event , $alertReservationCheckedIn);
        }

    }


    function sendEmail($event , $options)
    {

        if($options['email']){
            if($event->reservation->customer && $event->reservation->customer->email != null){
                $hotelEmail =  DB::table('settings')->where('key', '=', 'day_end')->where('team_id', '=', $event->reservation->team_id)->value('value');
                $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

                $data = [
                    'to' => $event->reservation->customer->email,
                    'reply_to' => $hotelEmail,
                    'subject' => $options['content'],
                    'html' => view('email.customer.reservation_checkin')
                                ->with(['reservation'   =>  $event->reservation , 'options' => $options])->render(),
                ];

                $send = sendMailUsingMailMicroservice($data);
                // Mail::to($event->reservation->customer->email)->send(new ReservationCheckInWelcomeMessage($event->reservation , $options));
            }
        }
    }

    function sendSms($event , $options)
    {
        if($options['sms']){
            $message = $this->formatSmsMessage($event->reservation , $options);

            if($event->reservation->customer && $event->reservation->customer->phone != null){
                $phone = intval( $event->reservation->customer->phone);
                sendSms($event->reservation->team_id, $message, $phone);
            }
        }
    }

    function formatSmsMessage($reservation , $options){


        $message = $options['content'];
        $message .= PHP_EOL;

        if($options['contentOptions']['contractNumber']){
            $message .= ' رقم العقد : '   . $reservation->number;
            $message .= PHP_EOL;
        }

        if($options['contentOptions']['date']){
            $message .= ' من : '   . date('Y/m/d' , strtotime($reservation->date_in)) . ' الى  :  ' . date('Y/m/d' , strtotime($reservation->date_out)) ;
            $message .= PHP_EOL;
        }

        if($options['contentOptions']['unitName']){
            $message .= '  الوحدة : '   . $reservation->unit->name;
            $message .= PHP_EOL;
        }

        if($options['contentOptions']['invoiceAmount']){
            $message .= '  المبلغ : '   . $reservation->total_price . __('SAR');
            $message .= PHP_EOL;
        }


        return $message  ;
    }
}

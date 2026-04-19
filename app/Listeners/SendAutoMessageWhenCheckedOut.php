<?php

namespace App\Listeners;

use Mail;
use Config;
use App\Integration;
use App\Handlers\Settings;
use App\Jobs\SHMS\CheckOut;
use App\NotificationControl;
use App\Broadcasting\SMSChannel;
use Illuminate\Support\Facades\DB;
use App\Events\ReservationCheckout;
use Liliom\Unifonic\UnifonicFacade;
use App\Notifications\ReservationRating;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Customer\ReservationCheckOutGoodByeMessage;

class SendAutoMessageWhenCheckedOut implements ShouldQueue
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
     * @param  ReservationCheckout  $event
     * @return void
     */
    public function handle(ReservationCheckout $event)
    {
        $query = NotificationControl::query();
        $alertReservationCheckedOut = $query->whereRaw(" `key` = ? ", ['alert_reservation_checked_out'])->whereRaw(" team_id = ? " , [$event->reservation->team_id])->first();
        $alertReservationCheckedOut = $alertReservationCheckedOut ? $alertReservationCheckedOut->value : null;
        if(!is_null($alertReservationCheckedOut)){
            $this->sendEmail($event , $alertReservationCheckedOut);
            $this->sendSms($event , $alertReservationCheckedOut);
        }
        $this->checkRating($event->reservation);
    }


    function sendEmail($event , $options)
    {

        if($options['email']){
            if($event->reservation->customer && !is_null($event->reservation->customer->email) && filter_var($event->reservation->customer->email, FILTER_VALIDATE_EMAIL) ){

                $hotelEmail =  DB::table('settings')->where('key', '=', 'day_end')->where('team_id', '=', $event->reservation->team_id)->value('value');
                $hotelEmail = ($hotelEmail != "" && !is_null($hotelEmail) && filter_var($hotelEmail, FILTER_VALIDATE_EMAIL)) ? $hotelEmail : 'no-reply@fandaqah.com';

                $data = [
                    'to' => $event->reservation->customer->email,
                    'reply_to' => $hotelEmail,
                    'subject' => $options['content'],
                    'html' => view('email.customer.reservation_checkout')
                                ->with(['reservation'   =>  $event->reservation , 'options' => $options])->render(),
                ];

                $send = sendMailUsingMailMicroservice($data);
                // Mail::to($event->reservation->customer->email)->send(new ReservationCheckOutGoodByeMessage($event->reservation , $options));
            }
        }
    }

    function sendSms($event , $options)
    {
        if($options['sms']){
             $message = $this->formatSmsMessage($event->reservation , $options);

            if($event->reservation->customer && $event->reservation->customer->phone != null){
                $phone = intval($event->reservation->customer->phone);
                sendSms($event->reservation->team_id, $message, $phone);
            }
        }
    }

    function formatSmsMessage($reservation , $options){


        $message =  $options['content'];
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


    function checkRating($reservation)
    {
        $enableEmail = \DB::table('settings')->where('key' , '=' , 'enable_ratings_email')->where('team_id' , '=' , $reservation->team_id)->value('value');
        $enableSms = \DB::table('settings')->where('key' , '=' , 'enable_ratings_sms')->where('team_id' , '=' , $reservation->team_id)->value('value');

        if ($enableEmail || $enableSms) {
            $time = \DB::table('settings')->where('key' , '=' , 'send_rating_after')->where('team_id' , '=' , $reservation->team_id)->value('value');

            $time = intval($time);
            if (empty($time) || is_null($time) and $time != 0) {
                $time = 24;
            }

            $meta = [];

            if ($enableEmail)
                $meta[] = 'mail';
            if ($enableSms)
                $meta[] = SMSChannel::class;

            if ($time != 0 and !empty($time) and !is_null($time)) {
                $when = now()->addHours($time);

                $reservation->customer->notifyAt(new ReservationRating($reservation, $meta), $when);

            } else {
                $reservation->customer->notify(new ReservationRating($reservation, $meta));
            }
        }
    }
}

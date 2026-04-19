<?php

namespace App\Listeners;

use Config;
use App\Team;
use App\Integration;
use App\NotificationControl;
use App\Events\ReservationCreated;
use Liliom\Unifonic\UnifonicFacade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Owner\ReservationCreated as ReservationCreatedMail;

class NotifyAdminstrationWhenNewReservationAdded implements ShouldQueue
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
     * @param  ReservationCreated  $event
     * @return void
     */
    public function handle(ReservationCreated $event)
    {
        $queryAdd = NotificationControl::query();
        $options = $queryAdd->whereRaw(" `key` = ? ", ['alert_reservation_added'])->whereRaw(" team_id = ? " , [$event->reservation->team_id])->first();
        $options = $options ?  $options->value : null;

        if(!is_null($options)){
            $this->sendEmail($event,$options);
            $this->sendSms($event,$options);
        }

    }

    function sendEmail($event,$options)
    {
        $queryEmails = NotificationControl::query();
        $emails = $queryEmails->whereRaw(" `key` = ? ", ['alert_email'])->whereRaw(" team_id = ? " , [$event->reservation->team_id])->first();
        $emails = $emails->value;

        if($options['email']){
            foreach ($emails as $obj) {
                if($event->reservation->status == 'confirmed'){
                    $nights = $event->reservation->nights;
                    $number = $event->reservation->number;
                    $data = [
                        'to' => $obj['email'],
                        'reply_to' => null,
                        'subject' => "حجز جديد رقم $number - $nights ليالي",
                        'html' => view('email.owner.reservation_created')
                        ->with(['reservation'   =>  $event->reservation])->render(),
                    ];
                    $send = sendMailUsingMailMicroservice($data);


                    // Mail::to($obj['email'])->send(new ReservationCreatedMail($event->reservation));
                }
            }
        }
    }

    function sendSms($event,$options)
    {
        $queryPhones = NotificationControl::query();
        $phones = $queryPhones->whereRaw(" `key` = ? ", ['alert_phone'])->whereRaw(" team_id = ? " , [$event->reservation->team_id])->first();
        $phones = $phones->value;
        if($options['sms']){
            $nights = $event->reservation->nights;
            $number = $event->reservation->number;
            $total_price = $event->reservation->total_price;
            $team_id = $event->reservation->team_id;
            $team = Team::find($team_id);
            $team_name = $team ? $team->name : '';

            $message = "حجز جديد رقم $number - $nights ليالي بقيمة $total_price ريال - $team_name";

            if(count($phones)){
                foreach($phones as $obj){
                    sendSms($event->reservation->team_id, $message, intval($obj['phone']));
                }
            }
        }
    }
}

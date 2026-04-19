<?php

namespace App\Listeners;

use Mail;
use Config;
use App\Team;
use App\Activity;
use App\Integration;
use App\NotificationControl;
use App\Events\ReservationCanceled;
use Liliom\Unifonic\UnifonicFacade;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\Owner\ReservationCanceled as ReservationCanceledMail;

class NotifyAdminstrationWhenReservationCanceled implements ShouldQueue
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
    public function handle(ReservationCanceled $event)
    {
        $queryCancel = NotificationControl::query();
        $options = $queryCancel->whereRaw(" `key` = ? ", ['alert_reservation_canceled'])->whereRaw(" team_id = ? " , [$event->reservation->team_id])->first();
        $options = $options ? $options->value : null;
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
                $nights = $event->reservation->nights;
                    $number = $event->reservation->number;
                    $logObj = Activity::where('team_id' , $event->reservation->team_id)
                                ->where('subject_id' , $event->reservation->id)
                                // ->where('causer_id' , auth()->user()->id)
                                ->where('description' , 'LIKE' , '%' . 'إلغاء الحجز' . '%')
                                ->first();
                    $data = [
                        'to' => $obj['email'],
                        'reply_to' => null,
                        'subject' => "إلغاء الحجز رقم $number - $nights ليالي",
                        'html' => view('email.owner.reservation_canceled')->with(['reservation'   =>  $event->reservation , 'logObj' => $logObj])->render(),
                    ];
                    $send = sendMailUsingMailMicroservice($data);
                // Mail::to($obj['email'])->send(new ReservationCanceledMail($event->reservation));
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
            $team_name = $team->name;

            $message = "إلغاء الحجز رقم $number - $nights ليالي بقيمة $total_price ريال - $team_name";

            foreach($phones as $obj){
                sendSms($event->reservation->team_id, $message, intval($obj['phone']));
            }

        }
    }
}

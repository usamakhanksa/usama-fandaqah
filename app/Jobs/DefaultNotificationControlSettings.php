<?php

namespace App\Jobs;

use App\NotificationControl;
use App\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class DefaultNotificationControlSettings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $team;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Team $team)
    {
        $this->team = $team ;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notficationControlSettings();
    }

    protected function notficationControlSettings(){


        $contentOptions = new \stdClass();
        $contentOptions->contractNumber = false;
        $contentOptions->date = false;
        $contentOptions->unitName = false;
        $contentOptions->invoiceAmount = false;


        $alertReservationAdded = new \stdClass();
        $alertReservationAdded->email = false;
        $alertReservationAdded->sms = false;


        $alertReservationCanceled = new \stdClass();
        $alertReservationCanceled->email = false;
        $alertReservationCanceled->sms = false;


        $alertReservationDeleted = new \stdClass();
        $alertReservationDeleted->email = false;
        $alertReservationDeleted->sms = false;

        $alertDailyReport = new \stdClass();
        $alertDailyReport->email = false;
        $alertDailyReport->sms = false;



        $alertEmail = new \stdClass();
        $alertEmail->email = $this->team->owner->email ;
        $emails [] = $alertEmail ;


        $alertPhone = new \stdClass();
        $alertPhone->phone = $this->team->owner->phone ;
        $phones [] = $alertPhone ;


        $alertReservationCheckedIn = new \stdClass();
        $alertReservationCheckedIn->email = false;
        $alertReservationCheckedIn->sms = false;
        $alertReservationCheckedIn->content = __('Welcome in :hotel_name , We wish you a pleasant stay' , ['hotel_name' => $this->team->name]);
        $alertReservationCheckedIn->contentOptions = $contentOptions ;



        $alertReservationCheckedOut = new \stdClass();
        $alertReservationCheckedOut->email = false;
        $alertReservationCheckedOut->sms = false;
        $alertReservationCheckedOut->content = __('We hope you had a good time with us in :hotel_name and we also hope to see you again' , ['hotel_name' => $this->team->name]);
        $alertReservationCheckedOut->contentOptions = $contentOptions ;


//        $alertReservationConfirmed = new \stdClass();
//        $alertReservationConfirmed->email = false;
//        $alertReservationConfirmed->sms = false;
//        $alertReservationConfirmed->content =  __('Your reservation has been confirmed in :hotel_name' , ['hotel_name' => $this->team->name]);
//        $alertReservationConfirmed->contentOptions = $contentOptions ;





        DB::table('notification_controls')->insert(
            [
                ['team_id' => $this->team->id, 'key' => 'alert_reservation_added' , 'value' => json_encode($alertReservationAdded) , 'type' => 'management'],
                ['team_id' => $this->team->id, 'key' => 'alert_reservation_deleted' , 'value' => json_encode($alertReservationCanceled) , 'type' => 'management'],
                ['team_id' => $this->team->id, 'key' => 'alert_reservation_canceled' , 'value' => json_encode($alertReservationDeleted) , 'type' => 'management'],
                ['team_id' => $this->team->id, 'key' => 'alert_daily_report' , 'value' => json_encode($alertDailyReport) , 'type' => 'management'],
                ['team_id' => $this->team->id, 'key' => 'alert_email' , 'value' => json_encode($emails) , 'type' => 'management'],
                ['team_id' => $this->team->id, 'key' => 'alert_phone' , 'value' => json_encode($phones) , 'type' => 'management'],
                ['team_id' => $this->team->id, 'key' => 'alert_reservation_checked_in' , 'value' => json_encode($alertReservationCheckedIn) , 'type' => 'customer'],
                ['team_id' => $this->team->id, 'key' => 'alert_reservation_checked_out' , 'value' => json_encode($alertReservationCheckedOut) , 'type' => 'customer'],
//                ['team_id' => $this->team->id, 'key' => 'alert_reservation_comfirmed' , 'value' => json_encode($alertReservationConfirmed) , 'type' => 'customer'],

            ]
        );
    }
}

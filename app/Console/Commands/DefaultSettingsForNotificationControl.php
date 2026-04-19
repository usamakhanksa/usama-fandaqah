<?php

namespace App\Console\Commands;

use App\NotificationControl;
use App\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DefaultSettingsForNotificationControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:tenant-default-notification-control-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $teams = Team::whereNull('deleted_at')->with('owner')->get();
        foreach ($teams as $team){
            if(!$this->checkTeamHasDefaultNotificationSettings($team)){

                $this->notficationControlSettings($team);

            }
        }

    }

    protected function notficationControlSettings($team){

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
        $alertEmail->email = $team->owner ? $team->owner->email : null ;
        $emails [] = $alertEmail ;


        $alertPhone = new \stdClass();
        $alertPhone->phone = $team->owner ? $team->owner->phone : null ;
        $phones [] = $alertPhone ;


        $alertReservationCheckedIn = new \stdClass();
        $alertReservationCheckedIn->email = false;
        $alertReservationCheckedIn->sms = false;
        $alertReservationCheckedIn->content = __('Welcome in :hotel_name , We wish you a pleasant stay' , ['hotel_name' => $team->name]);
        $alertReservationCheckedIn->contentOptions = $contentOptions ;



        $alertReservationCheckedOut = new \stdClass();
        $alertReservationCheckedOut->email = false;
        $alertReservationCheckedOut->sms = false;
        $alertReservationCheckedOut->content = __('We hope you had a good time with us in :hotel_name and we also hope to see you again' , ['hotel_name' => $team->name]);
        $alertReservationCheckedOut->contentOptions = $contentOptions ;


        $alertReservationConfirmed = new \stdClass();
        $alertReservationConfirmed->email = false;
        $alertReservationConfirmed->sms = false;
        $alertReservationConfirmed->content =  __('Your reservation has been confirmed in :hotel_name' , ['hotel_name' => $team->name]);
        $alertReservationConfirmed->contentOptions = $contentOptions ;

        DB::table('notification_controls')->insert(
            [
                ['team_id' => $team->id, 'key' => 'alert_reservation_added' , 'value' => json_encode($alertReservationAdded) , 'type' => 'management'],
                ['team_id' => $team->id, 'key' => 'alert_reservation_deleted' , 'value' => json_encode($alertReservationCanceled) , 'type' => 'management'],
                ['team_id' => $team->id, 'key' => 'alert_reservation_canceled' , 'value' => json_encode($alertReservationDeleted) , 'type' => 'management'],
                ['team_id' => $team->id, 'key' => 'alert_daily_report' , 'value' => json_encode($alertDailyReport) , 'type' => 'management'],
                ['team_id' => $team->id, 'key' => 'alert_email' , 'value' => json_encode($emails) , 'type' => 'management'],
                ['team_id' => $team->id, 'key' => 'alert_phone' , 'value' => json_encode($phones) , 'type' => 'management'],
                ['team_id' => $team->id, 'key' => 'alert_reservation_checked_in' , 'value' => json_encode($alertReservationCheckedIn) , 'type' => 'customer'],
                ['team_id' => $team->id, 'key' => 'alert_reservation_checked_out' , 'value' => json_encode($alertReservationCheckedOut) , 'type' => 'customer'],
                ['team_id' => $team->id, 'key' => 'alert_reservation_comfirmed' , 'value' => json_encode($alertReservationConfirmed) , 'type' => 'customer'],

            ]
        );
    }

    protected function checkTeamHasDefaultNotificationSettings($team){
        return NotificationControl::where('team_id' , $team->id)->exists();
    }
}

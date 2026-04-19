<?php

namespace App\Jobs;

use App\Team;
use App\User;
use Illuminate\Bus\Queueable;
use App\Broadcasting\SMSChannel;
use Illuminate\Queue\SerializesModels;
use App\Notifications\DailyBriefReport;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class DailyBriefReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $team_id;
    public $tries = 1;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($team_id = null)
    {
        $this->team_id = $team_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if($this->team_id){

        $team = Team::find($this->team_id);
        // hot fix because of id is comming null
        //10:31:23] production.ERROR: Trying to get property 'id' of non-object {"exception":"[object] (ErrorException(code: 0): Trying to get property 'id' of non-object at /home/forge/app.fandaqah.com/app/Jobs/DailyBriefReportJob.php:38)
        //deleted from notification controls 885,916,1012
            if($team){
            $alertDailyReport = getNotificationControlKeyValue('alert_daily_report', $team->id);
            if ($alertDailyReport) {

                if ($alertDailyReport->value->email) {
                    $alertEmails = getNotificationControlKeyValue('alert_email', $team->id);
                    $to = 'mail';
                    Notification::route($to, $alertEmails->value)
                        ->notifyNow(new DailyBriefReport($to, $team, $alertEmails));
                }

                if ($alertDailyReport->value->sms) {
                    $alertPhones = getNotificationControlKeyValue('alert_phone', $team->id);
                    $to = SMSChannel::class;
                    Notification::route($to, $alertPhones->value)
                        ->notifyNow(new DailyBriefReport($to, $team, $alertPhones));
                }
            }
        }
    }

    }
}

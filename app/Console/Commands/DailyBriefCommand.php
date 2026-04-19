<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DailyBriefReportJob;
use Illuminate\Support\Facades\DB;

class DailyBriefCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'daily:brief-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command command will schedule a job to fire daily brief report for each team';

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
        $notifcation_controls_settings = DB::select("SELECT * FROM `notification_controls` WHERE `key` = 'alert_daily_report' AND (JSON_EXTRACT(`value`, '$.email') = TRUE OR JSON_EXTRACT(`value`, '$.sms') = TRUE)");
        if (count($notifcation_controls_settings)) {
            foreach ($notifcation_controls_settings as $notification_controls_setting) {
                DailyBriefReportJob::dispatch($notification_controls_setting->team_id);
            }
        }
    }
}

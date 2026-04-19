<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class SetInitialEnableActivityLogsAsTrueForBasicActiveTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enable-activity-logs:active-tenant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is to set the default value of enable activity logs for all active tenants to true';

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
        $teams = Team::whereNull('deleted_at')
                ->where('current_billing_plan' , 'team-basic')
                ->where('ends_at' , '>' , now())
                ->get();
        if(count($teams)){
            foreach ($teams as $team) {
                $team->enable_activity_logs = true;
                $team->save();
            }
        }       
    }
}

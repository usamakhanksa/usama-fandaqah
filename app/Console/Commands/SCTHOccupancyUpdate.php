<?php

namespace App\Console\Commands;
use App\Team;
use Illuminate\Console\Command;
use App\Jobs\SCTH\OccupancyUpdate;
use App\Jobs\CreateOccupiedForTeam;
use Illuminate\Support\Facades\Log;

class SCTHOccupancyUpdate extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scth:occupancy-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'scth occupancy update for all tenants at 07:00 am daily';

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
        $teams = Team::whereNull('deleted_at')->get();
        foreach ($teams as $team) {
            if($team->integration_scth){
                    OccupancyUpdate::dispatch($team,null,true);
            }
        }
    }
}

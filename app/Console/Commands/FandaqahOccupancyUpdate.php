<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;
use App\Jobs\CreateOccupiedForTeam;

class FandaqahOccupancyUpdate extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:occupancy-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fandaqah occupancy update for all tenants';

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
        $team_ids = Team::whereNull('deleted_at')->get()->pluck('id');
        foreach ($team_ids as $team_id) {
            CreateOccupiedForTeam::dispatch($team_id);
        }
    }
}

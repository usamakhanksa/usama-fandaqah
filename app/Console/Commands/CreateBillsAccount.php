<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class CreateBillsAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'surebills:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new sure bills account for every team';

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
        $teams = Team::hasNotBillsAccount()->take(10)->get();

        if ($teams->count() < 1) {
            return false;
        }

        foreach ($teams as $team) {
            $team->createBillsAccount();
        }
    }
}

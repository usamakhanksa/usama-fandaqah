<?php

namespace App\Console\Commands;

use App\Team;
use App\ServicesCategory;
use Illuminate\Console\Command;

class AddUsersToServiceCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fill:service-categories-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add tenant uses to all services categories by default';

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
        $teams = Team::with(['users', 'serviceCategories'])->whereNull('deleted_at')->get();
        if (count($teams)) {
            foreach ($teams as $team) {
                if (count($team->serviceCategories)) {
                    foreach ($team->serviceCategories as $category) {
                        $category->users = json_encode($team->users()->pluck('id'));
                        $category->save();
                    }
                }
            }
        }
    }
}

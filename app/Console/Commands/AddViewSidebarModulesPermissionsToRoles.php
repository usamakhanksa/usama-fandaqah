<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class AddViewSidebarModulesPermissionsToRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:sidebar-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant permissions to specific roles to watch sidebar modules';

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

        $teams = Team::whereNull('deleted_at')->with('roles')->get();

        foreach ($teams as $team) {
            foreach ($team->roles as $role) {
                if ($role->name != 'POS') {
                    if (!$role->hasPermission('watch reservations table') || !$role->hasPermission('watch unit housing')) {
                        $role->grant('watch reservations table');
                        $role->grant('watch unit housing');
                    }
                }
            }
        }
    }
}

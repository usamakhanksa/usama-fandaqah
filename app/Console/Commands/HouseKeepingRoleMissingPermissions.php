<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class HouseKeepingRoleMissingPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:housekeeping-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'grant missing housekeeping permissions to its role';

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

        foreach($teams as $team){
            foreach ( $team->roles as $role) {
                 if($role->slug == 'housekeeping'){
                    if(!$role->hasPermission('watch unit housing')){
                        $role->grant('watch unit housing');
                    }

                    if(!$role->hasPermission('change to under cleaning')){
                        $role->grant('change to under cleaning');
                    }

                    if(!$role->hasPermission('change to available')){
                        $role->grant('change to available');
                    }
                 }
            }
        }
    }
}

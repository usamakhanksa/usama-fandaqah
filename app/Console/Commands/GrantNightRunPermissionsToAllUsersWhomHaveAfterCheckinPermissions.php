<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class GrantNightRunPermissionsToAllUsersWhomHaveAfterCheckinPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:night-run-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach all night run permissions to admin role';

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
          
                    if($role->hasPermission('change reservation price after checkin')){
                        $role->grant('change reservation price before night run');
                    }
                    if($role->hasPermission('change reservation unit after checkin')){
                        $role->grant('change reservation unit before night run');
                    }
                    if($role->hasPermission('change reservation source after checkin')){
                        $role->grant('change reservation source before night run');
                    }
                    if($role->hasPermission('extend reservation after checkin')){
                        $role->grant('extend reservation before night run');
                    }
                    if($role->hasPermission('change reservation rent type after checkin')){
                        $role->grant('change reservation rent type before night run');
                    }
                    if($role->hasPermission('change reservation calendar date after checkin')){
                        $role->grant('change reservation calendar date before night run');
                    }
                    if($role->hasPermission('cancel reservation after checkin')){
                        $role->grant('cancel reservation before night run');
                    }
           
               
            }
        }
    }
}

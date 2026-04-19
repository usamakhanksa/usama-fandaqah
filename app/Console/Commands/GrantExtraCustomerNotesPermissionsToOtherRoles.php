<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class GrantExtraCustomerNotesPermissionsToOtherRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:grant-extra-customers-notes-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant add customers  permission to other roles';

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
     * Adding show safe balance permission to other roles instead of housekeeping
     * @return mixed
     */
    public function handle()
    {

        $teams = Team::whereNull('deleted_at')->with('roles')->get();

        foreach($teams as $team){
            foreach ( $team->roles as $role) {
                 if($role->slug != 'housekeeping'){
                    $role->grant('edit notes');
                    $role->grant('delete notes');
                 }
            }
        }

    }
}

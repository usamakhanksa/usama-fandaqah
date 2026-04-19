<?php

namespace App\Console\Commands;

use App\Team;
use Illuminate\Console\Command;

class GrantExtendReservationsPermissionToOtherRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:extend-reservations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant extend reservations to other roles in fandaqah';

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
                 if($role->slug != 'housekeeping'){
                    $role->grant('extend reservations');
                 }
            }
        }
    }
}

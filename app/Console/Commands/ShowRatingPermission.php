<?php

namespace App\Console\Commands;

use App\Role;
use App\Team;
use Illuminate\Console\Command;

class ShowRatingPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:show-ratings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant show customer ratings permission to other roles';

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
                    $role->grant('show customers rating');
                 }
            }
        }

    }
}

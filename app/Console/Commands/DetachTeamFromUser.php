<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DetachTeamFromUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'detach:team';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detach team from user';

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
        $user_id = $this->ask('What is user id ?'); 
        $team_id = $this->ask('What is team id ?'); 
        
        $team_user = DB::table('team_users')->where('user_id' , $user_id)->where('team_id' , $team_id)->delete();
    }
}

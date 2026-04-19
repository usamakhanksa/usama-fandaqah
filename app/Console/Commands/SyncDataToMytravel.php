<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Illuminate\Console\Command;

class SyncDataToMytravel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:sync-data-to-mytravel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will allow the integration of fandaqah properties with its inventory to mytravel booking engine';

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
        //grant permission to sync data to mytravel to all users with admin, owner roles

        foreach ( Role::where('slug', 'admin')->get() as $role) {
            $role->grant('sync data to mytravel');
        }

    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Role;


class GrantViewChannelManagerPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:grant-view-channel-manager';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will add view channel manager permission to admin roles only';

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
        $roles = Role::where('slug', 'admin')->get();
        if(count($roles)){
            foreach ( $roles as $role) {
                $role->grant('view channel manager');
            }
        }
        $this->info('All Done');
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSidebarPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:sidebar-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates missing sidebar permissions and assigns them to the Super Admin role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->call('create:sidebar-permissions');
        $this->call('assign:sidebar-permissions-to-admin');
        
        $this->info('Sidebar permissions fixed successfully!');
        $this->info('You should now be able to see menu items in the sidebar API response.');
        
        return 0;
    }
}
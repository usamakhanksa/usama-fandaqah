<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class AddMissingPermissionsToAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:missing-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $roles = Role::where('slug' , 'admin')->get();
        foreach ($roles as $role){
            $role->grant('view settings');
            $role->grant('view reports');
            $role->grant('view financial');
            $role->grant('can edit reservation day price');
        }
    }
}

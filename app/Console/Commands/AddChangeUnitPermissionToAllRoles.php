<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class AddChangeUnitPermissionToAllRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:change-unit-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will add change unit permission to all roles except housekeeping';

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

        foreach ( Role::where('slug', '!=' ,  'housekeeping')->get() as $role) {
            $role->grant('change unit');
        }
    }
}

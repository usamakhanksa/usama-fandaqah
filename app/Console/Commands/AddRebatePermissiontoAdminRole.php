<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Role;


class AddRebatePermissiontoAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:add-rebate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will add rebate transaction permission to all admin roles';

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
        foreach ( Role::where('slug', 'admin')->get() as $role) {
            $role->grant('add rebates');
        }
    }
}

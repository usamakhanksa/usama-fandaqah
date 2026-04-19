<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class GrantCompaniesPermissionsToStaffRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:companies-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant Companies Permissions to Staff Role';

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
        foreach ( Role::where('slug', 'staff')->get() as $role) {
            $role->grant('view companies');
            $role->grant('create companies');
            $role->grant('update companies');
            $role->grant('view company profile');
        }
    }
}

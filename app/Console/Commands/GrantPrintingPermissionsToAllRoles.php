<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class GrantPrintingPermissionsToAllRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:print-permissions-to-all-roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will grant printing permissions to all roles ';

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
        foreach ( Role::get() as $role) {
            $role->grant('print invoices');
            $role->grant('print zatca invoices');
            $role->grant('print reservation contract');
            $role->grant('print reservation transactions');
            $role->grant('print transactions');
            $role->grant('print pos transactions');
            $role->grant('print reservation summary');
            $role->grant('print promissory notes');
        }
    }
}

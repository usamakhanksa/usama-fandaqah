<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class GrantCompaniesNotePermissionsToStaffRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:companies-note-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant Companies Notes Permissions to Staff Role';

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
            $role->grant('view companies notes');
            $role->grant('create companies notes');
            $role->grant('update companies notes');
            $role->grant('delete companies notes');
        }
    }
}

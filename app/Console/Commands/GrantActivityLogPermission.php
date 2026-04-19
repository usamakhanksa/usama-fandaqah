<?php

namespace App\Console\Commands;
use App\User;

use Illuminate\Console\Command;

class GrantActivityLogPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:activity-log-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant the "view activity log" permission to all users with the "view settings" permission';

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
        $users = User::whereNull('deleted_at')->get();
        if (count($users)) {
            foreach ($users as $user) {
                if (count($user->roles)) {
                    foreach ($user->roles as $role) {
                        $hasViewReportsPermission = $user->hasPermissionTo('view settings');
                        if ($hasViewReportsPermission) {

                            $role->grant('view activity log');


                        }
                    }
                }
            }
        }
        }
}

<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class RevokeOldPermissionsFromAllRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revoke:old-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revoke all permissions from all roles that we get ride of';

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
        $users = User::whereNUll('deleted_at')->get();
        if (count($users)) {
            foreach ($users as $user) {

                if (count($user->roles)) {
                    foreach ($user->roles as $role) {

                        if ($user->hasPermissionTo('view calenders')) {
                            $role->revoke('view calenders');
                        }

                        // if ($user->hasPermissionTo('change unit')) {
                        //     $role->revoke('change unit');
                        // }

                        // if ($user->hasPermissionTo('edit reservations')) {
                        //     $role->revoke('edit reservations');
                        // }

                        // if ($user->hasPermissionTo('edit reservations after checkin')) {
                        //     $role->revoke('edit reservations after checkin');
                        // }

                        // if ($user->hasPermissionTo('extend reservations')) {
                        //     $role->revoke('extend reservations');
                        // }

                        // if ($user->hasPermissionTo('cancel reservations')) {
                        //     $role->revoke('cancel reservations');
                        // }

                        // if ($user->hasPermissionTo('delete reservations')) {
                        //     $role->revoke('delete reservations');
                        // }
                    }
                }
            }
        }
    }
}

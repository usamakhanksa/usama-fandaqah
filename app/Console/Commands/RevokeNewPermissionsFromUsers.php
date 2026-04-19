<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class RevokeNewPermissionsFromUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revoke:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will revoke permissions';

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

                        if ($user->hasPermissionTo('change reservation price before checkin')) {
                            $role->revoke('change reservation price before checkin');
                        }
                        if ($user->hasPermissionTo('change reservation price after checkin')) {
                            $role->revoke('change reservation price after checkin');
                        }
                        if ($user->hasPermissionTo('change reservation unit before checkin')) {
                            $role->revoke('change reservation unit before checkin');
                        }

                        if ($user->hasPermissionTo('change reservation unit after checkin')) {
                            $role->revoke('change reservation unit after checkin');
                        }
                        if ($user->hasPermissionTo('change reservation source before checkin')) {
                            $role->revoke('change reservation source before checkin');
                        }

                        if ($user->hasPermissionTo('change reservation source after checkin')) {
                            $role->revoke('change reservation source after checkin');
                        }

                        if ($user->hasPermissionTo('extend reservation before checkin')) {
                            $role->revoke('extend reservation before checkin');
                        }

                        if ($user->hasPermissionTo('extend reservation after checkin')) {
                            $role->revoke('extend reservation after checkin');
                        }

                        if ($user->hasPermissionTo('change reservation rent type before checkin')) {
                            $role->revoke('change reservation rent type before checkin');
                        }

                        if ($user->hasPermissionTo('change reservation rent type after checkin')) {
                            $role->revoke('change reservation rent type after checkin');
                        }


                        if ($user->hasPermissionTo('change reservation calendar date before checkin')) {
                            $role->revoke('change reservation calendar date before checkin');
                        }


                        if ($user->hasPermissionTo('change reservation calendar date after checkin')) {
                            $role->revoke('change reservation calendar date after checkin');
                        }

                        if ($user->hasPermissionTo('cancel reservation before checkin')) {
                            $role->revoke('cancel reservation before checkin');
                        }

                        if ($user->hasPermissionTo('cancel reservation after checkin')) {
                            $role->revoke('cancel reservation after checkin');
                        }
                    }
                }
            }
        }
    }
}

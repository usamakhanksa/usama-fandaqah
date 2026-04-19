<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class AssingNewPermissionsToUsersWithRightRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will assign new before & after checkin permissions to users who alreay have the right permissions';

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
                        $hasReservationPricePermission = $user->hasPermissionTo('reservation price');
                        if ($hasReservationPricePermission) {
                            $role->grant('change reservation price before checkin');
                            $role->grant('change reservation price after checkin');
                        }

                        $hasReservationChangeUnitPermission = $user->hasPermissionTo('change unit');
                        if ($hasReservationChangeUnitPermission) {
                            $role->grant('change reservation unit before checkin');
                            $role->grant('change reservation unit after checkin');
                        }




                        $hasReservationExtendPermission = $user->hasPermissionTo('extend reservations');
                        if ($hasReservationExtendPermission) {
                            $role->grant('extend reservation before checkin');
                            $role->grant('extend reservation after checkin');
                        }

                        $hasEditReservations = $user->hasPermissionTo('edit reservations');
                        if ($hasEditReservations) {
                            $role->grant('change reservation calendar date before checkin');
                            $role->grant('change reservation rent type before checkin');
                            $role->grant('change reservation rent type after checkin');
                            $role->grant('change reservation source before checkin');
                            $role->grant('change reservation source after checkin');
                        }

                        $hasEditReservationAfterCheckin = $user->hasPermissionTo('edit reservations after checkin');
                        if ($hasEditReservationAfterCheckin) {
                            $role->grant('change reservation calendar date after checkin');
                        }


                        $hasReservationCancelPermission = $user->hasPermissionTo('cancel reservations');
                        if ($hasReservationCancelPermission) {
                            $role->grant('cancel reservation before checkin');
                            $role->grant('cancel reservation after checkin');
                        }
                    }
                }
            }
        }
    }
}

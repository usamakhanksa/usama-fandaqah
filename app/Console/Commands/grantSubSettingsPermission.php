<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;


class grantSubSettingsPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:subsettings-permission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant the "Sub-Settings" permission to all users with the "view settings" permission';

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

                            $role->grant('general seetings');
                            $role->grant('facility settings');
                            $role->grant('integration settings');
                            $role->grant('user and roles');
                            $role->grant('document settings');
                            $role->grant('notification settings');
                            $role->grant('finance settings');
                            $role->grant('ledger numbers');
                            $role->grant('reservation resource settings');
                            $role->grant('customer groups settings');
                            $role->grant('website settings');
                            $role->grant('rating settings');
                            $role->grant('services included in the price');
                            $role->grant('maintenance settings');


                        }
                    }
                }
            }
        }
        }
}

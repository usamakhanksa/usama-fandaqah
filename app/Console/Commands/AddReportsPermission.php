<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;


class AddReportsPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:add-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant  Report permissions based on view reports permission';

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
        //
        $users = User::whereNull('deleted_at')->get();
        if (count($users)) {
            foreach ($users as $user) {
                if (count($user->roles)) {
                    foreach ($user->roles as $role) {
                        $role->grant('can edit reservation day price');
                        $hasViewReportsPermission = $user->hasPermissionTo('revenues & taxes , fees');
                        if ($hasViewReportsPermission) {

                            $role->grant('revenues taxes fees');


                        }
                    }
                }
            }
        }


    }
}

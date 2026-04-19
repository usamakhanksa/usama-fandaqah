<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class AddCombinedOccupiedsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:add-combined-occupieds-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Grant combined occupieds report based on view occupieds permission';

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
                        $hasViewReportsPermission = $user->hasPermissionTo('occupieds report');
                        if ($hasViewReportsPermission) {

                            $role->grant('combined occupieds report');


                        }
                    }
                }
            }
        }
        return "done!";
    }
}

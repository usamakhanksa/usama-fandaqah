<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class AddInvoiceAndCreditNoteNewPermissionsToUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:invoice-credit-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add new add invoice , credit note permissions to all roles except house keeping';

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

                        if($role->slug != 'housekeeping' || $role->slug != 'house-keeping' || !$role->slug){

                            if(!$role->hasPermission('add invoice')){
                                $role->grant('add invoice');
                            }

                            if(!$role->hasPermission('add credit note')){
                                $role->grant('add credit note');
                            }

                        }

                       
                    }
                }
            }

            $this->info('All Done!');
        }
    }
}

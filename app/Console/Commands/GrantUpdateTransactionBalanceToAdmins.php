<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class GrantUpdateTransactionBalanceToAdmins extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'grant:update-transaction-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will add update transactions balance permission to admin roles only';

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
        $roles = Role::where('slug', 'admin')->get();
        if(count($roles)){
            foreach ( $roles as $role) {
                $role->grant('update transactions balance');
            }
        }
        $this->info('All Done');
    }
}

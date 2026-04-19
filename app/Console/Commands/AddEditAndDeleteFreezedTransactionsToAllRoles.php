<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class AddEditAndDeleteFreezedTransactionsToAllRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:edit-and-delete-freezed-transactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will add edit / delete freezed transactions permission to all roles ';

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
        foreach ( Role::get() as $role) {
            $role->grant('edit/delete freezed transactions after business day');
        }
    }
}

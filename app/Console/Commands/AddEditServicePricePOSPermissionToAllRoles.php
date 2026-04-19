<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;

class AddEditServicePricePOSPermissionToAllRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fandaqah:grant-edit-service-price-pos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command will add edit service price from pos to admin role only';

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
        $roles = Role::where('slug' , 'admin')->get();
        if(count($roles)){
            foreach ( $roles as $role) {
                $role->grant('edit service price pos');
            }
        }
        $this->info('All Done');
    }
}

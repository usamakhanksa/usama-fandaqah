<?php

namespace App\Console\Commands;

use App\Occupied;
use App\Role;
use App\Team;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateApp extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:app';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update App';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $teams = Team::whereDoesntHave('roles', function ($query) {
        //     $query->where('slug', '=', 'housekeeping');
        // })->get();

        // foreach ( Role::where('slug', 'housekeeping')->get() as $role) {
        //     $role->grant('view reservations');
        // }

        // foreach ( Role::where('slug', 'staff')->get() as $role) {
        //     $role->grant('add service transaction from reservation');
        //     $role->grant('edit service transaction from reservation');
        //     $role->grant('delete service transaction from reservation');
        //     $role->grant('add service transaction from pos');
        //     $role->grant('edit service transaction from pos');
        //     $role->grant('delete service transaction from pos');
        // }

        foreach (Role::where('slug', 'admin')->get() as $role) {
            foreach (config('novapermissions.permissions') as $key => $permissions) {
                $role->grant($key);
            }
        }


    }
}

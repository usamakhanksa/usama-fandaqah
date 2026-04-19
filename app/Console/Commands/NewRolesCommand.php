<?php

namespace App\Console\Commands;

use App\Role;
use App\Team;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pktharindu\NovaPermissions\Permission;

class NewRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:roles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'import new roles criteria with permissions';

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
        $team_id = $this->ask('What is your team id ?');
        if (!$team_id) {
            return $this->error('Your team id is required');
        }
        $team = Team::find($team_id);
        $team_users_ids = collect(DB::table('team_users')->select('user_id')->where('team_id', $team_id)->get())->pluck('user_id');
        $users = User::whereIn('id', $team_users_ids)->withTrashed()->get();
        $not_admin_users = [];
        foreach ($users as $user) {
            if (!$user->isAdmin()) {
                $not_admin_users[] = $user;
            }
        }
        // dd($not_admin_users);
        // dd($team->roles);
        foreach ($team->roles as $role) {
            if ($role->slug != 'admin') {
                // detach from user
                foreach ($not_admin_users as $user) {
                    // $user->roles()->dettach($role);
                    DB::table('role_user')->where('user_id', $user->id)->delete();
                }
                // revoke all permissions
                $role->revokeAll();
                // delete role
                $role->delete();
            }
        }
        $roles_array = [
            'Reception',
            'Housekeeping',
            'Acc Br',
            'Hotel Manager',
            'Service',
            'Sales',
            'Acc Ho',
            'Reservation',
            'HK',
            'Maintenance',
            'POS'
        ];

        $permissions = [];
        $permissions['Reception'] = explode(',', 'add comments,add customers,add guests,add notes,add payments,add receipts,add service transaction from pos,add service transaction from reservation,add services,change to available,change to under cleaning,change to under maintenance,check-in customer,check-out customer,checkin debtor customer,create companies,create companies notes,create financial,create reservations,delete guests,edit customer,edit notes,edit reservations,extend reservations,manage customers,reservation price,show customers rating,show safe balance,show statistics in reservation management,view calenders,view comments,view companies,view companies notes,view company profile,view contract,view customers,view financial,view guests,view notes,view pos,view reservations,view statements,watch reservations table,watch unit housing');

        $permissions['Housekeeping'] = explode(',', 'view reservations,watch reservations table,watch unit housing');
        $permissions['Acc Br'] = explode(',', 'add comments,add customers,add guests,add notes,add payments,add receipts,add service transaction from pos,add service transaction from reservation,add services,cancel reservations,change service price,change to available,change to under cleaning,change to under maintenance,change unit,check-in customer,check-out customer,checkin debtor customer,create companies,create companies notes,create financial,create reservations,delete comments,delete guests,delete own comments,edit checkin and checkout time,edit customer,edit notes,edit reservations,extend reservations,manage customers,reservation price,show customers rating,show safe balance,show statistics in reservation management,view calenders,view comments,view companies,view companies notes,view contract,view customers,view financial,view guests,view notes,view pos,view reports,view reservations,view services,view statements,watch reservations table,watch unit housing');
        $permissions['Hotel Manager'] = explode(',', 'add comments,add customers,add guests,add notes,add payments,add receipts,add service transaction from pos,add service transaction from reservation,add services,change service price,change to available,change to under cleaning,change to under maintenance,change unit,check-in customer,check-out customer,checkin debtor customer,create companies,create companies notes,create financial,create reservations,delete comments,delete guests,delete own comments,edit checkin and checkout time,edit customer,edit notes,edit reservations,extend reservations,manage customers,reservation price,show customers rating,show safe balance,show statistics in reservation management,view calenders,view comments,view companies,view companies notes,view company profile,view contract,view customers,view financial,view guests,view notes,view pos,view reports,view reservations,view statements,watch reservations table,watch unit housing');
        $permissions['Service'] = explode(',', 'add service transaction from pos,add service transaction from reservation,view pos,add pos date');
        $permissions['Sales'] = explode(',', 'add comments,add customers,add guests,add notes,add payments,add receipts,add service transaction from pos,add service transaction from reservation,create companies,create companies notes,create reservations,delete guests,edit customer,edit notes,extend reservations,manage customers,reservation price,show customers rating,show safe balance,show statistics in reservation management,view calenders,view comments,view companies,view companies notes,view company profile,view contract,view customers,view guests,view notes,view pos,view reports,view reservations,view statements,watch reservations table,watch unit housing');
        $permissions['Acc Ho'] = explode(',', 'add comments,add customers,add guests,add notes,add payments,add receipts,add service transaction from pos,add service transaction from reservation,add services,booking past,booking without min price,cancel reservations,change service price,change to available,change to under cleaning,change to under maintenance,change transactions date,change unit,check-in customer,check-out customer,checkin debtor customer,create companies,create companies notes,create financial,create reservations,delete comments,delete companies notes,delete customers,delete financial,delete guests,delete own comments,delete own statements,delete reservations,delete service transaction from pos,delete service transaction from reservation,delete statements,edit checkin and checkout time,edit customer,edit financial,edit notes,edit payments,edit receipts,edit reservations,edit service transaction from pos,edit service transaction from reservation,extend reservations,liquidation of dues before departure,manage customers,open closed contract,reservation price,show customers rating,show safe balance,show statistics in reservation management,update companies,update companies notes,view calenders,view comments,view companies,view companies notes,view company profile,view contract,view customers,view financial,view guests,view notes,view online payment,view pos,view reports,view reservations,view services,view statements,watch reservations table,watch unit housing');
        $permissions['Reservation'] = explode(',', 'add comments,add customers,add guests,add notes,add payments,add receipts,add service transaction from pos,add service transaction from reservation,create companies,create companies notes,create reservations,delete guests,edit customer,edit notes,extend reservations,manage customers,reservation price,show customers rating,show safe balance,show statistics in reservation management,view calenders,view comments,view companies,view companies notes,view contract,view customers,view guests,view notes,view pos,view reports,view reservations,view statements,watch reservations table,watch unit housing');
        $permissions['HK'] = explode(',', 'change to available,change to under cleaning,change to under maintenance,view calenders,watch reservations table,watch unit housing');
        $permissions['Maintenance'] = explode(',', 'change to available,change to under cleaning,change to under maintenance,view calenders,watch reservations table,watch unit housing');
        $permissions['POS'] = explode(',', 'view pos,add pos date,add service transaction from pos,edit service transaction from pos,delete service transaction from pos');

        foreach ($roles_array as $role_name) {
            $role = new Role();
            $role->name = $role_name;
            $role->slug = strtolower(str_replace(' ', '_', $role_name));
            $role->team_id = $team_id;
            $role->deletable = 1;
            $role->save();

            foreach ($permissions[$role_name] as $permission_slug) {
                Permission::create([
                    'role_id' => $role->id,
                    'permission_slug' => $permission_slug,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}

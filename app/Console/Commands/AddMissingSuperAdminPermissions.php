<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class AddMissingSuperAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:add-missing-superadmin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add missing permissions to Super Admin role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = Role::where('name', 'Super Admin')->first();
        
        if (!$role) {
            $this->error('Super Admin role not found.');
            return 1;
        }

        $perm1 = Permission::where('slug', 'watch reservations table')->first();
        $perm2 = Permission::where('slug', 'watch unit housing')->first();
        
        if (!$perm1 || !$perm2) {
            $this->error('One or both permissions not found.');
            return 1;
        }

        // Attach permissions if they're not already attached
        $existingPerms = $role->permissions->pluck('id')->toArray();
        
        $permsToAdd = [];
        if (!in_array($perm1->id, $existingPerms)) {
            $permsToAdd[] = $perm1->id;
        }
        
        if (!in_array($perm2->id, $existingPerms)) {
            $permsToAdd[] = $perm2->id;
        }
        
        if (!empty($permsToAdd)) {
            $role->permissions()->attach($permsToAdd);
            $this->info('Permissions added successfully to Super Admin role.');
        } else {
            $this->info('Super Admin role already has these permissions.');
        }

        return 0;
    }
}
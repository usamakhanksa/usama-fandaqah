<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class EnsureSidebarPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:ensure-sidebar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all required sidebar permissions are granted to Super Admin';

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
            $this->error('One or both permissions not found in the database.');
            return 1;
        }

        // Check if permissions are already granted
        $existingPerms = $role->permissions->pluck('id')->toArray();
        
        $addedCount = 0;
        if (!in_array($perm1->id, $existingPerms)) {
            $role->permissions()->attach($perm1->id);
            $this->info("Added 'watch reservations table' permission to Super Admin role.");
            $addedCount++;
        }
        
        if (!in_array($perm2->id, $existingPerms)) {
            $role->permissions()->attach($perm2->id);
            $this->info("Added 'watch unit housing' permission to Super Admin role.");
            $addedCount++;
        }
        
        if ($addedCount === 0) {
            $this->info('Super Admin role already has all required permissions.');
        } else {
            $this->info("$addedCount permissions added successfully to Super Admin role.");
        }

        return 0;
    }
}
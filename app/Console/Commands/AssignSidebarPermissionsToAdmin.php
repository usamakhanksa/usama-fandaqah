<?php

namespace App\Console\Commands;

use App\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AssignSidebarPermissionsToAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:sidebar-permissions-to-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign all sidebar permissions to the admin role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Find the admin role - try both "Admin" and "Super Admin"
        $adminRole = Role::where('name', 'Admin')->first();
        $roleName = 'Admin';
        
        if (!$adminRole) {
            $adminRole = Role::where('name', 'Super Admin')->first();
            $roleName = 'Super Admin';
        }
        
        if (!$adminRole) {
            $this->error('Admin or Super Admin role not found!');
            
            // Show available roles
            $roles = Role::all();
            if ($roles->count() > 0) {
                $this->info('Available roles:');
                foreach ($roles as $role) {
                    $this->line("- {$role->name} (ID: {$role->id})");
                }
            } else {
                $this->error('No roles found in the system.');
            }
            
            return 1;
        }

        $this->info("Assigning permissions to {$roleName} role (ID: {$adminRole->id})...");

        // Define all permissions from the sidebar configuration
        $sidebarConfig = config('sidebar', []);
        $permissionsAssigned = 0;
        $permissionsSkipped = 0;
        
        foreach ($sidebarConfig as $item) {
            // Check if the top-level item has a permission
            if (!empty($item['permission'])) {
                $permission = DB::table('permissions')->where('name', $item['permission'])->first();
                
                if ($permission) {
                    // Check if the relationship already exists
                    $exists = DB::table('permission_role')
                        ->where('role_id', $adminRole->id)
                        ->where('permission_id', $permission->id)
                        ->first();
                    
                    if (!$exists) {
                        // Insert the relationship with full permissions
                        DB::table('permission_role')->insert([
                            'role_id' => $adminRole->id,
                            'permission_id' => $permission->id,
                            'enabled' => true,
                            'anyone' => true,
                            'can_create' => true,
                            'can_edit' => true,
                            'can_view' => true,
                            'can_remove' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        $this->info("Granted permission: {$item['permission']}");
                        $permissionsAssigned++;
                    } else {
                        $this->line("Permission already granted: {$item['permission']}");
                    }
                } else {
                    $this->warn("Permission not found in DB: {$item['permission']}");
                    $permissionsSkipped++;
                }
            }

            // Check permissions for child items
            if (!empty($item['children'])) {
                foreach ($item['children'] as $child) {
                    if (!empty($child['permission'])) {
                        $permission = DB::table('permissions')->where('name', $child['permission'])->first();
                        
                        if ($permission) {
                            // Check if the relationship already exists
                            $exists = DB::table('permission_role')
                                ->where('role_id', $adminRole->id)
                                ->where('permission_id', $permission->id)
                                ->first();
                            
                            if (!$exists) {
                                // Insert the relationship with full permissions
                                DB::table('permission_role')->insert([
                                    'role_id' => $adminRole->id,
                                    'permission_id' => $permission->id,
                                    'enabled' => true,
                                    'anyone' => true,
                                    'can_create' => true,
                                    'can_edit' => true,
                                    'can_view' => true,
                                    'can_remove' => true,
                                    'created_at' => now(),
                                    'updated_at' => now(),
                                ]);
                                
                                $this->info("Granted permission: {$child['permission']}");
                                $permissionsAssigned++;
                            } else {
                                $this->line("Permission already granted: {$child['permission']}");
                            }
                        } else {
                            $this->warn("Permission not found in DB: {$child['permission']}");
                            $permissionsSkipped++;
                        }
                    }
                }
            }
        }

        $this->info("Successfully assigned {$permissionsAssigned} permissions to the {$roleName} role.");
        if ($permissionsSkipped > 0) {
            $this->warn("{$permissionsSkipped} permissions were skipped (not found in database).");
        }
        
        return 0;
    }
}
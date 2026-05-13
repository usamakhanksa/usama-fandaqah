<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSuperAdminPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:super-admin-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Copies permissions from permission_role to role_permission table for Super Admin role';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Find the Super Admin role
        $adminRole = \App\Role::where('name', 'Super Admin')->first();
        
        if (!$adminRole) {
            $this->error('Super Admin role not found!');
            return 1;
        }

        $this->info("Found Super Admin role (ID: {$adminRole->id})");

        // Get all permissions assigned to the Super Admin in the permission_role table
        $permissionRoleRecords = DB::table('permission_role')
            ->join('permissions', 'permission_role.permission_id', '=', 'permissions.id')
            ->where('permission_role.role_id', $adminRole->id)
            ->select('permissions.slug as permission_slug') // Use the slug field which matches the sidebar permissions
            ->distinct()
            ->get();

        $this->info("Found {$permissionRoleRecords->count()} permissions in permission_role table");

        $copiedCount = 0;
        
        foreach ($permissionRoleRecords as $record) {
            // Check if the permission already exists in role_permission table for this role
            $exists = DB::table('role_permission')
                ->where('role_id', $adminRole->id)
                ->where('permission_slug', $record->permission_slug)
                ->first();

            if (!$exists) {
                // Insert the permission into role_permission table
                DB::table('role_permission')->insert([
                    'role_id' => $adminRole->id,
                    'permission_slug' => $record->permission_slug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                $this->info("Added permission: {$record->permission_slug}");
                $copiedCount++;
            } else {
                $this->line("Permission already exists: {$record->permission_slug}");
            }
        }

        // Add any missing permissions that might be required by the dashboard
        $additionalPermissions = [
            'view ar dashboard',
            'view cashier shifts', 
            'view commissions'
        ];
        
        foreach ($additionalPermissions as $permission) {
            $exists = DB::table('role_permission')
                ->where('role_id', $adminRole->id)
                ->where('permission_slug', $permission)
                ->first();

            if (!$exists) {
                // Check if this permission exists in the permissions table
                $permissionExists = DB::table('permissions')->where('slug', $permission)->first();
                
                if ($permissionExists) {
                    DB::table('role_permission')->insert([
                        'role_id' => $adminRole->id,
                        'permission_slug' => $permission,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $this->info("Added missing permission: {$permission}");
                    $copiedCount++;
                } else {
                    // Create the permission if it doesn't exist
                    DB::table('permissions')->insert([
                        'name' => ucwords(str_replace('_', ' ', str_replace('-', ' ', $permission))),
                        'slug' => $permission,
                        'group' => 'dashboard',
                        'module' => $permission,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    DB::table('role_permission')->insert([
                        'role_id' => $adminRole->id,
                        'permission_slug' => $permission,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $this->info("Created and added missing permission: {$permission}");
                    $copiedCount++;
                }
            }
        }

        $this->info("Successfully copied {$copiedCount} permissions to role_permission table.");
        $this->info("Super Admin should now have all permissions properly recognized by the system.");

        return 0;
    }
}
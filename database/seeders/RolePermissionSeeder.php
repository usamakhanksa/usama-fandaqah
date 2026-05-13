<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use DB;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = config('novapermissions.permissions');
        
        // Create default roles for each team if teams exist
        $teams = \App\Models\Team::all();
        
        foreach ($teams as $team) {
            // Super Admin role - has all permissions
            $superAdminRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'super-admin-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Super Admin',
                'deletable' => 0,
            ]);
            
            // Hotel Owner role
            $hotelOwnerRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'hotel-owner-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Hotel Owner',
                'deletable' => 0,
            ]);
            
            // General Manager role
            $generalManagerRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'general-manager-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'General Manager',
                'deletable' => 0,
            ]);
            
            // Front Desk Manager role
            $frontDeskManagerRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'front-desk-manager-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Front Desk Manager',
                'deletable' => 0,
            ]);
            
            // Front Desk Agent role
            $frontDeskAgentRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'front-desk-agent-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Front Desk Agent',
                'deletable' => 0,
            ]);
            
            // Housekeeping Supervisor role
            $housekeepingSupervisorRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'housekeeping-supervisor-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Housekeeping Supervisor',
                'deletable' => 0,
            ]);

            // Housekeeper role
            $housekeeperRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'housekeeper-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Housekeeper',
                'deletable' => 0,
            ]);
            
            // Maintenance User role
            $maintenanceUserRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'maintenance-user-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Maintenance User',
                'deletable' => 0,
            ]);
            
            // Accountant role
            $accountantRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'accountant-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Accountant',
                'deletable' => 0,
            ]);
            
            // Cashier role
            $cashierRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'cashier-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Cashier',
                'deletable' => 0,
            ]);
            
            // Revenue Manager role
            $revenueManagerRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'revenue-manager-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Revenue Manager',
                'deletable' => 0,
            ]);
            
            // Marketing Manager role
            $marketingManagerRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'marketing-manager-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Marketing Manager',
                'deletable' => 0,
            ]);
            
            // Auditor role
            $auditorRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'auditor-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Auditor',
                'deletable' => 0,
            ]);
            
            // Integration Admin role
            $integrationAdminRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'integration-admin-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Integration Admin',
                'deletable' => 0,
            ]);
            
            // Read-only Viewer role
            $readOnlyViewerRole = Role::withoutGlobalScopes()->updateOrCreate([
                'slug' => 'read-only-viewer-'.$team->id,
                'team_id' => $team->id,
            ], [
                'name' => 'Read-only Viewer',
                'deletable' => 0,
            ]);
            
            // Assign permissions based on role responsibilities
            
            // Super Admin gets all permissions
            foreach (array_keys($permissions) as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $superAdminRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Hotel Owner permissions
            $hotelOwnerPermissions = [
                'view reservations', 'create reservations', 'update reservations', 'delete reservations', 
                'checkin reservations', 'checkout reservations', 'cancel reservations', 'transfer reservations', 
                'extend reservations', 'noshow reservations', 'export reservations',
                'view units', 'create units', 'update units', 'delete units', 'status units', 
                'maintenance units', 'cleaning units', 'export units',
                'view transactions', 'create transactions', 'update transactions', 'reverse transactions', 
                'export transactions',
                'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.delete', 'invoice.send', 
                'invoice.send_zatca', 'invoice.void', 'invoice.print', 'invoice.export',
                'view receipts', 'create receipts', 'edit receipts', 'delete receipts', 'cancel receipts', 
                'print receipts', 'export receipts',
                'view payments', 'create payments', 'edit payments', 'delete payments', 'complete payments', 
                'reverse payments', 'print payments', 'export payments',
                'view night-audit', 'run night-audit', 'rerun night-audit', 'export night-audit',
                'view settings', 'update settings',
                'view users', 'create users', 'update users', 'delete users',
                'view integrations', 'update integrations', 'test integrations',
                'view reports', 'export reports',
                'view guests', 'create guests', 'update guests', 'delete guests', 'export guests',
                'view companies', 'create companies', 'update companies', 'delete companies', 'export companies',
                'view services', 'create services', 'update services', 'delete services', 'export services',
                'view promissory notes', 'create promissory notes', 'update promissory notes', 
                'delete promissory notes', 'collect promissory notes', 'export promissory notes'
            ];
            
            foreach ($hotelOwnerPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $hotelOwnerRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // General Manager permissions (similar to Hotel Owner but with limited user management)
            $generalManagerPermissions = [
                'view reservations', 'create reservations', 'update reservations', 'checkin reservations', 
                'checkout reservations', 'cancel reservations', 'transfer reservations', 
                'extend reservations', 'noshow reservations', 'export reservations',
                'view units', 'create units', 'update units', 'status units', 
                'maintenance units', 'cleaning units', 'export units',
                'view transactions', 'create transactions', 'update transactions', 'reverse transactions', 
                'export transactions',
                'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.send', 'invoice.send_zatca', 
                'invoice.void', 'invoice.print', 'invoice.export',
                'view receipts', 'create receipts', 'edit receipts', 'cancel receipts', 
                'print receipts', 'export receipts',
                'view payments', 'create payments', 'edit payments', 'complete payments', 
                'reverse payments', 'print payments', 'export payments',
                'view night-audit', 'run night-audit', 'rerun night-audit', 'export night-audit',
                'view settings', 'update settings',
                'view users', 'create users', 'update users',
                'view integrations', 'test integrations',
                'view reports', 'export reports',
                'view guests', 'create guests', 'update guests', 'export guests',
                'view companies', 'create companies', 'update companies', 'export companies',
                'view services', 'create services', 'update services', 'export services',
                'view promissory notes', 'create promissory notes', 'update promissory notes', 
                'collect promissory notes', 'export promissory notes'
            ];
            
            foreach ($generalManagerPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $generalManagerRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Front Desk Manager permissions
            $frontDeskManagerPermissions = [
                'view reservations', 'create reservations', 'update reservations', 'checkin reservations', 
                'checkout reservations', 'cancel reservations', 'transfer reservations', 
                'extend reservations', 'noshow reservations', 'export reservations',
                'view units', 'status units', 'export units',
                'view transactions', 'create transactions', 'update transactions', 
                'export transactions',
                'view invoices', 'create invoices', 'export invoices',
                'view receipts', 'create receipts', 'edit receipts', 'print receipts', 'export receipts',
                'view payments', 'create payments', 'edit payments', 'complete payments', 
                'print payments', 'export payments',
                'view guests', 'create guests', 'update guests', 'export guests',
                'view companies', 'create companies', 'update companies', 'export companies',
                'view reports', 'export reports',
            ];
            
            foreach ($frontDeskManagerPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $frontDeskManagerRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Front Desk Agent permissions
            $frontDeskAgentPermissions = [
                'view reservations', 'create reservations', 'update reservations', 'checkin reservations', 
                'checkout reservations', 'cancel reservations', 'export reservations',
                'view units', 'status units', 'export units',
                'view transactions', 'create transactions', 'export transactions',
                'view invoices', 'create invoices', 'export invoices',
                'view receipts', 'create receipts', 'print receipts', 'export receipts',
                'view payments', 'create payments', 'complete payments', 'print payments', 'export payments',
                'view guests', 'create guests', 'update guests', 'export guests',
            ];
            
            foreach ($frontDeskAgentPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $frontDeskAgentRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Housekeeping Supervisor permissions
            $housekeepingSupervisorPermissions = [
                'view units', 'status units', 'export units',
                'view reservations', 'export reservations',
                'view guests', 'export guests',
            ];
            
            foreach ($housekeepingSupervisorPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $housekeepingSupervisorRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Housekeeper permissions
            $housekeeperPermissions = [
                'view units', 'status units',
                'view reservations',
            ];
            
            foreach ($housekeeperPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $housekeeperRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Maintenance User permissions
            $maintenanceUserPermissions = [
                'view units', 'status units', 'maintenance units',
                'view reservations', 'export reservations',
                'view guests', 'export guests',
            ];
            
            foreach ($maintenanceUserPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $maintenanceUserRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Accountant permissions
            $accountantPermissions = [
                'view transactions', 'update transactions', 'reverse transactions', 
                'export transactions',
                'invoice.view', 'invoice.create', 'invoice.edit', 'invoice.send', 'invoice.send_zatca', 
                'invoice.void', 'invoice.print', 'invoice.export',
                'view receipts', 'create receipts', 'edit receipts', 'cancel receipts', 
                'print receipts', 'export receipts',
                'view payments', 'create payments', 'edit payments', 'complete payments', 
                'reverse payments', 'print payments', 'export payments',
                'view night-audit', 'export night-audit',
                'view reports', 'export reports',
                'view promissory notes', 'update promissory notes', 
                'collect promissory notes', 'export promissory notes'
            ];
            
            foreach ($accountantPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $accountantRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Cashier permissions
            $cashierPermissions = [
                'view transactions', 'create transactions', 'export transactions',
                'invoice.view', 'invoice.create', 'invoice.print', 'invoice.export',
                'view receipts', 'create receipts', 'print receipts', 'export receipts',
                'view payments', 'create payments', 'complete payments', 'print payments', 'export payments',
                'view reports', 'export reports',
                'view promissory notes', 'collect promissory notes'
            ];
            
            foreach ($cashierPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $cashierRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Revenue Manager permissions
            $revenueManagerPermissions = [
                'view reservations', 'export reservations',
                'view reports', 'export reports',
                'view guests', 'export guests',
                'view companies', 'export companies',
            ];
            
            foreach ($revenueManagerPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $revenueManagerRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Marketing Manager permissions
            $marketingManagerPermissions = [
                'view reservations', 'export reservations',
                'view reports', 'export reports',
                'view guests', 'export guests',
                'view companies', 'export companies',
            ];
            
            foreach ($marketingManagerPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $marketingManagerRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Auditor permissions
            $auditorPermissions = [
                'view transactions', 'export transactions',
                'view invoices', 'export invoices',
                'view night-audit', 'export night-audit',
                'view reports', 'export reports',
                'view promissory notes', 'export promissory notes'
            ];
            
            foreach ($auditorPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $auditorRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Integration Admin permissions
            $integrationAdminPermissions = [
                'view integrations', 'update integrations', 'test integrations',
                'view settings', 'update settings',
                'view reports', 'export reports',
            ];
            
            foreach ($integrationAdminPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $integrationAdminRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Read-only Viewer permissions
            $readOnlyViewerPermissions = [
                'view reservations', 'export reservations',
                'view units', 'export units',
                'view transactions', 'export transactions',
                'view invoices', 'export invoices',
                'view night-audit', 'export night-audit',
                'view reports', 'export reports',
                'view guests', 'export guests',
                'view companies', 'export companies',
                'view services', 'export services',
                'view promissory notes', 'export promissory notes'
            ];
            
            foreach ($readOnlyViewerPermissions as $permission) {
                DB::table(config('novapermissions.role_permission_table'))->insertOrIgnore([
                    'role_id' => $readOnlyViewerRole->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
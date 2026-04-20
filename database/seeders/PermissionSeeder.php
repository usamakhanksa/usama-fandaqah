<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $roleNames = ['Super Admin','Manager','Receptionist','Accountant','Service Staff','Fandaqah Manager','Hotel Manager','Parking Man','Porter','Concierge','Spa Manager','Housekeeper','Cleaning Manager','Maintenance Supervisor','Event Planner','Hotel Receptionist'];
        foreach ($roleNames as $roleName) {
            DB::table('roles')->updateOrInsert(
                ['slug' => str($roleName)->slug()],
                ['name' => $roleName, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 2. Permissions
        $permissions = [
            'Reservations Management', 'Room Inventory', 'Financial Reports', 'Bills Processing',
            'Receipts Approval', 'Expense Review', 'POS Transactions', 'Services Management',
            'User Assignment', 'Dashboard Analytics', 'Guest Profiles', 'Company Profiles',
            'Unit Status Control', 'Check-In Operations', 'Check-Out Operations', 'Booking Files',
            'Credit Notes', 'Fund Movement Reports', 'Audit Logs', 'Managerial Reports',
        ];

        foreach ($permissions as $permissionName) {
            DB::table('permissions')->updateOrInsert(
                ['slug' => str($permissionName)->slug()],
                [
                    'name' => $permissionName,
                    'group' => 'operations',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 3. Assign all permissions to Super Admin role
        $superAdmin = DB::table('roles')->where('slug', 'super-admin')->first();
        $permissionIds = DB::table('permissions')->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $superAdmin->id, 'permission_id' => $permissionId],
                [
                    'enabled' => true,
                    'anyone' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_view' => true,
                    'can_remove' => true,
                    'updated_at' => now()
                ]
            );
        }
    }
}

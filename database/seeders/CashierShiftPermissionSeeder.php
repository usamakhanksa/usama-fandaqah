<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class CashierShiftPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Cashier Shifts', 'slug' => 'cashier_shifts.view', 'group' => 'Finance'],
            ['name' => 'Open Cashier Shift', 'slug' => 'cashier_shifts.open', 'group' => 'Finance'],
            ['name' => 'Close Cashier Shift', 'slug' => 'cashier_shifts.close', 'group' => 'Finance'],
            ['name' => 'Approve Cashier Shift', 'slug' => 'cashier_shifts.approve', 'group' => 'Finance'],
            ['name' => 'Reject Cashier Shift', 'slug' => 'cashier_shifts.reject', 'group' => 'Finance'],
            ['name' => 'Export Cashier Shifts', 'slug' => 'cashier_shifts.export', 'group' => 'Finance'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Assign to Admin role
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $permissionIds = Permission::whereIn('slug', array_column($permissions, 'slug'))->pluck('id')->toArray();
            foreach ($permissionIds as $id) {
                $admin->permissions()->syncWithoutDetaching([$id => [
                    'enabled' => 1,
                    'anyone' => 1,
                    'can_create' => 1,
                    'can_edit' => 1,
                    'can_view' => 1,
                    'can_remove' => 1,
                ]]);
            }
        }
    }
}

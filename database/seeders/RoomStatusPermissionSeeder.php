<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class RoomStatusPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Room Status Log', 'slug' => 'room_status.view', 'group' => 'Operations'],
            ['name' => 'Export Room Status Log', 'slug' => 'room_status.export', 'group' => 'Operations'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['slug' => $p['slug']], $p);
        }

        // Assign to admin role
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

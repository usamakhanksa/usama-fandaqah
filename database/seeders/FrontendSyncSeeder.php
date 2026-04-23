<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FrontendSyncSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage ar',
            'manage activities',
            'manage blocks',
            'manage cashiering',
            'manage comp accounting',
            'manage customer groups settings',
            'manage document settings',
            'manage events',
            'manage exports',
            'manage facility settings',
            'manage finance settings',
            'manage front desk workspace',
            'manage general settings',
            'manage header slider settings',
            'manage housekeeping',
            'manage included services settings',
            'manage integration settings',
            'manage interfaces',
            'manage ledger numbers settings',
            'manage maintenance settings',
            'manage memberships',
            'manage notifications settings',
            'manage profiles',
            'manage rating settings',
            'manage reservation resource settings',
            'manage reservations',
            'manage restrictions',
            'manage rooms',
            'manage sales',
            'manage service requests',
            'manage users and roles settings',
            'manage website settings',
            'run night audit',
            'view activity logs settings',
            'view arrivals',
            'view cleaning movement report',
            'view customer movement report',
            'view daily report',
            'view dashboard',
            'view departures',
            'view deposits report',
            'view employee contracts report',
            'view in-house',
            'view invoices report',
            'view maintenance movement report',
            'view monthly report',
            'view occupancy ratio report',
            'view reservation resources report',
            'view reservation transfers report',
            'view revenues taxes report',
            'view safe movement report',
            'view services report',
            'view units movement report',
            'view withdraws report',
        ];

        // Ensure Super Admin role exists
        $roleId = DB::table('roles')->updateOrInsert(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'updated_at' => now(), 'created_at' => now()]
        );
        
        $role = DB::table('roles')->where('slug', 'super-admin')->first();

        foreach ($permissions as $p) {
            $slug = $p; // The frontend uses the string itself as the slug in sidebarConfig
            $permId = DB::table('permissions')->updateOrInsert(
                ['slug' => $slug],
                ['name' => ucwords($p), 'group' => 'system', 'updated_at' => now(), 'created_at' => now()]
            );
            
            $perm = DB::table('permissions')->where('slug', $slug)->first();

            DB::table('permission_role')->updateOrInsert(
                ['role_id' => $role->id, 'permission_id' => $perm->id],
                [
                    'enabled' => true,
                    'can_view' => true,
                    'can_create' => true,
                    'can_edit' => true,
                    'can_remove' => true,
                    'updated_at' => now(),
                    'created_at' => now()
                ]
            );
        }
        
        // Ensure the admin user has the super-admin role
        DB::table('users')->where('email', 'admin@fandaqah.pms')->update(['role_id' => $role->id]);
        DB::table('users')->where('email', 'aya@hotel.test')->update(['role_id' => $role->id]);
    }
}

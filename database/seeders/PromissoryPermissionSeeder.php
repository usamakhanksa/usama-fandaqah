<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class PromissoryPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'View Promissory Notes', 'slug' => 'promissory_notes.view', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Create Promissory Notes', 'slug' => 'promissory_notes.create', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Edit Promissory Notes', 'slug' => 'promissory_notes.edit', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Delete Promissory Notes', 'slug' => 'promissory_notes.delete', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Collect Promissory Notes', 'slug' => 'promissory_notes.collect', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Reverse Promissory Collections', 'slug' => 'promissory_notes.reverse', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Renew Promissory Notes', 'slug' => 'promissory_notes.renew', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Cancel Promissory Notes', 'slug' => 'promissory_notes.cancel', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Export Promissory Notes', 'slug' => 'promissory_notes.export', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'View Promissory Collections', 'slug' => 'promissory_collections.view', 'group' => 'Finance', 'module' => 'finance'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        $roles = Role::whereIn('name', ['Admin', 'Super Admin', 'Finance Manager'])->get();
        foreach ($roles as $role) {
            foreach ($permissions as $p) {
                DB::table('role_permission')->insertOrIgnore([
                    'role_id' => $role->id,
                    'permission_slug' => $p['slug'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class CreditNotePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'View Credit Notes', 'slug' => 'credit_notes.view', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Create Credit Notes', 'slug' => 'credit_notes.create', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Edit Credit Notes', 'slug' => 'credit_notes.edit', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Delete Credit Notes', 'slug' => 'credit_notes.delete', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Cancel Credit Notes', 'slug' => 'credit_notes.cancel', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Submit Credit Notes to ZATCA', 'slug' => 'credit_notes.zatca_submit', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Download Credit Notes XML', 'slug' => 'credit_notes.zatca_download', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Export Credit Notes', 'slug' => 'credit_notes.export', 'group' => 'Finance', 'module' => 'finance'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

        // Assign to Admin roles
        $roles = Role::whereIn('name', ['Admin', 'Super Admin'])->get();
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

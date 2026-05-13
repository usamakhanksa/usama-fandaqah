<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class InvoiceTransferPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            ['name' => 'View Invoice Transfers', 'slug' => 'invoice_transfers.view', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Create Invoice Transfers', 'slug' => 'invoice_transfers.create', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Approve Invoice Transfers', 'slug' => 'invoice_transfers.approve', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Reject Invoice Transfers', 'slug' => 'invoice_transfers.reject', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Reverse Invoice Transfers', 'slug' => 'invoice_transfers.reverse', 'group' => 'Finance', 'module' => 'finance'],
            ['name' => 'Export Invoice Transfers', 'slug' => 'invoice_transfers.export', 'group' => 'Finance', 'module' => 'finance'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['slug' => $p['slug']], $p);
        }

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

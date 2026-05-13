<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinanceModulesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'banks.view' => 'View Banks',
            'banks.create' => 'Create Banks',
            'banks.edit' => 'Edit Banks',
            'banks.delete' => 'Delete Banks',
            'senders.view' => 'View Senders',
            'senders.create' => 'Create Senders',
            'senders.edit' => 'Edit Senders',
            'senders.delete' => 'Delete Senders',
            'commission_payments.view' => 'View Commission Payments',
            'commission_payments.create' => 'Create Commission Payments',
            'commission_payments.edit' => 'Edit Commission Payments',
            'commission_payments.delete' => 'Delete Commission Payments',
            'commission_payments.export' => 'Export Commission Payments',
        ];

        $roles = DB::table('roles')->get();
        $now = now();

        foreach ($roles as $role) {
            if (in_array($role->name, ['Super Admin', 'Accountant', 'General Manager', 'Admin', 'hotel_manager'])) {
                foreach ($permissions as $slug => $name) {
                    DB::table('role_permission')->insertOrIgnore([
                        'role_id' => $role->id,
                        'permission_slug' => $slug,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }
}

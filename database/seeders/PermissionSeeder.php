<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'receipts.view' => 'View Receipts',
            'receipts.create' => 'Create Receipts',
            'receipts.edit' => 'Edit Receipts',
            'receipts.delete' => 'Delete Receipts',
            'receipts.cancel' => 'Cancel Receipts',
            'receipts.print' => 'Print Receipts',
            'receipts.export' => 'Export Receipts',
            'payments.view' => 'View Payments',
            'payments.create' => 'Create Payments',
            'payments.edit' => 'Edit Payments',
            'payments.delete' => 'Delete Payments',
            'payments.confirm' => 'Confirm Payments',
            'payments.cancel' => 'Cancel Payments',
            'payments.reverse' => 'Reverse Payments',
            'payments.print' => 'Print Payments',
            'payments.export' => 'Export Payments',
            'invoices.view' => 'View Invoices',
            'invoices.create' => 'Create Invoices',
            'invoices.edit' => 'Edit Invoices',
            'invoices.delete' => 'Delete Invoices',
            'invoices.cancel' => 'Cancel Invoices',
            'invoices.zatca_submit' => 'Submit to ZATCA',
            'invoices.zatca_download' => 'Download ZATCA XML',
            'invoices.print' => 'Print Invoices',
            'invoices.export' => 'Export Invoices',
            'invoices.mark_paid' => 'Mark Invoice as Paid',
            'reports.view' => 'View Reports Dashboard',
            'reports.daily' => 'View Daily Reports',
            'reports.occupancy' => 'View Occupancy Reports',
            'reports.revenue' => 'View Revenue Reports',
            'reports.export' => 'Export Reports',
            'reports.adr_revpar' => 'View ADR & RevPAR Reports',
            'reports.custom_create' => 'Create Custom Reports',
            'reports.custom_run' => 'Run Custom Reports',
            'reports.custom_export' => 'Export Custom Reports',
            'reports.schedule_create' => 'Create Report Schedules',
            'reports.schedule_manage' => 'Manage Report Schedules',
        ];

        // Ensure permissions are added to the config-based permission system if it exists
        // Or just add them to the role_permission table for relevant roles
        
        $roles = DB::table('roles')->get();
        $now = now();

        foreach ($roles as $role) {
            // Super Admin bypasses via Gate::before, but we can add for clarity
            // Accountant and Admin should definitely have them
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

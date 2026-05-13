<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds Phase 1/2 permissions into existing roles.
 *
 * Role slug mapping (existing roles are NOT modified, only new permission_slugs are added):
 *  - admin            → gets ALL new permissions
 *  - hotel_manager    → gets operational + reporting permissions (no ETL/approve)
 *  - Staff            → gets view-only permissions
 */
class PmsPermissionSeeder extends Seeder
{
    /**
     * All new Phase 1/2 permission slugs grouped by access tier.
     */
    private array $adminOnly = [
        'rerun night audit',
        'approve promissories',
        'approve invoice transfers',
        'approve checkout balance transfers',
        'approve cashier shifts',
        'approve commissions',
        'approve payment corrections',
        'approve rebates',
        'approve room adjustments',
        'manage etl configuration',
        'delete noshow rules',
        'delete company groups',
        'delete early late charges',
    ];

    private array $managerLevel = [
        'view night audit',
        'run night audit',
        'export night audit',
        'view noshow rules',
        'create noshow rules',
        'edit noshow rules',
        'view company groups',
        'create company groups',
        'edit company groups',
        'export company groups',
        'view promissories',
        'create promissories',
        'edit promissories',
        'export promissories',
        'view invoice transfers',
        'create invoice transfers',
        'export invoice transfers',
        'view checkout balance transfers',
        'create checkout balance transfers',
        'view cashier shifts',
        'create cashier shifts',
        'close cashier shifts',
        'export cashier shifts',
        'view room status log',
        'create room status log',
        'export room status log',
        'view commissions',
        'create commissions',
        'edit commissions',
        'export commissions',
        'view early late charges',
        'create early late charges',
        'edit early late charges',
        'view payment corrections',
        'create payment corrections',
        'view rebates',
        'create rebates',
        'export rebates',
        'view room adjustments',
        'create room adjustments',
        'export room adjustments',
        'view dashboard',
        'view revenue dashboard',
        'view occupancy dashboard',
        'view ar dashboard',
        'export dashboard',
        'view etl monitoring',
        'view metabase reports',
    ];

    private array $staffLevel = [
        'view night audit',
        'view noshow rules',
        'view company groups',
        'view promissories',
        'view invoice transfers',
        'view checkout balance transfers',
        'view cashier shifts',
        'view room status log',
        'view commissions',
        'view early late charges',
        'view payment corrections',
        'view rebates',
        'view room adjustments',
        'view dashboard',
    ];

    public function run(): void
    {
        // Get all team-scoped roles (each hotel has its own set of roles)
        $roles = DB::table('roles')->get();

        foreach ($roles as $role) {
            $permissionsToAdd = match ($role->slug) {
                'admin'         => array_merge($this->adminOnly, $this->managerLevel),
                'hotel_manager' => $this->managerLevel,
                'Staff'         => $this->staffLevel,
                default         => [],
            };

            if (empty($permissionsToAdd)) {
                continue;
            }

            // Get existing permissions for this role to avoid duplicates
            $existing = DB::table('role_permission')
                ->where('role_id', $role->id)
                ->pluck('permission_slug')
                ->toArray();

            $inserts = [];
            $now = now();
            foreach ($permissionsToAdd as $slug) {
                if (!in_array($slug, $existing)) {
                    $inserts[] = [
                        'role_id'         => $role->id,
                        'permission_slug' => $slug,
                        'created_at'      => $now,
                        'updated_at'      => $now,
                    ];
                }
            }

            if (!empty($inserts)) {
                // Insert in chunks to avoid memory issues on large deployments
                foreach (array_chunk($inserts, 50) as $chunk) {
                    DB::table('role_permission')->insert($chunk);
                }
            }
        }
    }
}

<?php

require_once __DIR__.'/../vendor/autoload.php';

// Helper functions
function getPermissionGroup($slug) {
    $parts = explode('.', $slug);
    if (count($parts) > 1) {
        return $parts[0];
    }
    
    // Map common prefixes to groups
    if (str_starts_with($slug, 'view ')) {
        return 'view';
    } elseif (str_starts_with($slug, 'sidebar.')) {
        return 'sidebar';
    } elseif (str_starts_with($slug, 'dashboard')) {
        return 'dashboard';
    } elseif (str_starts_with($slug, 'reservations')) {
        return 'reservations';
    } elseif (str_starts_with($slug, 'guests')) {
        return 'guests';
    } elseif (str_starts_with($slug, 'rooms') || str_starts_with($slug, 'units')) {
        return 'rooms';
    } elseif (str_starts_with($slug, 'finance') || str_starts_with($slug, 'ar') || str_starts_with($slug, 'cashier')) {
        return 'finance';
    } elseif (str_starts_with($slug, 'settings')) {
        return 'settings';
    } elseif (str_starts_with($slug, 'users') || str_starts_with($slug, 'roles')) {
        return 'users';
    } elseif (str_starts_with($slug, 'reports')) {
        return 'reports';
    } elseif (str_starts_with($slug, 'integrations')) {
        return 'integrations';
    }
    
    return 'general';
}

function getPermissionModule($slug) {
    $parts = explode('.', $slug);
    if (count($parts) > 1) {
        return $parts[0];
    }
    return $slug;
}

// Create a Laravel application instance
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;
use App\Models\Permission;
use App\Models\Role;

echo "Fixing all permissions for the system...\n";

// Get or create Super Admin role
$superAdminRole = Role::where('name', 'Super Admin')->first();
if (!$superAdminRole) {
    echo "Super Admin role not found! Creating...\n";
    $superAdminRole = Role::create([
        'name' => 'Super Admin',
        'slug' => 'super-admin',
    ]);
    echo "Created Super Admin role.\n";
}

// Comprehensive list of all permissions needed in the system
$allPermissions = [
    // Dashboard permissions
    'view dashboard',
    'view occupancy dashboard',
    'view revenue dashboard',
    'view night audit',
    'view ar dashboard',
    'view cashier shifts',
    'view commissions',
    'view metabase reports',
    
    // Sidebar module permissions
    'reports.view',
    'guests.view',
    'guests.create',
    'guests.edit',
    'guests.delete',
    'reservations.view',
    'reservations.create',
    'reservations.edit',
    'reservations.delete',
    'view financial',
    'view settings',
    
    // Night audit permissions
    'run night audit',
    'rerun night audit',
    'rerun historical night audit',
    
    // Finance permissions
    'finance.payment_correction',
    'view payment corrections',
    'view rebates',
    'revenue.adjustment',
    
    // AR permissions
    'ar.city_ledger.view',
    'ar.invoice_transfer',
    'ar.manage_company_groups',
    'view promissories',
    'view promissory payment logs',
    
    // Operations permissions
    'cashier.view',
    'commission.manage',
    'room_status.view',
    'view early late charges',
    'view noshow rules',
    
    // General permissions
    'user and roles',
    'view company groups',
    'view checkout balance transfers',
    
    // Additional common permissions from the system
    'dashboard.view',
    'dashboard.finance',
    'front-desk.view',
    'housekeeping.view',
    'night-audit.view',
    'integrations.view',
];

echo "Total permissions to check: " . count($allPermissions) . "\n";

$addedCount = 0;
$roleAddedCount = 0;

foreach ($allPermissions as $permissionSlug) {
    // Check if permission exists
    $permission = Permission::where('slug', $permissionSlug)->first();
    
    if (!$permission) {
        // Create the permission
        $permission = Permission::create([
            'name' => ucwords(str_replace(['_', '-', '.'], ' ', $permissionSlug)),
            'slug' => $permissionSlug,
            'group' => getPermissionGroup($permissionSlug),
            'module' => getPermissionModule($permissionSlug),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $addedCount++;
        echo "Created permission: {$permissionSlug}\n";
    }
    
    // Check if permission is assigned to Super Admin role
    $rolePermissionExists = DB::table('role_permission')
        ->where('role_id', $superAdminRole->id)
        ->where('permission_slug', $permissionSlug)
        ->first();
        
    if (!$rolePermissionExists) {
        DB::table('role_permission')->insert([
            'role_id' => $superAdminRole->id,
            'permission_slug' => $permissionSlug,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $roleAddedCount++;
        echo "Added permission to Super Admin: {$permissionSlug}\n";
    }
}

echo "\nSummary:\n";
echo "- Created {$addedCount} new permissions\n";
echo "- Added {$roleAddedCount} permissions to Super Admin role\n";
echo "- Total permissions in database: " . Permission::count() . "\n";
echo "- Total permissions for Super Admin: " . DB::table('role_permission')->where('role_id', $superAdminRole->id)->count() . "\n";
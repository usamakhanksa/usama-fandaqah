<?php

require_once __DIR__.'/../vendor/autoload.php';

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
    
    // Room management permissions
    'rooms.view',
    'rooms.create',
    'rooms.edit',
    'rooms.delete',
    
    // Unit housing permissions
    'units.view',
    'units.create',
    'units.edit',
    'units.delete',
    
    // Services permissions
    'services.view',
    'services.create',
    'services.edit',
    'services.delete',
    
    // POS permissions
    'pos.view',
    'pos.create',
    'pos.edit',
    'pos.delete',
    
    // Company permissions
    'companies.view',
    'companies.create',
    'companies.edit',
    'companies.delete',
    
    // Customer permissions
    'customers.view',
    'customers.create',
    'customers.edit',
    'customers.delete',
    
    // Reports permissions
    'reports.occupancy',
    'reports.revenue',
    'reports.finance',
    'reports.operations',
    
    // Settings permissions
    'settings.view',
    'settings.edit',
    'settings.delete',
    
    // User management permissions
    'users.view',
    'users.create',
    'users.edit',
    'users.delete',
    
    // Role management permissions
    'roles.view',
    'roles.create',
    'roles.edit',
    'roles.delete',
    
    // Team permissions
    'teams.view',
    'teams.create',
    'teams.edit',
    'teams.delete',
    
    // Activity log permissions
    'activity_log.view',
    
    // Integration permissions
    'integrations.manage',
    
    // Channel manager permissions
    'channel_manager.view',
    'channel_manager.manage',
    
    // Metabase permissions
    'metabase.view',
    
    // Qoyod integration permissions
    'qoyod.view',
    'qoyod.manage',
    
    // ZATCA integration permissions
    'zatca.view',
    'zatca.manage',
    
    // Cleaning permissions
    'cleaning.view',
    'cleaning.manage',
    
    // Maintenance permissions
    'maintenance.view',
    'maintenance.manage',
    
    // Housekeeping permissions
    'housekeeping.manage',
    
    // Front desk operations
    'front_desk.checkin',
    'front_desk.checkout',
    'front_desk.walkin',
    'front_desk.room_assignment',
    'front_desk.room_swap',
    'front_desk.early_checkin',
    'front_desk.late_checkout',
    'front_desk.no_show',
    
    // Reservation operations
    'reservations.checkin',
    'reservations.checkout',
    'reservations.extend',
    'reservations.transfer',
    'reservations.cancel',
    'reservations.modify',
    
    // Financial operations
    'financial.payments',
    'financial.invoices',
    'financial.credit_notes',
    'financial.refunds',
    'financial.adjustments',
    
    // AR operations
    'ar.payments',
    'ar.invoices',
    'ar.aging',
    'ar.collections',
    
    // Cashier operations
    'cashier.shifts',
    'cashier.transactions',
    'cashier.reports',
    
    // Commission operations
    'commission.calculate',
    'commission.approve',
    'commission.pay',
    
    // Promissory operations
    'promissories.create',
    'promissories.edit',
    'promissories.delete',
    'promissories.payments',
    
    // Invoice transfer operations
    'invoice_transfers.create',
    'invoice_transfers.approve',
    'invoice_transfers.settle',
    
    // Company group operations
    'company_groups.create',
    'company_groups.edit',
    'company_groups.delete',
    'company_groups.link',
    'company_groups.unlink',
    
    // City ledger operations
    'city_ledger.view',
    'city_ledger.aging',
    'city_ledger.collections',
    
    // No-show rules
    'noshow_rules.view',
    'noshow_rules.create',
    'noshow_rules.edit',
    'noshow_rules.delete',
    
    // Stay charge configs
    'stay_charges.view',
    'stay_charges.create',
    'stay_charges.edit',
    'stay_charges.delete',
    
    // Service categories
    'service_categories.view',
    'service_categories.create',
    'service_categories.edit',
    'service_categories.delete',
    
    // Unit categories
    'unit_categories.view',
    'unit_categories.create',
    'unit_categories.edit',
    'unit_categories.delete',
    
    // Unit features
    'unit_features.view',
    'unit_features.create',
    'unit_features.edit',
    'unit_features.delete',
    
    // Unit options
    'unit_options.view',
    'unit_options.create',
    'unit_options.edit',
    'unit_options.delete',
    
    // Source management (Travel Agents)
    'sources.view',
    'sources.create',
    'sources.edit',
    'sources.delete',
    
    // Company profiles
    'company_profiles.view',
    'company_profiles.create',
    'company_profiles.edit',
    'company_profiles.delete',
    
    // Lead management
    'leads.view',
    'leads.create',
    'leads.edit',
    'leads.delete',
    
    // Booking management
    'bookings.view',
    'bookings.create',
    'bookings.edit',
    'bookings.delete',
    
    // Search functionality
    'search.global',
    
    // Upload functionality
    'uploads.create',
    'uploads.delete',
    
    // Lookup data
    'lookups.view',
    
    // Master data management
    'master_data.view',
    'master_data.create',
    'master_data.edit',
    'master_data.delete',
    
    // Sidebar navigation permissions (for dynamic sidebar)
    'sidebar.dashboard',
    'sidebar.reservations',
    'sidebar.front_desk',
    'sidebar.housekeeping',
    'sidebar.rooms',
    'sidebar.guests',
    'sidebar.companies',
    'sidebar.finance',
    'sidebar.ar',
    'sidebar.cashier',
    'sidebar.commissions',
    'sidebar.night_audit',
    'sidebar.reports',
    'sidebar.settings',
    'sidebar.users',
    'sidebar.roles',
    'sidebar.teams',
    'sidebar.integrations',
    'sidebar.metabase',
    'sidebar.qoyod',
    'sidebar.zatca',
    'sidebar.pos',
    'sidebar.services',
    'sidebar.cleaning',
    'sidebar.maintenance',
    'sidebar.channel_manager',
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
            'group' => $this->getPermissionGroup($permissionSlug),
            'module' => $this->getPermissionModule($permissionSlug),
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
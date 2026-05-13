<?php

require_once __DIR__.'/../vendor/autoload.php';

// Create a Laravel application instance
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;

$user = User::find(13);

if (!$user) {
    echo "User with ID 13 not found.\n";
    exit(1);
}

echo "User found: {$user->name}\n";

// Test commonly required permissions that might be causing 500 errors
$commonPermissions = [
    // Dashboard permissions
    'view dashboard',
    'view occupancy dashboard',
    'view revenue dashboard',
    'view night audit',
    'view ar dashboard',
    'view cashier shifts',
    'view commissions',
    'view metabase reports',
    
    // Common feature permissions
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
];

echo "Checking common permissions that might cause 500 errors:\n";
$missingPermissions = [];

foreach ($commonPermissions as $permission) {
    $hasPermission = $user->hasPermissionTo($permission);
    if (!$hasPermission) {
        $missingPermissions[] = $permission;
        echo "- {$permission}: NO\n";
    } else {
        echo "- {$permission}: YES\n";
    }
}

if (empty($missingPermissions)) {
    echo "\nAll common permissions are present!\n";
} else {
    echo "\nFound " . count($missingPermissions) . " missing permissions that might cause 500 errors.\n";
    
    // Try to add these missing permissions to the Super Admin role
    echo "Attempting to add missing permissions...\n";
    
    $role = \App\Role::where('name', 'Super Admin')->first();
    if ($role) {
        foreach ($missingPermissions as $permission) {
            // Check if permission exists in permissions table
            $permissionExists = DB::table('permissions')->where('slug', $permission)->first();
            
            if (!$permissionExists) {
                // Create the permission if it doesn't exist
                DB::table('permissions')->insert([
                    'name' => ucwords(str_replace('_', ' ', str_replace('-', ' ', $permission))),
                    'slug' => $permission,
                    'group' => 'general',
                    'module' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "Created permission: {$permission}\n";
            }
            
            // Add to role if not already there
            $rolePermExists = DB::table('role_permission')
                ->where('role_id', $role->id)
                ->where('permission_slug', $permission)
                ->first();
                
            if (!$rolePermExists) {
                DB::table('role_permission')->insert([
                    'role_id' => $role->id,
                    'permission_slug' => $permission,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "Added permission to Super Admin: {$permission}\n";
            }
        }
        
        echo "All missing permissions have been added to the Super Admin role.\n";
    } else {
        echo "Super Admin role not found!\n";
    }
}
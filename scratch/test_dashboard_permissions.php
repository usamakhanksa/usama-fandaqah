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

// Test dashboard-related permissions
$permissions = [
    'view dashboard',
    'view occupancy dashboard',
    'view revenue dashboard',
    'view night audit',
    'view ar dashboard',
    'view cashier shifts',
    'view commissions',
    'view metabase reports'
];

echo "Checking dashboard-related permissions:\n";
foreach ($permissions as $permission) {
    $hasPermission = $user->hasPermissionTo($permission);
    echo "- {$permission}: " . ($hasPermission ? "YES" : "NO") . "\n";
}

// Let's also check what permissions are available in the role_permission table for this user's role
echo "\nChecking role_permission table entries for Super Admin...\n";
$rolePermissions = DB::table('role_permission')
    ->join('roles', 'role_permission.role_id', '=', 'roles.id')
    ->where('roles.name', 'Super Admin')
    ->pluck('permission_slug');

echo "Total permissions for Super Admin: " . $rolePermissions->count() . "\n";

// Check if any of the required permissions are missing
$missingPermissions = [];
foreach ($permissions as $perm) {
    if (!$rolePermissions->contains($perm)) {
        $missingPermissions[] = $perm;
    }
}

if (!empty($missingPermissions)) {
    echo "Missing permissions: " . implode(", ", $missingPermissions) . "\n";
} else {
    echo "All required permissions are present!\n";
}
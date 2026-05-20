<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\User;

try {
    $user = User::first();
    if (!$user) {
        echo "No user found\n";
        exit;
    }

    echo "Testing hasPermissionTo method on user: {$user->id}\n";

    // Test hasPermissionTo
    try {
        $result = $user->hasPermissionTo('view dashboard');
        echo "hasPermissionTo('view dashboard'): " . ($result ? 'true' : 'false') . "\n";
    } catch (\Exception $e) {
        echo "ERROR in hasPermissionTo: " . $e->getMessage() . "\n";
    }

    // Test SidebarService
    echo "\nTesting SidebarService...\n";
    $sidebarService = new \App\Services\SidebarService();
    $menu = $sidebarService->getPermittedMenu($user);
    echo "Menu generated successfully with " . count($menu) . " top-level items\n";

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

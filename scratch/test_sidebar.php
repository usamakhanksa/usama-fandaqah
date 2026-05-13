<?php

require_once __DIR__.'/../vendor/autoload.php';

// Create a Laravel application instance
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\User;
use App\Services\SidebarService;

$user = User::find(13);

if (!$user) {
    echo "User with ID 13 not found.\n";
    exit(1);
}

echo "User found: {$user->name}\n";

$service = app(SidebarService::class);
$menuItems = $service->getPermittedMenu($user);

echo "Number of menu items: " . count($menuItems) . "\n";

if (count($menuItems) > 0) {
    echo "Menu items found! Super Admin permissions are working correctly.\n";
    echo "Sample menu keys: ";
    foreach (array_slice($menuItems, 0, 5) as $item) {
        echo $item['key'] . " ";
    }
    echo "\n";
} else {
    echo "No menu items found. There may still be an issue with permissions.\n";
}
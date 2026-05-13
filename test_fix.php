<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Test database connections
try {
    echo "Testing database connection...\n";
    DB::connection()->getPdo();
    echo "Database connection OK\n";
    
    // Check if integrations table has required columns
    echo "Checking integrations table...\n";
    $columns = Schema::getColumnListing('integrations');
    $required = ['id', 'key', 'team_id', 'deleted_at'];
    foreach ($required as $col) {
        if (in_array($col, $columns)) {
            echo "✓ Column '$col' exists\n";
        } else {
            echo "✗ Column '$col' missing\n";
        }
    }
    
    // Check if guests table has deleted_at
    echo "\nChecking guests table...\n";
    $columns = Schema::getColumnListing('guests');
    if (in_array('deleted_at', $columns)) {
        echo "✓ Column 'deleted_at' exists\n";
    } else {
        echo "✗ Column 'deleted_at' missing\n";
    }
    
    // Check middleware registration
    echo "\nChecking middleware...\n";
    $kernel = app(Illuminate\Contracts\Http\Kernel::class);
    $middleware = $kernel->getRouteMiddleware();
    if (isset($middleware['team.scope'])) {
        echo "✓ 'team.scope' middleware registered\n";
    } else {
        echo "✗ 'team.scope' middleware not registered\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
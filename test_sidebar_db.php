<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = DB::table('sidebar_items')->count();
    echo "sidebar_items table exists with $count rows\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

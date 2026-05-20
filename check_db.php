<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$table = 'reservations';
$columns = Schema::getColumnListing($table);
echo "Columns in '$table':\n";
print_r($columns);

$firstRow = DB::table($table)->first();
echo "\nFirst row sample:\n";
print_r($firstRow);

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Cleaning up legacy columns...\n";

Schema::table('housekeeping_tasks', function (Blueprint $table) {
    if (Schema::hasColumn('housekeeping_tasks', 'unit_id')) {
        echo "Dropping unit_id from housekeeping_tasks...\n";
        try {
            $table->dropForeign(['unit_id']);
        } catch (\Exception $e) { echo "FK drop failed, continuing...\n"; }
        $table->dropColumn('unit_id');
    }
});

Schema::table('room_restrictions', function (Blueprint $table) {
    if (Schema::hasColumn('room_restrictions', 'unit_id')) {
        echo "Dropping unit_id from room_restrictions...\n";
        try {
            $table->dropForeign(['unit_id']);
        } catch (\Exception $e) { echo "FK drop failed, continuing...\n"; }
        $table->dropColumn('unit_id');
    }
});

echo "Cleanup complete.\n";

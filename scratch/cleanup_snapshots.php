<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$duplicates = DB::select("
    SELECT team_id, business_date, run_number, MIN(id) as min_id
    FROM night_audit_occupancy_snapshot
    GROUP BY team_id, business_date, run_number
    HAVING COUNT(*) > 1
");

foreach ($duplicates as $dup) {
    DB::table('night_audit_occupancy_snapshot')
        ->where('team_id', $dup->team_id)
        ->where('business_date', $dup->business_date)
        ->where('run_number', $dup->run_number)
        ->where('id', '>', $dup->min_id)
        ->delete();
    echo "Deleted duplicates for Team: {$dup->team_id}, Date: {$dup->business_date}, Run: {$dup->run_number}\n";
}

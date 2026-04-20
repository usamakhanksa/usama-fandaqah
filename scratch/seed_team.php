<?php
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::where('email', 'usama@fandaqah.com')->first();
if (!$user) {
    echo "User not found\n";
    exit;
}

$team = Team::updateOrCreate(
    ['name' => 'Fandaqah Main Facility'],
    [
        'owner_id' => $user->id,
        'business_date' => date('Y-m-d'),
        'night_audit_cutoff_time' => '03:10:00',
        'night_audit_auto_run_time' => '06:00:00',
        'enable_zatca_phase_two' => 1,
        'tax_number' => '31000000000003',
    ]
);

$user->teams()->syncWithoutDetaching([$team->id]);

echo "Created Team ID: " . $team->id . "\n";

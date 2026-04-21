<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Unit;
use App\Models\Guest;
use App\Models\Reservation;

try {
    \Illuminate\Support\Facades\DB::enableQueryLog();
    $unit = Unit::first();
    $guest = Guest::first();
    echo "Unit ID: " . $unit->id . " Room ID: " . $unit->room_id . "\n";
    $res = Reservation::create([
        'unit_id' => $unit->id,
        'room_id' => $unit->room_id,
        'guest_id' => $guest->id,
        'check_in' => now(),
        'check_out' => now()->addDay(),
        'status' => 'Occupied',
        'reservation_status_id' => 2,
        'stay_type' => 'checked_in',
        'total_price' => 100
    ]);
    print_r(\Illuminate\Support\Facades\DB::getQueryLog());
    echo "Success: " . $res->id . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ReservationService;
use App\User;
use Illuminate\Support\Facades\Auth;

$user = User::first();
if (!$user) {
    echo "No user found in DB.\n";
    exit;
}
Auth::login($user);

$service = new ReservationService();

echo "Testing getCalendarGrid...\n";
try {
    $calendar = $service->getCalendarGrid([
        'start_date' => date('Y-m-01'),
        'end_date' => date('Y-m-t')
    ]);
    echo "SUCCESS: getCalendarGrid returned " . count($calendar['rooms']) . " rooms.\n";
} catch (\Exception $e) {
    echo "FAILED: getCalendarGrid - " . $e->getMessage() . "\n";
}

echo "\nTesting getArrivals...\n";
try {
    $arrivals = $service->getArrivals(['date' => date('Y-m-d')]);
    echo "SUCCESS: getArrivals returned " . $arrivals->total() . " records.\n";
} catch (\Exception $e) {
    echo "FAILED: getArrivals - " . $e->getMessage() . "\n";
}

echo "\nTesting getDepartures...\n";
try {
    $departures = $service->getDepartures(['date' => date('Y-m-d')]);
    echo "SUCCESS: getDepartures returned " . $departures->total() . " records.\n";
} catch (\Exception $e) {
    echo "FAILED: getDepartures - " . $e->getMessage() . "\n";
}

echo "\nTesting getInHouseGuests...\n";
try {
    $inHouse = $service->getInHouseGuests([]);
    echo "SUCCESS: getInHouseGuests returned " . $inHouse->total() . " records.\n";
} catch (\Exception $e) {
    echo "FAILED: getInHouseGuests - " . $e->getMessage() . "\n";
}

echo "\nTesting getOnlineReservations...\n";
try {
    $online = $service->getOnlineReservations([]);
    echo "SUCCESS: getOnlineReservations returned " . $online->total() . " records.\n";
} catch (\Exception $e) {
    echo "FAILED: getOnlineReservations - " . $e->getMessage() . "\n";
}

echo "\nTesting getOTAReservations...\n";
try {
    $ota = $service->getOTAReservations([]);
    echo "SUCCESS: getOTAReservations returned " . $ota->total() . " records.\n";
} catch (\Exception $e) {
    echo "FAILED: getOTAReservations - " . $e->getMessage() . "\n";
}

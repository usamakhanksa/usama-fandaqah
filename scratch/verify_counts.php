<?php

use Illuminate\Support\Facades\DB;

require dirname(__DIR__).'/vendor/autoload.php';
$app = require_once dirname(__DIR__).'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Reservations: " . \App\Models\Reservation::count() . PHP_EOL;
echo "Bookings: " . \App\Models\Booking::count() . PHP_EOL;
echo "Blocks: " . \App\Models\BookingBlock::count() . PHP_EOL;
echo "Events: " . \App\Models\BookingEvent::count() . PHP_EOL;

<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\BookingBlock;
use App\Models\BookingEvent;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder {
    public function run(): void {
        Booking::factory()->count(50)->create();

        // Seed some blocks
        BookingBlock::create([
            'property_reference' => 'RYD-001',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
            'reason' => 'maintenance',
            'notes' => 'Annual HVAC maintenance'
        ]);

        // Seed some events
        BookingEvent::create([
            'title' => 'VIP Property Inspection',
            'property_reference' => 'JED-002',
            'start_time' => now()->addDays(2)->setHour(10)->setMinute(0),
            'end_time' => now()->addDays(2)->setHour(12)->setMinute(0),
            'type' => 'inspection',
            'description' => 'Quality check for upcoming government delegation'
        ]);
    }
}

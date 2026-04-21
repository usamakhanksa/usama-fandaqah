<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoUnitHousingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = \App\Models\Unit::all();
        if ($units->isEmpty()) {
            // Create some units if none exist
            $floor = \App\Models\RoomFloor::first() ?? \App\Models\RoomFloor::create(['name' => 'Floor 1', 'level' => 1]);
            $type = \App\Models\UnitType::first() ?? \App\Models\UnitType::create(['name' => 'Double Room']);
            for ($i = 1; $i <= 20; $i++) {
                \App\Models\Unit::create([
                    'number' => 5000 + $i,
                    'name' => 'Premium Suite '.$i,
                    'room_floor_id' => $floor->id,
                    'unit_type_id' => $type->id,
                    'capacity' => rand(2, 4),
                    'beds' => rand(1, 2),
                    'baths' => rand(1, 2),
                    'price' => rand(1000, 2000),
                    'unit_status_id' => 1,
                ]);
            }
            $units = \App\Models\Unit::all();
        }

        foreach ($units as $index => $unit) {
            // Assign statuses based on index to ensure variety
            if ($index % 5 === 0) {
                $unit->update(['unit_status_id' => 2]); // Occupied
                
                // Create a demo reservation for occupied units
                $guest = \App\Models\Guest::create([
                    'name' => 'Guest ' . ($index + 1),
                    'email' => 'guest' . ($index + 1) . '@example.com',
                    'phone' => '+966' . rand(500000000, 599999999),
                ]);

                \App\Models\Reservation::create([
                    'unit_id' => $unit->id,
                    'room_id' => $unit->room_id,
                    'guest_id' => $guest->id,
                    'check_in' => now()->subDays(2),
                    'check_out' => now()->addDays(3),
                    'status' => 'Occupied',
                    'reservation_status_id' => 2, // Occupied
                    'stay_type' => 'checked_in',
                    'total_price' => $unit->price * 5,
                ]);
            } elseif ($index % 5 === 1) {
                $unit->update(['unit_status_id' => 3]); // Under Cleaning
            } elseif ($index % 5 === 2) {
                $unit->update(['unit_status_id' => 4]); // Under Maintenance
            } else {
                $unit->update(['unit_status_id' => 1]); // Available
            }
        }
    }
}

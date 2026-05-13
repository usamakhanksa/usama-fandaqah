<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\UnitCategory;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $categories = UnitCategory::where('team_id', $team->id)->get();
        if ($categories->isEmpty()) return;

        // Ensure room types exist
        $roomTypes = [
            ['id' => 1, 'name' => 'Single Room', 'base_price' => 100.00, 'team_id' => $team->id],
            ['id' => 2, 'name' => 'Double Room', 'base_price' => 180.00, 'team_id' => $team->id],
            ['id' => 3, 'name' => 'Suite', 'base_price' => 300.00, 'team_id' => $team->id],
        ];

        foreach ($roomTypes as $type) {
            DB::table('room_types')->updateOrInsert(
                ['id' => $type['id']],
                array_merge($type, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        // Ensure room floors exist
        $floors = [1, 2, 3, 4, 5];
        foreach ($floors as $floor) {
            DB::table('room_floors')->updateOrInsert(
                ['id' => $floor],
                [
                    'id' => $floor,
                    'name' => 'Floor ' . $floor,
                    'level' => $floor, // Added level
                    'team_id' => $team->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        foreach ($floors as $floor) {
            for ($i = 1; $i <= 10; $i++) {
                $unitNumber = ($floor * 100) + $i;
                $category = $categories->random();
                
                // Create or find a Room first
                $room = \App\Models\Room::firstOrCreate(
                    ['team_id' => $team->id, 'name' => 'Room ' . $unitNumber],
                    [
                        'team_id' => $team->id,
                        'name' => 'Room ' . $unitNumber,
                        'room_type_id' => 1, // Default or map from category
                        'room_floor_id' => $floor,
                        'status' => 'available',
                        'price_per_day' => 100.00,
                    ]
                );

                Unit::updateOrCreate(
                    ['team_id' => $team->id, 'unit_number' => (string)$unitNumber],
                    [
                        'name' => 'Room ' . $unitNumber,
                        'unit_category_id' => $category->id,
                        'room_id' => $room->id, // Link to room
                        'status' => rand(1, 7), // 4=Available, 5=Occupied, 2=Cleaning
                        'floor' => (string)$floor,
                        'capacity' => $category->number_of_adults,
                        'beds' => $category->number_of_beds,
                        'is_active' => true,
                        'enabled' => true,
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
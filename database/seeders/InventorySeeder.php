<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\HousekeepingTask;
use App\Models\RoomRestriction;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder {
    public function run() {
        // Seed Room Types first
        $roomTypes = [
            ['name' => 'Deluxe King', 'base_price' => 450],
            ['name' => 'Royal Suite', 'base_price' => 1200],
            ['name' => 'Executive Suite', 'base_price' => 850],
            ['name' => 'Standard Double', 'base_price' => 350],
        ];

        $typeMap = [];
        foreach ($roomTypes as $typeData) {
            $type = RoomType::create($typeData);
            $typeMap[$type->name] = $type->id;
        }

        $rooms = [
            ['room_number' => '101', 'type' => 'Deluxe King', 'floor' => '1st Floor', 'capacity' => 2, 'base_price' => 450, 'status' => 'available'],
            ['room_number' => '102', 'type' => 'Deluxe King', 'floor' => '1st Floor', 'capacity' => 2, 'base_price' => 450, 'status' => 'dirty'],
            ['room_number' => '201', 'type' => 'Royal Suite', 'floor' => '2nd Floor', 'capacity' => 4, 'base_price' => 1200, 'status' => 'cleaning'],
            ['room_number' => '202', 'type' => 'Executive Suite', 'floor' => '2nd Floor', 'capacity' => 2, 'base_price' => 850, 'status' => 'available'],
            ['room_number' => '301', 'type' => 'Standard Double', 'floor' => '3rd Floor', 'capacity' => 2, 'base_price' => 350, 'status' => 'out_of_order'],
        ];

        foreach ($rooms as $roomData) {
            $roomData['room_type_id'] = $typeMap[$roomData['type']] ?? null;
            // Also map 'type' field to 'number' if the migration used 'number' instead of 'room_number' in some batches
            // But based on my latest migration, it's 'room_number'.
            
            $room = Room::create($roomData);
            
            if ($room->status === 'dirty' || $room->status === 'cleaning') {
                HousekeepingTask::create([
                    'room_id' => $room->id,
                    'task_type' => $room->status === 'dirty' ? 'deep_clean' : 'light_clean',
                    'status' => $room->status === 'dirty' ? 'pending' : 'in_progress',
                    'notes' => 'Generated from initial inventory seed.',
                    'scheduled_at' => now(),
                ]);
            }
        }

        RoomRestriction::create([
            'room_id' => Room::first()->id,
            'start_date' => now(),
            'end_date' => now()->addDays(7),
            'restriction_type' => 'min_los',
            'value' => '3',
            'reason' => 'Peak season requirement'
        ]);
    }
}

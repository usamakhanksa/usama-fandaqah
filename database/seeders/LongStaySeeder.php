<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\LongStayContract;
use App\Models\UtilityMeter;
use App\Models\UtilityReading;
use App\Models\UnitInventory;
use App\Models\Unit;
use App\Customer;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class LongStaySeeder extends Seeder
{
    public function run(): void
    {
        $teamId = 1; // Assuming team ID 1 exists from previous seeders

        // 1. Create Buildings
        $buildings = [
            ['name_en' => 'Seaview Residence', 'name_ar' => 'ريزيدنس إطلالة البحر', 'address' => 'Jeddah Corniche', 'total_floors' => 10],
            ['name_en' => 'City Center Suites', 'name_ar' => 'أجنحة وسط المدينة', 'address' => 'Riyadh Olaya', 'total_floors' => 15],
        ];

        foreach ($buildings as $b) {
            Building::updateOrCreate(
                ['name_en' => $b['name_en'], 'team_id' => $teamId],
                $b
            );
        }

        $building = Building::where('team_id', $teamId)->first();
        $units = Unit::where('team_id', $teamId)->take(5)->get();
        $customer = Customer::where('team_id', $teamId)->first();

        if ($units->isNotEmpty() && $customer) {
            foreach ($units as $index => $unit) {
                // 2. Create Long Stay Contracts
                $contract = LongStayContract::updateOrCreate(
                    ['unit_id' => $unit->id, 'team_id' => $teamId],
                    [
                        'customer_id' => $customer->id,
                        'start_date' => Carbon::now()->subMonths($index + 1),
                        'end_date' => Carbon::now()->addMonths(6 - $index),
                        'billing_cycle' => 'monthly',
                        'amount' => 4500 + ($index * 500),
                        'security_deposit' => 2000,
                        'status' => 'active',
                        'terms' => 'Standard long-stay rental agreement terms and conditions apply.'
                    ]
                );

                // 3. Create Utility Meters
                $meterTypes = ['electricity', 'water'];
                foreach ($meterTypes as $type) {
                    $meter = UtilityMeter::updateOrCreate(
                        ['unit_id' => $unit->id, 'type' => $type],
                        [
                            'team_id' => $teamId,
                            'meter_number' => 'MTR-' . $unit->id . '-' . strtoupper($type),
                            'initial_reading' => '100.00'
                        ]
                    );

                    // 4. Create Utility Readings
                    UtilityReading::create([
                        'meter_id' => $meter->id,
                        'reading_date' => Carbon::now()->subMonth(),
                        'reading_value' => 150.50 + ($index * 10),
                        'created_by' => 1
                    ]);
                }

                // 5. Create Unit Inventory
                $items = [
                    ['item_name' => 'Smart TV 55"', 'quantity' => 1, 'condition' => 'new'],
                    ['item_name' => 'King Size Bed', 'quantity' => 1, 'condition' => 'good'],
                    ['item_name' => 'Microwave Oven', 'quantity' => 1, 'condition' => 'new'],
                    ['item_name' => 'Dining Table', 'quantity' => 1, 'condition' => 'good'],
                ];

                foreach ($items as $item) {
                    UnitInventory::updateOrCreate(
                        ['unit_id' => $unit->id, 'item_name' => $item['item_name']],
                        array_merge($item, ['team_id' => $teamId])
                    );
                }
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\Reservation;
use App\Customer;
use App\Company;
use App\Unit;
use App\Source;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'fandaqah-palace')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Get required data
        $customers = Customer::where('team_id', $team->id)->get();
        $companies = Company::where('team_id', $team->id)->get();
        $units = Unit::where('team_id', $team->id)->get();
        
        // Ensure reservation statuses exist
        $statuses = [
            ['id' => 1, 'name' => 'Confirmed'],
            ['id' => 2, 'name' => 'Pending'],
            ['id' => 3, 'name' => 'Cancelled'],
            ['id' => 4, 'name' => 'Checked In'],
            ['id' => 5, 'name' => 'Checked Out'],
            ['id' => 6, 'name' => 'No Show'],
        ];

        foreach ($statuses as $status) {
            DB::table('reservation_statuses')->updateOrInsert(
                ['id' => $status['id']],
                array_merge($status, ['created_at' => now(), 'updated_at' => now()])
            );
        }

        if ($customers->count() === 0 || $units->count() === 0) {
            $this->command->error('Required data (Customers or Units) not found. Please run prerequisite seeders first.');
            return;
        }

        // Status mapping for reservation_status_id
        $statusMap = [
            'confirmed' => 1,
            'pending' => 2,
            'cancelled' => 3,
            'checked_in' => 4,
            'checked_out' => 5,
            'no_show' => 6,
        ];

        // Generate reservations
        for ($i = 1; $i <= 30; $i++) {
            $customer = $customers->random();
            $unit = $units->random();
            $statusKey = array_rand($statusMap);
            $code = 'RES' . str_pad($i, 3, '0', STR_PAD_LEFT);

            // 1. Create a Guest record for the reservation
            $guest = \App\Guest::updateOrCreate(
                [
                    'team_id' => $team->id,
                    'id_number' => $customer->id_number,
                ],
                [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'nationality' => $customer->country_id ?? 1,
                    'id_type' => $customer->id_type ?? 1,
                    'gender' => $customer->gender ?? 'male',
                    'address' => $customer->address ?? 'Default Address',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // 2. Create the Reservation
            Reservation::updateOrCreate(
                [
                    'team_id' => $team->id,
                    'code' => $code,
                ],
                [
                    'guest_id' => $guest->id,
                    'room_id' => $unit->room_id ?? 1,
                    'unit_id' => $unit->id,
                    'reservation_status_id' => $statusMap[$statusKey],
                    'status' => $statusKey,
                    'reservation_category_type' => 'normal',
                    'company_id' => $companies->count() > 0 ? $companies->random()->id : null,
                    'shomoos_verification_status' => 'none',
                    'primary_payment_method' => 'cash',
                    'check_in' => Carbon::now()->addDays(rand(-5, 10)),
                    'check_out' => Carbon::now()->addDays(rand(11, 20)),
                    'total_price' => rand(500, 5000),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
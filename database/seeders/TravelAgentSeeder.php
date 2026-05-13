<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TravelAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teamId = \App\Team::first()->id ?? 1;

        $agents = [
            [
                'name' => ['en' => 'Booking.com', 'ar' => 'بوكينج.كوم'],
                'is_travel_agent' => true,
                'iata_number' => '12345678',
                'commission_rate' => 15,
                'commission_type' => 'percentage',
                'status' => true,
                'team_id' => $teamId
            ],
            [
                'name' => ['en' => 'Expedia', 'ar' => 'إكسبيديا'],
                'is_travel_agent' => true,
                'iata_number' => '87654321',
                'commission_rate' => 12,
                'commission_type' => 'percentage',
                'status' => true,
                'team_id' => $teamId
            ],
            [
                'name' => ['en' => 'Local Agent', 'ar' => 'وكيل محلي'],
                'is_travel_agent' => true,
                'iata_number' => null,
                'commission_rate' => 50,
                'commission_type' => 'fixed',
                'status' => true,
                'team_id' => $teamId
            ],
        ];

        foreach ($agents as $agent) {
            \App\Source::updateOrCreate(
                ['name->en' => $agent['name']['en'], 'team_id' => $agent['team_id']],
                $agent
            );
        }
    }
}

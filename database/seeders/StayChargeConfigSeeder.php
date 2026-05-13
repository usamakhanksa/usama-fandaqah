<?php

namespace Database\Seeders;

use App\Models\StayChargeConfig;
use App\Team;
use Illuminate\Database\Seeder;

class StayChargeConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::first();
        if (!$team) return;

        $configs = [
            // Early Check-in
            [
                'team_id' => $team->id,
                'charge_type' => 'early_checkin',
                'tier_from_hour' => '00:00:00',
                'tier_to_hour' => '06:00:00',
                'rate_type' => 'percentage_nightly_rate',
                'rate_amount' => 100,
                'applies_to' => 'all',
                'is_active' => true,
            ],
            [
                'team_id' => $team->id,
                'charge_type' => 'early_checkin',
                'tier_from_hour' => '06:00:01',
                'tier_to_hour' => '10:00:00',
                'rate_type' => 'percentage_nightly_rate',
                'rate_amount' => 50,
                'applies_to' => 'all',
                'is_active' => true,
            ],
            [
                'team_id' => $team->id,
                'charge_type' => 'early_checkin',
                'tier_from_hour' => '10:00:01',
                'tier_to_hour' => '12:00:00',
                'rate_type' => 'fixed',
                'rate_amount' => 0, // Grace period
                'applies_to' => 'all',
                'is_active' => true,
            ],

            // Late Checkout
            [
                'team_id' => $team->id,
                'charge_type' => 'late_checkout',
                'tier_from_hour' => '12:00:01',
                'tier_to_hour' => '14:00:00',
                'rate_type' => 'fixed',
                'rate_amount' => 0, // Grace period
                'applies_to' => 'all',
                'is_active' => true,
            ],
            [
                'team_id' => $team->id,
                'charge_type' => 'late_checkout',
                'tier_from_hour' => '14:00:01',
                'tier_to_hour' => '18:00:00',
                'rate_type' => 'percentage_nightly_rate',
                'rate_amount' => 50,
                'applies_to' => 'all',
                'is_active' => true,
            ],
            [
                'team_id' => $team->id,
                'charge_type' => 'late_checkout',
                'tier_from_hour' => '18:00:01',
                'tier_to_hour' => '23:59:59',
                'rate_type' => 'percentage_nightly_rate',
                'rate_amount' => 100,
                'applies_to' => 'all',
                'is_active' => true,
            ],
        ];

        foreach ($configs as $config) {
            StayChargeConfig::create($config);
        }
    }
}

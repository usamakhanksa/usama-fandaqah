<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\NightAuditLog;
use App\Models\NightAuditOccupancySnapshot;
use App\User;
use Carbon\Carbon;

class NightAuditSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'fandaqah-palace')->first() ?: Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Clean existing records to avoid unique constraint violations on subsequent seed runs
        NightAuditLog::where('team_id', $team->id)->delete();
        NightAuditOccupancySnapshot::where('team_id', $team->id)->delete();

        $user = User::first() ?? User::factory()->create();

        // Create night audit records for the last 14 days
        $currentDate = Carbon::today()->subDays(1); // Start from yesterday
        $businessDate = Carbon::today()->subDays(2); // Business date is typically one day behind

        for ($i = 0; $i < 14; $i++) {
            $totalRooms = 95;
            $roomsOccupied = rand(50, 80);
            $roomsAvailable = $totalRooms - $roomsOccupied;
            $adr = rand(600, 900);
            $revpar = ($roomsOccupied / $totalRooms) * $adr;

            // 1. Create the occupancy snapshot
            $snapshot = NightAuditOccupancySnapshot::create([
                'team_id' => $team->id,
                'business_date' => $businessDate,
                'run_number' => 1,
                'is_final' => true,
                'total_rooms' => $totalRooms,
                'rooms_available' => $roomsAvailable,
                'rooms_occupied' => $roomsOccupied,
                'rooms_cleaning' => rand(5, 10),
                'rooms_maintenance' => rand(1, 5),
                'rooms_complimentary' => rand(0, 3),
                'rooms_house_use' => rand(1, 2),
                'rooms_day_use' => rand(0, 4),
                'is_backfill' => false,
                'occupancy_pct' => ($roomsOccupied / $totalRooms) * 100,
                'adr' => $adr,
                'revpar' => $revpar,
                'arrivals_count' => rand(15, 25),
                'departures_count' => rand(15, 25),
                'stayovers_count' => max(0, $roomsOccupied - rand(15, 25)),
                'noshows_count' => rand(3, 8),
                'cancellations_count' => rand(2, 6),
                'new_bookings_count' => rand(10, 20),
                'room_revenue' => $roomsOccupied * $adr,
                'room_revenue_complimentary' => 0.00,
                'service_revenue' => rand(3000, 10000),
                'noshow_revenue' => rand(1000, 5000),
                'adjustment_revenue' => 0.00,
                'rebate_amount' => 0.00,
                'total_revenue' => ($roomsOccupied * $adr) + rand(3000, 10000),
                'vat_total' => rand(1000, 5000),
                'ewa_total' => 0.00,
                'total_deposits_collected' => rand(5000, 15000),
                'total_promissory_created' => rand(2000, 8000),
                'total_promissory_collected' => rand(1000, 5000),
                'outstanding_promissory_balance' => rand(5000, 15000),
                'adults_count' => rand(60, 120),
                'children_count' => rand(10, 30),
            ]);

            // 2. Create night audit log entry
            NightAuditLog::create([
                'team_id' => $team->id,
                'business_date' => $businessDate,
                'run_number' => 1,
                'status' => 'completed',
                'triggered_by' => 'manual',
                'triggered_by_user_id' => $user->id,
                'started_at' => $currentDate->copy()->setTime(23, 0), // 11 PM
                'completed_at' => $currentDate->copy()->addDay()->setTime(5, 30), // 5:30 AM next day
                'steps_completed' => ['room_status_verification', 'post_room_charges', 'no_show_cancellation', 'ledger_verification', 'close_business_day'],
                'steps_failed' => [],
                'noshows_flagged' => rand(3, 8),
                'noshow_charges_posted' => rand(2, 5),
                'transactions_frozen' => rand(40, 120),
                'occupancy_snapshot_id' => $snapshot->id,
                'rerun_of_log_id' => null,
                'notes' => 'Night audit business date closed successfully.',
            ]);

            // Move to previous day
            $currentDate->subDay();
            $businessDate->subDay();
        }

        // Create one active in-progress night audit for yesterday/today
        $snapshotInProgress = NightAuditOccupancySnapshot::create([
            'team_id' => $team->id,
            'business_date' => Carbon::yesterday(),
            'run_number' => 1,
            'is_final' => false,
            'total_rooms' => 95,
            'rooms_available' => 35,
            'rooms_occupied' => 60,
            'rooms_cleaning' => 8,
            'rooms_maintenance' => 2,
            'rooms_complimentary' => 1,
            'rooms_house_use' => 1,
            'rooms_day_use' => 2,
            'is_backfill' => false,
            'occupancy_pct' => (60 / 95) * 100,
            'adr' => 750,
            'revpar' => (60 / 95) * 750,
            'arrivals_count' => 18,
            'departures_count' => 15,
            'stayovers_count' => 42,
            'noshows_count' => 4,
            'cancellations_count' => 3,
            'new_bookings_count' => 12,
            'room_revenue' => 45000,
            'room_revenue_complimentary' => 0.00,
            'service_revenue' => 4500,
            'noshow_revenue' => 1500,
            'adjustment_revenue' => 0.00,
            'rebate_amount' => 0.00,
            'total_revenue' => 51000,
            'vat_total' => 2500,
            'ewa_total' => 0.00,
            'total_deposits_collected' => 8000,
            'total_promissory_created' => 4000,
            'total_promissory_collected' => 2000,
            'outstanding_promissory_balance' => 6000,
            'adults_count' => 85,
            'children_count' => 15,
        ]);

        NightAuditLog::create([
            'team_id' => $team->id,
            'business_date' => Carbon::yesterday(),
            'run_number' => 1,
            'status' => 'running',
            'triggered_by' => 'auto',
            'triggered_by_user_id' => null,
            'started_at' => Carbon::now()->subHours(2),
            'completed_at' => null,
            'steps_completed' => ['room_status_verification', 'post_room_charges'],
            'steps_failed' => [],
            'noshows_flagged' => 4,
            'noshow_charges_posted' => 0,
            'transactions_frozen' => 0,
            'occupancy_snapshot_id' => $snapshotInProgress->id,
            'rerun_of_log_id' => null,
            'notes' => 'Automatic night audit triggered. Pending final check-out clearances.',
        ]);
    }
}
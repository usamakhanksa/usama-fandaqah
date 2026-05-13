<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\NightAudit;
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
        $team = Team::where('slug', 'demo-hotel')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Create night audit records for the last 14 days
        $currentDate = Carbon::today()->subDays(1); // Start from yesterday
        $businessDate = Carbon::today()->subDays(2); // Business date is typically one day behind

        for ($i = 0; $i < 14; $i++) {
            // Create night audit entry
            NightAudit::create([
                'team_id' => $team->id,
                'business_date' => $businessDate,
                'audit_date' => $currentDate,
                'start_time' => $currentDate->copy()->setTime(23, 0), // 11 PM
                'end_time' => $currentDate->copy()->addDay()->setTime(5, 0), // 5 AM next day
                'status' => 'completed',
                'run_by' => rand(1, 3), // Random user ID (typically night auditor)
                'total_rooms_sold' => rand(45, 75), // Between 45-75 rooms
                'total_occupied_rooms' => rand(50, 80), // Between 50-80 occupied
                'total_arrivals' => rand(15, 25), // Arrivals for the day
                'total_departures' => rand(15, 25), // Departures for the day
                'total_no_shows' => rand(3, 8), // No-shows for the day
                'total_revenue' => rand(45000, 85000), // Revenue in SAR
                'room_revenue' => rand(30000, 55000), // Room revenue in SAR
                'food_beverage_revenue' => rand(8000, 15000), // F&B revenue in SAR
                'other_revenue' => rand(5000, 12000), // Other revenue in SAR
                'total_guests' => rand(60, 100), // Total guests
                'avg_daily_rate' => rand(600, 900), // Average daily rate in SAR
                'revpar' => rand(450, 700), // Revenue per available room
                'occupancy_rate' => rand(65, 85), // Occupancy rate percentage
                'run_number' => 1, // First run of the day
                'is_final' => true, // Final audit run
                'snapshot_data' => [
                    'rooms_occupied' => rand(50, 80),
                    'rooms_available' => 95 - rand(50, 80), // Assuming 95 total rooms
                    'rooms_vacant_ready' => rand(10, 20),
                    'rooms_vacant_not_ready' => rand(5, 10),
                    'rooms_occupied_dirty' => rand(5, 15),
                    'rooms_occupied_clean' => rand(40, 65),
                    'rooms_out_of_order' => rand(0, 5),
                    'total_revenue_breakdown' => [
                        'room_sales' => rand(30000, 55000),
                        'restaurant' => rand(5000, 10000),
                        'bar' => rand(2000, 5000),
                        'spa' => rand(1000, 3000),
                        'other' => rand(2000, 7000),
                    ],
                    'cash_flow' => [
                        'opening_cash' => rand(15000, 25000),
                        'cash_receipts' => rand(8000, 15000),
                        'cash_disbursements' => rand(2000, 5000),
                        'cash_refunds' => rand(500, 1500),
                        'closing_cash' => rand(18000, 30000),
                    ],
                    'transactions_processed' => rand(80, 150),
                    'credit_card_transactions' => rand(50, 100),
                    'accounts_receivable' => rand(10000, 25000),
                    'total_outstanding_balances' => rand(5000, 15000),
                ],
                'created_at' => $currentDate->copy()->setTime(23, 30), // Started around 11:30 PM
                'updated_at' => $currentDate->copy()->addDay()->setTime(5, 30), // Completed around 5:30 AM
            ]);

            // Move to previous day
            $currentDate->subDay();
            $businessDate->subDay();
        }

        // Create a pending night audit for last night (yesterday)
        NightAudit::create([
            'team_id' => $team->id,
            'business_date' => Carbon::yesterday(),
            'audit_date' => Carbon::yesterday(),
            'start_time' => Carbon::yesterday()->setTime(23, 0),
            'end_time' => null, // Not completed yet
            'status' => 'in_progress',
            'run_by' => rand(1, 3), // Random user ID
            'total_rooms_sold' => rand(45, 70),
            'total_occupied_rooms' => rand(50, 75),
            'total_arrivals' => rand(15, 20),
            'total_departures' => rand(15, 20),
            'total_no_shows' => rand(3, 6),
            'total_revenue' => rand(40000, 75000),
            'room_revenue' => rand(28000, 50000),
            'food_beverage_revenue' => rand(7000, 13000),
            'other_revenue' => rand(4000, 10000),
            'total_guests' => rand(55, 90),
            'avg_daily_rate' => rand(580, 850),
            'revpar' => rand(420, 650),
            'occupancy_rate' => rand(60, 80),
            'run_number' => 1,
            'is_final' => false, // May still be running
            'snapshot_data' => [
                'rooms_occupied' => rand(50, 75),
                'rooms_available' => 95 - rand(50, 75),
                'rooms_vacant_ready' => rand(8, 18),
                'rooms_vacant_not_ready' => rand(4, 8),
                'rooms_occupied_dirty' => rand(4, 12),
                'rooms_occupied_clean' => rand(38, 62),
                'rooms_out_of_order' => rand(0, 4),
                'total_revenue_breakdown' => [
                    'room_sales' => rand(28000, 50000),
                    'restaurant' => rand(4500, 9000),
                    'bar' => rand(1800, 4500),
                    'spa' => rand(800, 2500),
                    'other' => rand(1800, 6000),
                ],
                'cash_flow' => [
                    'opening_cash' => rand(18000, 28000),
                    'cash_receipts' => rand(7500, 14000),
                    'cash_disbursements' => rand(1800, 4500),
                    'cash_refunds' => rand(400, 1200),
                    'closing_cash' => null, // Not calculated yet
                ],
                'transactions_processed' => rand(75, 140),
                'credit_card_transactions' => rand(45, 90),
                'accounts_receivable' => rand(9000, 22000),
                'total_outstanding_balances' => rand(4500, 13000),
            ],
            'created_at' => Carbon::yesterday()->setTime(23, 15),
            'updated_at' => Carbon::now(),
        ]);

        // Create a completed night audit for today (if it has run)
        if (Carbon::now()->hour >= 6) { // If it's after 6 AM, assume audit ran
            NightAudit::create([
                'team_id' => $team->id,
                'business_date' => Carbon::today(),
                'audit_date' => Carbon::today(),
                'start_time' => Carbon::today()->setTime(23, 0),
                'end_time' => Carbon::today()->addDay()->setTime(5, 30),
                'status' => 'completed',
                'run_by' => rand(1, 3), // Random user ID
                'total_rooms_sold' => rand(40, 65),
                'total_occupied_rooms' => rand(45, 70),
                'total_arrivals' => rand(12, 18),
                'total_departures' => rand(12, 18),
                'total_no_shows' => rand(2, 5),
                'total_revenue' => rand(38000, 70000),
                'room_revenue' => rand(25000, 48000),
                'food_beverage_revenue' => rand(6500, 12000),
                'other_revenue' => rand(3500, 9000),
                'total_guests' => rand(50, 85),
                'avg_daily_rate' => rand(550, 800),
                'revpar' => rand(400, 620),
                'occupancy_rate' => rand(55, 75),
                'run_number' => 1,
                'is_final' => true,
                'snapshot_data' => [
                    'rooms_occupied' => rand(45, 70),
                    'rooms_available' => 95 - rand(45, 70),
                    'rooms_vacant_ready' => rand(10, 20),
                    'rooms_vacant_not_ready' => rand(3, 8),
                    'rooms_occupied_dirty' => rand(3, 10),
                    'rooms_occupied_clean' => rand(40, 60),
                    'rooms_out_of_order' => rand(0, 3),
                    'total_revenue_breakdown' => [
                        'room_sales' => rand(25000, 48000),
                        'restaurant' => rand(4000, 8500),
                        'bar' => rand(1500, 4000),
                        'spa' => rand(600, 2000),
                        'other' => rand(1600, 5500),
                    ],
                    'cash_flow' => [
                        'opening_cash' => rand(20000, 30000),
                        'cash_receipts' => rand(7000, 13000),
                        'cash_disbursements' => rand(1500, 4000),
                        'cash_refunds' => rand(300, 1000),
                        'closing_cash' => rand(23000, 38000),
                    ],
                    'transactions_processed' => rand(70, 130),
                    'credit_card_transactions' => rand(40, 85),
                    'accounts_receivable' => rand(8000, 20000),
                    'total_outstanding_balances' => rand(4000, 12000),
                ],
                'created_at' => Carbon::today()->setTime(23, 15),
                'updated_at' => Carbon::today()->addDay()->setTime(5, 45),
            ]);
        }
    }
}
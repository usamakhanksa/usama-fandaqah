<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\CustomReport;
use App\Models\ReportSchedule;
use App\User;
use Carbon\Carbon;

class ReportSeeder extends Seeder
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

        $user = User::first() ?? User::factory()->create();

        // 1. Seed Custom Reports
        $reportsData = [
            [
                'team_id' => $team->id,
                'name' => 'Monthly Room Occupancy Detail',
                'description' => 'A customized report displaying complete room status, floor division, and operational occupancy statistics.',
                'module' => 'rooms',
                'columns' => ['unit_number', 'room_type', 'floor', 'status', 'hk_status'],
                'filters' => ['status' => 'occupied'],
                'sort_by' => 'unit_number',
                'sort_direction' => 'asc',
                'group_by' => 'floor',
                'is_shared' => true,
                'created_by' => $user->id,
            ],
            [
                'team_id' => $team->id,
                'name' => 'VIP Guest Bookings Analysis',
                'description' => 'Analysis of high-revenue individuals, corporate VIP profiles, and booking channels.',
                'module' => 'guests',
                'columns' => ['guest_name', 'email', 'phone', 'nationality', 'VIP_status'],
                'filters' => ['type' => 'corporate'],
                'sort_by' => 'guest_name',
                'sort_direction' => 'asc',
                'group_by' => 'nationality',
                'is_shared' => true,
                'created_by' => $user->id,
            ],
            [
                'team_id' => $team->id,
                'name' => 'POS Service Sales Breakdown',
                'description' => 'Comprehensive financial analysis of restaurant sales, spa services, and room-posted quick charges.',
                'module' => 'pos',
                'columns' => ['order_number', 'service_name', 'qty', 'amount', 'payment_status'],
                'filters' => ['payment_status' => 'paid'],
                'sort_by' => 'amount',
                'sort_direction' => 'desc',
                'group_by' => 'service_name',
                'is_shared' => true,
                'created_by' => $user->id,
            ],
        ];

        $reports = [];
        foreach ($reportsData as $data) {
            $reports[] = CustomReport::create($data);
        }

        // 2. Seed Report Schedules
        $schedulesData = [
            [
                'team_id' => $team->id,
                'custom_report_id' => $reports[0]->id,
                'report_type' => 'occupancy',
                'name' => 'Daily Occupancy Report Email Dispatch',
                'frequency' => 'daily',
                'day_of_week' => null,
                'day_of_month' => null,
                'time' => '06:00:00', // 6 AM
                'recipients' => ['gm@demo.hotel', 'ops@demo.hotel', 'owner@demo.hotel'],
                'format' => 'pdf',
                'is_active' => true,
                'last_run_at' => Carbon::yesterday()->setTime(6, 0),
                'next_run_at' => Carbon::today()->setTime(6, 0),
                'created_by' => $user->id,
            ],
            [
                'team_id' => $team->id,
                'custom_report_id' => $reports[1]->id,
                'report_type' => 'custom',
                'name' => 'Weekly VIP Guest Report Dispatch',
                'frequency' => 'weekly',
                'day_of_week' => 1, // Monday
                'day_of_month' => null,
                'time' => '08:00:00', // 8 AM
                'recipients' => ['marketing@demo.hotel', 'gm@demo.hotel'],
                'format' => 'excel',
                'is_active' => true,
                'last_run_at' => Carbon::now()->subWeek()->startOfWeek()->setTime(8, 0),
                'next_run_at' => Carbon::now()->startOfWeek()->setTime(8, 0),
                'created_by' => $user->id,
            ],
            [
                'team_id' => $team->id,
                'custom_report_id' => $reports[2]->id,
                'report_type' => 'revenue',
                'name' => 'Monthly POS Financial Summary Dispatch',
                'frequency' => 'monthly',
                'day_of_week' => null,
                'day_of_month' => 1, // 1st of month
                'time' => '09:00:00', // 9 AM
                'recipients' => ['finance@demo.hotel', 'gm@demo.hotel', 'owner@demo.hotel'],
                'format' => 'both',
                'is_active' => true,
                'last_run_at' => Carbon::now()->subMonth()->startOfMonth()->setTime(9, 0),
                'next_run_at' => Carbon::now()->startOfMonth()->setTime(9, 0),
                'created_by' => $user->id,
            ],
        ];

        foreach ($schedulesData as $data) {
            ReportSchedule::create($data);
        }
    }
}
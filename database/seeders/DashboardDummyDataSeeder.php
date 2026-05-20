<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardDummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $teamId = 1;

        // 1. Occupancy Metrics
        for ($i = 30; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            DB::table('night_audit_occupancy_snapshot')->updateOrInsert(
                ['team_id' => $teamId, 'business_date' => $date],
                [
                    'run_number' => 1,
                    'total_rooms' => 100,
                    'rooms_occupied' => rand(40, 95),
                    'rooms_available' => rand(5, 60),
                    'occupancy_pct' => rand(40, 95),
                    'adr' => rand(350, 600),
                    'revpar' => rand(250, 500),
                    'total_revenue' => rand(20000, 50000),
                    'created_at' => now(),
                ]
            );
        }

        // 2. Revenue Metrics (Transactions)
        for ($i = 0; $i < 50; $i++) {
            DB::table('transactions')->insert([
                'team_id' => $teamId,
                'amount' => rand(100, 5000),
                'type' => rand(0, 1) ? 'payment' : 'charge',
                'kind' => 'reservation',
                'uuid' => \Illuminate\Support\Str::uuid(),
                'created_at' => Carbon::now()->subDays(rand(0, 30)),
            ]);
        }

        // 3. Housekeeping Metrics
        $units = DB::table('units')->where('team_id', $teamId)->get();
        foreach ($units as $unit) {
            DB::table('unit_cleanings')->insert([
                'team_id' => $teamId,
                'unit_id' => $unit->id,
                'created_by' => 1,
                'start_at' => Carbon::now()->subHours(rand(1, 48)),
                'completed_at' => Carbon::now()->subHours(rand(0, 24)),
                'completed_by' => 1,
                'created_at' => now(),
            ]);
        }

        // 4. ZATCA Compliance Logs
        for ($i = 0; $i < 10; $i++) {
            DB::table('integration_logs')->insert([
                'team_id' => $teamId,
                'integration_id' => 1, // Assuming ZATCA is 1
                'log_type' => 'success',
                'action' => 'report_invoice',
                'direction' => 'outbound',
                'request_payload' => json_encode(['invoice_id' => $i, 'status' => 'REPORTED']),
                'response_payload' => json_encode(['clearanceStatus' => 'CLEARED']),
                'created_at' => Carbon::now()->subDays(rand(0, 7)),
            ]);
        }
    }
}

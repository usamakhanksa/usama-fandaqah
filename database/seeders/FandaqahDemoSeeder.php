<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Reservation;

class FandaqahDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Teams / Hotels
        DB::table('teams')->updateOrInsert(['id' => 1], [
            'name' => 'Fandaqah Premium Hotel',
            'slug' => 'fandaqah-premium',
            'owner_id' => 1,
            'business_date' => now()->toDateString(),
            'currency' => 'SAR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);


        // 3. Users - update with current_team_id
        $usersData = [
            ['name' => 'System Admin', 'email' => 'admin@fandaqah.com'],
            ['name' => 'Finance Lead', 'email' => 'finance@fandaqah.com'],
            ['name' => 'Night Auditor', 'email' => 'audit@fandaqah.com'],
        ];

        foreach ($usersData as $u) {
            User::updateOrCreate(['email' => $u['email']], [
                'name' => $u['name'],
                'password' => Hash::make('password'),
                'current_team_id' => 1,
            ]);
        }

        // 4. Companies & Groups
        DB::table('company_groups')->updateOrInsert(['id' => 1], [
            'name' => 'Saudi Government',
            'created_at' => now(),
        ]);

        DB::table('companies')->updateOrInsert(['id' => 1], [
            'name' => 'Aramco',
            'team_id' => 1,
            'company_group_id' => 1,
            'tax_number' => '300012345600003',
            'created_at' => now(),
        ]);

        // 5. Financial Operations
        $reservation = Reservation::first();
        if ($reservation) {
            $promissory = DB::table('promissories')->where('reservation_id', $reservation->id)->first();
            if (!$promissory) {
                $promissoryId = DB::table('promissories')->insertGetId([
                    'team_id' => 1,
                    'reservation_id' => $reservation->id,
                    'company_id' => 1,
                    'total_amount' => 5000,
                    'collected_amount' => 1000,
                    'status' => 'active',
                    'due_date' => now()->addDays(30),
                    'created_at' => now(),
                ]);
            } else {
                $promissoryId = $promissory->id;
            }

            if (!DB::table('promissory_payment_log')->where('promissory_id', $promissoryId)->exists()) {
                $transactionId = DB::table('transactions')->insertGetId([
                    'team_id' => 1,
                    'amount' => 1000,
                    'type' => 'deposit',
                    'kind' => 'payment',
                    'uuid' => (string) Str::uuid(),
                    'created_at' => now(),
                ]);

                DB::table('promissory_payment_log')->insert([
                    'promissory_id' => $promissoryId,
                    'transaction_id' => $transactionId,
                    'team_id' => 1,
                    'amount_applied' => 1000,
                    'payment_type' => 'BankTransfer',
                    'applied_at' => now(),
                    'applied_by' => 1,
                    'created_at' => now(),
                ]);
            }

            DB::table('invoice_transfers')->updateOrInsert([
                'reservation_id' => $reservation->id,
                'promissory_id' => $promissoryId
            ], [
                'team_id' => 1,
                'company_id' => 1,
                'amount' => 5000,
                'transferred_by' => 1,
                'transferred_at' => now(),
                'notes' => 'Bulk transfer for corporate stay',
            ]);
        }

        // 6. Night Audit & Snapshots
        $bizDate = now()->subDay()->toDateString();
        $snapshot = DB::table('night_audit_occupancy_snapshot')->where('business_date', $bizDate)->first();
        if (!$snapshot) {
            $snapshotId = DB::table('night_audit_occupancy_snapshot')->insertGetId([
                'team_id' => 1,
                'business_date' => $bizDate,
                'run_number' => 1,
                'total_rooms' => 100,
                'rooms_occupied' => 65,
                'rooms_available' => 35,
                'occupancy_pct' => 65.00,
                'adr' => 450.00,
                'revpar' => 292.50,
                'total_revenue' => 29250.00,
                'created_at' => now(),
            ]);
        } else {
            $snapshotId = $snapshot->id;
        }

        DB::table('night_audit_log')->updateOrInsert([
            'team_id' => 1,
            'business_date' => $bizDate,
            'run_number' => 1
        ], [
            'status' => 'completed',
            'triggered_by' => 'auto',
            'started_at' => now()->subDay()->setTime(3, 0),
            'completed_at' => now()->subDay()->setTime(3, 15),
            'occupancy_snapshot_id' => $snapshotId,
            'dw_synced_at' => now(),
        ]);

        // 7. ETL Watermarks
        if (Schema::hasTable('etl_watermark')) {
            $tables = ['reservations', 'transactions', 'guests', 'invoice_transfers'];
            foreach ($tables as $t) {
                DB::table('etl_watermark')->updateOrInsert(['table_name' => $t], [
                    'last_load_timestamp' => now(),
                    'last_load_id' => 1000,
                ]);
            }
        }

        // 8. Cashier & Status Logs
        DB::table('cashier_shifts')->updateOrInsert([
            'team_id' => 1,
            'user_id' => 1,
            'shift_date' => now()->toDateString()
        ], [
            'opened_at' => now()->subHours(8),
            'closed_at' => now()->subHours(1),
            'opening_balance' => 1000,
            'closing_balance' => 2500,
            'system_balance' => 2500,
            'status' => 'closed',
            'created_at' => now(),
        ]);

        $unit = DB::table('units')->first();
        if ($unit) {
            DB::table('room_status_log')->insert([
                'team_id' => 1,
                'unit_id' => $unit->id,
                'from_status' => 1,
                'to_status' => 2,
                'changed_by' => 1,
                'changed_at' => now(),
            ]);
        }

        // 9. Travel Agents & Commissions
        DB::table('sources')->updateOrInsert(['id' => 1], [
            'name' => json_encode(['en' => 'Booking.com', 'ar' => 'Booking.com']),
            'is_travel_agent' => 0,
            'commission_rate' => 15.00,
            'commission_type' => 'percentage',
            'team_id' => 1,
            'deleteable' => 1,
            'created_at' => now(),
        ]);

        if ($reservation) {
            DB::table('commission_payments')->updateOrInsert([
                'reservation_id' => $reservation->id,
                'source_id' => 1
            ], [
                'team_id' => 1,
                'period_from' => now()->startOfMonth()->toDateString(),
                'period_to' => now()->endOfMonth()->toDateString(),
                'room_revenue_base' => 1000,
                'commission_rate' => 15.00,
                'commission_type' => 'percentage',
                'commission_amount' => 150,
                'status' => 'pending',
                'created_at' => now(),
            ]);
        }

        $this->command->info('Fandaqah Demo Seeding Completed Successfully with exact Sidebar Permissions.');
    }
}

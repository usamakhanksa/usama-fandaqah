<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelModuleScaffoldSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('resource_categories')->count() === 0) {
            foreach (['Crib', 'Rollaway', 'Baby Chair'] as $name) {
                DB::table('resource_categories')->insert(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        if (DB::table('profile_groups')->count() === 0) {
            DB::table('profile_groups')->insert([
                ['name' => 'VIP', 'discount_percent' => 12, 'description' => 'Top-tier repeat guests', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Corporate', 'discount_percent' => 8, 'description' => 'Contracted company stays', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('included_services')->count() === 0) {
            DB::table('included_services')->insert([
                ['name' => 'Breakfast', 'description' => 'Complimentary buffet breakfast', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'WiFi', 'description' => 'High-speed room internet', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('ledger_sequences')->count() === 0) {
            DB::table('ledger_sequences')->insert([
                ['type' => 'folio', 'prefix' => 'FOL', 'next_number' => 1001, 'created_at' => now(), 'updated_at' => now()],
                ['type' => 'invoice', 'prefix' => 'INV', 'next_number' => 5001, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        if (DB::table('scheduled_reports')->count() === 0 && DB::table('users')->exists()) {
            DB::table('scheduled_reports')->insert([
                'user_id' => DB::table('users')->value('id'),
                'report_name' => 'Daily report',
                'parameters_json' => json_encode(['scope' => 'hotel']),
                'frequency' => 'daily',
                'email_recipients' => 'manager@hotel.test',
                'last_run_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}

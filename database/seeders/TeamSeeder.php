<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    public function run()
    {
        // Ensure we have a root user first
        $user = User::where('email', 'admin@fandaqah.com')->first();
        if (!$user) {
            $user = User::create([
                'name' => 'Fandaqah Admin',
                'email' => 'admin@fandaqah.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Create a primary demo hotel team (fandaqah-palace)
        $team = Team::updateOrCreate([
            'slug' => 'fandaqah-palace',
        ], [
            'name' => 'Fandaqah Palace Hotel & Suites',
            'owner_id' => $user->id,
            'country_id' => 1,
            'currency' => 'SAR',
            'business_date' => Carbon::today(),
            'night_audit_auto_enabled' => true,
            'night_audit_auto_run_time' => '23:30',
            'night_audit_cutoff_time' => '04:00',
            'is_demo' => true,
            'demo_expires_at' => Carbon::now()->addYears(1),
            'street_name' => 'Prince Mohammed Bin Abdulaziz St',
            'building_number' => '7842',
            'city' => 'Riyadh',
            'postal_code' => '12222',
            'district' => 'Al Sulaimaniyah',
            'country_code' => 'SA',
            'tax_number' => '300012345600003', // Valid Saudi VAT format
            'timezone' => 'Asia/Riyadh',
            'enable_zatca_phase_two' => true,
            'enable_website' => true,
            'phone' => '+966112223344',
            'email' => 'info@fandaqah-palace.com',
            'website' => 'https://fandaqah-palace.com',
        ]);

        // Create a legacy demo hotel team (demo-hotel) to support other seeders
        $demoTeam = Team::updateOrCreate([
            'slug' => 'demo-hotel',
        ], [
            'name' => 'Demo Hotel & Resort',
            'owner_id' => $user->id,
            'country_id' => 1,
            'currency' => 'SAR',
            'business_date' => Carbon::today(),
            'night_audit_auto_enabled' => true,
            'night_audit_auto_run_time' => '23:30',
            'night_audit_cutoff_time' => '04:00',
            'is_demo' => true,
            'demo_expires_at' => Carbon::now()->addYears(1),
            'street_name' => 'Prince Mohammed Bin Abdulaziz St',
            'building_number' => '7842',
            'city' => 'Riyadh',
            'postal_code' => '12222',
            'district' => 'Al Sulaimaniyah',
            'country_code' => 'SA',
            'tax_number' => '300012345600003', // Valid Saudi VAT format
            'timezone' => 'Asia/Riyadh',
            'enable_zatca_phase_two' => true,
            'enable_website' => true,
            'phone' => '+966112223344',
            'email' => 'info@demo-hotel.com',
            'website' => 'https://demo-hotel.com',
        ]);

        $user->update(['current_team_id' => $team->id]);

        // Seed Team Counters (using the new generic table structure)
        $counters = [
            'invoice' => ['prefix' => 'INV-', 'start' => 1000],
            'receipt' => ['prefix' => 'RCP-', 'start' => 5000],
            'reservation' => ['prefix' => 'RES-', 'start' => 2000],
            'folio' => ['prefix' => 'FOL-', 'start' => 8000],
            'promissory' => ['prefix' => 'PRM-', 'start' => 100],
        ];

        foreach ($counters as $key => $config) {
            // Seed for fandaqah-palace
            DB::table('team_counters')->updateOrInsert(
                ['team_id' => $team->id, 'key' => $key, 'prefix' => $config['prefix']],
                [
                    'value' => $config['start'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // Seed for demo-hotel
            DB::table('team_counters')->updateOrInsert(
                ['team_id' => $demoTeam->id, 'key' => $key, 'prefix' => $config['prefix']],
                [
                    'value' => $config['start'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Seed Team Contact Persons
        DB::table('team_contact_persons')->updateOrInsert(
            ['team_id' => $team->id, 'email' => 'gm@fandaqah-palace.com'],
            [
                'name_en' => 'Ahmed Al-Shehri',
                'name_ar' => 'أحمد الشهري',
                'job_title_en' => 'General Manager',
                'job_title_ar' => 'المدير العام',
                'phone' => '+966555666777',
                'is_primary' => true,
                'created_at' => now(),
            ]
        );

        DB::table('team_contact_persons')->updateOrInsert(
            ['team_id' => $demoTeam->id, 'email' => 'gm@demo-hotel.com'],
            [
                'name_en' => 'Ahmed Al-Shehri',
                'name_ar' => 'أحمد الشهري',
                'job_title_en' => 'General Manager',
                'job_title_ar' => 'المدير العام',
                'phone' => '+966555666777',
                'is_primary' => true,
                'created_at' => now(),
            ]
        );
    }
}
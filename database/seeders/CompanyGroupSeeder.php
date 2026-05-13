<?php

namespace Database\Seeders;

use App\Models\CompanyGroup;
use App\Models\CompanyProfile;
use App\Models\Promissory;
use App\Team;
use App\User;
use Illuminate\Database\Seeder;

class CompanyGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $team = Team::first();
        if (!$team) return;

        // Create Groups
        $g1 = CompanyGroup::create([
            'team_id' => $team->id,
            'name' => 'Aramco Group',
            'name_ar' => 'مجموعة أرامكو',
            'credit_limit' => 500000,
            'payment_terms_days' => 45,
        ]);

        $g2 = CompanyGroup::create([
            'team_id' => $team->id,
            'name' => 'SABIC Consolidated',
            'name_ar' => 'سابك الموحدة',
            'credit_limit' => 300000,
            'payment_terms_days' => 30,
        ]);

        // Link existing companies or create new ones
        $companies = [
            ['name' => 'Aramco Riyadh', 'group_id' => $g1->id, 'limit' => 100000],
            ['name' => 'Aramco Dhahran', 'group_id' => $g1->id, 'limit' => 200000],
            ['name' => 'SABIC Marketing', 'group_id' => $g2->id, 'limit' => 150000],
        ];

        foreach ($companies as $c) {
            $profile = CompanyProfile::create([
                'company_name' => $c['name'],
                'company_group_id' => $c['group_id'],
                'credit_limit' => $c['limit'],
                'status' => 'active',
            ]);

            // Add some aging debt
            Promissory::create([
                'team_id' => $team->id,
                'company_id' => $profile->id,
                'reservation_id' => 1,
                'user_id' => 1,
                'total_amount' => 5000,
                'collected_amount' => 0,
                'status' => 'pending',
                'created_at' => now()->subDays(40), // 31-60 bucket
            ]);

            Promissory::create([
                'team_id' => $team->id,
                'company_id' => $profile->id,
                'reservation_id' => 1,
                'user_id' => 1,
                'total_amount' => 2000,
                'collected_amount' => 0,
                'status' => 'pending',
                'created_at' => now()->subDays(100), // 90+ bucket
            ]);
        }
    }
}

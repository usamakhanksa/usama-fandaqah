<?php

namespace Database\Seeders;

use App\Models\ClientProfile;
use App\Models\ClientActivity;
use App\Models\ClientMembership;
use App\Models\ClientSale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClientRelationsSeeder extends Seeder {
    public function run(): void {
        $saudiCities = ['Riyadh', 'Jeddah', 'Mecca', 'Medina', 'Dammam', 'Khobar'];
        
        $clients = [
            [
                'first_name' => 'Usama',
                'last_name' => 'Khan',
                'email' => 'usama@fandaqah.com',
                'phone' => '+966500000001',
                'national_id' => '1000000001',
                'type' => 'investor',
                'city' => 'Riyadh',
                'address' => 'Olaya District, Riyadh',
            ],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Al-Saud',
                'email' => 'ahmed.saud@example.sa',
                'phone' => '+966500000002',
                'national_id' => '1000000002',
                'type' => 'buyer',
                'city' => 'Jeddah',
                'address' => 'Al-Shati, Jeddah',
            ],
            [
                'first_name' => 'Sara',
                'last_name' => 'Al-Fassi',
                'email' => 'sara.fassi@example.sa',
                'phone' => '+966500000003',
                'national_id' => '1000000003',
                'type' => 'tenant',
                'city' => 'Mecca',
                'address' => 'Aziziyah, Mecca',
            ],
        ];

        foreach ($clients as $clientData) {
            $profile = ClientProfile::create($clientData);

            // Membership
            ClientMembership::create([
                'client_profile_id' => $profile->id,
                'tier' => $profile->type === 'investor' ? 'platinum' : 'gold',
                'points' => rand(100, 5000),
                'joined_at' => Carbon::now()->subMonths(rand(1, 12)),
                'expires_at' => Carbon::now()->addYear(),
            ]);

            // Activities
            for ($i = 0; $i < 3; $i++) {
                ClientActivity::create([
                    'client_profile_id' => $profile->id,
                    'type' => ['call', 'meeting', 'email', 'viewing'][rand(0, 3)],
                    'subject' => 'Follow up on ' . ($profile->type === 'investor' ? 'ROI report' : 'property viewing'),
                    'description' => 'Discussed details and next steps.',
                    'scheduled_at' => Carbon::now()->addDays(rand(-10, 10)),
                    'status' => ['pending', 'completed'][rand(0, 1)],
                ]);
            }

            // Sales
            if ($profile->type !== 'tenant') {
                ClientSale::create([
                    'client_profile_id' => $profile->id,
                    'property_reference' => 'FAN-' . rand(1000, 9999),
                    'amount' => rand(500000, 5000000),
                    'status' => ['pending', 'closed', 'cancelled'][rand(0, 2)],
                    'closed_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class CustomerSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $customers = [
            [
                'name' => 'Saeed Al-Ghamdi',
                'email' => 'saeed@example.com',
                'phone' => '+966501112223',
                'id_number' => '1000111222',
                'customer_type' => 1, // Individual
                'country_id' => 1, // Saudi
            ],
            [
                'name' => 'Mohammed Al-Otaibi',
                'email' => 'otaibi@example.com',
                'phone' => '+966503334445',
                'id_number' => '1000333444',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+14155551234',
                'id_number' => 'P1234567',
                'customer_type' => 1,
                'country_id' => 2, // USA or other
            ],
            [
                'name' => 'Aramco Guest',
                'email' => 'corporate@aramco.com',
                'phone' => '+966138720111',
                'customer_type' => 2, // Corporate
                'country_id' => 1,
            ],
            [
                'name' => 'Fahad Al-Rashid',
                'email' => 'fahad@example.com',
                'phone' => '+966505556667',
                'id_number' => '1000555666',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Sultan Al-Harbi',
                'email' => 'sultan@example.com',
                'phone' => '+966507778889',
                'id_number' => '1000777888',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Khalid Al-Dosari',
                'email' => 'khalid@example.com',
                'phone' => '+966509990001',
                'id_number' => '1000999000',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Ahmed Mansour',
                'email' => 'ahmed@example.com',
                'phone' => '+966501234567',
                'id_number' => '1000123456',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Youssef Khalil',
                'email' => 'youssef@example.com',
                'phone' => '+966507654321',
                'id_number' => '1000765432',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Omar Hassan',
                'email' => 'omar@example.com',
                'phone' => '+9665011122233',
                'id_number' => '1000111223',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Zaid Ibrahim',
                'email' => 'zaid@example.com',
                'phone' => '+9665033344455',
                'id_number' => '1000333445',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Layla Ahmed',
                'email' => 'layla@example.com',
                'phone' => '+9665055566677',
                'id_number' => '1000555667',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Fatima Ali',
                'email' => 'fatima@example.com',
                'phone' => '+9665077788899',
                'id_number' => '1000777890',
                'customer_type' => 1,
                'country_id' => 1,
            ],
            [
                'name' => 'Sarah Smith',
                'email' => 'sarah@example.com',
                'phone' => '+447712345678',
                'id_number' => 'E1234567',
                'customer_type' => 1,
                'country_id' => 3, // UK
            ],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->updateOrInsert(
                ['email' => $customer['email'], 'team_id' => $team->id],
                array_merge($customer, ['team_id' => $team->id, 'created_at' => now()])
            );
        }

        // Generate more random customers to ensure we have at least 30
        for ($i = 1; $i <= 20; $i++) {
            $name = "Guest " . $i;
            $email = "guest" . $i . "@example.com";
            DB::table('customers')->updateOrInsert(
                ['email' => $email, 'team_id' => $team->id],
                [
                    'team_id' => $team->id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => '+96650' . str_pad($i, 7, '0', STR_PAD_LEFT),
                    'id_number' => '1000' . str_pad($i, 6, '0', STR_PAD_LEFT),
                    'customer_type' => 1,
                    'country_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
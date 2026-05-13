<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Company;
use App\Models\CompanyGroup;
use App\Country;

class CompanyAndGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the demo team
        $team = Team::where('slug', 'fandaqah-palace')->first();
        
        if (!$team) {
            $this->command->error('Demo team not found. Please run TeamSeeder first.');
            return;
        }

        // Get countries
        $saudiArabia = Country::where('code', 'SA')->first();
        $egypt = Country::where('code', 'EG')->first();
        $uae = Country::where('code', 'AE')->first();

        if (!$saudiArabia || !$egypt || !$uae) {
            $this->command->error('Countries not found. Please run CountryCitySeeder first.');
            return;
        }

        // Create company groups
        $groups = [
            [
                'team_id' => $team->id,
                'name' => 'Premium Corporate Group',
                'description' => json_encode([
                    'en' => 'Premium corporate clients with special rates and benefits',
                    'ar' => 'مجموعة عملاء تجارية متميزة بمعدلات ومزايا خاصة'
                ]),
                'discount_rate' => 15.00,
                'credit_limit' => 50000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => 'Government Agencies',
                'description' => json_encode([
                    'en' => 'Government agencies with special billing arrangements',
                    'ar' => 'الجهات الحكومية مع ترتيبات فوترة خاصة'
                ]),
                'discount_rate' => 10.00,
                'credit_limit' => 100000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'name' => 'Tour Operators',
                'description' => json_encode([
                    'en' => 'Tour operators with volume discounts',
                    'ar' => 'مشغلي الجولات مع خصومات بالحجم'
                ]),
                'discount_rate' => 20.00,
                'credit_limit' => 75000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $groupIds = [];
        foreach ($groups as $group) {
            $groupObj = CompanyGroup::firstOrCreate(
                [
                    'name' => $group['name'],
                    'team_id' => $group['team_id']
                ],
                $group
            );
            $groupIds[] = $groupObj->id;
        }

        // Create companies
        $companies = [
            [
                'team_id' => $team->id,
                'entity_type' => 'corporate',
                'name' => 'Saudi Arabian Airlines',
                'phone' => '+966112345678',
                'email' => 'reservations@saudiairlines.com',
                'city' => 'Riyadh',
                'address' => 'King Fahd Road, PO Box 1234',
                'tax_number' => '1234567891',
                'postal_code' => '11564',
                'district' => 'Olaya',
                'building_number' => '123',
                'street_name' => 'King Fahd Road',
                'country_id' => $saudiArabia->id,
                'company_group_id' => $groupIds[0],
                'payment_terms_days' => 30,
                'credit_limit' => 25000.00,
                'currency' => 'SAR',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'entity_type' => 'corporate',
                'name' => 'National Guard Health Affairs',
                'phone' => '+966114567890',
                'email' => 'procurement@NGHA.org',
                'city' => 'Riyadh',
                'address' => 'King Abdullah Medical City, PO Box 5678',
                'tax_number' => '2345678902',
                'postal_code' => '11426',
                'district' => 'Alsudari',
                'building_number' => '456',
                'street_name' => 'Prince Majid Bin Abdul Aziz',
                'country_id' => $saudiArabia->id,
                'company_group_id' => $groupIds[1],
                'payment_terms_days' => 45,
                'credit_limit' => 40000.00,
                'currency' => 'SAR',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'entity_type' => 'corporate',
                'name' => 'Saudi Tourism Authority',
                'phone' => '+966117890123',
                'email' => 'partnerships@tourism.gov.sa',
                'city' => 'Riyadh',
                'address' => 'King Abdulaziz Branch Rd, PO Box 9012',
                'tax_number' => '3456789013',
                'postal_code' => '11564',
                'district' => 'Olaya',
                'building_number' => '789',
                'street_name' => 'King Abdulaziz Branch Road',
                'country_id' => $saudiArabia->id,
                'company_group_id' => $groupIds[1],
                'payment_terms_days' => 30,
                'credit_limit' => 30000.00,
                'currency' => 'SAR',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'entity_type' => 'tour_operator',
                'name' => 'Global Tour Operators',
                'phone' => '+20234567890',
                'email' => 'bookings@globaltours.com',
                'city' => 'Cairo',
                'address' => 'Pyramids Road, Downtown, PO Box 1011',
                'tax_number' => '4567890124',
                'postal_code' => '12345',
                'district' => 'Downtown',
                'building_number' => '321',
                'street_name' => 'Pyramids Road',
                'country_id' => $egypt->id,
                'company_group_id' => $groupIds[2],
                'payment_terms_days' => 21,
                'credit_limit' => 15000.00,
                'currency' => 'USD',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'entity_type' => 'corporate',
                'name' => 'Emirates Group',
                'phone' => '+97141234567',
                'email' => 'hotels@emirates.com',
                'city' => 'Dubai',
                'address' => 'Garhoud, PO Box 12200',
                'tax_number' => '5678901235',
                'postal_code' => '123456',
                'district' => 'Garhoud',
                'building_number' => '555',
                'street_name' => 'Airport Road',
                'country_id' => $uae->id,
                'company_group_id' => $groupIds[0],
                'payment_terms_days' => 30,
                'credit_limit' => 35000.00,
                'currency' => 'AED',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'entity_type' => 'corporate',
                'name' => 'Almarai Company',
                'phone' => '+966138131313',
                'email' => 'travel@almarai.com',
                'city' => 'Dammam',
                'address' => 'King Fahd Highway, Industrial Area 2, PO Box 2046',
                'tax_number' => '6789012346',
                'postal_code' => '31411',
                'district' => 'Industrial Area 2',
                'building_number' => '888',
                'street_name' => 'King Fahd Highway',
                'country_id' => $saudiArabia->id,
                'company_group_id' => $groupIds[0],
                'payment_terms_days' => 30,
                'credit_limit' => 20000.00,
                'currency' => 'SAR',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $team->id,
                'entity_type' => 'tour_operator',
                'name' => 'Middle East Travel Solutions',
                'phone' => '+966119876543',
                'email' => 'groups@metravel.com',
                'city' => 'Riyadh',
                'address' => 'Olaya Street, Business District, PO Box 1516',
                'tax_number' => '7890123457',
                'postal_code' => '11564',
                'district' => 'Olaya',
                'building_number' => '999',
                'street_name' => 'Olaya Street',
                'country_id' => $saudiArabia->id,
                'company_group_id' => $groupIds[2],
                'payment_terms_days' => 14,
                'credit_limit' => 18000.00,
                'currency' => 'SAR',
                'is_demo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($companies as $company) {
            Company::firstOrCreate(
                [
                    'name' => $company['name'],
                    'team_id' => $company['team_id']
                ],
                $company
            );
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryCitySeeder extends Seeder
{
    public function run()
    {
        // Countries
        $countries = [
            ['name' => 'Saudi Arabia', 'title' => json_encode(['en' => 'Saudi Arabia', 'ar' => 'المملكة العربية السعودية']), 'code' => 'SA', 'phone_code' => '966', 'iso2' => 'SA'],
            ['name' => 'United Arab Emirates', 'title' => json_encode(['en' => 'United Arab Emirates', 'ar' => 'الإمارات العربية المتحدة']), 'code' => 'AE', 'phone_code' => '971', 'iso2' => 'AE'],
            ['name' => 'Kuwait', 'title' => json_encode(['en' => 'Kuwait', 'ar' => 'الكويت']), 'code' => 'KW', 'phone_code' => '965', 'iso2' => 'KW'],
            ['name' => 'Egypt', 'title' => json_encode(['en' => 'Egypt', 'ar' => 'مصر']), 'code' => 'EG', 'phone_code' => '20', 'iso2' => 'EG'],
        ];

        foreach ($countries as $country) {
            DB::table('countries')->updateOrInsert(['iso2' => $country['iso2']], $country);
        }

        // Cities
        $cities = [
            ['name' => 'Riyadh', 'title' => json_encode(['en' => 'Riyadh', 'ar' => 'الرياض']), 'country_id' => 1],
            ['name' => 'Jeddah', 'title' => json_encode(['en' => 'Jeddah', 'ar' => 'جدة']), 'country_id' => 1],
            ['name' => 'Makkah', 'title' => json_encode(['en' => 'Makkah', 'ar' => 'مكة المكرمة']), 'country_id' => 1],
        ];

        foreach ($cities as $city) {
            DB::table('cities')->updateOrInsert(['name' => $city['name']], $city);
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\UnitCategory;
use Illuminate\Support\Facades\DB;

class UnitCategorySeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $categories = [
            [
                'name_en' => 'Standard Single',
                'name_ar' => 'غرفة مفردة قياسية',
                'description_en' => 'Comfortable room for solo travelers',
                'description_ar' => 'غرفة مريحة للمسافرين المنفردين',
                'number_of_adults' => 1,
                'number_of_children' => 0,
                'sunday_day_price' => 350,
                'monday_day_price' => 350,
                'tuesday_day_price' => 350,
                'wednesday_day_price' => 350,
                'thursday_day_price' => 450,
                'friday_day_price' => 500,
                'saturday_day_price' => 450,
                'hour_price' => 50,
                'month_price' => 8500,
                'unit_size' => 28,
                'number_of_beds' => 1,
            ],
            [
                'name_en' => 'Deluxe Double',
                'name_ar' => 'غرفة مزدوجة ديلوكس',
                'description_en' => 'Spacious room with king-size bed',
                'description_ar' => 'غرفة واسعة بسرير كينج',
                'number_of_adults' => 2,
                'number_of_children' => 1,
                'sunday_day_price' => 550,
                'monday_day_price' => 550,
                'tuesday_day_price' => 550,
                'wednesday_day_price' => 550,
                'thursday_day_price' => 650,
                'friday_day_price' => 750,
                'saturday_day_price' => 650,
                'hour_price' => 75,
                'month_price' => 12000,
                'unit_size' => 42,
                'number_of_beds' => 1,
            ],
            [
                'name_en' => 'Executive Suite',
                'name_ar' => 'جناح تنفيذي',
                'description_en' => 'Luxury suite with separate lounge',
                'description_ar' => 'جناح فاخر مع صالة منفصلة',
                'number_of_adults' => 2,
                'number_of_children' => 2,
                'sunday_day_price' => 1200,
                'monday_day_price' => 1200,
                'tuesday_day_price' => 1200,
                'wednesday_day_price' => 1200,
                'thursday_day_price' => 1500,
                'friday_day_price' => 1800,
                'saturday_day_price' => 1500,
                'hour_price' => 200,
                'month_price' => 25000,
                'unit_size' => 85,
                'number_of_beds' => 2,
            ],
        ];

        foreach ($categories as $cat) {
            $exists = UnitCategory::where('team_id', $team->id)
                ->where('name', 'LIKE', '%"en":"' . $cat['name_en'] . '"%')
                ->first();

            $data = [
                'name' => ['en' => $cat['name_en'], 'ar' => $cat['name_ar']],
                'description' => ['en' => $cat['description_en'], 'ar' => $cat['description_ar']],
                'number_of_adults' => $cat['number_of_adults'],
                'number_of_children' => $cat['number_of_children'],
                'sunday_day_price' => $cat['sunday_day_price'],
                'monday_day_price' => $cat['monday_day_price'],
                'tuesday_day_price' => $cat['tuesday_day_price'],
                'wednesday_day_price' => $cat['wednesday_day_price'],
                'thursday_day_price' => $cat['thursday_day_price'],
                'friday_day_price' => $cat['friday_day_price'],
                'saturday_day_price' => $cat['saturday_day_price'],
                'hour_price' => $cat['hour_price'],
                'month_price' => $cat['month_price'],
                'unit_size' => $cat['unit_size'],
                'number_of_beds' => $cat['number_of_beds'],
                'team_id' => $team->id,
            ];

            if ($exists) {
                $exists->update($data);
            } else {
                UnitCategory::create($data);
            }
        }
    }
}
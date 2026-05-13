<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class ServiceCategorySeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $categories = [
            ['en' => 'Restaurant', 'ar' => 'مطعم'],
            ['en' => 'Room Service', 'ar' => 'خدمة الغرف'],
            ['en' => 'Laundry', 'ar' => 'مغسلة'],
            ['en' => 'Transportation', 'ar' => 'نقل'],
            ['en' => 'Minibar', 'ar' => 'ميني بار'],
            ['en' => 'Miscellaneous', 'ar' => 'متفرقات'],
        ];

        foreach ($categories as $cat) {
            $jsonName = json_encode(['en' => $cat['en'], 'ar' => $cat['ar']]);
            
            $exists = DB::table('service_categories')
                ->where('team_id', $team->id)
                ->where('name', 'LIKE', '%"en":"' . $cat['en'] . '"%')
                ->exists();

            if (!$exists) {
                DB::table('service_categories')->insert([
                    'team_id' => $team->id,
                    'name' => $jsonName,
                    'status' => 1,
                    'show_in_reservation' => 1,
                    'show_in_pos' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
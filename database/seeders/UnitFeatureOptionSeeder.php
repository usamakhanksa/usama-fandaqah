<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class UnitFeatureOptionSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $features = [
            ['en' => 'WiFi', 'ar' => 'واي فاي'],
            ['en' => 'Smart TV', 'ar' => 'تلفزيون ذكي'],
            ['en' => 'Mini Bar', 'ar' => 'ميني بار'],
            ['en' => 'Sea View', 'ar' => 'إطلالة بحرية'],
            ['en' => 'City View', 'ar' => 'إطلالة على المدينة'],
        ];

        foreach ($features as $feature) {
            $jsonName = json_encode(['en' => $feature['en'], 'ar' => $feature['ar']]);
            
            $exists = DB::table('unit_options')
                ->where('team_id', $team->id)
                ->where('name', 'LIKE', '%"en":"' . $feature['en'] . '"%')
                ->exists();

            if (!$exists) {
                DB::table('unit_options')->insert([
                    'team_id' => $team->id,
                    'name' => $jsonName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
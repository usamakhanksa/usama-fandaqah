<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class SourceChannelSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $sources = [
            ['en' => 'Walk-in', 'ar' => 'حجز مباشر'],
            ['en' => 'Phone', 'ar' => 'هاتف'],
            ['en' => 'Hotel Website', 'ar' => 'موقع الفندق'],
            ['en' => 'Booking.com', 'ar' => 'بوكينج'],
            ['en' => 'Expedia', 'ar' => 'اكسبيديا'],
            ['en' => 'Corporate', 'ar' => 'شركات'],
            ['en' => 'Travel Agent', 'ar' => 'وكيل سفر'],
            ['en' => 'STA AH', 'ar' => 'منصة ستا'],
        ];

        foreach ($sources as $source) {
            $jsonName = json_encode(['en' => $source['en'], 'ar' => $source['ar']]);
            
            $exists = DB::table('sources')
                ->where('team_id', $team->id)
                ->where('name', 'LIKE', '%"en":"' . $source['en'] . '"%')
                ->exists();

            if (!$exists) {
                DB::table('sources')->insert([
                    'team_id' => $team->id,
                    'name' => $jsonName,
                    'status' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
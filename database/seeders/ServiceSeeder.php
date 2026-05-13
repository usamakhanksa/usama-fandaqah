<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class ServiceSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $services = [
            ['name_en' => 'Buffet Breakfast', 'name_ar' => 'فطور بوفيه', 'price' => 50, 'category' => 'Restaurant'],
            ['name_en' => 'Club Sandwich', 'name_ar' => 'كلوب ساندوتش', 'price' => 35, 'category' => 'Room Service'],
            ['name_en' => 'Full Suit Laundry', 'name_ar' => 'غسيل بدلة كاملة', 'price' => 45, 'category' => 'Laundry'],
            ['name_en' => 'Airport Pickup', 'name_ar' => 'توصيل من المطار', 'price' => 150, 'category' => 'Transportation'],
            ['name_en' => 'Extra Bed', 'name_ar' => 'سرير إضافي', 'price' => 100, 'category' => 'Miscellaneous'],
            ['name_en' => 'Late Checkout Fee', 'name_ar' => 'رسوم مغادرة متأخرة', 'price' => 150, 'category' => 'Miscellaneous'],
        ];

        foreach ($services as $service) {
            $catId = DB::table('service_categories')
                ->where('team_id', $team->id)
                ->whereRaw("JSON_EXTRACT(name, '$.en') = ?", [$service['category']])
                ->value('id');

            DB::table('services')->updateOrInsert(
                ['name_en' => $service['name_en'], 'team_id' => $team->id],
                [
                    'team_id' => $team->id,
                    'service_category_id' => $catId,
                    'name_en' => $service['name_en'],
                    'name_ar' => $service['name_ar'],
                    'price' => $service['price'],
                    'is_active' => true,
                    'created_at' => now(),
                ]
            );
        }
    }
}
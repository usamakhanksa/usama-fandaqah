<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use Illuminate\Support\Str;

class WebsiteSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        // Website Settings
        $settings = [
            ['key' => 'primary_color', 'value' => '#b8860b', 'type' => 'string'],
            ['key' => 'secondary_color', 'value' => '#000000', 'type' => 'string'],
            ['key' => 'hero_title_en', 'value' => 'Experience Luxury at Fandaqah Palace', 'type' => 'string'],
            ['key' => 'hero_title_ar', 'value' => 'استمتع بالفخامة في قصر فندقة', 'type' => 'string'],
            ['key' => 'contact_phone', 'value' => '+966112223344', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            DB::table('website_settings')->updateOrInsert(
                ['team_id' => $team->id, 'key' => $setting['key']],
                array_merge($setting, ['team_id' => $team->id, 'created_at' => now()])
            );
        }

        // Website Pages
        $pages = [
            [
                'title_en' => 'About Us',
                'title_ar' => 'من نحن',
                'slug' => 'about-us',
                'content_en' => 'Fandaqah Palace offers world-class luxury in the heart of Riyadh.',
                'content_ar' => 'يقدم قصر فندقة فخامة عالمية المستوى في قلب الرياض.',
            ],
            [
                'title_en' => 'Privacy Policy',
                'title_ar' => 'سياسة الخصوصية',
                'slug' => 'privacy-policy',
                'content_en' => 'Your privacy is important to us.',
                'content_ar' => 'خصوصيتك تهمنا.',
            ],
        ];

        foreach ($pages as $page) {
            DB::table('website_pages')->updateOrInsert(
                ['team_id' => $team->id, 'slug' => $page['slug']],
                array_merge($page, ['team_id' => $team->id, 'is_published' => true, 'created_at' => now()])
            );
        }
    }
}
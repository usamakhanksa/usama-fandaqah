<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class OfferSpecialPricePromoSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        // Offers
        $offers = [
            [
                'name' => 'Summer Escape',
                'name_ar' => 'هروب الصيف',
                'discount_percent' => 15,
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
            ],
            [
                'name' => 'Early Bird',
                'name_ar' => 'الحجز المبكر',
                'discount_percent' => 10,
                'start_date' => now(),
                'end_date' => now()->addYear(),
            ],
        ];

        foreach ($offers as $offer) {
            DB::table('offers')->updateOrInsert(
                ['name' => $offer['name'], 'team_id' => $team->id],
                array_merge($offer, ['team_id' => $team->id, 'is_active' => true, 'created_at' => now()])
            );
        }

        // Promo Codes
        $promos = [
            ['code' => 'WELCOME20', 'discount_percent' => 20, 'is_active' => true],
            ['code' => 'RIYADH2026', 'discount_amount' => 100, 'is_active' => true],
        ];

        foreach ($promos as $promo) {
            DB::table('promo_codes')->updateOrInsert(
                ['code' => $promo['code'], 'team_id' => $team->id],
                array_merge($promo, ['team_id' => $team->id, 'created_at' => now()])
            );
        }
    }
}
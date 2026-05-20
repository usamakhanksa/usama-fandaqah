<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;
use App\User;

class OfferSpecialPricePromoSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        $user = User::where('email', 'admin@fandaqah.com')->first() ?: User::first();
        $userId = $user ? $user->id : 1;

        // Offers
        $offers = [
            [
                'name' => 'Summer Escape',
                'name_ar' => 'هروب الصيف',
                'offer_type' => 'percentage_discount',
                'discount_value' => 15.00,
                'discount_percentage' => 15.00,
                'valid_from' => now()->toDateString(),
                'valid_to' => now()->addMonths(3)->toDateString(),
            ],
            [
                'name' => 'Early Bird',
                'name_ar' => 'الحجز المبكر',
                'offer_type' => 'percentage_discount',
                'discount_value' => 10.00,
                'discount_percentage' => 10.00,
                'valid_from' => now()->toDateString(),
                'valid_to' => now()->addYear()->toDateString(),
            ],
        ];

        foreach ($offers as $offer) {
            DB::table('offers')->updateOrInsert(
                ['name' => $offer['name'], 'team_id' => $team->id],
                array_merge($offer, [
                    'team_id' => $team->id,
                    'is_active' => true,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        // Promo Codes
        $promos = [
            [
                'code' => 'WELCOME20',
                'name' => 'Welcome Discount',
                'name_ar' => 'خصم الترحيب',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'valid_from' => now()->toDateString(),
                'valid_to' => now()->addYear()->toDateString(),
                'is_active' => true,
            ],
            [
                'code' => 'RIYADH2026',
                'name' => 'Riyadh Season 2026',
                'name_ar' => 'موسم الرياض ٢٠٢٦',
                'discount_type' => 'fixed_amount',
                'discount_value' => 100.00,
                'valid_from' => now()->toDateString(),
                'valid_to' => now()->addYear()->toDateString(),
                'is_active' => true,
            ],
        ];

        foreach ($promos as $promo) {
            DB::table('promo_codes')->updateOrInsert(
                ['code' => $promo['code'], 'team_id' => $team->id],
                array_merge($promo, [
                    'team_id' => $team->id,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Team;

class PaymentMethodBankSenderSeeder extends Seeder
{
    public function run()
    {
        $team = Team::where('slug', 'fandaqah-palace')->first();
        if (!$team) return;

        // Payment Methods
        $methods = [
            ['name_en' => 'Cash', 'name_ar' => 'نقدي'],
            ['name_en' => 'Mada', 'name_ar' => 'مدى'],
            ['name_en' => 'Visa', 'name_ar' => 'فيزا'],
            ['name_en' => 'MasterCard', 'name_ar' => 'ماستركارد'],
            ['name_en' => 'Bank Transfer', 'name_ar' => 'تحويل بنكي'],
            ['name_en' => 'STC Pay', 'name_ar' => 'إس تي سي باي'],
        ];

        foreach ($methods as $method) {
            $jsonName = json_encode(['en' => $method['name_en'], 'ar' => $method['name_ar']]);
            
            $exists = DB::table('payment_methods')
                ->where('team_id', $team->id)
                ->where('name', 'LIKE', '%"en":"' . $method['name_en'] . '"%')
                ->exists();

            if (!$exists) {
                DB::table('payment_methods')->insert([
                    'team_id' => $team->id,
                    'name' => $jsonName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Banks
        $banks = [
            ['name_en' => 'Al Rajhi Bank', 'name_ar' => 'مصرف الراجحي'],
            ['name_en' => 'SNB (Al Ahli)', 'name_ar' => 'البنك الأهلي السعودي'],
            ['name_en' => 'Riyad Bank', 'name_ar' => 'بنك الرياض'],
        ];

        foreach ($banks as $bank) {
            $jsonName = json_encode(['en' => $bank['name_en'], 'ar' => $bank['name_ar']]);

            $exists = DB::table('banks')
                ->where('team_id', $team->id)
                ->where('name', 'LIKE', '%"en":"' . $bank['name_en'] . '"%')
                ->exists();

            if (!$exists) {
                DB::table('banks')->insert([
                    'team_id' => $team->id,
                    'name' => $jsonName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
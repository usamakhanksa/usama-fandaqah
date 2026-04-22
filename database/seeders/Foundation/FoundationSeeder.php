<?php
namespace Database\Seeders\Foundation;

use App\Models\Foundation\Customer;
use App\Models\Foundation\Reservation;
use App\Models\Foundation\Unit;
use App\Models\Foundation\UnitCategory;
use Illuminate\Database\Seeder;

class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $categories = UnitCategory::factory()->count(3)->create();
        Unit::factory()->count(12)->recycle($categories)->create();

        Customer::factory()->create([
            'first_name_en' => 'Ahmed',
            'last_name_en' => 'Al-Fahad',
            'phone' => '+966512345678',
            'city' => 'Riyadh',
        ]);

        Customer::factory()->create([
            'first_name_en' => 'Sara',
            'last_name_en' => 'Al-Qahtani',
            'phone' => '+966598765432',
            'city' => 'Jeddah',
        ]);

        Customer::factory()->count(8)->create();

        Reservation::factory()->count(20)->create();
    }
}

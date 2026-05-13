<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            ['id' => 1, 'name' => json_encode(['en' => 'Hotel', 'ar' => 'فندق'])],
            ['id' => 2, 'name' => json_encode(['en' => 'Furnished Apartments', 'ar' => 'شقق مفروشة'])],
            ['id' => 3, 'name' => json_encode(['en' => 'Resort', 'ar' => 'منتجع'])],
            ['id' => 4, 'name' => json_encode(['en' => 'Aparthotel', 'ar' => 'فندق شقق'])],
        ];

        foreach ($types as $type) {
            DB::table('hotel_types')->updateOrInsert(['id' => $type['id']], $type);
        }
    }
}
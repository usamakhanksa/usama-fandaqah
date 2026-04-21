<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \Illuminate\Support\Facades\DB::table('unit_statuses')->delete();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        \Illuminate\Support\Facades\DB::table('unit_statuses')->insert([
            ['id' => 1, 'name' => 'Available', 'slug' => 'available', 'color' => '#10b981'],
            ['id' => 2, 'name' => 'Occupied', 'slug' => 'occupied', 'color' => '#ef4444'],
            ['id' => 3, 'name' => 'Under Cleaning', 'slug' => 'under_cleaning', 'color' => '#f59e0b'],
            ['id' => 4, 'name' => 'Under Maintenance', 'slug' => 'under_maintenance', 'color' => '#6b7280'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

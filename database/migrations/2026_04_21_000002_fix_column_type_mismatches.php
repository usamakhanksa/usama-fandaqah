<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // Fix customers (formerly customer) team_id type
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                // First ensure data can be converted (sanitize)
                DB::statement("UPDATE customers SET team_id = '0' WHERE team_id = '' OR team_id IS NULL");
                $table->unsignedBigInteger('team_id')->change();
            });
        }

        // Standardize Financial columns to Decimal (instead of Double)
        if (Schema::hasTable('reservations')) {
            Schema::table('reservations', function (Blueprint $table) {
                $table->decimal('total_price', 14, 2)->default(0)->change();
                $table->decimal('sub_total', 14, 2)->default(0)->change();
                $table->decimal('vat_total', 14, 2)->default(0)->change();
                $table->decimal('ewa_total', 14, 2)->default(0)->change();
            });
        }

        // Fix users.id if it's still int(10) - Ensure it's BigInt for Filament/Spark compatibility
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->bigIncrements('id')->change();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Downward migration not recommended for structural type changes in this phase
    }
};

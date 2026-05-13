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
        Schema::table('team_counters', function (Blueprint $table) {
            $table->integer('start_from')->default(1)->after('prefix');
            $table->integer('current_value')->default(0)->after('start_from');
            $table->integer('padding')->default(5)->after('current_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_counters', function (Blueprint $table) {
            $table->dropColumn(['start_from', 'current_value', 'padding']);
        });
    }
};
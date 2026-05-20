<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('room_floors', function (Blueprint $table) {
            if (!Schema::hasColumn('room_floors', 'building_id')) {
                $table->foreignId('building_id')->nullable()->after('id')->constrained('buildings')->onDelete('cascade');
            }
            if (!Schema::hasColumn('room_floors', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('building_id')->constrained('teams')->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('room_floors', function (Blueprint $table) {
            $table->dropConstrainedForeignId('building_id');
            $table->dropConstrainedForeignId('team_id');
        });
    }
};

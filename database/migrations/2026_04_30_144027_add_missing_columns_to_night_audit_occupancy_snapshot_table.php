<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('night_audit_occupancy_snapshot', function (Blueprint $t) {
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'run_number')) {
                $t->tinyInteger('run_number')->default(1)->after('business_date');
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'is_final')) {
                $t->boolean('is_final')->default(true)->after('run_number');
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'rooms_available')) {
                $t->unsignedBigInteger('rooms_available')->after('total_rooms')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'rooms_occupied')) {
                $t->unsignedBigInteger('rooms_occupied')->after('rooms_available')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'rooms_cleaning')) {
                $t->unsignedBigInteger('rooms_cleaning')->after('rooms_occupied')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'rooms_maintenance')) {
                $t->unsignedBigInteger('rooms_maintenance')->after('rooms_cleaning')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'occupancy_pct')) {
                $t->decimal('occupancy_pct', 5, 2)->after('rooms_maintenance')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'arrivals_count')) {
                $t->unsignedBigInteger('arrivals_count')->after('revpar')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'departures_count')) {
                $t->unsignedBigInteger('departures_count')->after('arrivals_count')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'room_revenue')) {
                $t->decimal('room_revenue', 14, 2)->after('departures_count')->default(0);
            }
            if (!Schema::hasColumn('night_audit_occupancy_snapshot', 'total_revenue')) {
                $t->decimal('total_revenue', 14, 2)->after('room_revenue')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('night_audit_occupancy_snapshot', function (Blueprint $t) {
            $t->dropColumn(['run_number', 'is_final', 'rooms_available', 'rooms_occupied', 'rooms_cleaning', 'rooms_maintenance', 'occupancy_pct', 'arrivals_count', 'departures_count', 'room_revenue', 'total_revenue']);
        });
    }
};

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
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'number')) {
                $table->string('number')->nullable()->after('code');
            }
            if (!Schema::hasColumn('reservations', 'status')) {
                $table->string('status', 20)->default('confirmed')->after('reservation_status_id');
            }
            if (!Schema::hasColumn('reservations', 'total_price')) {
                $table->decimal('total_price', 12, 2)->default(0)->after('status');
            }
            if (!Schema::hasColumn('reservations', 'date_in')) {
                $table->dateTime('date_in')->nullable()->after('check_out');
            }
            if (!Schema::hasColumn('reservations', 'date_out')) {
                $table->dateTime('date_out')->nullable()->after('date_in');
            }
            if (!Schema::hasColumn('reservations', 'adults')) {
                $table->integer('adults')->default(1)->after('date_out');
            }
            if (!Schema::hasColumn('reservations', 'children')) {
                $table->integer('children')->default(0)->after('adults');
            }
            if (!Schema::hasColumn('reservations', 'cancellation_reason')) {
                $table->text('cancellation_reason')->nullable()->after('children');
            }
            if (!Schema::hasColumn('reservations', 'is_no_show_charged')) {
                $table->boolean('is_no_show_charged')->default(false)->after('cancellation_reason');
            }
            if (!Schema::hasColumn('reservations', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('total_price')->constrained('teams')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['status', 'total_price', 'team_id']);
        });
    }
};

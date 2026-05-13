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
            // Financial fields for reports
            if (!Schema::hasColumn('reservations', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->default(0)->after('check_out');
            }
            if (!Schema::hasColumn('reservations', 'room_revenue')) {
                $table->decimal('room_revenue', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('reservations', 'no_show_charge')) {
                $table->decimal('no_show_charge', 12, 2)->default(0)->after('room_revenue');
            }
            if (!Schema::hasColumn('reservations', 'refund_amount')) {
                $table->decimal('refund_amount', 12, 2)->default(0)->after('no_show_charge');
            }

            // Cancellation timestamp field
            if (!Schema::hasColumn('reservations', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            }

            // No-show flag already exists, but ensure it's boolean
            if (Schema::hasColumn('reservations', 'noshow_flag')) {
                $table->boolean('noshow_flag')->default(false)->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn([
                'total_amount',
                'room_revenue',
                'no_show_charge',
                'refund_amount',
                'cancelled_at',
            ]);
        });
    }
};

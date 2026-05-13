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
            if (!Schema::hasColumn('reservations', 'group_reservation_id')) {
                $table->unsignedBigInteger('group_reservation_id')->nullable()->index()->after('id');
                $table->foreign('group_reservation_id')->references('id')->on('group_reservations')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            if (Schema::hasColumn('reservations', 'group_reservation_id')) {
                $table->dropForeign(['group_reservation_id']);
                $table->dropColumn('group_reservation_id');
            }
        });
    }
};

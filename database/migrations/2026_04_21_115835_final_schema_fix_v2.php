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
        // Fix leads table
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'first_name')) {
                $table->string('first_name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('leads', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
        });

        // Fix reservations table
        Schema::table('reservations', function (Blueprint $table) {
            if (!Schema::hasColumn('reservations', 'status')) {
                $table->string('status', 50)->nullable()->after('reservation_status_id');
            }
            if (!Schema::hasColumn('reservations', 'total_price')) {
                $table->decimal('total_price', 12, 2)->default(0)->after('check_out');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};

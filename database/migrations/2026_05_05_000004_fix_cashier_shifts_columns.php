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
        Schema::table('cashier_shifts', function (Blueprint $table) {
            // Add missing columns that the seeder expects
            if (!Schema::hasColumn('cashier_shifts', 'total_payments')) {
                $table->decimal('total_payments', 12, 2)->nullable()->after('closing_balance');
            }
            if (!Schema::hasColumn('cashier_shifts', 'total_refunds')) {
                $table->decimal('total_refunds', 12, 2)->nullable()->after('total_payments');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('cashier_shifts', 'total_payments')) {
                $table->dropColumn('total_payments');
            }
            if (Schema::hasColumn('cashier_shifts', 'total_refunds')) {
                $table->dropColumn('total_refunds');
            }
        });
    }
};
